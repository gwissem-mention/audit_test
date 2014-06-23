<?php

namespace HopitalNumerique\AccountBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    /**
     * Index Action
     */
    public function indexAction()
    {
        //On récupère l'user connecté
        $user = $this->get('security.context')->getToken()->getUser();
        
        //get Flash messages visible for this user
        $messages = $this->get('hopitalnumerique_flash.manager.flash')->getMessagesForUser( $user );

        return $this->render('HopitalNumeriqueAccountBundle:Default:index.html.twig', array(
            'messages' => $messages
        ));
    }
}