<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Controller;

/**
 * Contrôleur concernant les publications de la communauté de pratique.
 */
class PublicationController extends \Symfony\Bundle\FrameworkBundle\Controller\Controller
{
    /**
     * Affiche toutes les publications disponibles.
     */
    public function listAction()
    {
        return $this->render(
            'HopitalNumeriqueCommunautePratiqueBundle:Publication:list.html.twig',
            array(
                'groupes' => $this->container->get('hopitalnumerique_communautepratique.manager.groupe')
                    ->findAll()
            )
        );
    }
}
