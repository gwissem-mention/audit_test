<?php

namespace HopitalNumerique\CartBundle\Model\Report;

use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcours;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursDetails;

class GuidedSearch implements ItemInterface
{
    /**
     * @var RechercheParcours $guidedSearchReference
     */
    protected $guidedSearchReference;

    /**
     * @var array $references
     */
    protected $references;

    /**
     * GuidedSearch constructor.
     *
     * @param RechercheParcours $guidedSearchReference
     * @param array $references
     */
    public function __construct(RechercheParcours $guidedSearchReference, $references)
    {
        $this->guidedSearchReference = $guidedSearchReference;
        $this->references = $references;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->guidedSearchReference->getId();
    }
    
    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->guidedSearchReference->getReference()->getLibelle();
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->guidedSearchReference->getDescription();
    }

    /**
     * @return \DateTime
     */
    public function getPublicationDate()
    {
        return $this->guidedSearchReference->getRecherchesParcoursGestion()->getBroadcastDate();
    }

    /**
     * @return RechercheParcoursDetails[]
     */
    public function getChildren()
    {
        return $this->guidedSearchReference->getRecherchesParcoursDetails();
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'guided_search';
    }

    /**
     * @return array
     */
    public function getReferences()
    {
        return $this->references;
    }
}
