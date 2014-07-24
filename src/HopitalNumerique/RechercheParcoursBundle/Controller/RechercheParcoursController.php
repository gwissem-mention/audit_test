<?php

namespace HopitalNumerique\RechercheParcoursBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class RechercheParcoursController extends Controller
{
    /**
     * Index Action
     */
    public function indexAction()
    {
        $recherchesParcours = $this->get('hopitalnumerique_recherche_parcours.manager.recherche_parcours')->findBy(array(), array('order' => 'ASC'));

        return $this->render('HopitalNumeriqueRechercheParcoursBundle:RechercheParcours:index.html.twig', array(
            'recherchesParcours' => $recherchesParcours
        ));
    }
}