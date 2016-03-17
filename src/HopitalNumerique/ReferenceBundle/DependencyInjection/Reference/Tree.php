<?php
namespace HopitalNumerique\ReferenceBundle\DependencyInjection\Reference;

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
     */
    public function __construct(ReferenceManager $referenceManager)
    {
        $this->referenceManager = $referenceManager;
    }


    /**
     * Retourne les options permettant la création de l'arbre.
     *
     * @return array Options
     */
    public function getOptions()
    {
        $references = $this->getOrderedReferences();

        $jsTreeOptionsData = $this->getTreeOptionsDataPart($references);
        $jsTreeOptions = [
            'core' => [
                'data' => $jsTreeOptionsData
            ],
            'checkbox' => [
                'visible' => true,
                'three_state' => false
            ],
            'plugins' => [
                'checkbox'
            ]
        ];

        return $jsTreeOptions;
    }

    /**
     * Retourne les données (paramètre data) des options de l'arbre.
     *
     * @return array Data
     */
    private function getTreeOptionsDataPart(array $orderedReferences)
    {
        $jsTreeOptionsDataPart = [];

        foreach ($orderedReferences as $referenceParemeters) {
            $jsTreeOptionsDataPart[] = [
                'id' => $referenceParemeters['reference']->getId(),
                'text' => $referenceParemeters['reference']->getLibelle(),
                'children' => $this->getTreeOptionsDataPart($referenceParemeters['enfants'])
            ];
        }

        return $jsTreeOptionsDataPart;
    }

    /**
     * Retourne les références classées.
     *
     * @param array<\HopitalNumerique\DomaineBundle\Entity\Domaine>|null $domaines   Domaines des références
     * @param boolean|null                                               $parentable Parentable
     * @return \Doctrine\Common\Collections\Collection Références
     */
    public function getOrderedReferences($parentable = true, $domaines = null)
    {
        if (null === $domaines) {
            $referencesConditions = [
                'lock' => false,
                'parentable' => $parentable
            ];

            $references = $this->referenceManager->findBy($referencesConditions, ['order' => 'ASC']);
        } else {
            $references = $this->referenceManager->findByDomaines($domaines, true, false, $parentable);
        }

        return $this->getOrderedReferencesTreePart($references);
    }

    /**
     * Retourne un arbre de références.
     *
     * @param array<\HopitalNumerique\ReferenceBundle\Entity\Reference\Reference> $references Références à trier
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference\Reference $referenceParent Référence parente
     * @return array Arbre
     */
    public function getOrderedReferencesTreePart($references, Reference $referenceParent = null)
    {
        $referencesSubTree = [];

        foreach ($references as $i => $reference) {
            if ((null === $referenceParent && 0 === count($reference->getParents())) || (null !== $referenceParent && $reference->hasParent($referenceParent))) {
                unset($references[$i]);
                $referencesSubTreeNode = [
                    'reference' => $reference,
                    'enfants' => $this->getOrderedReferencesTreePart($references, $reference)
                ];
                $referencesSubTree[] = $referencesSubTreeNode;
            }
        }

        return $referencesSubTree;
    }
}
