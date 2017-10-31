<?php

namespace HopitalNumerique\ReferenceBundle\DependencyInjection;

use HopitalNumerique\DomaineBundle\Entity\Domaine;
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
     *
     * @return array Arbre
     */
    public function getReferencesTree($domaines, $inRecherche = null, Reference $referenceRoot = null)
    {
        $references = $this->referenceManager->findByDomaines($domaines, true, false, null, true, $inRecherche);

        $referencesTree = $this->treeService->getOrderedReferencesTreePart($references, $referenceRoot);

        // If root reference defined, select shared references too
        if ($referenceRoot) {
            $referencesTree = array_merge(
                $referencesTree,
                $this->treeService->getOrderedReferencesTreePart($references, $this->referenceManager->findOneById(Reference::SHARED_REFERENCES_ID))
            );

            $referencesTree = array_unique($referencesTree, SORT_REGULAR);

            usort($referencesTree, function ($a, $b) {
                if ($a['reference']->getOrder() === $b['reference']->getOrder()) {
                    return 0;
                }

                return $a['reference']->getOrder() > $b['reference']->getOrder() ? 1 : -1;
            });
        }

        return $referencesTree;
    }

    /**
     * Retourne l'arbre des références avec pour chaque référence son EntityHasReference si existant.
     *
     * @param array<\HopitalNumerique\DomaineBundle\Entity\Domaine> $domaines   Domaines
     * @param int                                                   $entityType Type d'entité
     * @param int                                                   $entityId   ID de l'entité
     *
     * @return array Arbre
     */
    public function getReferencesTreeWithEntitiesHasReferences($domaines, $entityType, $entityId)
    {
        $referencesTree = $this->getReferencesTree($domaines, null, $this->referenceManager->getReferenceRootCommun($domaines));
        $entitiesHasReferences = $this->entityHasReferenceManager->findBy([
            'entityType' => $entityType,
            'entityId' => $entityId,
        ]);

        $this->addEntitiesHasReferencesInReferencesSubtree($referencesTree, $entitiesHasReferences);

        return $referencesTree;
    }

    /**
     * Retourne l'arbre des références uniquement si la branche a un enfant référencé
     *
     * @param Domaine[] $domains
     * @param int $entityType
     * @param int $entityId
     *
     * @return array
     */
    public function getReferencesTreeOnlyWithEntitiesHasReferences($domains, $entityType, $entityId)
    {
        $referencesTree = $this->getReferencesTreeWithEntitiesHasReferences($domains, $entityType, $entityId);

        return $this->cleanSubTree($referencesTree);
    }

    /**
     * Remove branch if no child with reference registered
     *
     * @param array $children
     *
     * @return array
     */
    private function cleanSubTree($children)
    {
        foreach ($children as $k => $branch) {
            if (!$this->branchHasReference($branch)) {
                unset($children[$k]);
            } else {
                $children[$k]['enfants'] = $this->cleanSubTree($branch['enfants']);
            }
        }

        return $children;
    }

    /**
     * Test if branch has a reference registered
     *
     * @param array $branch
     *
     * @return bool
     */
    private function branchHasReference($branch)
    {
        if (!is_null($branch['entityHasReference'])) {
            return true;
        }

        foreach ($branch['enfants'] as $child) {
            if (!is_null($child['entityHasReference']) || $this->branchHasReference($child)) {
                return true;
            }
        }

        return false;
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
     *
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
     * @param bool  $primary Primary
     * @param float $note    Note
     *
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
     *
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
     *
     * @param array $referenceIds
     * @param array $referencesSubtree
     *
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
