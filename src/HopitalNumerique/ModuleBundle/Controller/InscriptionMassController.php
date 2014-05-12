<?php

namespace HopitalNumerique\ModuleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use HopitalNumerique\ModuleBundle\HopitalNumeriqueModuleBundle;

/**
 * Inscription des actions de mass controller.
 * 
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class InscriptionMassController extends Controller
{
    /**
     * Action de masse sur l'état d'inscription
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Redirect
     */
    public function accepterInscriptionMassAction( $primaryKeys, $allPrimaryKeys )
    {
        return $this->toggleEtat($primaryKeys, $allPrimaryKeys, 407, 'inscription');
    }
    
    /**
     * Action de masse sur l'état d'inscription
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Redirect
     */
    public function refuserInscriptionMassAction( $primaryKeys, $allPrimaryKeys )
    {
        return $this->toggleEtat($primaryKeys, $allPrimaryKeys, 408, 'inscription');
    }
    
    /**
     * Action de masse sur l'état d'inscription
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Redirect
     */
    public function annulerInscriptionMassAction( $primaryKeys, $allPrimaryKeys )
    {
        return $this->toggleEtat($primaryKeys, $allPrimaryKeys, 409, 'inscription');
    }
    
    /**
     * Action de masse sur l'état de participation
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Redirect
     */
    public function aParticiperParticipationMassAction( $primaryKeys, $allPrimaryKeys )
    {
        return $this->toggleEtat($primaryKeys, $allPrimaryKeys, 411, 'participation');
    }
    
    /**
     * Action de masse sur l'état de participation
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Redirect
     */
    public function aPasParticiperParticipationMassAction( $primaryKeys, $allPrimaryKeys )
    {
        return $this->toggleEtat($primaryKeys, $allPrimaryKeys, 412, 'participation');
    }
    
    
    
    /**
     * Action de masse sur les états
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     * @param int   $idReference    Id de la référence à mettre à la place
     *
     * @return Redirect
     */
    private function toggleEtat( $primaryKeys, $allPrimaryKeys, $idReference, $etat )
    {
        //check connected user ACL
        $user = $this->get('security.context')->getToken()->getUser();
    
        if( $this->get('nodevo_acl.manager.acl')->checkAuthorization( $this->generateUrl('hopital_numerique_user_delete', array('id'=>1)) , $user ) == -1 ){
            $this->get('session')->getFlashBag()->add('warning', 'Vous n\'avez pas les droits suffisants pour activer des utilisateurs.' );
            return $this->redirect( $this->generateUrl('hopitalnumerique_module_module') );
        }
    
        //get all selected Users
        $inscriptions = $this->get('hopitalnumerique_module.manager.inscription')->findBy( array('id' => $primaryKeys) );
        
        //récupère une inscription pour récuperer les module / session (php < php5.4)
        $tempInscription = $inscriptions[0];
    
        //get ref and Toggle State
        $ref = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => $idReference) );
        switch ($etat)
        {
        	case 'inscription':
                $this->get('hopitalnumerique_module.manager.inscription')->toogleEtatInscription( $inscriptions, $ref );
                if(407 === $ref->getId())
                {
                    //Envoyer mail d'acceptation de l'inscription
                    $mails = $this->get('nodevo_mail.manager.mail')->sendAcceptationInscriptionMassMail($inscriptions,array());
                    foreach ($mails as $mail)
                    {
                        $this->get('mailer')->send($mail);
                    }
                }
                elseif (408 === $ref->getId())
                {
                    //Envoyer mail de refus de l'inscription
                    $mails = $this->get('nodevo_mail.manager.mail')->sendRefusInscriptionMassMail($inscriptions,array());
                    foreach ($mails as $mail)
                    {
                        $this->get('mailer')->send($mail);
                    }
                }
                //inform user connected
                $this->get('session')->getFlashBag()->add('info', 'Inscription(s) modifiée(s) avec succès.' );
        	    break;
        	case 'participation':
                $this->get('hopitalnumerique_module.manager.inscription')->toogleEtatParticipation( $inscriptions, $ref );
                if(411 === $ref->getId())
                {
                    //Envoyer mail du formulaire d'évluation de la session
                    $mails = $this->get('nodevo_mail.manager.mail')->sendFormulaireEvaluationsMassMail($inscriptions,array());
                    foreach ($mails as $mail)
                    {
                        $this->get('mailer')->send($mail);
                    }
                }
        	    //inform user connected
                $this->get('session')->getFlashBag()->add('info', 'Participation(s) modifiée(s) avec succès.' );
        	    break;
        }
    
        return $this->redirect( $this->generateUrl('hopitalnumerique_module_module_session_inscription', array('id' => $tempInscription->getSession()->getId())) );
    }
}