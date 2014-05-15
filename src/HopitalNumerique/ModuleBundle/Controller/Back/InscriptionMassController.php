<?php

namespace HopitalNumerique\ModuleBundle\Controller\Back;

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
     * Export CSV de la liste des sessions 
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     */
    public function exportCsvAction( $primaryKeys, $allPrimaryKeys )
    {
        //get all selected Users
        if($allPrimaryKeys == 1)
            $inscriptions = $this->get('hopitalnumerique_module.manager.inscription')->findAll();
        else
            $inscriptions = $this->get('hopitalnumerique_module.manager.inscription')->findBy( array('id' => $primaryKeys) );

        $colonnes = array( 
                            'id'                        => 'id', 
                            'user.nom'                  => 'Nom', 
                            'user.prenom'               => 'Prénom', 
                            'user.username'             => 'Nom du compte', 
                            'user.email'                => 'Adresse e-mail',
                            'session.moduleTitre'       => 'Titre du module',
                            'session.dateSessionString' => 'Date de la session',
                            'etatInscription.libelle'   => 'Inscription',
                            'etatParticipation.libelle' => 'Participation',
                            'etatEvaluation.libelle'    => 'Evaluation',
                            'dateInscriptionString'     => 'Date d\'inscription'
                        );

        $kernelCharset = $this->container->getParameter('kernel.charset');

        return $this->get('hopitalnumerique_module.manager.inscription')->exportCsv( $colonnes, $inscriptions, 'export-utilisateurs.csv', $kernelCharset );
    }

    /**
     * Envoyer un mail aux utilisateurs
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Redirect
     */
    public function envoyerMailMassAction( $primaryKeys, $allPrimaryKeys )
    {
        //get all selected Users
        if($allPrimaryKeys == 1)
            $inscriptions = $this->get('hopitalnumerique_module.manager.inscription')->findAll();
        else
            $inscriptions = $this->get('hopitalnumerique_module.manager.inscription')->findBy( array('id' => $primaryKeys) );
        
        //get emails
        $list = array();
        foreach($inscriptions as $inscription)
        {
            if($inscription->getUser()->getEmail() != "")
                $list[] = $inscription->getUser()->getEmail();
        }

        //to
        $to = $this->get('security.context')->getToken()->getUser()->getEmail();
        
        //bcc list
        $bcc = join(';', $list);
        
        return $this->render('HopitalNumeriqueModuleBundle:Inscription:mailto.html.twig', array(
            'mailto' => 'mailto:'.$to.'?bcc='.$bcc
        ));
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
                //A participé
                if(411 === $ref->getId())
                {
                    //Envoyer mail du formulaire d'évluation de la session
                    $mails = $this->get('nodevo_mail.manager.mail')->sendFormulaireEvaluationsMassMail($inscriptions,array());
                    foreach ($mails as $mail)
                    {
                        $this->get('mailer')->send($mail);
                    }
                    $this->get('hopitalnumerique_module.manager.inscription')->toogleEtatEvaluation( $inscriptions, $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => 28) ) );
                }
                //N'a pas participé
                elseif(412 === $ref->getId())
                {
                    $this->get('hopitalnumerique_module.manager.inscription')->toogleEtatEvaluation( $inscriptions, $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => 27) ) );
                }
        	    //inform user connected
                $this->get('session')->getFlashBag()->add('info', 'Participation(s) modifiée(s) avec succès.' );
        	    break;
        }
    
        return $this->redirect( $this->generateUrl('hopitalnumerique_module_module_session_inscription', array('id' => $tempInscription->getSession()->getId())) );
    }
}