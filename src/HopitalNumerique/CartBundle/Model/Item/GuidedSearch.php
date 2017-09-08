<?php

namespace HopitalNumerique\CartBundle\Model\Item;

use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcours;

class GuidedSearch extends Item
{
    /**
     * @var RechercheParcours $guidedSearchReference
     */
    protected $guidedSearchReference;

    /**
     * Person constructor.
     *
     * @param RechercheParcours $guidedSearchReference
     */
    public function __construct(RechercheParcours $guidedSearchReference)
    {
        $this->guidedSearchReference = $guidedSearchReference;
    }

    /**
     * @return RechercheParcours
     */
    public function getObject()
    {
        return $this->guidedSearchReference;
    }

    public function getTitle()
    {
        return $this->guidedSearchReference->getReference()->getLibelle();
    }

    /**
     * @return string
     */
    public function getObjectType()
    {
        return \HopitalNumerique\CartBundle\Entity\Item::GUIDED_SEARCH_TYPE;
    }

    /**
     * @return int
     */
    public function getObjectId()
    {
        return $this->guidedSearchReference->getId();
    }

    /**
     * @inheritdoc
     */
    public function getRoute()
    {
        return 'account_service';
    }

    /**
     * @inheritdoc
     */
    public function getRouteParameters()
    {
        return [];
    }

    public function getUriFragment()
    {
        return 'guided-search-widget';
    }

    /**
     * @inheritdoc
     */
    public function getDomains()
    {
        return $this->guidedSearchReference->getRecherchesParcoursGestion()->getDomaines();
    }
}
