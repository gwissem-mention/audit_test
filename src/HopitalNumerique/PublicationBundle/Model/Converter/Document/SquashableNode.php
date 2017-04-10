<?php

namespace HopitalNumerique\PublicationBundle\Model\Converter\Document;

use HopitalNumerique\PublicationBundle\Entity\Converter\Document\NodeInterface;

/**
 * Add squash functionality on node
 */
class SquashableNode extends NodeDecorator
{
    private $identityMap = [];
    private $squashMap = [];

    /**
     * @return NodeInterface
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

    /**
     * @param NodeInterface $node
     *
     * @return NodeInterface
     */
    private function squashNode(NodeInterface $node)
    {
        if (null === $node->getSquashIn()) {
            return $this;
        }

        $node->getSquashIn()->setContent(
            implode("\n", [
                $node->getSquashIn()->getContent(),
                '<h' . $node->getDeep() . '>' . $node->getTitle() . '</h' . $node->getDeep() . '>',
                $node->getContent(),
            ])
        );

        foreach ($node->getFootnotes() as $id => $note) {
            $node->getSquashIn()->addFootnote($id, $note);
        }

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

    /**
     * @param NodeInterface $node
     */
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
