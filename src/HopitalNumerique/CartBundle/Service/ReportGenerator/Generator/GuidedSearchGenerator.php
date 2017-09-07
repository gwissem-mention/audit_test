<?php

namespace HopitalNumerique\CartBundle\Service\ReportGenerator\Generator;

use HopitalNumerique\CartBundle\Entity\Report;
use HopitalNumerique\CartBundle\Model\Report\GuidedSearch;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\CartBundle\Model\Report\Publication;
use HopitalNumerique\CoreBundle\DependencyInjection\Entity;
use HopitalNumerique\CartBundle\Model\Report\ItemInterface;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcours;
use HopitalNumerique\ReferenceBundle\DependencyInjection\Referencement;
use HopitalNumerique\CartBundle\Service\ReportGenerator\ItemGeneratorInterface;

class GuidedSearchGenerator implements ItemGeneratorInterface
{
    /**
     * @var Referencement $referencement
     */
    protected $referencement;

    /**
     * GuidedSearchGenerator constructor.
     *
     * @param Referencement $referencement
     */
    public function __construct(Referencement $referencement)
    {
        $this->referencement = $referencement;
    }

    /**
     * @param $object
     *
     * @return bool
     */
    public function support($object)
    {
        return $object instanceof RechercheParcours;
    }

    /**
     * @param RechercheParcours $guidedSearchReference
     * @param Report $report
     *
     * @return ItemInterface
     */
    public function process($guidedSearchReference, Report $report)
    {
        $referencesTree = $this->referencement->getReferencesTreeOnlyWithEntitiesHasReferences(
            $guidedSearchReference->getRecherchesParcoursGestion()->getDomaines(),
            Entity::ENTITY_TYPE_RECHERCHE_PARCOURS,
            $guidedSearchReference->getId()
        );

        $item = new GuidedSearch(
            $guidedSearchReference,
            $referencesTree
        );

        return $item;
    }

}
