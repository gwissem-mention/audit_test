<?php

namespace HopitalNumerique\CartBundle\Model\Report;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Fiche;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\ReferenceBundle\Entity\EntityHasReference;

class CDPGroup implements ItemInterface
{
    /**
     * @var Fiche[] $summary
     */
    protected $summary = [];

    /**
     * @var Groupe $group
     */
    protected $group;

    /**
     * @var array $references
     */
    protected $references;

    /**
     * CDPGroup constructor.
     *
     * @param Groupe $group
     * @param array $references
     */
    public function __construct(Groupe $group, $references)
    {
        $this->group = $group;
        $this->references = $references;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->group->getId();
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->group->getTitre();
    }

    /**
     * @return \DateTime
     */
    public function getBeginningDate()
    {
        return $this->group->getDateDemarrage();
    }

    /**
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->group->getDateFin();
    }

    /**
     * @return string
     */
    public function getShortDescription()
    {
        return $this->group->getDescriptionCourte();
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->group->getDescriptionHtml();
    }

    /**
     * @param $summary
     *
     * @return CDPGroup
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
        return 'cdpGroup';
    }

}
