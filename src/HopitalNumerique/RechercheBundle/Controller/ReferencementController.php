<?php
namespace HopitalNumerique\RechercheBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Contrôleur de la recherche avancée.
 */
class ReferencementController extends Controller
{
    /**
     * Recherche avancée.
     */
    public function indexAction()
    {
        $currentDomaine = $this->container->get('hopitalnumerique_domaine.dependency_injection.current_domaine')->get();
        $referencesTree = $this->container->get('hopitalnumerique_reference.dependency_injection.reference.tree')->getOrderedReferences(null, [$currentDomaine], true);

        return $this->render('HopitalNumeriqueRechercheBundle:Referencement:index.html.twig', [
            'referencesTree' => $referencesTree
        ]);
    }

    /**
     * Retourne les entités trouvées selon les références choisies.
     */
    public function jsonEntitiesByReferencesAction(Request $request)
    {
        $referenceIds = $request->request->get('references', []);
        $entitiesPropertiesByGroup = $this->container->get('hopitalnumerique_recherche.doctrine.referencement.reader')->getEntitiesPropertiesByReferenceIdsByGroup($referenceIds);

        return new JsonResponse($entitiesPropertiesByGroup);
    }

    /**
     * Affiche une entité.
     */
    public function viewEntityAction(Request $request, $entityType, $entityId)
    {
        $entity = $this->container->get('hopitalnumerique_core.dependency_injection.entity')->getEntityByTypeAndId($entityType, $entityId);

        return $this->render('HopitalNumeriqueRechercheBundle:Referencement:view_entity.html.twig', [
            'category' => $this->container->get('hopitalnumerique_core.dependency_injection.entity')->getCategoryByEntity($entity),
            'title' => $this->container->get('hopitalnumerique_core.dependency_injection.entity')->getTitleByEntity($entity),
            'subtitle' => $this->container->get('hopitalnumerique_core.dependency_injection.entity')->getSubtitleByEntity($entity),
            'url' => $this->container->get('hopitalnumerique_core.dependency_injection.entity')->getFrontUrlByEntity($entity),
            'description' => $this->container->get('hopitalnumerique_core.dependency_injection.entity')->getDescriptionByEntity($entity),
            'pertinenceNiveau' => $request->request->get('pertinenceNiveau')
        ]);
    }
}
