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
    public function indexAction(Request $request)
    {
        $currentDomaine = $this->container->get('hopitalnumerique_domaine.dependency_injection.current_domaine')->get();
        $referencesTree = $this->container->get('hopitalnumerique_reference.dependency_injection.reference.tree')->getOrderedReferences(null, [$currentDomaine], true);

        if ($request->request->has('references')) {
            $choosenReferenceIds = $request->request->get('references');
        } else {
            $choosenReferenceIds = $this->container->get('hopitalnumerique_recherche.dependency_injection.referencement.requete_session')->getReferenceIds();
        }

        return $this->render('HopitalNumeriqueRechercheBundle:Referencement:index.html.twig', [
            'referencesTree' => $referencesTree,
            'categoriesProperties' => $this->container->get('hopitalnumerique_recherche.doctrine.referencement.category')->getCategoriesProperties(),
            'choosenReferenceIds' => $choosenReferenceIds,
            'domaines' => $this->container->get('hopitalnumerique_domaine.manager.domaine')->getAllArray()
        ]);
    }

    /**
     * Affiche la recherche par référencement avec des références prédéfinies.
     */
    public function indexWithReferencesAction($referenceString)
    {
        $referenceIds = explode('-', $referenceString);
        $this->container->get('hopitalnumerique_recherche.dependency_injection.referencement.requete_session')->setReferenceIds($referenceIds);

        return $this->redirectToRoute('hopitalnumerique_recherche_referencement_index');
    }

    /**
     * Retourne les entités trouvées selon les références choisies.
     */
    public function jsonEntitiesByReferencesAction(Request $request)
    {
        $referenceIds = $request->request->get('references', []);
        $entityTypeIds = $request->request->get('entityTypeIds', null);
        $publicationCategoryIds = $request->request->get('publicationCategoryIds', null);

        $entitiesPropertiesByGroup = $this->container->get('hopitalnumerique_recherche.doctrine.referencement.reader')->getEntitiesPropertiesByReferenceIdsByGroup($referenceIds, $entityTypeIds, $publicationCategoryIds);

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
