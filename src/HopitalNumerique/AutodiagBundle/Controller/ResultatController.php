<?php

namespace HopitalNumerique\AutodiagBundle\Controller;

use HopitalNumerique\AutodiagBundle\Entity\Outil;
use HopitalNumerique\AutodiagBundle\Entity\Resultat;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Resultat controller.
 */
class ResultatController extends Controller
{
    /**
     * Affiche la liste des Resultats.
     */
    public function indexAction(Outil $outil)
    {
        $grid = $this->get('hopitalnumerique_autodiag.grid.resultat');
        $grid->setSourceCondition('outil', $outil->getId() );

        return $grid->render('HopitalNumeriqueAutodiagBundle:Resultat:index.html.twig', array('outil'=>$outil));
    }

    /**
     * Affiche le détail d'un résultat
     */
    public function detailAction( Resultat $resultat )
    {
        $tab = $this->get('hopitalnumerique_autodiag.manager.resultat')->formateResultat( $resultat );

        return $this->render( 'HopitalNumeriqueAutodiagBundle:Resultat:detail.html.twig' , array(
            'resultat'  => $resultat,
            'chapitres' => $tab['back']
        ));
    }
}