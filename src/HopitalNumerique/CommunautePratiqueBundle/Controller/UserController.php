<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

/**
 * Contrôleur des utilisateurs.
 */
class UserController extends \Symfony\Bundle\FrameworkBundle\Controller\Controller
{
    /**
     * Affiche tous les membres de la communauté.
     */
    public function listAction($page = 1, Request $request)
    {
        $this->container->get('hopitalnumerique_communautepratique.dependency_injection.annuaire')->setFiltres($request);
        $rechercheForm = $this->createForm('hopitalnumerique_communautepratiquebundle_user_recherche');
        $rechercheForm->handleRequest($request);

        return $this->render('HopitalNumeriqueCommunautePratiqueBundle:User:list.html.twig', array(
            'rechercheForm' => $rechercheForm->createView(),
            'pagerFantaMembres' => $this->container->get('hopitalnumerique_communautepratique.dependency_injection.annuaire')->getPagerfantaUsers($page)
        ));
    }
}
