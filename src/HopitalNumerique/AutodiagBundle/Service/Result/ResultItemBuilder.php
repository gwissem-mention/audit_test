<?php
namespace HopitalNumerique\AutodiagBundle\Service\Result;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container;
use HopitalNumerique\AutodiagBundle\Entity\AutodiagEntry;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\AutodiagBundle\Model\Result\Item;
use HopitalNumerique\AutodiagBundle\Model\Result\ItemAttribute;
use HopitalNumerique\AutodiagBundle\Repository\Autodiag\Attribute\WeightRepository;
use HopitalNumerique\AutodiagBundle\Service\Attribute\AttributeBuilderProvider;
use HopitalNumerique\AutodiagBundle\Service\Attribute\PresetableAttributeBuilderInterface;
use HopitalNumerique\AutodiagBundle\Service\Synthesis\Completion;

class ResultItemBuilder
{
    /**
     * @var Completion
     */
    protected $completion;

    /**
     * @var AttributeBuilderProvider
     */
    protected $attributeBuilder;

    /**
     * @var WeightRepository
     */
    protected $weightRepository;

    public function __construct(Completion $completion, AttributeBuilderProvider $attributeBuilder, WeightRepository $weightRepository)
    {
        $this->completion = $completion;
        $this->attributeBuilder = $attributeBuilder;
        $this->weightRepository = $weightRepository;
    }

    public function build(Container $container, Synthesis $synthesis)
    {
        $resultItem = new Item();
        $weights = $this->weightRepository->getWeightByContainerIndexedByAttributeId($container);

        $resultItem->setLabel($container->getLabel());

        /* @TODO Supprimer "getTotalNumberOfAttributes" et remplacer par le service Completion */
        $resultItem->setNumberOfQuestions($container->getTotalNumberOfAttributes());
        $resultItem->setNumberOfAnswers($this->completion->getAnswersCount($synthesis, $container));

        $colorationInversed = 0;
        foreach ($container->getAttributes() as $attribute) {

            $itemAttribute = new ItemAttribute($attribute->getLabel());
            $itemAttribute->setColorationInversed($attribute->isColorationInversed());
            $itemAttribute->setAttributeId($attribute->getId());
            if (isset($weights[$attribute->getId()])) {
                $itemAttribute->setWeight($weights[$attribute->getId()]);
            }
            $resultItem->addAttribute($itemAttribute);

            $colorationInversed += $attribute->isColorationInversed() ? 1 : -1;

            $builder = $this->attributeBuilder->getBuilder($attribute->getType());

            if ($builder instanceof PresetableAttributeBuilderInterface) {
                $options = $builder->getPreset($synthesis->getAutodiag())->getPreset();
            } else {
                $attributeOptions = $attribute->getOptions();
                $options = [];
                foreach ($attributeOptions as $option) {
                    $options[$option->getValue()] = $option->getLabel();
                }
            }

            foreach ($synthesis->getEntries() as $entry) {
                /** @var AutodiagEntry $entry*/
                foreach ($entry->getValues() as $entryValue) {
                    if ($entryValue->getAttribute()->getId() === $attribute->getId()) {
                        $response = $builder->transform($entryValue->getValue());
                        if (is_array($response)) {

                            $responseValue = array_sum($response) / count($response);
                            foreach ($response as $code => &$value) {
                                if (null === $value) {
                                    $value = " - ";
                                } else {
                                    $value = isset($options[$code]) ? $options[$code][$value] : $value;
                                }
                            }
                            $response = implode(' - ', $response);
                        } else {
                            $responseValue = $response;
                            if (isset($options[$response])) {
                                $response = $options[$response];
                            } elseif (null === $response) {
                                $response = "-";
                            }
                        }

                        $itemAttribute->setResponse(
                            $responseValue,
                            $response
                        );
                    }
                }
            }
        }

        foreach ($container->getChilds() as $child) {
            $resultItem->addChildren(
                $this->build($child, $synthesis)
            );
        }

        return $resultItem;
    }
}
