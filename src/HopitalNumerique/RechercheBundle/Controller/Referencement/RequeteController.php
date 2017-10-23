<?php

namespace HopitalNumerique\RechercheBundle\Controller\Referencement;

use HopitalNumerique\CoreBundle\DependencyInjection\Entity;
use HopitalNumerique\RechercheBundle\Entity\Requete;
use HopitalNumerique\RechercheBundle\Form\Type\RequeteType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Contrôleur concernant la sauvegarde des requêtes de la recherche par référencement.
 */
class RequeteController extends Controller
{
    /**
     * Visualiser une requête.
     *
     * @param Requete $requete
     *
     * @return RedirectResponse
     */
    public function viewAction(Requete $requete)
    {
        if (null !== $this->getUser() && $this->getUser()->equals($requete->getUser())) {
            $this->container->get('hopitalnumerique_recherche.dependency_injection.referencement.requete_session')->setRequete($requete);
        }

        return $this->redirectToRoute('hopital_numerique_recherche_homepage');
    }

    /**
     * Popin de détails.
     *
     * @param Requete $requete
     *
     * @return Response
     */
    public function popinDetailAction(Requete $requete)
    {
        $currentDomaine = $this->container->get('hopitalnumerique_domaine.dependency_injection.current_domaine')->get();
        $referencesTree = $this->container->get('hopitalnumerique_reference.dependency_injection.reference.tree')->getOrderedReferences(null, null, [$currentDomaine], true);

        $filtreCategoryLabels = [];
        if (null !== $requete->getEntityTypeIds()) {
            foreach ($requete->getEntityTypeIds() as $entityTypeId) {
                switch ($entityTypeId) {
                    case Entity::ENTITY_TYPE_FORUM_TOPIC:
                        $filtreCategoryLabels[] = $this->get('hopitalnumerique_reference.manager.reference')
                            ->findOneById($this->container->getParameter('ref_forum_topic_id'))
                        ;
                        break;
                    case Entity::ENTITY_TYPE_AMBASSADEUR:
                        $filtreCategoryLabels[] = $this->get('hopitalnumerique_reference.manager.reference')
                            ->findOneById($this->container->getParameter('ref_ambassadeur_id'))
                        ;
                        break;
                    case Entity::ENTITY_TYPE_RECHERCHE_PARCOURS:
                        $filtreCategoryLabels[] = $this->get('hopitalnumerique_reference.manager.reference')
                            ->findOneById($this->container->getParameter('ref_recherche_parcours_id'))
                        ;
                        break;
                    case Entity::ENTITY_TYPE_COMMUNAUTE_PRATIQUES_GROUPE:
                        $filtreCategoryLabels[] = $this->get('hopitalnumerique_reference.manager.reference')
                            ->findOneById($this->container->getParameter('ref_com_pratique_id'))
                        ;
                        break;
                }
            }
        }
        foreach ($this->container->get('hopitalnumerique_reference.manager.reference')
                     ->findBy(['id' => $requete->getPublicationCategoryIds()]) as $publicationCategory) {
            $filtreCategoryLabels[] = $publicationCategory->getLibelle();
        }

        $allReferences = $this->container->get('hopitalnumerique_reference.manager.reference')->getAllIndexedById();

        $references = [];
        foreach ($requete->getRefs() as $reference) {
            if (isset($allReferences[$reference])) {
                $references[] = $allReferences[$reference];
            }
        }

        return $this->render('HopitalNumeriqueRechercheBundle:Referencement\Requete:popin_detail.html.twig', [
            'referencesTree' => $referencesTree,
            'requete' => $requete,
            'references' => $references,
            'filtreCategoryLabels' => $filtreCategoryLabels,
        ]);
    }

    /**
     * Popin de sauvegarde.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function popinSaveAction(Request $request)
    {
        $searches = $this->container->get('hopitalnumerique_recherche.repository.requete')->getSavedSearchesByUser(
            $this->container->get('security.token_storage')->getToken()->getUser(),
            $this->container->get('hopitalnumerique_domaine.dependency_injection.current_domaine')->get()
        );

        $this->container->get('hopitalnumerique_recherche.dependency_injection.referencement.requete_session')->setWantToSaveRequete(true);
        $requete = $this->container->get('hopitalnumerique_recherche.dependency_injection.referencement.requete_session')->getRequete();
        if (null === $requete) {
            $requete = $this->container->get('hopitalnumerique_recherche.manager.requete')->createEmpty();
        }
        $requeteForm = null;
        if (null !== $this->getUser()) {
            $requeteForm = $this->createForm(RequeteType::class, $requete);
            $requeteForm->handleRequest($request);
        }

        return $this->render('HopitalNumeriqueRechercheBundle:Referencement\Requete:popin_save.html.twig', [
            'requete' => $requete,
            'requeteForm' => (null !== $requeteForm ? $requeteForm->createView() : null),
            'nbRecherches' => (count($searches) + 1),
        ]);
    }

    /**
     * Enregistre la requête.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function saveAction(Request $request)
    {
        if ($request->request->has('save-as-new')) {
            $requete = null;
        } else {
            $requete = $this->container->get('hopitalnumerique_recherche.dependency_injection.referencement.requete_session')->getRequete();
        }
        if (null === $requete) {
            $requete = $this->container->get('hopitalnumerique_recherche.manager.requete')->createEmpty();
            $requete->setUser($this->getUser());
            $requete->setDomaine($this->container->get('hopitalnumerique_domaine.dependency_injection.current_domaine')->get());
        }

        $requeteForm = $this->createForm(RequeteType::class, $requete);
        $requeteForm->handleRequest($request);

        if ($requeteForm->isValid()) {
            $this->container->get('hopitalnumerique_recherche.dependency_injection.referencement.requete_session')->saveRequete($requete);
            $this->addFlash('success', 'Recherche enregistrée.');
        }

        return $this->redirectToRoute('hopital_numerique_recherche_homepage');
    }

    /**
     * Enregistre la session de la requête.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function saveSessionAction(Request $request)
    {
        $referenceIds = $request->request->get('referenceIds', []);
        $entityTypesIds = $request->request->get('entityTypesIds', null);
        $publicationCategoryIds = $request->request->get('publicationCategoryIds', null);
        $searchedText = $request->request->get('searchedText', null);
        $resultsCount = $request->request->get('resultsCount', 0);

        $this->container->get('hopitalnumerique_recherche.dependency_injection.referencement.requete_session')->setReferenceIds($referenceIds);
        $this->container->get('hopitalnumerique_recherche.dependency_injection.referencement.requete_session')->setEntityTypeIds($entityTypesIds);
        $this->container->get('hopitalnumerique_recherche.dependency_injection.referencement.requete_session')->setPublicationCategoryIds($publicationCategoryIds);
        $this->container->get('hopitalnumerique_recherche.dependency_injection.referencement.requete_session')->setSearchedText($searchedText);
        $this->container->get('hopitalnumerique_recherche.dependency_injection.referencement.requete_session')->saveStatistique($resultsCount);


        return new JsonResponse(['success' => true]);
    }

    /**
     * Supprime la requête en session.
     */
    public function removeSessionAction()
    {
        $this->container->get('hopitalnumerique_recherche.dependency_injection.referencement.requete_session')->remove();

        return new JsonResponse(['success' => true]);
    }
}
