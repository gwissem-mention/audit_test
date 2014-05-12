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
        $inscription->setEtatInscription(   $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array('id' => 406) ) );
        $inscription->setEtatParticipation( $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array('id' => 410) ) );
        $inscription->setEtatEvaluation(    $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array('id' => 413) ) );

        $form = $this->createForm('hopitalnumerique_module_inscription', $inscription);

        $request = $this->get('request');

        if ( $form->handleRequest($request)->isValid() ) 
        {
            $this->get('hopitalnumerique_module.manager.inscription')->save($inscription);

            // On envoi une 'flash' pour indiquer à l'utilisateur que le fichier n'existe pas: suppression manuelle sur le serveur
            $this->get('session')->getFlashBag()->add( ('success') , 'Votre inscription a été prise en compte.' );

            return $this->redirect($this->generateUrl('hopitalnumerique_module_session_informations_front', array( 'id' => $inscription->getSession()->getId() ) ));
        }

        return $this->render('HopitalNumeriqueModuleBundle:Front/Inscription:add.html.twig', array(
            'form'    => $form->createView(),
            'session' => $session
        ));
    }
}
