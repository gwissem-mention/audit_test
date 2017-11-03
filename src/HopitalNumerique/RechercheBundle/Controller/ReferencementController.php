<?php

namespace HopitalNumerique\RechercheBundle\Controller;

use HopitalNumerique\RechercheBundle\DependencyInjection\Referencement\RequeteSession;
use HopitalNumerique\RechercheBundle\Entity\Requete;
use HopitalNumerique\UserBundle\Entity\User;
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
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        /** @var RequeteSession $requeteSession */
        $requeteSession = $this->get('hopitalnumerique_recherche.dependency_injection.referencement.requete_session');
        $openRequeteSavingPopin = false;

        // If the user asked to save his search while he was not logged in
        if ($this->get('session')->get(RequeteSession::SESSION_WANT_SAVE_REQUETE)
            && $requeteSession->isAnonymousUser()
        ) {
            $requeteSession->setWantToSaveRequete(false);
            $requeteSession->setAnonymousUser(false);

            $openRequeteSavingPopin = true;
        }

        $request->getSession()->set('urlToRedirect', $request->getUri());
        $requeteSession->setWantToSaveRequete(false);
        $currentDomaine = $this->container->get('hopitalnumerique_domaine.dependency_injection.current_domaine')->get();
        $referencesTree = $this->container->get('hopitalnumerique_reference.dependency_injection.reference.tree')->getOrderedReferences($currentDomaine->getReferenceRoot(), null, [$currentDomaine], true);

        if ($request->request->has('references')) {
            $choosenReferenceIds = $request->request->get('references');
        } else {
            $choosenReferenceIds = $requeteSession->getReferenceIds();

            if (count($choosenReferenceIds) === 0 && !$requeteSession->hasSearchedText()) {
                if (null !== $this->getUser()) {
                    $requeteDefault = $this->container->get('hopitalnumerique_recherche.manager.requete')->findDefaultByUser($this->getUser());
                    if (null !== $requeteDefault) {
                        $requeteSession->setRequete($requeteDefault);
                        $choosenReferenceIds = $requeteSession->getReferenceIds();
                    }
                }
                if (count($choosenReferenceIds) === 0) {
                    $choosenReferenceIds = $this->container->get('hopitalnumerique_account.doctrine.reference.contexte')->getReferenceIds();
                }
            }
        }

        return $this->render('HopitalNumeriqueRechercheBundle:Referencement:index.html.twig', [
            'recherches' => $this->getDoctrine()->getRepository(Requete::class)->findBy(['user' => $this->getUser()]),
            'referencesTree' => $referencesTree,
            'requete' => $requeteSession->getRequete(),
            'categoriesProperties' => $this->container->get('hopitalnumerique_recherche.doctrine.referencement.category')->getCategoriesProperties(),
            'choosenReferenceIds' => $choosenReferenceIds,
            'entityTypeIds' => $requeteSession->getEntityTypeIds(),
            'publicationCategoryIds' => $requeteSession->getPublicationCategoryIds(),
            'searchedText' => $requeteSession->getSearchedText(),
            'domaines' => $this->container->get('hopitalnumerique_domaine.manager.domaine')->getAllArray(),
            'exaleadIsActivated' => $this->container->get('hopitalnumerique_recherche.manager.search')->getActivationExalead(),
            'openRequeteSavingPopin' => $openRequeteSavingPopin,
        ]);
    }

    /**
     * Retourne les entités trouvées selon les références choisies.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function jsonEntitiesByReferencesAction(Request $request)
    {
        $entityTypeIds = $request->request->get('entityTypeIds', null);
        $publicationCategoryIds = $request->request->get('publicationCategoryIds', null);
        $exaleadSearchedText = $request->request->get('exaleadSearch', null);
        $foundWords = [];
        $resultFilters = [];

        $reader = $this->container->get(
            'hopitalnumerique_recherche.doctrine.referencement.reader'
        );

        $reader->setIsSearchedText(null !== $exaleadSearchedText);

        if (null !== $exaleadSearchedText) { // Recherche Exalead
            $groupedReferenceIds = $request->request->get('references', null);
            $exaleadSearch = $this->container->get(
                'hopitalnumerique_recherche.dependency_injection.referencement.exalead.search'
            );
            $exaleadSearch->setText($exaleadSearchedText);
            $resultFilters['objetIds'] = $exaleadSearch->getObjetIds();
            $resultFilters['contenuIds'] = $exaleadSearch->getContenuIds();

            // Si autre filtre qu'Exalead
            if ((null !== $groupedReferenceIds && count($groupedReferenceIds) > 0) || (null !== $publicationCategoryIds && count($publicationCategoryIds) > 0)) {
                $exaleadEntitiesPropertiesByGroup = $exaleadSearch->getEntitiesPropertiesByGroup();
                $dbEntitiesPropertiesByGroup = $reader->getEntitiesPropertiesByReferenceIdsByGroup($groupedReferenceIds, $entityTypeIds, $publicationCategoryIds, $resultFilters);

                if ((null !== $groupedReferenceIds && count($groupedReferenceIds) > 0)) { // Cas où on a au moins une référence
                    $exaleadEntitiesPropertiesByGroup = $exaleadSearch->mergeEntitiesPropertiesByGroup($exaleadEntitiesPropertiesByGroup, $dbEntitiesPropertiesByGroup, true);
                } else { // Pas de référence, filtrer uniquement Exalead sur ses catégories
                    $exaleadEntitiesPropertiesByGroup = $exaleadSearch->mergeEntitiesPropertiesByGroup($exaleadEntitiesPropertiesByGroup, $dbEntitiesPropertiesByGroup, false);
                    $exaleadEntitiesPropertiesByGroup = $exaleadSearch->deleteOthersCategoriesFromEntitiesPropertiesByGroup($exaleadEntitiesPropertiesByGroup, $publicationCategoryIds);
                }
                $entitiesPropertiesByGroup = $exaleadEntitiesPropertiesByGroup;
            } else {
                $entitiesPropertiesByGroup = $exaleadSearch->getEntitiesPropertiesByGroup();
            }
            $foundWords = $exaleadSearch->getFoundWords();
        } else {
            $groupedReferenceIds = $request->request->get('references', []);
            $entitiesPropertiesByGroup = $reader->getEntitiesPropertiesByReferenceIdsByGroup($groupedReferenceIds, $entityTypeIds, $publicationCategoryIds, $resultFilters);
        }

        $rolesAllowedToAccessFrontReferencement = [
            'ROLE_ADMINISTRATEUR_1',
            'ROLE_ADMINISTRATEUR_DE_DOMAINE_106',
            'ROLE_ADMINISTRATEUR_DU_DOMAINE_HN_107',
        ];

        $showCog = false;

        if ($this->getUser() instanceof User) {
            if (in_array($this->getUser()->getRole(), $rolesAllowedToAccessFrontReferencement)) {
                $showCog = true;
            }
        }

        return new JsonResponse([
            'results' => $entitiesPropertiesByGroup,
            'foundWords' => $foundWords,
            'showCog' => $showCog,
        ]);
    }

    /**
     * Retourne les entités résultats.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function jsonEntitiesAction(Request $request)
    {
        $rolesAllowedToAccessFrontReferencement = [
            'ROLE_ADMINISTRATEUR_1',
            'ROLE_ADMINISTRATEUR_DE_DOMAINE_106',
            'ROLE_ADMINISTRATEUR_DU_DOMAINE_HN_107',
        ];

        $showCog = false;
        if ($this->getUser() instanceof User
            && in_array(
                $this->getUser()->getRole(),
                $rolesAllowedToAccessFrontReferencement
            )
        ) {
            $showCog = true;
        }

        $entitiesByType = $request->request->get('entitiesByType');

        foreach ($entitiesByType as $entityType => $entitiesPropertiesById) {
            $cartItemType = $this->get('hopitalnumerique\cartbundle\service\getcartabletype')
                ->getCartableType($entityType);

            $entityIds = array_keys($entitiesPropertiesById);

            $entities = $this->container->get('hopitalnumerique_core.dependency_injection.entity')
                ->getEntitiesByTypeAndIds($entityType, $entityIds);
            $dependencyInjectionEntity = $this->get('hopitalnumerique_core.dependency_injection.entity');
            foreach ($entities as $entity) {
                $entityId = $this->container->get('hopitalnumerique_core.dependency_injection.entity')
                    ->getEntityId($entity);
                $subtitle = $dependencyInjectionEntity->getSubtitleByEntity($entity);

                $objetId = null;
                $objet = $this->get('hopitalnumerique_objet.repository.objet')->find($entityId);
                $objetContenu = $this->get('hopitalnumerique_objet.repository.contenu')->find($entityId);

                if (null !== $objetContenu && !empty($objetContenu->getObjet()->getSynthese()) && null !== $subtitle) {
                        $objetId = $objetContenu->getObjet()->getId();
                } elseif (null !== $objet && !empty($objet->getSynthese())) {
                    $objetId = $objet->getId();
                }

                $entitiesByType[$entityType][$entityId]['viewHtml'] =
                    $this->renderView(
                        'HopitalNumeriqueRechercheBundle:Referencement:view_entity.html.twig',
                        [
                            'id' => $dependencyInjectionEntity->getEntityId($entity),
                            'type' => $dependencyInjectionEntity->getEntityType($entity),
                            'category' => $dependencyInjectionEntity->getCategoryByEntity($entity),
                            'title' => $dependencyInjectionEntity->getTitleByEntity($entity),
                            'subtitle' => $subtitle,
                            'url' => $dependencyInjectionEntity->getFrontUrlByEntity($entity),
                            'description' => $dependencyInjectionEntity->getDescriptionByEntity($entity),
                            'pertinenceNiveau' => $entitiesPropertiesById[$entityId]['pertinenceNiveau'],
                            'source' => $dependencyInjectionEntity->getSourceByEntity($entity),
                            'showCog' => $showCog,
                            'cartItemType' => $cartItemType,
                            'objetId' => $objetId,
                        ]
                    );
            }
        }

        return new JsonResponse($entitiesByType);
    }
}
