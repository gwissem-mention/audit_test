<?php
namespace HopitalNumerique\ReferenceBundle\Doctrine\Referencement;

use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\DomaineBundle\Manager\DomaineManager;
use HopitalNumerique\CoreBundle\DependencyInjection\Entity as EntityService;
use HopitalNumerique\ReferenceBundle\DependencyInjection\Referencement as ReferencementService;
use HopitalNumerique\ReferenceBundle\Entity\EntityHasNote;
use HopitalNumerique\ReferenceBundle\Entity\EntityHasReference;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\ReferenceBundle\Manager\EntityHasNoteManager;
use HopitalNumerique\ReferenceBundle\Manager\EntityHasReferenceManager;

/**
 * Service gérant l'enregistrement des notes de référencement.
 */
class NoteSaver
{
    /**
     * @var \HopitalNumerique\ReferenceBundle\DependencyInjection\Reference\Referencement Service Referencement
     */
    private $referencementService;

    /**
     * @var \HopitalNumerique\CoreBundle\DependencyInjection\Entity EntityService
     */
    private $entityService;

    /**
     * @var \HopitalNumerique\ReferenceBundle\Manager\EntityHasReferenceManager EntityHasReferenceManager
     */
    private $entityHasReferenceManager;

    /**
     * @var \HopitalNumerique\ReferenceBundle\Manager\EntityHasNoteManager EntityHasNoteManager
     */
    private $entityHasNoteManager;

    /**
     * @var \HopitalNumerique\DomaineBundle\Manager\DomaineManager DomaineManager
     */
    private $domaineManager;


    /**
     * @var array Toutes les EntityHasReference groupés par Type + ID d'entité
     */
    private $entitiesHaveReferencesClassifiedByEntityTypeClassifiedByEntityId = null;


    /**
     * Constructeur.
     */
    public function __construct(ReferencementService $referencementService, EntityService $entityService, EntityHasReferenceManager $entityHasReferenceManager, EntityHasNoteManager $entityHasNoteManager, DomaineManager $domaineManager)
    {
        $this->referencementService = $referencementService;
        $this->entityService = $entityService;
        $this->entityHasReferenceManager = $entityHasReferenceManager;
        $this->entityHasNoteManager = $entityHasNoteManager;
        $this->domaineManager = $domaineManager;
    }


    //<-- Sauvegarde globale
    /**
     * Enregistre tous les scores d'un domaine.
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine Domaine
     */
    public function saveScoresForDomaine(Domaine $domaine)
    {
        $referencesTreeWithScores = $this->getReferencesTreeWithScores($domaine);
        if (null === $this->entitiesHaveReferencesClassifiedByEntityTypeClassifiedByEntityId) {
            $this->entitiesHasReferencesByEntityTypeByEntityId = $this->entityHasReferenceManager->getAllClassifiedByEntityTypeClassifiedByEntityId();
        }

        foreach ($this->entitiesHasReferencesByEntityTypeByEntityId as $entityType => $entitiesHasReferencesByEntityId) {
            foreach ($entitiesHasReferencesByEntityId as $entityId => $entitiesHasReferences) {
                $entity = $this->entityService->getEntityByTypeAndId($entityType, $entityId);

                if ($this->entityService->entityHasDomaine($entity, $domaine)) {
                    $note = $this->getNoteForEntitiesHaveReferences($entitiesHasReferences, $referencesTreeWithScores);
                    $this->save($entityType, $entityId, $domaine, $note);
                }
            }
        }
    }
    //-->


    //<-- Sauvegarde partielle
    /**
     * Enregistre les notes d'une entité.
     *
     * @param object $entity Entity
     */
    public function saveScoresForEntityTypeAndEntityId($entityType, $entityId)
    {
        //$domaines = $this->domaineManager->findAll();
        $domaines = $this->entityService->getDomainesByEntity($this->entityService->getEntityByTypeAndId($entityType, $entityId));
        $entitiesHasReferences = $this->entityHasReferenceManager->findBy([
            'entityType' => $entityType,
            'entityId' => $entityId
        ]);

        foreach ($domaines as $domaine) {
            $referencesTreeWithScores = $this->getReferencesTreeWithScores($domaine);

            $note = $this->getNoteForEntitiesHaveReferences($entitiesHasReferences, $referencesTreeWithScores);
            $this->save($entityType, $entityId, $domaine, $note);
        }
    }
    //-->


    /**
     * Ajoute les scores pour chaque référence à l'arbre.
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine Domaine
     * @return array Arbre
     */
    private function getReferencesTreeWithScores(Domaine $domaine)
    {
        $referencesTree = $this->referencementService->getReferencesTree([$domaine], true);

        $this->addScoresInReferencesSubtree($referencesTree, EntityHasNote::SCORE_GLOBAL);

        return $referencesTree;
    }

