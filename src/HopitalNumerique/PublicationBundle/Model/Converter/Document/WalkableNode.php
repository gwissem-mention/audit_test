<?php

namespace HopitalNumerique\PublicationBundle\Model\Converter\Document;

use Doctrine\Common\Collections\ArrayCollection;

class WalkableNode extends NodeDecorator
{
    public function prev()
    {
        $siblings = $this->getSiblings();

        if (null === $siblings || $siblings->count() === 0) {
            return $this->getParent();
        }

        $siblings->first();
        $last = null;
        while ($siblings->current()->getId() !== $this->getId()) {
            $last = $siblings->current();
            $siblings->next();
        }

        if (null === $last) {
            return $this->getParent();
        }

        return $last;
    }

    /**
     * @return ArrayCollection|null
     */
    public function getSiblings()
    {
        return $this->getParent() ? $this->getParent()->getChildrens() : null;
    }

    /**
     * Get node level in tree
     *
     * @param bool $deep
     * @return array
     */
    public function getLevel($deep = false)
    {
        $level = [];

        if ($deep && null !== $this->getParent()) {
            $level = array_merge($level, (new WalkableNode($this->getParent()))->getLevel(true));
        }

        $siblings = $this->getSiblings();
        if (null !== $siblings) {
            $level[] = $siblings->indexOf($this->node) + 1;

            return $level;
        }

        return [];
    }
}
