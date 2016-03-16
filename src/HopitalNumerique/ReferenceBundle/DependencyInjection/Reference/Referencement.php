<?php
namespace HopitalNumerique\ReferenceBundle\DependencyInjection\Reference;

use HopitalNumerique\ReferenceBundle\DependencyInjection\Reference\Tree as TreeService;
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
     * Constructeur.
     */
    public function __construct(TreeService $treeService, ReferenceManager $referenceManager)
    {
        $this->treeService = $treeService;
        $this->referenceManager = $referenceManager;
    }


    /**
     * Retourne l'arbre des références pour le référencement.
     *
     * @param array<\HopitalNumerique\DomaineBundle\Entity\Domaine> $domaines Domaines
     * @return array Arbre
     */
    public function getReferencesTree(array $domaines)
    {
        $references = $this->referenceManager->findByDomaines($domaines, null, null, true);

        $this->treeService->getOrderedReferencesTreePart($references);
    }

    /**
     * Retourne un sous-arbre de références.
     *
     * @param array<\HopitalNumerique\ReferenceBundle\Entity\Reference\Reference> $references Références à trier
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference\Reference $referenceParent Référence parente
     * @return array Arbre
     */
    /*private function getOrderedReferencesTreePart($references, Reference $referenceParent = null)
    {
        $referencesSubTree = [];

        foreach ($references as $i => $reference) {
            if ((null === $referenceParent && 0 === count($reference->getParents())) || (null !== $referenceParent && $reference->hasParent($referenceParent))) {
                unset($references[$i]);
                $referencesSubTree[] = [
                    'reference' => $reference,
                    'enfants' => $this->getOrderedReferencesTreePart($references, $reference)
                ];
            }
        }

        return $referencesSubTree;
    }*/
}
