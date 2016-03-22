<?php
namespace HopitalNumerique\ReferenceBundle\Doctrine\Referencement;

use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ReferenceBundle\DependencyInjection\Referencement as ReferencementService;
use HopitalNumerique\ReferenceBundle\Entity\EntityHasReference;
use HopitalNumerique\ReferenceBundle\Manager\EntityHasNoteManager;
use HopitalNumerique\ReferenceBundle\Manager\EntityHasReferenceManager;

/**
 * Service gérant l'enregistrement des notes de référencement.
 */
class NoteSaver
{
    /**
     * @var integer Score maximal
     */
    const SCORE_GLOBAL = 1000;


    /**
     * @var \HopitalNumerique\ReferenceBundle\DependencyInjection\Reference\Referencement Service Referencement
     */
    private $referencementService;

    /**
     * @var \HopitalNumerique\ReferenceBundle\Manager\EntityHasReferenceManager EntityHasReferenceManager
     */
    private $entityHasReferenceManager;

    /**
     * @var \HopitalNumerique\ReferenceBundle\Manager\EntityHasNoteManager EntityHasNoteManager
     */
    private $entityHasNoteManager;


    /**
     * @var array Toutes les EntityHasReference groupés par Type + ID d'entité
     */
    private $entitiesHaveReferencesClassifiedByEntityTypeClassifiedByEntityId = null;


    /**
     * Constructeur.
     */
    public function __construct(ReferencementService $referencementService, EntityHasReferenceManager $entityHasReferenceManager, EntityHasNoteManager $entityHasNoteManager)
    {
        $this->referencementService = $referencementService;
        $this->entityHasReferenceManager = $entityHasReferenceManager;
        $this->entityHasNoteManager = $entityHasNoteManager;
    }


    /**
     * Ajoute les scores pour chaque référence à l'arbre.
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine Domaine
     * @return array Arbre
     */
    private function getReferencesTreeWithScores(Domaine $domaine)
    {
        $referencesTree = $this->referencementService->getReferencesTree([$domaine]);

        $this->addScoresInReferencesSubtree($referencesTree, self::SCORE_GLOBAL);

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
                $note = $this->getNoteForEntitiesHaveReferences($entitiesHasReferences, $referencesTreeWithScores);

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
                    $this->entityHasNoteManager->remove($entityHasNote);
                }
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
            $referenceScore = $this->getNoteForEntityHasReference($entityHasReference, $referencesTreeWithScores);
            if (null === $referenceScore) {
                return null;
            }
            $score += $referenceScore;
        }

        return $score;
    }

    /**
     * Retourne le score d'un EntityHasReference.
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\EntityHasReference $entityHasReference          EntityHasReference
     * @param array                                                       $referencesSubtreeWithScores Arbre des références avec les scores
     * @return float|null Score
     */
    private function getNoteForEntityHasReference(EntityHasReference $entityHasReference, array $referencesSubtreeWithScores)
    {
        foreach ($referencesSubtreeWithScores as $referenceParameters) {
            if ($referenceParameters['reference']->getId() === $entityHasReference->getReference()->getId()) {
                return $referenceParameters['score'];
            }

            $score = $this->getNoteForEntityHasReference($entityHasReference, $referenceParameters['enfants']);
            if (null !== $score) {
                return $score;
            }
        }

        return null;
    }
}
