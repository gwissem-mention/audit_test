<?php

namespace HopitalNumerique\PublicationBundle\Form\DataTransformer\Converter\Document;

use HopitalNumerique\PublicationBundle\Entity\Converter\Document\Node;
use HopitalNumerique\PublicationBundle\Model\Converter\Document\WalkableNode;
use Symfony\Component\Form\DataTransformerInterface;

class SquashWithPreviousTransformer implements DataTransformerInterface
{
    /**
     * @var \HopitalNumerique\PublicationBundle\Entity\Converter\Document\Node
     */
    private $node;

    /**
     * @param \HopitalNumerique\PublicationBundle\Entity\Converter\Document\Node $node
     */
    public function setNode(Node $node)
    {
        $this->node = $node;
    }

    public function transform($value)
    {
        return $value !== null;
    }

    public function reverseTransform($value)
    {
        if (false === $value) {
            return null;
        }

        $prev = (new WalkableNode($this->node))->prev();
        while ($prev->isExcluded() && null !== $prev) {
            $prev = (new WalkableNode($prev))->prev();
        }

        return $prev;
    }
}
