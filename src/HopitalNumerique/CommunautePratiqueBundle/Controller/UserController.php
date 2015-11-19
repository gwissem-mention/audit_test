<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Controller;

/**
 * Contrôleur des utilisateurs.
 */
class UserController extends \Symfony\Bundle\FrameworkBundle\Controller\Controller
{
    /**
     * Affiche tous les membres de la communauté.
     */
    public function listAction($page = 1)
    {
        $rechercheForm = $this->createForm('hopitalnumerique_communautepratiquebundle_user_recherche');

        return $this->render('HopitalNumeriqueCommunautePratiqueBundle:User:list.html.twig', array(
            'rechercheForm' => $rechercheForm->createView(),
            'pagerFantaMembres' => $this->container->get('hopitalnumerique_communautepratique.dependency_injection.annuaire')->getPagerfantaUsers($page)
        ));
    }
}
