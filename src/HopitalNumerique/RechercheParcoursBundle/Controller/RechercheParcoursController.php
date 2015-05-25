<?php

namespace HopitalNumerique\RechercheParcoursBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursGestion;

class RechercheParcoursController extends Controller
{
    /**
     * Index Action
     */
    public function indexAction(RechercheParcoursGestion $rechercheParcoursGestion)
    {
        $recherchesParcours = $this->get('hopitalnumerique_recherche_parcours.manager.recherche_parcours')->findBy(array('recherchesParcoursGestion' => $rechercheParcoursGestion), array('order' => 'ASC'));

        return $this->render('HopitalNumeriqueRechercheParcoursBundle:RechercheParcours:Back/index.html.twig', array(
            'recherchesParcours' => $recherchesParcours
        ));
    }

    /**
     * Met à jour l'ordre des différentes questions
     */
    public function reorderAction()
    {
        //get datas serialzed
        $datas = $this->get('request')->request->get('datas');

        //execute reorder
        $this->get('hopitalnumerique_recherche_parcours.manager.recherche_parcours')->reorder( $datas );
        $this->getDoctrine()->getManager()->flush();

        return new Response('{"success":true}', 200);
    }

    // ----------- FRONT --------------

    /**
     * Index du front Action
     */
    public function indexFrontAction(RechercheParcoursGestion $rechercheParcoursGestion)
    {
        //Tableau des étapes du projet
        $etapes = $this->get('hopitalnumerique_recherche_parcours.manager.recherche_parcours')->findBy(array('recherchesParcoursGestion' => $rechercheParcoursGestion), array('order' => 'ASC'));

        return $this->render('HopitalNumeriqueRechercheParcoursBundle:RechercheParcours:Front/index.html.twig', array(
            'etapes' => $etapes
        ));
    }
}