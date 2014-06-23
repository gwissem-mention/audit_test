<?php

namespace HopitalNumerique\AdminBundle\Controller;

use Gedmo\Loggable\Entity\LogEntry;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * 
 */
class DefaultController extends Controller
{
    /**
     * Index Action
     */
    public function indexAction()
    {
        return $this->render('HopitalNumeriqueAdminBundle:Default:index.html.twig', array());
    }
}