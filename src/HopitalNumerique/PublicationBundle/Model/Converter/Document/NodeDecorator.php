<?php

namespace HopitalNumerique\PublicationBundle\Model\Converter\Document;

use HopitalNumerique\PublicationBundle\Entity\Converter\Document\Media;
use HopitalNumerique\PublicationBundle\Entity\Converter\Document\NodeInterface;
use Symfony\Component\HttpFoundation\File\File;

abstract class NodeDecorator implements NodeInterface
{
    /**
     * @var NodeInterface
     */
    protected $node;

    public function __construct(NodeInterface $node)
    {
        $this->node = $node;
    }

    public function getId()
    {
        return $this->node->getId();
    }

    public function appendContent($text)
    {
        return $this->node->appendContent($text);
    }

    public function getTitle()
    {
        return $this->node->getTitle();
    }

    public function getContent()
    {
        return $this->node->getContent();
    }

    public function setContent($content)
    {
        return $this->node->setContent($content);
    }

    public function getHighestParent()
    {
        return $this->node->getHighestParent();
    }

    public function isExcluded()
    {
        return $this->node->isExcluded();
    }

    public function setExcluded($excluded)
    {
        return $this->node->setExcluded($excluded);
    }

    public function getSquashIn()
    {
        return $this->node->getSquashIn();
    }

    public function setSquashIn(NodeInterface $squashIn = null)
    {
        return $this->node->setSquashIn($squashIn);
    }

    public function getParent()
    {
        return $this->node->getParent();
    }

    public function unsetParent()
    {
        return $this->node->unsetParent();
    }

    public function setParent(NodeInterface $node)
    {
        return $this->node->setParent($node);
    }

    public function addChildren(NodeInterface $child)
    {
        return $this->node->addChildren($child);
    }

    public function removeChildren(NodeInterface $node)
    {
        return $this->node->removeChildren($node);
    }

    public function getChildrens()
    {
        return $this->node->getChildrens();
    }

    public function getDeep()
    {
        return $this->node->getDeep();
    }

    public function addFile(File $file)
    {
        return $this->node->addFile($file);
    }

    /**
     * @param bool $deep
     * @return \Symfony\Component\HttpFoundation\File\File[]
     */
    public function getFiles($deep = false)
    {
        return $this->node->getFiles($deep);
    }

    public function getMedias($deep = false)
    {
        return $this->node->getMedias($deep);
    }

    public function addMedia(Media $media)
    {
        return $this->node->addMedia($media);
    }
}
