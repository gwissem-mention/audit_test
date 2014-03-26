<?php

namespace Nodevo\ContactBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    /**
     * Index Action
     */
    public function indexAction()
    {
        return $this->render('NodevoContactBundle:Default:index.html.twig', array());
    }
}