<?php

namespace HopitalNumerique\ModuleBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Inscription controller.
 * 
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class InscriptionController extends Controller
{
    /**
     * Affiche la liste des Inscription.
     *
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function indexAction(\HopitalNumerique\ModuleBundle\Entity\Session $session)
    {
        $grid = $this->get('hopitalnumerique_module.grid.inscription');
        $grid->setSourceCondition('session', $session->getId());

        return $grid->render('HopitalNumeriqueModuleBundle:Inscription:index.html.twig', array('session' => $session));
    }
    
    /**
     * Passe l'état de l'inscription à 'acceptée'
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function accepterInscriptionAction(\HopitalNumerique\ModuleBundle\Entity\Inscription $inscription)
    {
        $refRefuse = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array('id'=> 332) );
        
        $inscription->setEtatInscription($refRefuse);

        //Envoyer mail d'acceptation de l'inscription
        $mail = $this->get('nodevo_mail.manager.mail')->sendAcceptationInscriptionMail($inscription->getUser(),array(
                    'date'      => $inscription->getSession()->getDateSession()->format('d/m/Y'),
                    'module'    => $inscription->getSession()->getModule()->getTitre()
                ));
        $this->get('mailer')->send($mail);
        
        //Suppression de l'entitée
        $this->get('hopitalnumerique_module.manager.inscription')->save( $inscription );
    
        $this->get('session')->getFlashBag()->add('info', 'L\'inscription de ' . $inscription->getUser()->getAppellation() . ' est acceptée.');
    
        return $this->redirect( $this->generateUrl('hopitalnumerique_module_module_session_inscription', array('id' => $inscription->getSession()->getId())) );
    }
    
    /**
     * Passe l'état de l'inscription à 'refusée'
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function refuserInscriptionAction(\HopitalNumerique\ModuleBundle\Entity\Inscription $inscription)
    {
        $refRefuse = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array('id'=> 333) );
    
        $inscription->setEtatInscription($refRefuse);

        //Envoyer mail de refus de l'inscription
        $mail = $this->get('nodevo_mail.manager.mail')->sendRefusInscriptionMail($inscription->getUser(),array(
                    'date'      => $inscription->getSession()->getDateSession()->format('d/m/Y'),
                    'module'    => $inscription->getSession()->getModule()->getTitre()
                ));
        $this->get('mailer')->send($mail);
    
        //Suppression de l'entitée
        $this->get('hopitalnumerique_module.manager.inscription')->save( $inscription );
    
        $this->get('session')->getFlashBag()->add('info', 'L\'inscription de ' . $inscription->getUser()->getAppellation() . ' est refusée.');
    
        return $this->redirect( $this->generateUrl('hopitalnumerique_module_module_session_inscription', array('id' => $inscription->getSession()->getId())) );
    }
    
    /**
     * Passe l'état de l'inscription à 'annulée'
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function annulerInscriptionAction(\HopitalNumerique\ModuleBundle\Entity\Inscription $inscription)
    {
        $refAnnule = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array('id'=> 334) );
    
        $inscription->setEtatInscription($refAnnule);
    
        //Suppression de l'entitée
        $this->get('hopitalnumerique_module.manager.inscription')->save( $inscription );
    
        $this->get('session')->getFlashBag()->add('info', 'L\'inscription de ' . $inscription->getUser()->getAppellation() . ' est annulée.');
    
        return $this->redirect( $this->generateUrl('hopitalnumerique_module_module_session_inscription', array('id' => $inscription->getSession()->getId())) );
    }

}