<?php

namespace HopitalNumerique\StatBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PointdurController extends Controller
{
    /**
     * Affiche les statistiques des points durs
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function indexAction( )
    {
        //Récupération de l'entité en fonction du paramètre
        //$module = $this->get('hopitalnumerique_module.manager.module')->findOneBy( array( 'id' => $id) );

        return $this->render('HopitalNumeriqueStatBundle:Back:partials/PointsDurs/bloc.html.twig', array(
            //'module' => $module,
        ));
    }
}