    /**
     * Ajoute au sous-arbre les scores de chaque référence.
     *
     * @param array $referencesSubtree Sous-arbre de références
     * @param float $scoreParent       Score du parent
     */
    private function addScoresInReferencesSubtree(array &$referencesSubtree, $scoreParent)
    {
        if (count($referencesSubtree) > 0) {
            $scoreEnfant = $scoreParent / count($referencesSubtree);

            foreach (array_keys($referencesSubtree) as $i) {
                $referencesSubtree[$i]['score'] = $scoreEnfant;
                $this->addScoresInReferencesSubtree($referencesSubtree[$i]['enfants'], $scoreEnfant);
            }
        }
    }

    /**
     * Retourne le score d'un EntityHasReference.
     *
     * @param array<\HopitalNumerique\ReferenceBundle\Entity\EntityHasReference> $entitiesHasReferences       EntitiesHasReferences
     * @param array                                                              $referencesSubtreeWithScores Arbre des références avec les scores
     * @return float|null Score
     */
    private function getNoteForEntitiesHaveReferences(array $entitiesHasReferences, array $referencesTreeWithScores)
    {
        $score = 0;

        foreach ($entitiesHasReferences as $entityHasReference) {
            if (0 === count($entityHasReference->getReference()->getParents())) {
                $score += $this->getNoteForEntityHasReferenceForEachReferenceParent($referencesTreeWithScores, $entityHasReference, null);
            } else {
                foreach ($entityHasReference->getReference()->getParents() as $referenceParent) {
                    $score += $this->getNoteForEntityHasReferenceForEachReferenceParent($referencesTreeWithScores, $entityHasReference, $referenceParent);
                }
            }
        }

        return $score;
    }

    /**
     * Retourne le score d'un EntityHasReference.
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\EntityHasReference $entityHasReference                EntityHasReference
     * @param array                                                       $referencesSubtreeWithScores       Arbre des références avec les scores
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference|null     $entityHasReferenceReferenceParent Référence parent du EntityHasReference dont il faut récupérer la note
     * @return float|null Score
     */
    private function getNoteForEntityHasReferenceForEachReferenceParent($referencesTreeWithScores, $entityHasReference, $referenceParent)
    {
        $referenceScore = $this->getNoteForEntityHasReference($referencesTreeWithScores, null, $entityHasReference, $referenceParent);

        if (null === $referenceScore) {
            //return null;
            $referenceScore = 0; // On compte 0 dans le cas où inRecherche = faux
        }

        return $referenceScore;
    }

    /**
     * Retourne le score d'un EntityHasReference.
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\EntityHasReference $entityHasReference                EntityHasReference
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference|null     $subtreeReferenceParent            Référence parent du sous-arbre
     * @param array                                                       $referencesSubtreeWithScores       Arbre des références avec les scores
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference|null     $entityHasReferenceReferenceParent Référence parent du EntityHasReference dont il faut récupérer la note
     * @return float|null Score
     */
    private function getNoteForEntityHasReference(array $referencesSubtreeWithScores, Reference $subtreeReferenceParent = null, EntityHasReference $entityHasReference, Reference $entityHasReferenceReferenceParent = null)
    {
        foreach ($referencesSubtreeWithScores as $referenceParameters) {
            if ((
                    (null === $subtreeReferenceParent && null === $entityHasReferenceReferenceParent)
                    || (null !== $subtreeReferenceParent && null !== $entityHasReferenceReferenceParent && $subtreeReferenceParent->getId() === $entityHasReferenceReferenceParent->getId())
                )
                && ($referenceParameters['reference']->getId() === $entityHasReference->getReference()->getId())
            ) {
                return $referenceParameters['score'];
            }

            $score = $this->getNoteForEntityHasReference($referenceParameters['enfants'], $referenceParameters['reference'], $entityHasReference, $entityHasReferenceReferenceParent);
            if (null !== $score) {
                return $score;
            }
        }

        return null;
    }

    /**
     * Enregistre en base la note.
     *
     * @param string                                         $entityType Type d'entité
     * @param integer                                        $entityId   ID de l'entité
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine    Domaine
     * @param float                                          $note       Note
     */
    private function save($entityType, $entityId, Domaine $domaine, $note)
    {
        $entityHasNote = $this->entityHasNoteManager->findOneBy([
            'entityType' => $entityType,
            'entityId' => $entityId,
            'domaine' => $domaine
        ]);

        if (null !== $note) {
            if (null === $entityHasNote) {
                $entityHasNote = $this->entityHasNoteManager->createEmpty();
                $entityHasNote->setEntityType($entityType);
                $entityHasNote->setEntityId($entityId);
                $entityHasNote->setDomaine($domaine);
            }
            $entityHasNote->setNote($note);
            $this->entityHasNoteManager->save($entityHasNote);
        } elseif (null !== $entityHasNote) {
            $this->entityHasNoteManager->delete($entityHasNote);
        }
    }
}
