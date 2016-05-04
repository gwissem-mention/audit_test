<?php
namespace HopitalNumerique\RechercheBundle\Controller\Referencement;

use HopitalNumerique\CoreBundle\DependencyInjection\Entity;
use HopitalNumerique\RechercheBundle\Entity\Requete;
use HopitalNumerique\RechercheBundle\Form\Type\RequeteType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Contrôleur concernant la sauvegarde des requêtes de la recherche par référencement.
 */
class RequeteController extends Controller
{
    /**
     * Visualiser une requête.
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
     */
    public function popinDetailAction(Requete $requete)
    {
        $currentDomaine = $this->container->get('hopitalnumerique_domaine.dependency_injection.current_domaine')->get();
        $referencesTree = $this->container->get('hopitalnumerique_reference.dependency_injection.reference.tree')->getOrderedReferences(null, [$currentDomaine], true);

        $filtreCategoryLabels = [];
        if (null !== $requete->getEntityTypeIds()) {
            foreach ($requete->getEntityTypeIds() as $entityTypeId) {
                switch ($entityTypeId) {
                    case Entity::ENTITY_TYPE_FORUM_TOPIC:
                        $filtreCategoryLabels[] = Entity::CATEGORY_FORUM_TOPIC_LABEL;
                        break;
                    case Entity::ENTITY_TYPE_AMBASSADEUR:
                        $filtreCategoryLabels[] = Entity::CATEGORY_AMBASSADEUR_LABEL;
                        break;
                    case Entity::ENTITY_TYPE_RECHERCHE_PARCOURS:
                        $filtreCategoryLabels[] = Entity::CATEGORY_RECHERCHE_PARCOURS_LABEL;
                        break;
                    case Entity::ENTITY_TYPE_COMMUNAUTE_PRATIQUES_GROUPE:
                        $filtreCategoryLabels[] = Entity::CATEGORY_COMMUNAUTE_PRATIQUES_GROUPE_LABEL;
                        break;
                }
            }
        }
        foreach ($this->container->get('hopitalnumerique_reference.manager.reference')->findBy(['id' => $requete->getPublicationCategoryIds()]) as $publicationCategory) {
            $filtreCategoryLabels[] = $publicationCategory->getLibelle();
        }

        return $this->render('HopitalNumeriqueRechercheBundle:Referencement\Requete:popin_detail.html.twig', [
            'referencesTree' => $referencesTree,
            'requete' => $requete,
            'filtreCategoryLabels' => $filtreCategoryLabels
        ]);
    }

    /**
     * Popin de sauvegarde.
     */
    public function popinSaveAction(Request $request)
    {
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
            'requeteForm' => (null !== $requeteForm ? $requeteForm->createView() : null)
        ]);
    }

    /**
     * Enregistre la requête.
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
            $this->addFlash('success', 'Requête enregistrée.');
        }

        return $this->redirectToRoute('hopital_numerique_recherche_homepage');
    }

    /**
     * Enregistre la session de la requête.
     */
    public function saveSessionAction(Request $request)
    {
        $referenceIds = $request->request->get('referenceIds', []);

        $this->container->get('hopitalnumerique_recherche.dependency_injection.referencement.requete_session')->setReferenceIds($referenceIds);
        $this->container->get('hopitalnumerique_recherche.dependency_injection.referencement.requete_session')->setEntityTypeIds($request->request->get('entityTypesIds', null));
        $this->container->get('hopitalnumerique_recherche.dependency_injection.referencement.requete_session')->setPublicationCategoryIds($request->request->get('publicationCategoryIds', null));

        return new JsonResponse(['success' => true]);
    }
}
