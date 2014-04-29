<?php

namespace HopitalNumerique\ModuleBundle\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SessionFrontController extends Controller
{
    /**
     * Affiche la description d'une session dans une popin
     *
     * @param Session $session Session à afficher
     */
    public function descriptionAction(\HopitalNumerique\ModuleBundle\Entity\Session $session )
    {
        return $this->render('HopitalNumeriqueModuleBundle:Front/Module:description.html.twig', array(
                'session' => $session
        ));
    }

}
