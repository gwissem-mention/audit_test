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
        return $this->render('HopitalNumeriqueStatBundle:Back:show.html.twig', array());
    }
}
