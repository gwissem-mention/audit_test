<?php

namespace HopitalNumerique\MediaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    /**
     * Index Action
     */
    public function indexAction()
    {
        return $this->render('HopitalNumeriqueMediaBundle:Default:index.html.twig', array());
    }


    /**
     * Index Action
     */
    public function moxieAction()
    {
        return $this->render('HopitalNumeriqueMediaBundle:Default:moxie.html.twig', array());
    }
}