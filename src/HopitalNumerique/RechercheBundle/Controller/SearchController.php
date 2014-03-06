<?php

namespace HopitalNumerique\RechercheBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SearchController extends Controller
{
    /**
     * Index Action
     */
    public function indexAction()
    {
        $elements = $this->get('hopitalnumerique_reference.manager.reference')->getArboFormat(false, false, true);

        return $this->render('HopitalNumeriqueRechercheBundle:Search:index.html.twig', array(
            'elements' => $elements['CATEGORIES_RECHERCHE']
        ));
    }

    /**
     * Retourne les résultats de la recherche
     */
    public function getResultsAction()
    {
        $references = $this->get('request')->request->get('references');
        $objets     = $this->get('hopitalnumerique_recherche.manager.search')->getObjetsForRecherche( $references );
        
        return $this->render('HopitalNumeriqueRechercheBundle:Search:getResults.html.twig', array(
            'objets' => $objets
        ));
    }
}