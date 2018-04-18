<?php

namespace HopitalNumerique\CartBundle\Model\Report;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container\Chapter;

class AutodiagChapter implements ItemInterface
{
    /**
     * @var Chapter $chapter
     */
    public $chapter;

    /**
     * AutodiagChapter constructor.
     */
    public function __construct(Chapter $chapter)
    {
        $this->chapter = $chapter;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->chapter->getAutodiag()->getId();
    }

    /**
     * @return int
     */
    public function getChapterId()
    {
        return $this->chapter->getId();
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->chapter->getLabel();
    }

    /**
     * @return string
     */
    public function getAutodiagTitle()
    {
        return $this->chapter->getAutodiag()->getTitle();
    }

    /**
     * @return \DateTime
     */
    public function getLastUpdateDate()
    {
        return $this->chapter->getAutodiag()->getPublicUpdatedDate();
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSubChapters()
    {
        return $this->chapter->getChilds();
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'autodiagChapter';
    }

    /**
     * @return array
     */
    public function getReferences()
    {
        return [];
    }
}
