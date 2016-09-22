<?php
namespace HopitalNumerique\AutodiagBundle\Service\Result;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\AutodiagBundle\Model\Result\Item;
use HopitalNumerique\AutodiagBundle\Model\Result\ItemAttribute;
use HopitalNumerique\AutodiagBundle\Repository\AutodiagEntry\ValueRepository;
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
     * @var ValueRepository
     */
    protected $valueRepository;

    protected $responses = null;

    public function __construct(Completion $completion, AttributeBuilderProvider $attributeBuilder, ValueRepository $valueRepository)
    {
        $this->completion = $completion;
        $this->attributeBuilder = $attributeBuilder;
        $this->valueRepository = $valueRepository;
    }

    public function build(Container $container, Synthesis $synthesis)
    {
        $resultItem = new Item();

        $resultItem->setLabel($container->getLabel());

        $resultItem->setNumberOfQuestions($this->completion->getAttributesCount($container));
        $resultItem->setNumberOfAnswers($this->completion->getAnswersCount($synthesis, $container));

        $colorationInversed = 0;
        foreach ($this->getResponses($synthesis, $container) as $attribute) {

            $colorationInversed += $attribute['colorationInversed'] ? 1 : -1;

            if ($synthesis->getEntries()->count() === 1) {
                $this->computeResultItemAttribute($resultItem, $synthesis, $attribute);
            }
        }
        $resultItem->setColorationInversed($colorationInversed > 0);

        foreach ($container->getChilds() as $child) {
            $resultItem->addChildren(
                $this->build($child, $synthesis)
            );
        }

        return $resultItem;
    }

    protected function getResponses(Synthesis $synthesis, Container $container)
    {
        if (null === $this->responses) {
            $this->responses = $this->valueRepository->getFullValuesByEntry(
                $synthesis->getAutodiag()->getId(),
                $synthesis->getEntries()->first()->getId()
            );
        }

        foreach ($this->responses as $response) {
            if (in_array($container->getId(), $response['container_id'])) {
                yield $response;
            }
        }
    }

    protected function computeResultItemAttribute(Item $item, Synthesis $synthesis, array $attribute)
    {
        $builder = $this->attributeBuilder->getBuilder($attribute['type']);

        $itemAttribute = new ItemAttribute($attribute['attribute_label']);
        $itemAttribute->setColorationInversed($attribute['colorationInversed']);
        $item->addAttribute($itemAttribute);

        $responseText = $attribute['option_label'];

        if (null === $responseText && null !== $attribute['value_value']) {
            if ($builder instanceof PresetableAttributeBuilderInterface) {
                $preset = $builder->getPreset($synthesis->getAutodiag());

                if (null !== $preset) {
                    $preset = $preset->getPreset();
                    $response = $builder->transform($attribute['value_value']);

                    $responseText = [];
                    foreach ($response as $key => $value) {
                        if (array_key_exists($key, $preset) && array_key_exists($value, $preset[$key])) {
                            $responseText[] = $preset[$key][$value];
                        } else {
                            $responseText[] = null;
                        }
                    }
                    $responseText = implode(' - ', $responseText);
                }
            }
        }

        $itemAttribute->setResponse(
            $builder->computeScore($attribute['value_value']),
            $responseText ?: '-'
        );
    }
}
