<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Controller;

/**
 * Accueil de la communauté de pratiques.
 */
class AccueilController extends \Symfony\Bundle\FrameworkBundle\Controller\Controller
{
    /**
     * Accueil de la communauté de pratiques.
     */
    public function indexAction()
    {
        return $this->render(
            'HopitalNumeriqueCommunautePratiqueBundle:Accueil:index.html.twig',
            array(
                'totalMembres' => $this->container->get('hopitalnumerique_user.manager.user')
                    ->findCommunautePratiqueMembresCount(),
                'membres' => $this->container->get('hopitalnumerique_user.manager.user')
                    ->findCommunautePratiqueRandomMembres(9)
            )
        );
    }
}
