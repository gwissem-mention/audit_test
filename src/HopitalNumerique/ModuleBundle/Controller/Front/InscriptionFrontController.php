<?php

namespace HopitalNumerique\ModuleBundle\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class InscriptionFrontController extends Controller
{
    /**
     * Liste toutes les informations de la session
     *
     * @param HopitalNumeriqueModuleBundleEntitySession $session Session à laquelle l'inscription doit etre faite
     *
     * @return [type]
     */
    public function addAction(Request $request, \HopitalNumerique\ModuleBundle\Entity\Session $session )
    {
        //On récupère l'utilisateur qui est connecté
        $user = $this->get('security.context')->getToken()->getUser();

        //Création d'une nouvelle inscription
        $inscription = $this->get('hopitalnumerique_module.manager.inscription')->createEmpty();
        $inscription->setUser( $user );
        $inscription->setSession( $session );

        $form = $this->createForm('hopitalnumerique_module_inscription', $inscription);

        $request = $this->get('request');
        if ( $form->handleRequest($request)->isValid() ) 
        {
            $this->get('hopitalnumerique_module.manager.inscription')->save($inscription);

            return $this->redirect($this->generateUrl('hopitalnumerique_module_module_front'));
        }

        return $this->render('HopitalNumeriqueModuleBundle:Front/Inscription:add.html.twig', array(
            'form' => $form->createView()
        ));
    }
}
