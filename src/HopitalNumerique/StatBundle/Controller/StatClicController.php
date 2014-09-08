<?php

namespace HopitalNumerique\StatBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class StatClicController extends Controller
{
    /**
     * Affiche les tableaux des statistiques
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function indexAction( )
    {
        return $this->render('HopitalNumeriqueStatBundle:Back:partials/StatClic/bloc.html.twig', array());
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
        $dateDebut    = $request->request->get('datedebut-statClic');
        $dateFin      = $request->request->get('dateFin-statClic');

        //Récupération des dates sous forme DateTime
        $dateDebutDateTime = $dateDebut === "" ? null : new \DateTime($dateDebut);
        $dateFinDateTime   = $dateFin   === "" ? null : new \DateTime($dateFin);

        $statClics = $this->get('hopitalnumerique_recherche.manager.statClic')->getNbNoteByReponse($dateDebutDateTime, $dateFinDateTime);
        
        return $this->render('HopitalNumeriqueStatBundle:Back:partials/StatClic/tableau.html.twig', array(
            'statClics' => $statClics
        ));
    }
}
