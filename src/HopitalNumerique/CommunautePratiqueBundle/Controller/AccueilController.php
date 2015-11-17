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
            array()
        );
    }
}
