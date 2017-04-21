<?php

namespace HopitalNumerique\PublicationBundle\Entity\Converter\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\File\File;

interface NodeInterface
{
    /**
     * @return integer
     */
    public function getId();

    /**
     * @param $text
     *
     * @return NodeInterface
     */
    public function appendContent($text);

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @return string
     */
    public function getContent();

    /**
     * @param $content string
     *
     * @return NodeInterface
     */
    public function setContent($content);

    /**
     * @return array
     */
    public function getFootnotes();

    /**
     * @param $id
     * @param $note
     *
     * @return NodeInterface
     */
    public function addFootnote($id, $note);

    /**
     * @return NodeInterface
     */
    public function getHighestParent();

    /**
     * @return boolean
     */
    public function isExcluded();

    /**
     * @param $excluded
     * @return mixed
     */
    public function setExcluded($excluded);

    /**
     * @return NodeInterface|null
     */
    public function getSquashIn();

    /**
     * @param null $squashIn
     *
     * @return NodeInterface
     */
    public function setSquashIn(NodeInterface $squashIn = null);

    /**
     * @return NodeInterface|null
     */
    public function getParent();

    /**
     *
     */
    public function unsetParent();

    /**
     * @param NodeInterface $node
     */
    public function setParent(NodeInterface $node);

    /**
     * @param NodeInterface $child
     */
    public function addChildren(NodeInterface $child);

    /**
     * @param NodeInterface $node
     */
    public function removeChildren(NodeInterface $node);

    /**
     * @return NodeInterface[]|ArrayCollection
     */
    public function getChildrens();

    /**
     * @return integer
     */
    public function getDeep();

    /**
     * @param File $file
     *
     * @return mixed
     */
    public function addFile(File $file);

    /**
     * @param bool $deep
     * @return File[]
     */
    public function getFiles($deep = false);

    /**
     * @param bool $deep
     * @return Media[]
     */
    public function getMedias($deep = false);

    /**
     * @param Media $media
     *
     * @return NodeInterface
     */
    public function addMedia(Media $media);
}
