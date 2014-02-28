<?php

namespace HopitalNumerique\RechercheBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    /**
     * Index Action
     */
    public function indexAction()
    {
        return $this->render('HopitalNumeriqueRechercheBundle:Default:index.html.twig', array());
    }
}