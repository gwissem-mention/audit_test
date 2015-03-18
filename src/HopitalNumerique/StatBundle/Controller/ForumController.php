<?php

namespace HopitalNumerique\StatBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author    Yann 'YRO' Rochereau
 * @copyright Nodevo
 */
class ForumController extends Controller
{
    /**
     * Affiche les tableaux des statistiques
     */
    public function indexAction( )
    {
        return $this->render('HopitalNumeriqueStatBundle:Back:partials/Forum/bloc.html.twig', array());
    }

    /**
     * Génération du tableau à exporter
     *
     * @param  Symfony\Component\HttpFoundation\Request  $request
     * 
     * @return View
     */
    public function generateTableauAction( Request $request )
    {
        //Récupération de la requete
        $dateDebut    = $request->request->get('datedebut-forum');
        $dateFin      = $request->request->get('dateFin-forum');

        //Récupération des dates sous forme DateTime
        $dateDebutDateTime = $dateDebut === "" ? null : new \DateTime($dateDebut);
        $dateFinDateTime   = $dateFin   === "" ? null : new \DateTime($dateFin);

        $stats = $this->get('hopitalnumerique_stat.manager.statrecherche')->getStatsForum($dateDebutDateTime, $dateFinDateTime);
        
        return $this->render('HopitalNumeriqueStatBundle:Back:partials/Forum/tableau.html.twig', array(
            'stats' => $stats
        ));
    }
}
