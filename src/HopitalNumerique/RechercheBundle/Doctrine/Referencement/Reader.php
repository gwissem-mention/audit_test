<?php
namespace HopitalNumerique\RechercheBundle\Doctrine\Referencement;

use HopitalNumerique\DomaineBundle\DependencyInjection\CurrentDomaine;
use HopitalNumerique\ReferenceBundle\Manager\EntityHasNoteManager;
use HopitalNumerique\ReferenceBundle\Manager\EntityHasReferenceManager;

/**
 * Lecteur de la recherche par référencement.
 */
class Reader
{
    /**
     * @var \HopitalNumerique\DomaineBundle\DependencyInjection\CurrentDomaine CurrentDomaine
     */
    private $currentDomaine;

    /**
     * @var \HopitalNumerique\ReferenceBundle\Manager\EntityHasReferenceManager EntityHasReferenceManager
     */
    private $entityHasReferenceManager;

    /**
     * @var \HopitalNumerique\ReferenceBundle\Manager\EntityHasNoteManager EntityHasNoteManager
     */
    private $entityHasNoteManager;


    /**
     * Constructor.
     */
    public function __construct(CurrentDomaine $currentDomaine, EntityHasReferenceManager $entityHasReferenceManager, EntityHasNoteManager $entityHasNoteManager)
    {
        $this->currentDomaine = $currentDomaine;
        $this->entityHasReferenceManager = $entityHasReferenceManager;
        $this->entityHasNoteManager = $entityHasNoteManager;
    }


    /**
     * Retourne les entités.
     *
     * @param array<integer> $referenceIds ID des références
     * @return array Entités
     */
    public function getEntityPropertiesByReferenceIds(array $referenceIds)
    {
        $currentDomaine = $this->currentDomaine->get();

        $entitiesHaveReference = $this->entityHasReferenceManager->getWithNotes($currentDomaine, $referenceIds);
    }
}
