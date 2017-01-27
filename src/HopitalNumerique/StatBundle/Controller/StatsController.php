<?php

namespace HopitalNumerique\StatBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class StatsController extends Controller
{
    public function showAction()
    {
        return $this->render('HopitalNumeriqueStatBundle:Back:show.html.twig', []);
    }
}
