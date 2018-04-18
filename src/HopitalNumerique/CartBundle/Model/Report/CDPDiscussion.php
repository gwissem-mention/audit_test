<?php

namespace HopitalNumerique\CartBundle\Model\Report;

use Doctrine\Common\Collections\ArrayCollection;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Message;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Fiche;
use HopitalNumerique\ReferenceBundle\Entity\EntityHasReference;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;

class CDPDiscussion implements ItemInterface
{
    /**
     * @var Fiche[] $summary
     */
    protected $summary = [];

    /**
     * @var Discussion $discussion
     */
    protected $discussion;

    /**
     * @var array $references
     */
    protected $references;

    /**
     * CDPDiscussion constructor.
     *
     * @param Discussion $discussion
     * @param array $references
     */
    public function __construct(Discussion $discussion, $references)
    {
        $this->discussion = $discussion;
        $this->references = $references;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->discussion->getId();
    }

    /**
     * @return Groupe[]
     */
    public function getGroups()
    {
        return $this->discussion->getGroups();
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->discussion->getTitle();
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->discussion->getCreatedAt();
    }

    /**
     * @return ArrayCollection|Message[]
     */
    public function getMessages()
    {
        return $this->discussion->getMessages();
    }

    /**
     * @param $summary
     *
     * @return CDPDiscussion
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * @return array|\Doctrine\Common\Collections\Collection|Fiche[]
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * @return EntityHasReference[]
     */
    public function getReferences()
    {
        return $this->references;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'cdpDiscussion';
    }
}
