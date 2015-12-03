<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Fiche;

/**
 * Contrôleur concernant les fiches.
 */
class FicheController extends Controller
{
    /**
     * Visualisation d'une fiche.
     */
    public function viewAction(Fiche $fiche)
    {
        if (!$this->container->get('hopitalnumerique_communautepratique.dependency_injection.security')
            ->canAccessFiche($fiche)) {
            return $this->redirect($this->generateUrl('hopital_numerique_homepage'));
        }

        return $this->render('HopitalNumeriqueCommunautePratiqueBundle:Fiche:view.html.twig', array(
            'fiche' => $fiche,
            'canClose' => $this->container->get('hopitalnumerique_communautepratique.dependency_injection.security')
                ->canCloseFiche($fiche),
            'canOpen' => $this->container->get('hopitalnumerique_communautepratique.dependency_injection.security')
                ->canOpenFiche($fiche)
        ));
    }

    /**
     * Ajout d'une fiche
     */
    public function addAction(Groupe $groupe, Request $request)
    {
        if (!$this->container->get('hopitalnumerique_communautepratique.dependency_injection.security')
            ->canAccessGroupe($groupe)) {
            return $this->redirect($this->generateUrl('hopital_numerique_homepage'));
        }

        $nouvelleFiche = $this->container->get('hopitalnumerique_communautepratique.manager.fiche')->createEmpty();
        $nouvelleFiche->setGroupe($groupe);
        $nouvelleFiche->setUser($this->getUser());

        return $this->editAction($nouvelleFiche, $request);
    }

    /**
     * Édition d'une fiche.
     */
    public function editAction(Fiche $fiche, Request $request)
    {
        if (!$this->container->get('hopitalnumerique_communautepratique.dependency_injection.security')
            ->canEditFiche($fiche)) {
            return $this->redirect($this->generateUrl('hopital_numerique_homepage'));
        }

        $ficheFormulaire = $this->createForm('hopitalnumerique_communautepratiquebundle_fiche', $fiche);
        $ficheFormulaire->handleRequest($request);

        if ($ficheFormulaire->isSubmitted()) {
            if ($ficheFormulaire->isValid()) {
                $this->container->get('hopitalnumerique_communautepratique.manager.fiche')->save($fiche);
                $this->container->get('session')->getFlashBag()->add('success', 'Fiche enregistrée avec succès.');
                return $this->redirect($this->generateUrl(
                    'hopitalnumerique_communautepratique_groupe_view',
                    array('groupe' => $fiche->getGroupe()->getId())
                ));
            } else {
                $this->container->get('session')->getFlashBag()->add('danger', 'Fiche non enregistrée.');
            }
        }

        return $this->render('HopitalNumeriqueCommunautePratiqueBundle:Fiche:edit.html.twig', array(
            'ficheFormulaire' => $ficheFormulaire->createView(),
            'fiche' => $fiche,
            'documents' => $this->container->get('hopitalnumerique_communautepratique.manager.document')
                ->findByIndexedById(array('groupe' => $fiche->getGroupe(), 'user' => $this->getUser()))
        ));
    }

    /**
     * Résout la fiche.
     */
    public function closeAction(Fiche $fiche)
    {
        if (!$this->container->get('hopitalnumerique_communautepratique.dependency_injection.security')
            ->canCloseFiche($fiche)) {
            return new JsonResponse(array('success' => false));
        }

        $fiche->setResolu(true);
        $this->container->get('hopitalnumerique_communautepratique.manager.fiche')->save($fiche);

        return new JsonResponse(array('success' => true));
    }

    /**
     * Rouvre la fiche.
     */
    public function openAction(Fiche $fiche)
    {
        if (!$this->container->get('hopitalnumerique_communautepratique.dependency_injection.security')
            ->canOpenFiche($fiche)) {
            return new JsonResponse(array('success' => false));
        }

        $fiche->setResolu(false);
        $this->container->get('hopitalnumerique_communautepratique.manager.fiche')->save($fiche);

        return new JsonResponse(array('success' => true));
    }
}
