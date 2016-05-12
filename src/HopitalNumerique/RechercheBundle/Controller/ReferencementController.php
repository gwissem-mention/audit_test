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
        $this->container->get('hopitalnumerique_recherche.dependency_injection.referencement.requete_session')->setWantToSaveRequete(false);
        $currentDomaine = $this->container->get('hopitalnumerique_domaine.dependency_injection.current_domaine')->get();
        $referencesTree = $this->container->get('hopitalnumerique_reference.dependency_injection.reference.tree')->getOrderedReferences(null, [$currentDomaine], true);

        if ($request->request->has('references')) {
            $choosenReferenceIds = $request->request->get('references');
        } else {
            $choosenReferenceIds = $this->container->get('hopitalnumerique_recherche.dependency_injection.referencement.requete_session')->getReferenceIds();
            if (count($choosenReferenceIds) === 0 && !$this->container->get('hopitalnumerique_recherche.dependency_injection.referencement.requete_session')->hasSearchedText()) {
                if (null !== $this->getUser()) {
                    $requeteDefault = $this->container->get('hopitalnumerique_recherche.manager.requete')->findDefaultByUser($this->getUser());
                    if (null !== $requeteDefault) {
                        $this->container->get('hopitalnumerique_recherche.dependency_injection.referencement.requete_session')->setRequete($requeteDefault);
                        $choosenReferenceIds = $this->container->get('hopitalnumerique_recherche.dependency_injection.referencement.requete_session')->getReferenceIds();
                    }
                }
                if (count($choosenReferenceIds) === 0) {
                    $choosenReferenceIds = $this->container->get('hopitalnumerique_account.dependency_injection.doctrine.reference.contexte')->getReferenceIds();
                }
            }
        }

        return $this->render('HopitalNumeriqueRechercheBundle:Referencement:index.html.twig', [
            'referencesTree' => $referencesTree,
            'requete' => $this->container->get('hopitalnumerique_recherche.dependency_injection.referencement.requete_session')->getRequete(),
            'categoriesProperties' => $this->container->get('hopitalnumerique_recherche.doctrine.referencement.category')->getCategoriesProperties(),
            'choosenReferenceIds' => $choosenReferenceIds,
            'entityTypeIds' => $this->container->get('hopitalnumerique_recherche.dependency_injection.referencement.requete_session')->getEntityTypeIds(),
            'publicationCategoryIds' => $this->container->get('hopitalnumerique_recherche.dependency_injection.referencement.requete_session')->getPublicationCategoryIds(),
            'searchedText' => $this->container->get('hopitalnumerique_recherche.dependency_injection.referencement.requete_session')->getSearchedText(),
            'domaines' => $this->container->get('hopitalnumerique_domaine.manager.domaine')->getAllArray(),
            'exaleadIsActivated' => $this->container->get('hopitalnumerique_recherche.manager.search')->getActivationExalead()
        ]);
    }

    /**
     * Affiche la recherche par référencement avec des références prédéfinies.
     */
    public function indexWithReferencesAction($referenceString)
    {
        $referenceIds = explode('-', $referenceString);
        $this->container->get('hopitalnumerique_recherche.dependency_injection.referencement.requete_session')->setReferenceIds($referenceIds);

        return $this->redirectToRoute('hopital_numerique_recherche_homepage');
    }

    /**
     * Retourne les entités trouvées selon les références choisies.
     */
    public function jsonEntitiesByReferencesAction(Request $request)
    {
        $entityTypeIds = $request->request->get('entityTypeIds', null);
        $publicationCategoryIds = $request->request->get('publicationCategoryIds', null);
        $exaleadSearchedText = $request->request->get('exaleadSearch', null);
        $foundedWords = [];
        $resultFilters = [];

        $this->container->get('hopitalnumerique_recherche.doctrine.referencement.reader')->setIsSearchedText(null !== $exaleadSearchedText);
        if (null !== $exaleadSearchedText) {
            $groupedReferenceIds = $request->request->get('references', null);
            $this->container->get('hopitalnumerique_recherche.dependency_injection.referencement.exalead.search')->setText($exaleadSearchedText);
            $resultFilters['objetIds'] = $this->container->get('hopitalnumerique_recherche.dependency_injection.referencement.exalead.search')->getObjetIds();
            $resultFilters['contenuIds'] = $this->container->get('hopitalnumerique_recherche.dependency_injection.referencement.exalead.search')->getContenuIds();

            if (null !== $groupedReferenceIds && count($groupedReferenceIds) > 0) {
                $entitiesPropertiesByGroup = $this->container->get('hopitalnumerique_recherche.doctrine.referencement.reader')->getEntitiesPropertiesByReferenceIdsByGroup($groupedReferenceIds, $entityTypeIds, $publicationCategoryIds, $resultFilters);
                foreach ($entitiesPropertiesByGroup as $group => $entitiesProperties) {
                    foreach ($entitiesProperties as $i => $entityProperties) {
                        $entitiesPropertiesByGroup[$group][$i] = $this->container->get('hopitalnumerique_recherche.dependency_injection.referencement.exalead.search')->mergeEntityProperties($entityProperties);
                    }
                }
            } else {
                $entitiesPropertiesByGroup = $this->container->get('hopitalnumerique_recherche.dependency_injection.referencement.exalead.search')->getEntitiesPropertiesByGroup();
            }
        } else {
            $groupedReferenceIds = $request->request->get('references', []);
            $entitiesPropertiesByGroup = $this->container->get('hopitalnumerique_recherche.doctrine.referencement.reader')->getEntitiesPropertiesByReferenceIdsByGroup($groupedReferenceIds, $entityTypeIds, $publicationCategoryIds, $resultFilters);
        }

        return new JsonResponse([
            'results' => $entitiesPropertiesByGroup,
            'foundedWords' => $foundedWords
        ]);
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
