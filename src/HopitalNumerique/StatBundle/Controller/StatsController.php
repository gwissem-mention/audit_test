<?php

namespace HopitalNumerique\StatBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class StatsController extends Controller
{
    /**
     * Affiche les tableaux des statistiques
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function showAction( )
    {
        //Récupération de l'entité en fonction du paramètre
        //$module = $this->get('hopitalnumerique_module.manager.module')->findOneBy( array( 'id' => $id) );

        return $this->render('HopitalNumeriqueStatBundle:Back:show.html.twig', array(
            //'module' => $module,
        ));
    }
}
