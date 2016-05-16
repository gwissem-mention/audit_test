<?php
namespace HopitalNumerique\RechercheBundle\Doctrine\Referencement;

use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ReferenceBundle\DependencyInjection\Reference\Tree as ReferenceTree;
use HopitalNumerique\ReferenceBundle\DependencyInjection\Referencement;
use HopitalNumerique\ReferenceBundle\Manager\EntityHasReferenceManager;

/**
 * Classe prenant en compte la modulation des références choisies
 */
class Modulation
{
    /**
     * @var \HopitalNumerique\ReferenceBundle\DependencyInjection\Referencement Referencement
     */
    private $referencement;

    /**
     * @var \HopitalNumerique\ReferenceBundle\DependencyInjection\Reference\Tree ReferenceTree
     */
    private $referenceTree;

    /**
     * @var \HopitalNumerique\ReferenceBundle\Manager\EntityHasReferenceManager EntityHasReferenceManager
     */
    private $entityHasReferenceManager;


    /**
     * Constructeur.
     */
    public function __construct(Referencement $referencement, ReferenceTree $referenceTree, EntityHasReferenceManager $entityHasReferenceManager)
    {
        $this->referencement = $referencement;
        $this->referenceTree = $referenceTree;
        $this->entityHasReferenceManager = $entityHasReferenceManager;
    }


    public function getModulatedReferenceIdsByGroupedReferenceIds(array $referenceIds, Domaine $domaine)
    {
        $referencesTree = $this->referencement->getReferencesTree([$domaine]);
        $referencesTree = $this->referenceTree->addCheckedReferenceIdsInTree($referencesTree, $referenceIds);
        //$referencesTree = $this->alsoCheckReferenceParentsInReferencesTree($referencesTree);

        $referenceParentIds = $this->getReferenceParentIdsByReferencesTree($referencesTree);
        $referenceEnfantIds = $this->getReferenceEnfantIdsByReferenceIds($referenceIds, $referencesTree);

        return array_merge($referenceIds, $referenceParentIds, $referenceEnfantIds);
        
        /*$referenceFrereIds = $this->getReferenceFrereIdsByReferencesTree($referencesTree);

        $referenceResults = $this->entityHasReferenceManager->getModulatedReferenceIdsByReferenceIds($referenceIds, $referenceParentIds, $referenceFrereIds);
        $referenceIds = [];
        foreach ($referenceResults as $referenceResult) {
            $referenceIds[] = $referenceResult[1];
        }
        return $referenceIds;*/
    }

    private function getReferenceParentIdsByReferencesTree(array $referencesTree)
    {
        return $this->getReferenceParentIdsByReferencesSubtree($referencesTree);
    }

    private function getReferenceParentIdsByReferencesSubtree(array $referencesTree)
    {
        $referenceParentIds = [];

        foreach ($referencesTree as $referenceSubtree) {
            if (!$referenceSubtree['checked'] && $this->hasCheckedReferenceInReferencesSubtree($referenceSubtree['enfants'])) {
                $referenceParentIds[] = $referenceSubtree['reference']->getId();
            }
            foreach ($this->getReferenceParentIdsByReferencesSubtree($referenceSubtree['enfants']) as $referenceParentId) {
                $referenceParentIds[] = $referenceParentId;
            }
        }

        return $referenceParentIds;
    }

    private function getReferenceFrereIdsByReferencesTree(array $referencesTree)
    {
        return $this->getReferenceFrereIdsByReferencesSubtree($referencesTree);
    }

    private function getReferenceFrereIdsByReferencesSubtree(array $referencesTree)
    {
        $referenceFrereIds = [];

        foreach ($referencesTree as $referenceSubtree) {
            foreach ($this->getReferenceFrereIdsByReferencesSubtree($referenceSubtree['enfants']) as $referenceFrereId) {
                $referenceFrereIds[] = $referenceFrereId;
            }
            if ($referenceSubtree['checked']) {
                foreach ($referencesTree as $referenceFratrieSubtree) {
                    if (!$referenceFratrieSubtree['checked']) {
                        $referenceFrereIds[] = $referenceFratrieSubtree['reference']->getId();
                    }
                }
                break;
            }
        }

        return $referenceFrereIds;
    }

    private function getReferenceEnfantIdsByReferenceIds(array $referenceIds, array $referencesTree)
    {
        return array_values(array_unique($this->getReferenceEnfantIdsByReferenceIdsInSubtree($referenceIds, $referencesTree)));
    }

    private function getReferenceEnfantIdsByReferenceIdsInSubtree(array $referenceIds, array $referencesSubtree)
    {
        $referenceEnfantIds = [];

        foreach ($referencesSubtree as $referenceSubtree) {
            if (in_array($referenceSubtree['reference']->getId(), $referenceIds)) {
                $referenceEnfantIds = array_merge($referenceEnfantIds, $this->referenceTree->getAllReferenceIdsByTree($referenceSubtree['enfants']));
            }

            $referenceEnfantIds = array_merge($referenceEnfantIds, $this->getReferenceEnfantIdsByReferenceIdsInSubtree($referenceIds, $referenceSubtree['enfants']));
        }

        return $referenceEnfantIds;
    }

    private function alsoCheckReferenceParentsInReferencesTree(array $referencesTree)
    {
        $referencesTreeWithCheckedParents = [];

        foreach ($referencesTree as $referenceSubtree) {
            if (!$referenceSubtree['checked'] && $this->hasCheckedReferenceInReferencesSubtree($referenceSubtree['enfants'])) {
                $referenceSubtree['checked'] = true;
            }
            $referenceSubtree['enfants'] = $this->alsoCheckReferenceParentsInReferencesTree($referenceSubtree['enfants']);
            //$this->alsoCheckReferenceParentsInReferencesTree($referenceSubtree['enfants']);

            $referencesTreeWithCheckedParents[] = $referenceSubtree;
        }

        return $referencesTreeWithCheckedParents;
    }

    private function hasCheckedReferenceInReferencesSubtree(array $referencesSubtree)
    {
        foreach ($referencesSubtree as $referenceSubtree) {
            if ($referenceSubtree['checked']) {
                return true;
            }
            $hasCheckedReference = $this->hasCheckedReferenceInReferencesSubtree($referenceSubtree['enfants']);
            if ($hasCheckedReference) {
                return true;
            }
        }

        return false;
    }
}
