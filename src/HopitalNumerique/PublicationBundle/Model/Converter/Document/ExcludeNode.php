<?php

namespace HopitalNumerique\PublicationBundle\Model\Converter\Document;

/**
 * Add exclude functionality on node
 *
 * @package HopitalNumerique\PublicationBundle\Model\Converter\Document
 */
class ExcludeNode extends NodeDecorator
{
    public function exclude()
    {
        foreach ($this->getChildrens() as $children) {
            (new ExcludeNode($children))->exclude();
        }

        if (null !== $this->getParent() && $this->isExcluded()) {
            foreach ($this->getChildrens() as $children) {
                $children->setParent($this->getParent());
            }

            $this->unsetParent();
        }

        return $this;
    }
}
