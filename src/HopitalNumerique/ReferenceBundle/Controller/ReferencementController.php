<?php
namespace HopitalNumerique\ReferenceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Referencement controller.
 */
class ReferencementController extends Controller
{
    /**
     * Affiche la fenêtre contenant le référencement d'une entité.
     *
     * @param object $entity Entité
     */
    public function popinAction($entityType, $entityId)
    {
        $entity = $this->container->get('hopitalnumerique_reference.dependency_injection.referencement.entity')->getEntityByTypeAndId($entityType, $entityId);
        if (null === $entity) {
            throw new \Exception('Entité non trouvée pour TYPE = "'.$entityType.'" et ID = "'.$entityId.'".');
        }

        $referencesTree = $this->container->get('hopitalnumerique_reference.dependency_injection.referencement')->getReferencesTreeWithEntitiesHasReferences(
            $entity->getDomaines(),
            $entityType,
            $entityId
        );

        return $this->render('HopitalNumeriqueReferenceBundle:Referencement:popin.html.twig', array(
            'referencesTree' => $referencesTree
        ));
    }
}
