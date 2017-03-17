<?php

namespace HopitalNumerique\PublicationBundle\Model\Converter\Document;

use HopitalNumerique\PublicationBundle\Entity\Converter\Document\NodeInterface;

/**
 * Add squash functionality on node
 *
 * @package HopitalNumerique\PublicationBundle\Model\Converter\Document
 */
class SquashableNode extends NodeDecorator
{
    private $identityMap = [];
    private $squashMap = [];

    /**
     * @return \HopitalNumerique\PublicationBundle\Entity\Converter\Document\NodeInterface
     */
    public function squash()
    {
        $this->buildMap($this);
        $sources = array_diff(array_keys($this->squashMap), array_values($this->squashMap));

        while (!empty($sources)) {
            foreach ($sources as $source) {
                $this->squashNode($this->identityMap[$source]);
                unset($this->squashMap[$source]);
            }
            $sources = array_diff(array_keys($this->squashMap), array_values($this->squashMap));
        }

        return $this;
    }

    private function squashNode(NodeInterface $node)
    {
        if (null === $node->getSquashIn()) {
            return $this;
        }

        $node->getSquashIn()->setContent(
            implode("\n", [$node->getSquashIn()->getContent(), $node->getTitle(), $node->getContent()])
        );

        foreach ($node->getMedias() as $media) {
            $node->getSquashIn()->addMedia($media);
        }

        $node->unsetParent();

        foreach ($node->getChildrens() as $children) {
            $children->setParent($node->getSquashIn());
        }

        $node->setSquashIn(null);

        return $node;
    }

    private function buildMap(NodeInterface $node)
    {
        if (null !== $node->getSquashIn()) {
            $this->identityMap[$node->getId()] = $node;
            $this->squashMap[$node->getId()] = $node->getSquashIn()->getId();
        }

        foreach ($node->getChildrens() as $children) {
            $this->buildMap($children);
        }
    }
}
