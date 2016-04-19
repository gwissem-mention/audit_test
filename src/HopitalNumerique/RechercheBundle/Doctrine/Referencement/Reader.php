<?php
namespace HopitalNumerique\RechercheBundle\Doctrine\Referencement;

use HopitalNumerique\DomaineBundle\DependencyInjection\CurrentDomaine;
use HopitalNumerique\ReferenceBundle\DependencyInjection\Referencement;
use HopitalNumerique\ReferenceBundle\Manager\EntityHasNoteManager;
use HopitalNumerique\ReferenceBundle\Manager\EntityHasReferenceManager;

/**
 * Lecteur de la recherche par référencement.
 */
class Reader
{
    /**
     * @var \HopitalNumerique\ReferenceBundle\DependencyInjection\Referencement Referencement
     */
    private $referencement;

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
    public function __construct(Referencement $referencement, CurrentDomaine $currentDomaine, EntityHasReferenceManager $entityHasReferenceManager, EntityHasNoteManager $entityHasNoteManager)
    {
        $this->referencement = $referencement;
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
    private function getEntitiesPropertiesByReferenceIds(array $referenceIds)
    {
        $currentDomaine = $this->currentDomaine->get();
        $entitiesProperties = $this->entityHasReferenceManager->getWithNotes($currentDomaine, $referenceIds);
        usort($entitiesProperties, [$this, 'orderEntitiesProperties']);

        return $entitiesProperties;
    }

    /**
     * Callable pour trier les EntitiesProperties.
     *
     * @param array $entityProperties1 EntityProperties 1
     * @param array $entityProperties2 EntityProperties 2
     */
    private function orderEntitiesProperties($entityProperties1, $entityProperties2)
    {
        if ($entityProperties1['primary'] && $entityProperties2['primary']) {
            return 0;
        }
        if ($entityProperties1['primary'] && !$entityProperties2['primary']) {
            return 1;
        }
        if (!$entityProperties1['primary'] && $entityProperties2['primary']) {
            return -1;
        }

        if (null == $entityProperties1['note'] && null == $entityProperties2['note']) {
            return 0;
        }
        if (null == $entityProperties2['note']) {
            return 1;
        }
        if (null == $entityProperties1['note']) {
            return -1;
        }

        if ($entityProperties1['note'] == $entityProperties2['note']) {
            return 0;
        }
        if ($entityProperties1['note'] > $entityProperties2['note']) {
            return 1;
        } else {
            return -1;
        }
    }

    /**
     * Retourne les entités par groupe.
     *
     * @param array<integer> $referenceIds ID des références
     * @return array Entités
     */
    public function getEntitiesPropertiesByReferenceIdsByGroup(array $referenceIds)
    {
        $entitiesPropertiesByGroup = [
            'points-durs' => [],
            'productions' => []
        ];
        $entitiesProperties = $this->getEntitiesPropertiesByReferenceIds($referenceIds);

        foreach ($entitiesProperties as $entityProperties) {
            $group = (null !== $entityProperties['objetPointDurTypeId'] ? 'points-durs' : 'productions');
            $entitiesPropertiesByGroup[$group][] = [
                'entityType' => $entityProperties['entityType'],
                'entityId' => $entityProperties['entityId'],
                'pertinenceNiveau' => $this->referencement->getPertinenceNiveauByPrimaryAndNote($entityProperties['primary'], $entityProperties['note'])
            ];
        }

        return $entitiesPropertiesByGroup;
    }
}
