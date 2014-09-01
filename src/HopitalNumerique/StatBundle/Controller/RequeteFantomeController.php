<?php

namespace HopitalNumerique\StatBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Nodevo\ToolsBundle\Tools\Curl as NodevoCurl;

class RequeteFantomeController extends Controller
{
    /**
     * Affiche les statistiques des items de requete
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo™
     */
    public function indexAction( )
    {
        return $this->render('HopitalNumeriqueStatBundle:Back:partials/RequeteFantome/bloc.html.twig', array());
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
        $dateDebut    = $request->request->get('datedebut-requeteFantom');
        $dateFin      = $request->request->get('dateFin-requeteFantom');

        //Récupération des dates sous forme DateTime
        $dateDebutDateTime = $dateDebut === "" ? null : new \DateTime($dateDebut);
        $dateFinDateTime   = $dateFin   === "" ? null : new \DateTime($dateFin);

        $requeteFantomes = $this->get('hopitalnumerique_stat.manager.statrecherche')->getStatFantome($dateDebutDateTime, $dateFinDateTime);

        $elements = $this->get('hopitalnumerique_reference.manager.reference')->getArboFormat(false, false, true);
        
        return $this->render('HopitalNumeriqueStatBundle:Back:partials/RequeteFantome/tableau.html.twig', array(
            'requeteFantomes' => $requeteFantomes,
            'elements'        => $elements['CATEGORIES_RECHERCHE']
        ));
    }
}