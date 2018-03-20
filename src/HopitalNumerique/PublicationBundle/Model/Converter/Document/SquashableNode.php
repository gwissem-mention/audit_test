<?php

namespace HopitalNumerique\PublicationBundle\Model\Converter\Document;

use HopitalNumerique\PublicationBundle\Entity\Converter\Document\NodeInterface;

/**
 * Add squash functionality on node
 */
class SquashableNode extends NodeDecorator
{
    private static $identityMap = [];
    private static $squashMap = [];

    /**
     * @return NodeInterface
     */
    public function squash()
    {
        $this->buildMap($this);

        foreach ($this->getChildrens() as $child) {
            (new SquashableNode($child))->squash();
        }

        if (null !== $this->getSquashIn()) {
            foreach (self::$squashMap as $source => $target) {
                if ($this->getId() === $target) {
                    self::$identityMap[$source]->setSquashIn($this->getSquashIn());
                    self::$squashMap[$source] = $this->getSquashIn()->getId();
                }
            };

            $this->squashNode($this);

            unset(self::$squashMap[$this->getId()]);
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
        foreach ($node->getChildrens() as $child) {
            $this->buildMap($child);
        }

        if (null !== $node->getSquashIn()) {
            self::$identityMap[$node->getId()] = $node;
            self::$squashMap[$node->getId()] = $node->getSquashIn()->getId();
        }
    }
}
