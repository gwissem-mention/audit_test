<?php

namespace HopitalNumerique\GlossaireBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    /**
     * Index Action
     */
    public function indexAction()
    {
        return $this->render('HopitalNumeriqueGlossaireBundle:Default:index.html.twig', array());
    }
}