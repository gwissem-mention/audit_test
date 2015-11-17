<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Controller;

/**
 * Contrôleur concernant les groupes de la communauté de pratique.
 */
class GroupeController extends \Symfony\Bundle\FrameworkBundle\Controller\Controller
{
    /**
     * Affiche tous les groupes disponibles.
     */
    public function listAction()
    {
        $groupes = $this->container->get('hopitalnumerique_communautepratique.manager.groupe')->findAll();
        
        return $this->render(
            'HopitalNumeriqueCommunautePratiqueBundle:Groupe:list.html.twig',
            array(
                'groupes' => $groupes
            )
        );
    }
}
