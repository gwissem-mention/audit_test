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

        $choosenReferenceIds = $this->container->get('hopitalnumerique_recherche.dependency_injection.referencement.requete_session')->getReferenceIds();

        return $this->render('HopitalNumeriqueRechercheBundle:Referencement:index.html.twig', [
            'referencesTree' => $referencesTree,
            'choosenReferenceIds' => $choosenReferenceIds
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
     * Retourne les entités résultats.
     */
    public function jsonEntitiesAction(Request $request)
    {
        $entitiesByType = $request->request->get('entitiesByType');

        foreach ($entitiesByType as $entityType => $entitiesPropertiesById) {
            $entityIds = array_keys($entitiesPropertiesById);

            $entities = $this->container->get('hopitalnumerique_core.dependency_injection.entity')->getEntitiesByTypeAndIds($entityType, $entityIds);
            foreach ($entities as $entity) {
                $entityId = $this->container->get('hopitalnumerique_core.dependency_injection.entity')->getEntityId($entity);
                $entitiesByType[$entityType][$entityId]['viewHtml'] = $this->renderView('HopitalNumeriqueRechercheBundle:Referencement:view_entity.html.twig', [
                    'category' => $this->container->get('hopitalnumerique_core.dependency_injection.entity')->getCategoryByEntity($entity),
                    'title' => $this->container->get('hopitalnumerique_core.dependency_injection.entity')->getTitleByEntity($entity),
                    'subtitle' => $this->container->get('hopitalnumerique_core.dependency_injection.entity')->getSubtitleByEntity($entity),
                    'url' => $this->container->get('hopitalnumerique_core.dependency_injection.entity')->getFrontUrlByEntity($entity),
                    'description' => $this->container->get('hopitalnumerique_core.dependency_injection.entity')->getDescriptionByEntity($entity),
                    'pertinenceNiveau' => $entitiesPropertiesById[$entityId]['pertinenceNiveau']
                ]);
            }
        }

        return new JsonResponse($entitiesByType);
    }
}
