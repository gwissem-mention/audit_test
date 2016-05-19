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
        $request->getSession()->set('urlToRedirect', $request->getUri());
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
                    $choosenReferenceIds = $this->container->get('hopitalnumerique_account.doctrine.reference.contexte')->getReferenceIds();
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
        $foundWords = [];
        $resultFilters = [];

        $this->container->get('hopitalnumerique_recherche.doctrine.referencement.reader')->setIsSearchedText(null !== $exaleadSearchedText);
        if (null !== $exaleadSearchedText) { // Recherche Exalead
            $groupedReferenceIds = $request->request->get('references', null);
            $this->container->get('hopitalnumerique_recherche.dependency_injection.referencement.exalead.search')->setText($exaleadSearchedText);
            $resultFilters['objetIds'] = $this->container->get('hopitalnumerique_recherche.dependency_injection.referencement.exalead.search')->getObjetIds();
            $resultFilters['contenuIds'] = $this->container->get('hopitalnumerique_recherche.dependency_injection.referencement.exalead.search')->getContenuIds();

            // Si autre filtre qu'Exalead
            if ((null !== $groupedReferenceIds && count($groupedReferenceIds) > 0) || (null !== $publicationCategoryIds && count($publicationCategoryIds) > 0)) {
                $exaleadEntitiesPropertiesByGroup = $this->container->get('hopitalnumerique_recherche.dependency_injection.referencement.exalead.search')->getEntitiesPropertiesByGroup();
                $dbEntitiesPropertiesByGroup = $this->container->get('hopitalnumerique_recherche.doctrine.referencement.reader')->getEntitiesPropertiesByReferenceIdsByGroup($groupedReferenceIds, $entityTypeIds, $publicationCategoryIds, $resultFilters);

                if ((null !== $groupedReferenceIds && count($groupedReferenceIds) > 0)) { // Cas où on a au moins une référence
                    $exaleadEntitiesPropertiesByGroup = $this->container->get('hopitalnumerique_recherche.dependency_injection.referencement.exalead.search')->mergeEntitiesPropertiesByGroup($exaleadEntitiesPropertiesByGroup, $dbEntitiesPropertiesByGroup, true);
                } else { // Pas de référence, filtrer uniquement Exalead sur ses catégories
                    $exaleadEntitiesPropertiesByGroup = $this->container->get('hopitalnumerique_recherche.dependency_injection.referencement.exalead.search')->mergeEntitiesPropertiesByGroup($exaleadEntitiesPropertiesByGroup, $dbEntitiesPropertiesByGroup, false);
                    $exaleadEntitiesPropertiesByGroup = $this->container->get('hopitalnumerique_recherche.dependency_injection.referencement.exalead.search')->deleteOthersCategoriesFromEntitiesPropertiesByGroup($exaleadEntitiesPropertiesByGroup, $publicationCategoryIds);
                }
                $entitiesPropertiesByGroup = $exaleadEntitiesPropertiesByGroup;
            } else {
                $entitiesPropertiesByGroup = $this->container->get('hopitalnumerique_recherche.dependency_injection.referencement.exalead.search')->getEntitiesPropertiesByGroup();
            }
            $foundWords = $this->container->get('hopitalnumerique_recherche.dependency_injection.referencement.exalead.search')->getFoundWords();
        } else {
            $groupedReferenceIds = $request->request->get('references', []);
            $entitiesPropertiesByGroup = $this->container->get('hopitalnumerique_recherche.doctrine.referencement.reader')->getEntitiesPropertiesByReferenceIdsByGroup($groupedReferenceIds, $entityTypeIds, $publicationCategoryIds, $resultFilters);
        }

        return new JsonResponse([
            'results' => $entitiesPropertiesByGroup,
            'foundWords' => $foundWords
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
