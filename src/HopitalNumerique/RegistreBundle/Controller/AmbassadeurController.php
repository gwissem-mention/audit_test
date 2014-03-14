<?php

namespace HopitalNumerique\RegistreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AmbassadeurController extends Controller
{
    /**
     * Index Action
     */
    public function indexAction()
    {
        return $this->render('HopitalNumeriqueRegistreBundle:Ambassadeur:index.html.twig', array());
    }
}