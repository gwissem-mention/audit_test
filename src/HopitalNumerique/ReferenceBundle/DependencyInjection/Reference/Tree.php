<?php

namespace HopitalNumerique\ReferenceBundle\DependencyInjection\Reference;

use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;

/**
 * Service permettant la création d'un arbre de références.
 */
class Tree
{
    /**
     * @var \HopitalNumerique\ReferenceBundle\Manager\ReferenceManager ReferenceManager
     */
    private $referenceManager;

    /**
     * Constructeur.
     *
     * @param ReferenceManager $referenceManager
     */
    public function __construct(ReferenceManager $referenceManager)
    {
        $this->referenceManager = $referenceManager;
    }

    /**
     * Retourne les options permettant la création de l'arbre.
     *
     * @param Domaine[]      $domaines
     * @param array<integer> $forbiddenReferenceIds ID des références à ne pas afficher
     *
     * @return array Options
     */
    public function getOptions($domaines, $forbiddenReferenceIds = [])
    {
        $references = $this->getOrderedReferences(null, true, $domaines);

        $jsTreeOptionsData = $this->getTreeOptionsDataPart($references, $forbiddenReferenceIds);

        $jsTreeOptions = [
            'core' => [
                'data' => $jsTreeOptionsData,
            ],
            'checkbox' => [
                'visible' => true,
                'three_state' => false,
            ],
            'plugins' => [
                'checkbox',
            ],
        ];

        return $jsTreeOptions;
    }

    /**
     * Retourne les données (paramètre data) des options de l'arbre.
     *
     * @param array<integer> $forbiddenReferenceIds ID des références à ne pas afficher
     *
     * @return array Data
     */
    private function getTreeOptionsDataPart(array $orderedReferences, $forbiddenReferenceIds = [])
    {
        $jsTreeOptionsDataPart = [];

        foreach ($orderedReferences as $referenceParemeters) {
            $referenceId = $referenceParemeters['reference']['id'];
            // Éviter qu'un parent soit lui-même un des ses enfants (boucles infinies)
            if (!in_array($referenceId, $forbiddenReferenceIds)) {
                $domaineLibelles = [];
                foreach ($referenceParemeters['reference']['domaines'] as $domaine) {
                    $domaineLibelles[] = $domaine['nom'];
                }

                $jsTreeOptionsDataPart[] = [
                    'id' => $referenceId,
                    'text' => $referenceParemeters['reference']['libelle'] . (count($referenceParemeters['reference']['domaines']) > 0 ? ' <em><small>- ' . implode(' ; ', $domaineLibelles) . '</small></em>' : ''),
                    'children' => $this->getTreeOptionsDataPart($referenceParemeters['enfants'], $forbiddenReferenceIds),
                ];
            }
        }

        return $jsTreeOptionsDataPart;
    }

    /**
     * Retourne les références classées.
     *
     * @param Reference|null $referenceRoot
     * @param bool|null      $parentable  Parentable
     * @param Domaine[]|null $domaines    Domaines des références
     * @param bool|null      $inRecherche InRecherche ?
     *
     * @return Reference[]
     */
    public function getOrderedReferences(
        Reference $referenceRoot = null,
        $parentable = true,
        $domaines = null,
        $inRecherche = null
    ) {
        if (null === $domaines) {
            $referencesConditions = [
                'lock' => false,
            ];
            if (null !== $inRecherche) {
                $referencesConditions['inRecherche'] = $inRecherche;
            }
            $references = $this->referenceManager->findBy($referencesConditions, ['order' => 'ASC']);
        } else {
            $references = $this->referenceManager->findByDomaines(
                $domaines,
                true,
                false,
                $parentable,
                null,
                $inRecherche,
                null,
                true
            );
        }

        $referencesTree = $this->getOrderedReferencesTreePart($references, $referenceRoot);

        // If root reference defined, select shared references too
        if ($referenceRoot) {
            $referencesTree = array_merge(
                $referencesTree,
                $this->getOrderedReferencesTreePart($references, $this->referenceManager->findOneById(Reference::SHARED_REFERENCES_ID))
            );

            $referencesTree = array_unique($referencesTree, SORT_REGULAR);

            usort($referencesTree, function ($a, $b) {
                if ($a['reference']['order'] === $b['reference']['order']) {
                    return 0;
                }

                return $a['reference']['order'] > $b['reference']['order'] ? 1 : -1;
            });
        }

        // 3 - 30
        return $referencesTree;
    }

