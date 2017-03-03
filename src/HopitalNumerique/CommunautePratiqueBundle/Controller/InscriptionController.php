<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Contrôleur gérant l'inscription à la communauté de pratique.
 */
class InscriptionController extends \Symfony\Bundle\FrameworkBundle\Controller\Controller
{
    /**
     * Inscrit l'utilisateur connecté.
     */
    public function ajaxInscritAction()
    {
        $user = $this->getUser();

        if (null !== $user && !$this->container
            ->get('hopitalnumerique_communautepratique.dependency_injection.inscription')
            ->hasInformationManquante($user)) {
            if (!$user->isInscritCommunautePratique()) {
                $user->setInscritCommunautePratique(true);
                $this->container->get('hopitalnumerique_user.manager.user')->save($user);
                $this->get('session')->getFlashBag()->add('success', 'L\'inscription à la communauté de pratique a été confirmée.');
            }

            return new JsonResponse([
                'url' => $this->generateUrl('hopitalnumerique_communautepratique_accueil_index'),
            ]);
        } else {
            $this->get('session')->getFlashBag()->add('danger', 'L\'inscription à la communauté de pratique a échouée. Veuillez vérifier vos informations.');

            return new JsonResponse([
                'url' => $this->get('communautepratique_router')->getUrl(),
            ]);
        }
    }

    /**
     * Désinscrit l'utilisateur connecté.
     */
    public function ajaxDesinscritAction()
    {
        $user = $this->getUser();

        if (null !== $user) {
            $this->container->get('hopitalnumerique_user.manager.user')->desinscritCommunautePratique($user);
            $this->get('session')->getFlashBag()->add('success', 'Vous avez bien quitté la communauté. Vous pouvez vous y ré-inscrire à tout moment, merci de votre participation !');

            return new JsonResponse([
                'url' => $this->get('communautepratique_router')->getUrl(),
            ]);
        } else {
            $this->get('session')->getFlashBag()
                ->add('danger', 'La désinscription de la communauté de pratique a échouée.');

            return new JsonResponse(['url' => $this->generateUrl('hopital_numerique_homepage')]);
        }
    }
}
