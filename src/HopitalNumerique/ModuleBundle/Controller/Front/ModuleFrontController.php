<?php

namespace HopitalNumerique\ModuleBundle\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ModuleFrontController extends Controller
{
    public function indexAction()
    {
        return $this->render('HopitalNumeriqueModuleBundle:Front/Module:index.html.twig', array());
    }

}
