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
     * @param array<integer>      $groupedReferenceIds    ID des références
     * @param array<integer>|null $entityTypeIds          ID des types d'entité à récupérer
     * @param array<integer>|null $publicationCategoryIds ID des catégories de publications à récupérer
     * @return array Entités
     */
    private function getEntitiesPropertiesByReferenceIds(array $groupedReferenceIds, array $entityTypeIds = null, array $publicationCategoryIds = null)
    {
        $currentDomaine = $this->currentDomaine->get();
        $entitiesProperties = $this->entityHasReferenceManager->getWithNotes($currentDomaine, $groupedReferenceIds, $entityTypeIds, $publicationCategoryIds);
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
        if (intval($entityProperties1['primarySum']) > intval($entityProperties2['primarySum'])) {
            return -1;
        }
        if (intval($entityProperties1['primarySum']) < intval($entityProperties2['primarySum'])) {
            return 1;
        }

        if (intval($entityProperties1['referencesCount']) > intval($entityProperties2['referencesCount'])) {
            return -1;
        }
        if (intval($entityProperties1['referencesCount']) < intval($entityProperties2['referencesCount'])) {
            return 1;
        }

        if ($entityProperties1['note'] > $entityProperties2['note']) {
            return -1;
        }
        if ($entityProperties1['note'] < $entityProperties2['note']) {
            return 1;
        }

        return 0;
    }

    /**
     * Retourne les entités par groupe.
     *
     * @param array<integer>      $groupedReferenceIds    ID des références
     * @param array<integer>|null $entityTypeIds          ID des types d'entité à récupérer
     * @param array<integer>|null $publicationCategoryIds ID des catégories de publications à récupérer
     * @return array Entités
     */
    public function getEntitiesPropertiesByReferenceIdsByGroup(array $groupedReferenceIds, array $entityTypeIds = null, array $publicationCategoryIds = null)
    {
        $entitiesPropertiesByGroup = [
            'points-durs' => [],
            'productions' => []
        ];

        if (count($groupedReferenceIds) > 0) {
            $entitiesProperties = $this->getEntitiesPropertiesByReferenceIds($groupedReferenceIds, $entityTypeIds, $publicationCategoryIds);

            foreach ($entitiesProperties as $entityProperties) {
                $group = (null !== $entityProperties['objetPointDurTypeId'] ? 'points-durs' : 'productions');
                $entitiesPropertiesByGroup[$group][] = [
                    'entityType' => $entityProperties['entityType'],
                    'entityId' => $entityProperties['entityId'],
                    'pertinenceNiveau' => $this->referencement->getPertinenceNiveauByPrimaryAndNote($entityProperties['primarySum'], $entityProperties['note'])
                ];
            }
        }

        return $entitiesPropertiesByGroup;
    }
}
