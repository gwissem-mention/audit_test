<?php
namespace HopitalNumerique\ReferenceBundle\DependencyInjection;

use HopitalNumerique\ReferenceBundle\DependencyInjection\Reference\Tree as TreeService;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\ReferenceBundle\Manager\EntityHasReferenceManager;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;

/**
 * Service gérant le référencement des choses.
 */
class Referencement
{
    /**
     * @var \HopitalNumerique\ReferenceBundle\DependencyInjection\Reference\Tree Service Tree
     */
    private $treeService;

    /**
     * @var \HopitalNumerique\ReferenceBundle\Manager\ReferenceManager ReferenceManager
     */
    private $referenceManager;

    /**
     * @var \HopitalNumerique\ReferenceBundle\Manager\EntityHasReferenceManager EntityHasReferenceManager
     */
    private $entityHasReferenceManager;


    /**
     * Constructeur.
     */
    public function __construct(TreeService $treeService, ReferenceManager $referenceManager, EntityHasReferenceManager $entityHasReferenceManager)
    {
        $this->treeService = $treeService;
        $this->referenceManager = $referenceManager;
        $this->entityHasReferenceManager = $entityHasReferenceManager;
    }


    /**
     * Retourne l'arbre des références pour le référencement.
     *
     * @param array<\HopitalNumerique\DomaineBundle\Entity\Domaine> $domaines Domaines
     * @return array Arbre
     */
    public function getReferencesTree($domaines, $inRecherche = null)
    {
        $references = $this->referenceManager->findByDomaines($domaines, true, false, null, true, $inRecherche);

        return $this->treeService->getOrderedReferencesTreePart($references);
    }

    /**
     * Retourne l'arbre des références avec pour chaque référence son EntityHasReference si existant.
     *
     * @param array<\HopitalNumerique\DomaineBundle\Entity\Domaine> $domaines   Domaines
     * @param integer                                               $entityType Type d'entité
     * @param integer                                               $entityId   ID de l'entité
     * @return array Arbre
     */
    public function getReferencesTreeWithEntitiesHasReferences($domaines, $entityType, $entityId)
    {
        $referencesTree = $this->getReferencesTree($domaines);
        $entitiesHasReferences = $this->entityHasReferenceManager->findBy([
            'entityType' => $entityType,
            'entityId' => $entityId
        ]);

        $this->addEntitiesHasReferencesInReferencesSubtree($referencesTree, $entitiesHasReferences);

        return $referencesTree;
    }

    /**
     * Ajoute au sous-arbre les scores de chaque référence.
     *
     * @param array $referencesSubtree Sous-arbre de références
     * @param float $scoreParent       Score du parent
     */
    private function addEntitiesHasReferencesInReferencesSubtree(array &$referencesSubtree, array &$entitiesHasReferences)
    {
        foreach (array_keys($referencesSubtree) as $i) {
            $referencesSubtree[$i]['entityHasReference'] = $this->getEntityHasReferenceByReference($referencesSubtree[$i]['reference'], $entitiesHasReferences);
            $this->addEntitiesHasReferencesInReferencesSubtree($referencesSubtree[$i]['enfants'], $entitiesHasReferences);
        }
    }

    /**
     * Retourne le EntityHasReference pour la référence.
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference                 $reference             Référence
     * @param array<\HopitalNumerique\ReferenceBundle\Entity\EntityHasReference> $entitiesHasReferences EntitiesHasReferences
     * @return \HopitalNumerique\ReferenceBundle\Entity\EntityHasReference|null EntityHasReference
     */
    private function getEntityHasReferenceByReference(Reference $reference, array $entitiesHasReferences)
    {
        foreach ($entitiesHasReferences as $entityHasReference) {
            if ($reference->equals($entityHasReference->getReference())) {
                return $entityHasReference;
            }
        }

        return null;
    }

    /**
     * Retourne le niveau de pertinence d'une entité.
     *
     * @param boolean $primary Primary
     * @param float   $note    Note
     * @return int Niveau
     */
    public function getPertinenceNiveauByPrimaryAndNote($primary, $note)
    {
        if ($primary) {
            return 1;
        }

        if ($note < 500) {
            return 2;
        }

        return 3;
    }


    /**
     * Retourne les IDs de référence groupés par grandes catégories.
     *
     * @param array<integer>                                 $referenceIds IDs des références
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine      Domaine
     * @return array IDs par groupe
     */
    public function getReferenceIdsKeyedByGroup(array $referenceIds, array $referencesTree)
    {
        $referenceIdsByGroup = [];

        foreach ($referencesTree as $referencesSubtree) {
            $foundReferenceIds = $this->getReferenceIdsInSubtree($referenceIds, $referencesSubtree['enfants']);

            if (count($foundReferenceIds) > 0) {
                $referenceIdsByGroup[] = $foundReferenceIds;
                $referenceIds = array_diff($referenceIds, $foundReferenceIds);
            }
        }

        return $referenceIdsByGroup;
    }

    public function getReferenceIdsByGroupedReferenceIds($groupedReferenceIds)
    {
        $referenceIds = [];

        foreach ($groupedReferenceIds as $groupReferenceIds) {
            foreach ($groupReferenceIds as $referenceId) {
                $referenceIds[] = $referenceId;
            }
        }

        return $referenceIds;
    }

    /**
     * Parmi des IDs de références, retourne ceux présents dans l'arbre de références.
     * @param array $referenceIds
     * @param array $referencesSubtree
     * @return array<integer> Ids trouvés
     */
    private function getReferenceIdsInSubtree(array $referenceIds, array $referencesSubtree)
    {
        $foundedReferenceIds = [];

        foreach ($referencesSubtree as $referenceSubtree) {
            if (in_array($referenceSubtree['reference']->getId(), $referenceIds)) {
                $foundedReferenceIds[] = $referenceSubtree['reference']->getId();
                $referenceIds = array_diff($referenceIds, [$referenceSubtree['reference']->getId()]);
            }

            $foundedReferenceIds = array_merge($foundedReferenceIds, $this->getReferenceIdsInSubtree($referenceIds, $referenceSubtree['enfants']));
        }

        return $foundedReferenceIds;
    }
}
