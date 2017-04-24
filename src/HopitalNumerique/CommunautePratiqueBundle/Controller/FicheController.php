<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Fiche;
use Symfony\Component\HttpFoundation\Response;

/**
 * Contrôleur concernant les fiches.
 */
class FicheController extends Controller
{
    /**
     * Visualisation d'une fiche.
     *
     * @param Fiche $fiche
     *
     * @return RedirectResponse|Response
     */
    public function viewAction(Fiche $fiche)
    {
        if (!$this->container->get('hopitalnumerique_communautepratique.dependency_injection.security')
            ->canAccessFiche($fiche)) {
            return $this->redirect($this->generateUrl('hopital_numerique_homepage'));
        }

        return $this->render('HopitalNumeriqueCommunautePratiqueBundle:Fiche:view.html.twig', [
            'fiche' => $fiche,
            'canClose' => $this->container->get('hopitalnumerique_communautepratique.dependency_injection.security')
                ->canCloseFiche($fiche),
            'canOpen' => $this->container->get('hopitalnumerique_communautepratique.dependency_injection.security')
                ->canOpenFiche($fiche),
        ]);
    }

    /**
     * Ajout d'une fiche.
     *
     * @param Groupe  $groupe
     * @param Request $request
     *
     * @return RedirectResponse|Response
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
     *
     * @param Fiche   $fiche
     * @param Request $request
     *
     * @return RedirectResponse|Response
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
                    'hopitalnumerique_communautepratique_fiche_view',
                    ['fiche' => $fiche->getId()]
                ));
            } else {
                $this->container->get('session')->getFlashBag()->add('danger', 'Fiche non enregistrée.');
            }
        }

        return $this->render('HopitalNumeriqueCommunautePratiqueBundle:Fiche:edit.html.twig', [
            'ficheFormulaire' => $ficheFormulaire->createView(),
            'fiche' => $fiche,
        ]);
    }

    /**
     * Suppression d'une fiche.
     *
     * @param Fiche $fiche
     *
     * @return RedirectResponse
     */
    public function deleteAction(Fiche $fiche)
    {
        $groupe = $fiche->getGroupe();

        if (!$this->get('hopitalnumerique_communautepratique.dependency_injection.security')
            ->canDeleteFiche($fiche)
        ) {
            $this->addFlash('danger', 'Vous n\'avez pas les droits pour supprimer cette fiche.');
        }

        $this->get('hopitalnumerique_communautepratique.manager.fiche')->delete($fiche);
        $this->addFlash('success', 'Fiche supprimée.');

        return $this->redirect(
            $this->generateUrl('hopitalnumerique_communautepratique_groupe_view', ['groupe' => $groupe->getId()])
        );
    }

    /**
     * Résout la fiche.
     *
     * @param Fiche $fiche
     *
     * @return JsonResponse
     */
    public function closeAction(Fiche $fiche)
    {
        if (!$this->get('hopitalnumerique_communautepratique.dependency_injection.security')
            ->canCloseFiche($fiche)
        ) {
            return new JsonResponse(['success' => false]);
        }

        $fiche->setResolu(true);
        $this->get('hopitalnumerique_communautepratique.manager.fiche')->save($fiche);

        return new JsonResponse(['success' => true]);
    }

    /**
     * Rouvre la fiche.
     *
     * @param Fiche $fiche
     *
     * @return JsonResponse
     */
    public function openAction(Fiche $fiche)
    {
        if (!$this->get('hopitalnumerique_communautepratique.dependency_injection.security')
            ->canOpenFiche($fiche)) {
            return new JsonResponse(['success' => false]);
        }

        $fiche->setResolu(false);
        $this->get('hopitalnumerique_communautepratique.manager.fiche')->save($fiche);

        return new JsonResponse(['success' => true]);
    }
}
