<?php
namespace HopitalNumerique\RechercheBundle\Controller\Referencement;

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
     * Popin de sauvegarde.
     */
    public function popinSaveAction(Request $request)
    {
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