    /**
     * Retourne un arbre de références.
     * Le & pour $references permet d'éviter les doublons qui cassent le fonctionnement à cause d'ID identiques.
     *
     * @param Reference[] $references      Références à trier
     * @param Reference   $referenceParent Référence parente
     *
     * @return array Arbre
     */
    public function getOrderedReferencesTreePart($references, $referenceParent = null)
    {
        $referencesSubTree = [];

        foreach ($references as $i => $reference) {
            if ($reference instanceof Reference) {
                if ((null === $referenceParent && 0 === count($reference->getParents()))
                    || (null !== $referenceParent
                        && $reference->hasParent(
                            $referenceParent
                        ))
                ) {
                    $referencesSubTreeNode = [
                        'reference' => $reference,
                        'enfants' => $this->getOrderedReferencesTreePart($references, $reference),
                    ];
                    $referencesSubTree[] = $referencesSubTreeNode;
                }
            } else {
                $hasParent = false;

                if ($referenceParent != null) {
                    $referenceParentId = $referenceParent instanceof Reference ? $referenceParent->getId()
                        : $referenceParent['id']
                    ;

                    foreach ($reference['parents'] as $parent) {
                        if ($parent['id'] == $referenceParentId) {
                            $hasParent = true;
                            continue;
                        }
                    }
                }
                if ((null === $referenceParent && 0 === count($reference['parents']))
                    || (null !== $referenceParent
                        && $hasParent)
                ) {
                    $referencesSubTreeNode = [
                        'reference' => $reference,
                        'enfants' => $this->getOrderedReferencesTreePart($references, $reference),
                    ];

                    $referencesSubTree[] = $referencesSubTreeNode;
                }
            }
        }

        return $referencesSubTree;
    }

    /**
     * @param array $referencesTree
     * @param array $checkedReferenceIds
     *
     * @return array
     */
    public function addCheckedReferenceIdsInTree(array $referencesTree, array $checkedReferenceIds)
    {
        $referencesTree = $this->addCheckedReferenceIdsInSubtree($referencesTree, $checkedReferenceIds);

        return $referencesTree;
    }

    /**
     * @param array $referencesSubtree
     * @param array $checkedReferenceIds
     *
     * @return array
     */
    public function addCheckedReferenceIdsInSubtree(array $referencesSubtree, array &$checkedReferenceIds)
    {
        $referencesSubtreeWithCheckedReferenceIds = [];

        foreach ($referencesSubtree as $referenceSubtree) {
            $referenceSubtree['checked'] = in_array($referenceSubtree['reference']->getId(), $checkedReferenceIds);

            if ($referenceSubtree['checked']) {
                $checkedReferenceIds = array_diff($checkedReferenceIds, [$referenceSubtree['reference']->getId()]);
            }

            $referenceSubtree['enfants'] = $this->addCheckedReferenceIdsInSubtree(
                $referenceSubtree['enfants'],
                $checkedReferenceIds
            );

            $referencesSubtreeWithCheckedReferenceIds[] = $referenceSubtree;
        }

        return $referencesSubtreeWithCheckedReferenceIds;
    }

    /**
     * @param array $referencesTree
     *
     * @return array
     */
    public function getAllReferenceIdsByTree(array $referencesTree)
    {
        $referenceIds = [];

        foreach ($referencesTree as $referenceSubtree) {
            $referenceIds[] = $referenceSubtree['reference']->getId();
            $referenceIds = array_merge($referenceIds, $this->getAllReferenceIdsByTree($referenceSubtree['enfants']));
        }

        return $referenceIds;
    }
}
