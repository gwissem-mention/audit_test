<?php

namespace HopitalNumerique\CartBundle\Model\Item;


use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container\Chapter;

class AutodiagChapter extends Item
{
    /**
     * @var Chapter $chapter
     */
    protected $chapter;

    /**
     * AutodiagChapter constructor.
     *
     * @param Chapter $chapter
     */
    public function __construct(Chapter $chapter)
    {
        $this->chapter = $chapter;
    }

    /**
     * @return Chapter
     */
    public function getObject()
    {
        return $this->chapter;
    }


    public function getParentTitle()
    {
        return $this->chapter->getAutodiag()->getTitle();
    }

    public function getTitle()
    {
        return $this->chapter->getLabel();
    }

    /**
     * @return string
     */
    public function getObjectType()
    {
        return \HopitalNumerique\CartBundle\Entity\Item::AUTODIAG_CHAPTER_TYPE;
    }

    /**
     * @return int
     */
    public function getObjectId()
    {
        return $this->chapter->getId();
    }

    /**
     * @inheritdoc
     */
    public function getRoute()
    {
        return 'hopitalnumerique_autodiag_entry_add';
    }

    /**
     * @inheritdoc
     */
    public function getRouteParameters()
    {
        return [
            'autodiag' => $this->chapter->getAutodiag()->getId(),
        ];
    }

    /**
     * @return string
     */
    public function getUriFragment()
    {
        return (string) $this->chapter->getId();
    }

    /**
     * @inheritdoc
     */
    public function getDomains()
    {
        return $this->chapter->getAutodiag()->getDomaines();
    }
}
