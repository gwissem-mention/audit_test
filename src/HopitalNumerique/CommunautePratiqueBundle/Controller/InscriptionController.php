<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Controller;

use HopitalNumerique\CommunautePratiqueBundle\Domain\Command\DisenrollUserCommand;
use HopitalNumerique\CommunautePratiqueBundle\Domain\Command\DisenrollUserHandler;
use HopitalNumerique\CommunautePratiqueBundle\Domain\Command\EnrollUserCommand;
use HopitalNumerique\CommunautePratiqueBundle\Domain\Command\EnrollUserHandler;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Contrôleur gérant l'inscription à la communauté de pratique.
 */
class InscriptionController extends Controller
{
    /**
     * Inscrit l'utilisateur connecté.
     */
    public function ajaxInscritAction()
    {
        $user = $this->getUser();

        if (null !== $user
            && !$this->get('hopitalnumerique_communautepratique.dependency_injection.inscription')
                 ->hasInformationManquante($user)
        ) {
            if (!$user->isInscritCommunautePratique()) {
                $this->get(EnrollUserHandler::class)->handle(new EnrollUserCommand($user));
                $this->addFlash(
                    'success',
                    'L\'inscription à la communauté de pratique a été confirmée.'
                );
            }

            return new JsonResponse([
                'url' => $this->generateUrl('hopitalnumerique_communautepratique_accueil_index'),
            ]);
        } else {
            $this->addFlash(
                'danger',
                'L\'inscription à la communauté de pratique a échouée. Veuillez vérifier vos informations.'
            );

            return new JsonResponse([
                'url' => $this->get('communautepratique_router')->getUrl(),
            ]);
        }
    }

    /**
     * @return RedirectResponse
     */
    public function inscriptionAction()
    {
        $user = $this->getUser();

        if (null !== $user
            && !$this->get('hopitalnumerique_communautepratique.dependency_injection.inscription')
                ->hasInformationManquante($user)
        ) {
            if (!$user->isInscritCommunautePratique()) {
                $this->get(EnrollUserHandler::class)->handle(new EnrollUserCommand($user));
                $this->addFlash('success', 'L\'inscription à la communauté de pratique a été confirmée.');
            }

            return $this->redirectToRoute('hopitalnumerique_communautepratique_accueil_index');
        } else {
            $this->addFlash(
                'danger',
                'L\'inscription à la communauté de pratique a échouée. Veuillez vérifier vos informations.'
            );

            return $this->redirect($this->get('communautepratique_router')->getUrl());
        }
    }

    /**
     * Désinscrit l'utilisateur connecté.
     */
    public function ajaxDesinscritAction()
    {
        $user = $this->getUser();

        if (null !== $user) {
            $this->get(DisenrollUserHandler::class)->handle(new DisenrollUserCommand($user));
            $this->addFlash('success', 'Vous avez bien quitté la communauté. Vous pouvez vous y ré-inscrire à tout moment, merci de votre participation !');

            return new JsonResponse([
                'url' => $this->get('communautepratique_router')->getUrl(),
            ]);
        } else {
            $this->addFlash('danger', 'La désinscription de la communauté de pratique a échouée.');

            return new JsonResponse(['url' => $this->generateUrl('hopital_numerique_homepage')]);
        }
    }
}
