<?php
namespace HopitalNumerique\AutodiagBundle\Service\Result;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\AutodiagBundle\Model\Result\Item;
use HopitalNumerique\AutodiagBundle\Model\Result\ItemAttribute;
use HopitalNumerique\AutodiagBundle\Service\Synthesis\Completion;

class ResultItemBuilder
{
    /**
     * @var Completion
     */
    protected $completion;

    public function __construct(Completion $completion)
    {
        $this->completion = $completion;
    }

    public function build(Container $container, Synthesis $synthesis)
    {
        $resultItem = new Item();

        $resultItem->setLabel($container->getLabel());
        $resultItem->setNumberOfQuestions($container->getTotalNumberOfAttributes());
        $resultItem->setNumberOfAnswers($this->completion->getAnswersCount($synthesis, $container));



        foreach ($container->getAttributes() as $attribute) {
            $itemAttribute = new ItemAttribute($attribute->getLabel());
            $resultItem->addAttribute($itemAttribute);
        }

        return $resultItem;
    }
}
