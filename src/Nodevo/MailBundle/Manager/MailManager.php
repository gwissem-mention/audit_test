<?php

namespace Nodevo\MailBundle\Manager;


use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use FOS\UserBundle\Model\UserInterface;

use Symfony\Component\HttpFoundation\Request;

use HopitalNumerique\DomaineBundle\Manager\DomaineManager;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\AutodiagBundle\Entity\Outil;
use HopitalNumerique\UserBundle\Manager\UserManager;
/**
 * Manager de l'entité Mail.
 */
class MailManager extends BaseManager
{
    protected $_class = 'Nodevo\MailBundle\Entity\Mail';
    
    /**
     * @var \Swift_Mailer Mailer
     */
    private $mailer;

    private $_allowAdd;
    private $_allowDelete;

    /**
     * Envoie du mail en CCI à l'expediteur aussi
     * 
     * @var boolean
     */
    private $_expediteurEnCopie;
    private $_nomExpediteur;
    private $_mailExpediteur;
    private $_destinataire;
    private $_twig;

    /**
     * Adresses mails en Copie Caché de l'anap
     * @var array() Tableau clé: Nom affiché => valeur : Adresse mail
     */
    private $_mailAnap = '';
    private $_router;
    private $_requestStack;

    private $_session;
    private $_domaineManager;
    private $_userManager;
    private $_optionsMail = array();

    /**
     * Constructeur du manager, on lui passe l'entity Manager de doctrine, un booléen si on peut ajouter des mails
     *
     * @param EntityManager $em Entity      Manager de Doctrine
     * @param Array         $options        Tableau d'options
     */
    public function __construct(EntityManager $em, \Swift_Mailer $mailer, \Twig_Environment $twig, $router, RequestStack $requestStack, $session, DomaineManager $domaineManager, UserManager $userManager,$options = array())
    {        
        parent::__construct($em);
        
        $this->mailer = $mailer;

        $this->_twig               = $twig;
        $this->_router             = $router;
        $this->_requestStack       = $requestStack;
        $this->_allowAdd           = isset($options['allowAdd'])          ? $options['allowAdd']          : true;
        $this->_allowDelete        = isset($options['allowDelete'])       ? $options['allowDelete']       : true;
        $this->_expediteurEnCopie  = isset($options['expediteurEnCopie']) ? $options['expediteurEnCopie'] : false;
        $this->_nomExpediteur      = isset($options['nomExpediteur'])     ? $options['nomExpediteur']     : '';
        $this->_mailExpediteur     = isset($options['mailExpediteur'])    ? $options['mailExpediteur']    : '';
        $this->_destinataire       = isset($options['destinataire'])      ? $options['destinataire']      : '';

        $this->_session        = $session;
        $this->_domaineManager = $domaineManager;
        $this->_userManager    = $userManager;

        $this->setOptions();
    }

    public function getDestinataire()
    {
        return $this->_destinataire;
    }

    /**
     * L'ajout de mail est-il autorisé ?
     * 
     * @return boolean
     */
    public function isAllowedToAdd()
    {
        return $this->_allowAdd;
    }

    /**
     * La suppression de mail est-elle autorisée ?
     * 
     * @return boolean
     */
    public function isAllowedToDelete()
    {
        return $this->_allowDelete;
    }
    
    /**
     * Envoi un mail du type AjoutUser
     *
     * @param User $user Utilisateur qui recevras l'email
     *
     * @return Swift_Message
     */
    public function sendAjoutUserFromAdminMail( $user, $options )
    {
        $mail = $this->findOneById(1);
        
        return $this->generationMail($user, $mail, $options);
    }  

    /**
     * Envoi un mail du type AjoutUser
     *
     * @param User $user Utilisateur qui recevras l'email
     *
     * @return Swift_Message
     */
    public function sendAjoutUserMail( $user, $options )
    {
        $mail = $this->findOneById(2);
        $url = $this->_router->generate('fos_user_registration_confirm', array('token' => $user->getConfirmationToken()), true);
        $options['url'] = $url;
        $check = 1;
        return $this->generationMail($user, $mail, $options, $check);
    }
    
    /**
     * Envoi un mail de confirmation de candidature expert
     *
     * @param User $user Utilisateur qui recevras l'email
     *
     * @return Swift_Message
     */
    public function sendCandidatureExpertMail( $user )
    {
        $mail = $this->findOneById(8);
        
        return $this->generationMail($user, $mail);
    }
    
    /**
     * Envoi un mail de confirmation de candidature ambassadeur
     *
     * @param User $user Utilisateur qui recevras l'email
     *
     * @return Swift_Message
     */
    public function sendCandidatureAmbassadeurMail( $user )
    {
        $mail = $this->findOneById(9);
    
        return $this->generationMail($user, $mail);
    }
    
    /**
     * Envoi un mail de confirmation de candidature ambassadeur
     *
     * @param User  $user    Utilisateur qui recevras l'email
     * @param array $options Variables à remplacer dans le template : '%nomDansLeTemplate' => valeurDeRemplacement
     *
     * @return Swift_Message
     */
    public function sendCandidatureAmbassadeurCMSIMail( $user, $options )
    {
        $mail = $this->findOneById(24);
    
        return $this->generationMail($user, $mail, $options);
    }

    /**
     * Envoi un mail des réponses+question d'un questionnaire rempli par un utilisateur (différent des autres envoie de mail)
     *
     * @param array $user    Utilisateurs qui recevras l'email (tableau configuré en config.yml/parameters.yml)
     * @param array $options Variables à remplacer dans le template : '%nomDansLeTemplate' => valeurDeRemplacement
     *
     * @return Swift_Message
     */
    public function sendCandidatureExpertAdminMail( $users, $options  )
    {
        $mail = $this->findOneById(28);
        
        //tableau de SwiftMessage a envoyé
        $mailsToSend = array();
        
        foreach ($users as $recepteurMail => $recepteurName)
        {   
            $options["u"]  = $recepteurName;
            $options = $this->getAllOptions($options);

            //prepare content
            $content        = $this->replaceContent($mail->getBody(), NULL , $options);
            $expediteurMail = $this->replaceContent($mail->getExpediteurMail(), NULL, $options);
            $expediteurName = $this->replaceContent($mail->getExpediteurName(), NULL, $options);
            $content        = $this->replaceContent($mail->getBody(), NULL, $options);
            $from           = array($expediteurMail => $expediteurName );
            $subject        = $this->replaceContent($mail->getObjet(), null, $options);
            
            $mailsToSend[] = $this->sendMail( $subject, $from, array($recepteurMail => $recepteurName), $content, $this->getMailDomaine() );
        }

        return $mailsToSend;
    }
    
    /**
     * Envoi un mail de validation de candidature expert
     *
     * @param User $user Utilisateur qui recevras l'email
     *
     * @return Swift_Message
     */
    public function sendValidationCandidatureExpertMail( $user )
    {
        $mail = $this->findOneById(11);
    
        return $this->generationMail($user, $mail);
    }
    
    /**
     * Envoi un mail de validation de candidature ambassadeur
     *
     * @param User $user Utilisateur qui recevras l'email
     * @param User $CMSI CMSI qui recevras l'email en copie
     *
     * @return Swift_Message
     */
    public function sendValidationCandidatureAmbassadeurMail( $user, $CMSI = null )
    {
        $mail = $this->findOneById(12);
    
        $mailValidation = $this->generationMail($user, $mail);

        if(!is_null($CMSI))
        {
            $mailValidation->setCc(array($CMSI->getEmail() => $CMSI->getNomPrenom()));
        }
        return $mailValidation;
    }
    
    /**
     * Envoi un mail de refus de candidature expert
     *
     * @param User  $user    Utilisateur qui recevras l'email
     * @param array $options Variables à remplacer dans le template : 'nomDansLeTemplate' => valeurDeRemplacement
     *
     * @return Swift_Message
     */
    public function sendRefusCandidatureExpertMail( $user, $options )
    {
        $mail = $this->findOneById(13);
    
        return $this->generationMail($user, $mail, $options);
    }
    
    /**
     * Envoi un mail de validation de candidature ambassadeur
     *
     * @param User  $user    Utilisateur qui recevras l'email
     * @param array $options Variables à remplacer dans le template : 'nomDansLeTemplate' => valeurDeRemplacement
     * @param User $CMSI CMSI qui recevras l'email en copie
     *
     * @return Swift_Message
     */
    public function sendRefusCandidatureAmbassadeurMail( $user, $options, $CMSI = null )
    {
        $mail = $this->findOneById(14);
    
        $mailRefus = $this->generationMail($user, $mail, $options);
        
        if(!is_null($CMSI))
        {
            $mailRefus->setCc(array($CMSI->getEmail() => $CMSI->getNomPrenom()));
        }
        return $mailRefus;
    }
    
    /**
     * Envoi un mail de recréation du mot de passe
     *
     * @param User  $user    Utilisateur qui recevras l'email
     * @param array $options Variables à remplacer dans le template : 'nomDansLeTemplate' => valeurDeRemplacement
     *
     * @return Swift_Message
     */
    public function sendResetPasswordMail( $user, $options )
    {
        $mail = $this->findOneById(26);
    
        return $this->generationMail($user, $mail, $options);
    }
   
    /**
     * Envoi un mail de notification de la requete
     *
     * @param User  $user    Utilisateur qui recevras l'email
     * @param array $options Variables à remplacer dans le template : '%nomDansLeTemplate' => valeurDeRemplacement
     *
     * @return Swift_Message
     */
    public function sendNotificationRequete( $user, $options )
    {
        $mail = $this->findOneById(29);

        return $this->generationMail($user, $mail, $options);
    }

    /**
     * Envoi un mail d'acceptation de l'inscription à une session d'un module
     *
     * @param User  $user    Utilisateur qui recevras l'email
     * @param array $options Variables à remplacer dans le template : '%nomDansLeTemplate' => valeurDeRemplacement
     *
     * @return Swift_Message
     */
    public function sendAcceptationInscriptionMail( $user, $options )
    {
        $mail = $this->findOneById(31);
        return $this->generationMail($user, $mail, $options);
    }

    /**
     * Envoi un mail de refus de l'inscription à une session d'un module
     *
     * @param User  $user    Utilisateur qui recevras l'email
     * @param array $options Variables à remplacer dans le template : '%nomDansLeTemplate' => valeurDeRemplacement
     *
     * @return Swift_Message
     */
    public function sendRefusInscriptionMail( $user, $options )
    {
        $mail = $this->findOneById(32);
    
        return $this->generationMail($user, $mail, $options);
    }

    /**
     * Envoi un mail d'acceptation de l'inscription à une session d'un module
     *
     * @param Inscriptions $inscriptions  
     * @param array        $options      Variables à remplacer dans le template : '%nomDansLeTemplate' => valeurDeRemplacement
     *
     * @return Swift_Message
     */
    public function sendAcceptationInscriptionMassMail( $inscriptions, $options )
    {
        $mail = $this->findOneById(31);

        $toSend = array();
        foreach ($inscriptions as $key => $inscription) 
        {
            if($inscription->getSession()->getModule()->getMailConfirmationInscription())
            {
                $toSend[] = $this->generationMail($inscription->getUser(), $mail, array(
                                'date'    => $inscription->getSession()->getDateSession()->format('d/m/Y'),
                                'module'  => $inscription->getSession()->getModule()->getTitre()
                )); 
            }
        }
    
        return $toSend;
    }

    /**
     * Envoi un mail de refus de l'inscription à une session d'un module
     *
     * @param Inscriptions $inscriptions  
     * @param array $options Variables à remplacer dans le template : '%nomDansLeTemplate' => valeurDeRemplacement
     *
     * @return Swift_Message
     */
    public function sendRefusInscriptionMassMail( $inscriptions, $options )
    {
        $mail = $this->findOneById(32);

        $toSend = array();
        foreach ($inscriptions as $key => $inscription) 
        {
            if($inscription->getSession()->getModule()->getMailRefusInscription())
            {
                $toSend[] = $this->generationMail($inscription->getUser(), $mail, array(
                                'date'      => $inscription->getSession()->getDateSession()->format('d/m/Y'),
                                'module'    => $inscription->getSession()->getModule()->getTitre(),
                                'textRefus' => $options['textRefus']
                ));
            }
        }
    
        return $toSend;
    }

    /**
     * Envoi un mail d'acceptation de l'inscription à une session d'un module
     *
     * @param Inscriptions $inscriptions  
     * @param array        $options      Variables à remplacer dans le template : '%nomDansLeTemplate' => valeurDeRemplacement
     *
     * @return Swift_Message
     */
    public function sendRappelInscriptionMail( $inscriptions, $options )
    {
        $mail = $this->findOneById(35);

        $toSend = array();
        foreach ($inscriptions as $key => $inscription) 
        {
            if($inscription->getSession()->getModule()->getMailRappelEvalution())
            {
                $mailTemp = $this->generationMail($inscription->getUser(), $mail, array(
                                'date'      => $inscription->getSession()->getDateSession()->format('d/m/Y'),
                                'module'    => $inscription->getSession()->getModule()->getTitre(),
                                'texteMail' => $inscription->getSession()->getTextMailRappel()
                ));

                if(!is_null($inscription->getSession()->getAbsolutePath()) && trim($inscription->getSession()->getAbsolutePath()) !== "")
                {
                    $mailTemp->attach(\Swift_Attachment::fromPath($inscription->getSession()->getAbsolutePath()), "application/octet-stream");
                }
                elseif(!is_null($inscription->getSession()->getModule()->getAbsolutePath()) && trim($inscription->getSession()->getModule()->getAbsolutePath()) !== "")
                {
                    $mailTemp->attach(\Swift_Attachment::fromPath($inscription->getSession()->getModule()->getAbsolutePath()), "application/octet-stream");
                }

                $toSend[] = $mailTemp;
            }
        }
    
        return $toSend;
    }

    /**
     * Envoi un mail pour acceder au formulaire d'évaluation à une session d'un module
     *
     * @param Inscriptions $inscriptions  
     * @param array        $options      Variables à remplacer dans le template : '%nomDansLeTemplate' => valeurDeRemplacement
     *
     * @return Swift_Message
     */
    public function sendFormulaireEvaluationsMassMail( $inscriptions, $options )
    {
        $mail = $this->findOneById(33);

        $toSend = array();
        $domaine = $this->_domaineManager->findOneById($this->_session->get('domaineId'));

        foreach ($inscriptions as $key => $inscription) 
        {
            if($inscription->getSession()->getModule()->getMailAlerteEvaluation())
            {

                $toSend[] = $this->generationMail($inscription->getUser(), $mail, array(
                                'date'    => $inscription->getSession()->getDateSession()->format('d/m/Y'),
                                'module'  => $inscription->getSession()->getModule()->getTitre(),
                                'url'     => '<a href="'. $this->_requestStack->getCurrentRequest()->getUriForPath( $this->_router->generate( 'hopitalnumerique_module_evaluation_form_front', array(
                                                'id' => $inscription->getSession()->getId() 
                                            ))) .'" target="_blank" >'. $domaine->getNom() .'</a>'

                ));
            }
        }
    
        return $toSend;
    }
    
    /**
     * Envoie un courriel concernant les demandes d'intervention.
     *
     * @param \Nodevo\MailBundle\Entity\Mail $mail Le courriel à envoyer
     * @param \HopitalNumerique\UserBundle\Entity\User $destinataire Le destinataire du message
     * @param array $options Les paramètres à remplacer
     * @return \Swift_Message Le message près à être envoyer
     */
    public function sendInterventionMail(\Nodevo\MailBundle\Entity\Mail $mail, \HopitalNumerique\UserBundle\Entity\User $destinataire, $options)
    {
        return $this->generationMail($destinataire, $mail, $options);
    }
    
    /**
     * [sendInscriptionSession description]
     *
     * @param  [type] $user    [description]
     * @param  [type] $options [description]
     *
     * @return [type]
     */
    public function sendInscriptionSession( $user, $options )
    {
        $mail = $this->findOneById(34);
    
        return $this->generationMail($user, $mail, $options);
    }
    
    /**
     * Envoi un mail pour notifier le changement de domaine
     *
     * @param User  $user    Utilisateur qui recevras l'email
     * @param array $options Variables à remplacer dans le template : 'nomDansLeTemplate' => valeurDeRemplacement
     *
     * @return Swift_Message
     */
    public function sendDomaineChanged( $user, $options )
    {
        $mail = $this->findOneById(41);
    
        return $this->generationMail($user, $mail, $options);
    }
    
    /**
     * [sendInscriptionSession description]
     *
     * @param  [type] $user    [description]
     * @param  [type] $options [description]
     *
     * @return [type]
     */
    public function sendNouveauMessageForumMail( $user, $options, $topicId )
    {

        //Création du lien dans le mail
        $options['lienversmessage'] = '<a href="'. $this->_requestStack->getCurrentRequest()->getUriForPath( $this->_router->generate( 'ccdn_forum_user_topic_show', array(
                                            'forumName' => $options['forum'],
                                            'topicId'    => $topicId
                                        ))) .'" target="_blank" >Nouveau message</a>';

        $mail = $this->findOneById(36);
    
        return $this->generationMail($user, $mail, $options);
    }
    
    /**
     * [sendInscriptionSession description]
     *
     * @param  [type] $user    [description]
     * @param  [type] $options [description]
     *
     * @return [type]
     */
    public function sendNouveauMessageForumAttenteModerationMail( $options, $topicId )
    {
        $mail = $this->findOneById(51);

        //Création du lien dans le mail
        $options['lienversmessage'] = '<a href="'. $this->_requestStack->getCurrentRequest()->getUriForPath( $this->_router->generate( 'ccdn_forum_user_topic_show', array(
                                            'forumName' => $options['forum'],
                                            'topicId'    => $topicId
                                        ))) .'" target="_blank" >Nouveau message</a>';
 
        $domaine = $this->_domaineManager->findOneById($this->_session->get('domaineId'));
        $destinataire = $domaine->getAdresseMailContact();
        $options = $this->getAllOptions($options);

        $mailExpediteur = $this->replaceContent($mail->getExpediteurMail(), NULL, $options);
        $nameExpediteur = $this->replaceContent($mail->getExpediteurName(), NULL, $options);
        
        //prepare content
        $content = $this->replaceContent($mail->getBody(), NULL , $options);
        $from    = array($mailExpediteur => $nameExpediteur );
        $cci     = $this->_expediteurEnCopie ? array_merge( $this->getMailDomaine(), $from ) : $this->getMailDomaine();
        $cci     = ($mail->getId() === 1 || $mail->getId() === 2) ? false : $cci;
        $subject = $this->replaceContent($mail->getObjet(), NULL, $options);
        
        $mailToSend = $this->sendMail( $subject, $from, array($destinataire), $content, $this->getMailDomaine() );

        return $mailToSend;
    }

    /**
     * [sendInscriptionSession description]
     *
     * @param  [type] $users    [description]
     * @param  [type] $options [description]
     *
     * @return Swift_Message
     */
    public function sendNouveauRapportDeBugMail( $users, $options)
    {
        //Création du lien dans le mail
        $mail = $this->findOneById(40);
        
        //tableau de SwiftMessage a envoyé
        $mailsToSend = array();
        
        foreach ($users as $recepteurMail)
        {
            $recepteurName = $recepteurMail;
            
            $options["nomdestinataire"]  = $recepteurName;
            $options["maildestinataire"] = $recepteurMail;
            $options["u"]  = $recepteurName;
            $options = $this->getAllOptions($options);
            
            //prepare content
            $content        = $this->replaceContent($mail->getBody(), NULL , $options);
            $expediteurMail = $this->replaceContent($mail->getExpediteurMail(), NULL, $options);
            $expediteurName = $this->replaceContent($mail->getExpediteurName(), NULL, $options);
            $content        = $this->replaceContent($mail->getBody(), NULL, $options);
            $from           = array($expediteurMail => $expediteurName );
            $subject        = $this->replaceContent($mail->getObjet(), NULL, $options);
            
            $mailsToSend[] = $this->sendMail( $subject, $from, array($recepteurMail => $recepteurName), $content, $this->getMailDomaine() );
        }

        return $mailsToSend;
    }

    /**
     * Envoi un mail de contact (différent des autres envoie de mail)
     *
     * @param array $user    Utilisateurs qui recevras l'email (tableau configuré en config.yml)
     * @param array $options Variables à remplacer dans le template : '%nomDansLeTemplate' => valeurDeRemplacement
     *
     * @return Swift_Message[]
     */
    public function sendContactMail( $users, $options )
    {
        $mail = $this->findOneById(30);
        
        //tableau de SwiftMessage a envoyé
        $mailsToSend = array();
        
        foreach ($users as $recepteurMail => $recepteurName)
        {
            $options["nomdestinataire"]  = $recepteurName;
            $options["maildestinataire"] = $recepteurMail;
            $options = $this->getAllOptions($options);
            
            //prepare content
            $content        = $this->replaceContent($mail->getBody(), NULL , $options);
            $expediteurMail = $this->replaceContent($mail->getExpediteurMail(), NULL, $options);
            $expediteurName = $this->replaceContent($mail->getExpediteurName(), NULL, $options);
            $content        = $this->replaceContent($mail->getBody(), NULL, $options);
            $from           = array($expediteurMail => $expediteurName );
            $subject        = $this->replaceContent($mail->getObjet(), NULL, $options);
            
            $mailsToSend[] = $this->sendMail( $subject, $from, array($recepteurMail => $recepteurName), $content, $this->getMailDomaine() );
        }

        return $mailsToSend;
    }

    /**
     * Envoi un mail de partage de résultat d'autodiag (différent des autres envoie de mail)
     *
     * @param array                                            $options  Variables à remplacer dans le template : '%nomDansLeTemplate' => valeurDeRemplacement
     * @param \HopitalNumerique\AutodiagBundle\Entity\Resultat $resultat Résultat à partager
     *
     * @return Swift_Message
     */
    public function sendPartageResultatAutodiag( $options, $resultat )
    {
        $mail = $this->findOneById(50);

        if(is_null($resultat->getUser()))
        {
            return null;
        }
        
        $user                      = $resultat->getUser();
        $options["nomexpediteur"]  = $user->getPrenom() . ' ' . $user->getNom();
        $options["mailexpediteur"] = $user->getEmail();
        $destinataire              = $options["destinataire"];
        $options                   = $this->getAllOptions($options);
        
        //prepare content
        $content        = $this->replaceContent($mail->getBody(), NULL , $options);
        $expediteurMail = $this->replaceContent($mail->getExpediteurMail(), NULL, $options);
        $expediteurName = $this->replaceContent($mail->getExpediteurName(), NULL, $options);
        $content        = $this->replaceContent($mail->getBody(), NULL, $options);

        $from           = array($expediteurMail => $expediteurName );
        $subject        = $this->replaceContent($mail->getObjet(), $resultat->getUser(), $options);
        
        $mail = $this->sendMail( $subject, $from, array($destinataire), $content, $this->getMailDomaine() );

        $fileName = __ROOT_DIRECTORY__ . '/files/autodiag/' . $resultat->getPdf();

        if(file_exists($fileName))
        {
            $mail->attach(\Swift_Attachment::fromPath($fileName));
        }

        return $mail;
    }

    /**
     * Envoi un mail de contact (différent des autres envoie de mail)
     *
     * @param array $user    Utilisateurs qui recevras l'email (tableau configuré en config.yml)
     * @param array $options Variables à remplacer dans le template : '%nomDansLeTemplate' => valeurDeRemplacement
     *
     * @return Swift_Message
     */
    public function sendAutodiagSauvegardetMail( $users, $options )
    {
        $mail = $this->findOneById(42);
        
        //tableau de SwiftMessage a envoyé
        $mailsToSend = array();
        
        foreach ($users as $recepteurMail => $recepteurName)
        {
            $options["nomdestinataire"]  = $recepteurName;
            $options["maildestinataire"] = $recepteurMail;
            $options = $this->getAllOptions($options);
            
            //prepare content
            $content        = $this->replaceContent($mail->getBody(), NULL , $options);
            $expediteurMail = $this->replaceContent($mail->getExpediteurMail(), NULL, $options);
            $expediteurName = $this->replaceContent($mail->getExpediteurName(), NULL, $options);
            $content        = $this->replaceContent($mail->getBody(), NULL, $options);
            $from           = array($expediteurMail => $expediteurName );
            $subject        = $this->replaceContent($mail->getObjet(), NULL, $options);
            
            $mailsToSend[] = $this->sendMail( $subject, $from, array($recepteurMail => $recepteurName), $content, $this->getMailDomaine() );
        }

        return $mailsToSend;
    }

    /**
     * Envoi un mail des réponses+question d'un questionnaire rempli par un utilisateur (différent des autres envoie de mail)
     *
     * @param array $user    Utilisateurs qui recevras l'email (tableau configuré en config.yml/parameters.yml)
     * @param array $options Variables à remplacer dans le template : '%nomDansLeTemplate' => valeurDeRemplacement
     *
     * @return Swift_Message
     */
    public function sendReponsesQuestionnairesMail( $users, $options )
    {
        $mail = $this->findOneById(37);
        
        //tableau de SwiftMessage a envoyé
        $mailsToSend = array();
        
        foreach ($users as $recepteurMail => $recepteurName)
        {   
            $options["u"]  = $recepteurName;
            $options = $this->getAllOptions($options);

            //prepare content
            $content        = $this->replaceContent($mail->getBody(), NULL , $options);
            $expediteurMail = $this->replaceContent($mail->getExpediteurMail(), NULL, $options);
            $expediteurName = $this->replaceContent($mail->getExpediteurName(), NULL, $options);
            $content        = $this->replaceContent($mail->getBody(), NULL, $options);
            $from           = array($expediteurMail => $expediteurName );
            $subject        = $this->replaceContent($mail->getObjet(), NULL, $options);
            
            $mailsToSend[] = $this->sendMail( $subject, $from, array($recepteurMail => $recepteurName), $content, $this->getMailDomaine() );
        }

        return $mailsToSend;
    }

    /**
     * Retourne un email de test
     *
     * @param integer $id   ID du mail
     * @param User    $user Utilisateur destinataire
     *
     * @return Swift_Message
     */
    public function sendMessageTest( $id, $user )
    {
        $mail = $this->findOneById($id);

        // on remplace les champs par les valeurs récupérées si les champs sont vides
        $this->_mailExpediteur = ($this->_mailExpediteur == '') ? $mail->getExpediteurMail() : $this->_mailExpediteur;
        $this->_nomExpediteur  = ($this->_nomExpediteur == '')  ? $mail->getExpediteurName() : $this->_nomExpediteur;
        $this->_destinataire   = ($this->_destinataire == '')   ? $user->getEmail()          : $this->_destinataire;

        $options = $this->getAllOptions(array($this->_mailExpediteur => $this->_nomExpediteur));

        $expediteurMail = $this->replaceContent($this->_mailExpediteur, NULL, $options);
        $expediteurName = $this->replaceContent($this->_nomExpediteur, NULL, $options);

        $from = array($expediteurMail => $expediteurName );

        //return test email
        return $this->sendMail( $mail->getObjet(), $from, $this->_destinataire, $mail->getBody() );
    }

    /**
     * Envoi le courriel de partage d'un autodiagnostic.
     *
     * @param array $user    Utilisateurs qui recevras l'email (tableau configuré en config.yml)
     * @param array $options Variables à remplacer dans le template : '%nomDansLeTemplate' => valeurDeRemplacement
     *
     * @return Swift_Message
     */
    public function sendPartageAutodiagnostic(User $partageur, User $destinataire, Outil $autodiagnostic)
    {
        $mail = $this->findOneById(43);
        
        $courrielDestinataires = array
        (
            array($partageur->getEmail() => $partageur->getAppellation()),
            array($destinataire->getEmail() => $destinataire->getAppellation())
        );
        $options = array
        (
            'autodiagnostic' => $autodiagnostic->getTitle()
        );
        $options = $this->getAllOptions($options);
        
        foreach ($courrielDestinataires as $courrielDestinataire)
        {
            $this->mailer->send
            (
                $this->sendMail
                (
                    $this->replaceContent($mail->getObjet(), $user, $options),
                    $partageur->getEmail(),
                    $courrielDestinataire,
                    $this->replaceContent($mail->getBody(), $partageur, $options),
                    $this->getMailDomaine()
                )
            );
        }
    }

    private function getMailDomaine()
    {
        if (null !== $this->_mailAnap && $this->_mailAnap === "") {
            $domaine = $this->_domaineManager->findOneById($this->_session->get('domaineId'));
            $this->_mailAnap = $domaine->getAdresseMailContact();
        }

        return $this->_mailAnap;
    }

    private function getAllOptions(array $options)
    {
        return array_merge($options, $this->_optionsMail);
    }

    private function setOptions()
    {
        $domaine = $this->_domaineManager->findOneById( $this->_session->get('domaineId') );

        $this->_optionsMail = array(
            'subjectDomaine' => $this->getDomaineSubjet(),
            'mailContactDomaineCurrent' => $domaine->getAdresseMailContact(),
            'nomContactDomaineCurrent' => $domaine->getNom(),
        );
        

        return $this;
    }

    /**
     * Retourne le domaine courant sous forme de label pour le sujet du mail
     *
     * @return [type]
     */
    private function getDomaineSubjet()
    {
        $chaine = new \Nodevo\ToolsBundle\Tools\Chaine(is_null($this->_domaineManager->findOneById($this->_session->get('domaineId'))) ? 'Hopital Numérique' : $this->_domaineManager->findOneById($this->_session->get('domaineId'))->getNom());
        return str_replace(' ', '', strtoupper($chaine->supprimeAccents()));
    }

    /**
     * Génération du mail avec le template NodevoMailBundle::template.mail.html.twig + envoi à l'user
     * 
     * @param \HopitalNumerique\UserBundle\Entity\User $user
     * @param \Nodevo\MailBundle\Entity\Mail           $mail
     * @param array                                    $options Variables à remplacer dans le template : '%nomDansLeTemplate' => valeurDeRemplacement
     * 
     * @return Swift_Message objet \Swift pour l'envoie du mail
     */
    private function generationMail( $user, $mail, $options = array(), $check=0 )
    {
        $options = $this->getAllOptions($options);

        $mailExpediteur = $this->replaceContent($mail->getExpediteurMail(), null, $options);
        $nameExpediteur = $this->replaceContent($mail->getExpediteurName(), null, $options);

        //prepare content
        $body    = $this->replaceContent($mail->getBody(), $user, $options);
        $from    = array($mailExpediteur => $nameExpediteur );
        $cci     = $this->_expediteurEnCopie ? array_merge( $this->getMailDomaine(), $from ) : $this->getMailDomaine();
        $cci     = ($mail->getId() === 1 || $mail->getId() === 2) ? false : $cci;
        $subject = $this->replaceContent($mail->getObjet(), $user, $options);

        return $this->sendMail( $subject, $from, $user->getEmail(), $body, $cci, $check );
    }

    /**
     * Remplace les variables du mail par les vrais valeurs
     *
     * @param string $content Contenu Templaté du mail
     * @param User   $user    User qui recevras l'email
     * @param array  $options Variables à remplacer dans le template : '%nomDansLeTemplate' => valeurDeRemplacement
     *
     * @return string
     */
    private function replaceContent( $content, $user, $options)
    {        
        $options = $this->getAllOptions($options);

        //Si il y a des variables spécifique dans le template courant
        if(!empty($options))
        {
            foreach ($options as $key => $option)
            {
                //Récupération de la variable du template
                $variableARemplacer = '%' . $key;
                //Remplacement de la mise en forme
                $message = nl2br($option);
                //Mise à jour du contenu passé en arg
                $content = str_replace($variableARemplacer, $message, $content);
            }
        }
        
        if(!is_null($user))
        {
            $content = str_replace('%u', $user->getPrenom() . ' ' . $user->getNom(), $content);
            $content = str_replace('%p', $user->getPlainPassword(), $content);
        }
        
        $content = str_replace('%s', '<a href="'. $this->_requestStack->getCurrentRequest()->getUriForPath($this->_router->generate('hopital_numerique_homepage')) .'" target="_blank" >'. $this->_domaineManager->findOneById($this->_session->get('domaineId'))->getNom() .'</a>', $content);

        return $content;
    }

    /**
     * Envoi un mail
     *
     * @param string $subject      Sujet du mail
     * @param string $from         Expéditeur
     * @param string $destinataire Destinataire
     * @param string $body         Contenu du mail
     *
     * @return \Swift_Message
     */
    private function sendMail( $subject, $from, $destinataire, $body, $bcc = false, $check = 0 )
    {
        $body = quoted_printable_decode($body);

        $user_mail = $this->_userManager->findOneBy(array("email" => $destinataire));
        
        if(($user_mail != null && !$user_mail->isActif()) || $check != 0) {
            return \Swift_Message::newInstance();
        }

        //prepare content HTML
        $bodyHtml = str_replace( array("\r\n","\n"), '<br />', $body );
        $template = $this->_twig->loadTemplate( "NodevoMailBundle::template.mail.html.twig" );
        $bodyHtml = $template->render(array("content" => $bodyHtml));

        //prepare content TEXT
        $pattern = '/<a[^>]+href=([\'"])(.+?)\1[^>]*>(.*)<\/a>/i';
        if( preg_match_all($pattern, $body, $matches) ){
            foreach($matches[1] as $key => $value)
                $body = str_replace($matches[0][$key], '(' . $matches[2][$key] . ')' . $matches[3][$key] , $body);
        }
        $template = $this->_twig->loadTemplate( "NodevoMailBundle::template.mail.txt.twig" );
        $bodyTxt  = $template->render(array("content" => strip_tags($body) ));

        //prepare Mail
        $mail = \Swift_Message::newInstance()
                        ->setSubject( $this->replaceContent($subject, NULL, array() ) )
                        ->setFrom( $from )
                        ->setTo( $destinataire )
                        ->setBody( $bodyTxt )
                        ->addPart( $bodyHtml, 'text/html' );

        if( $bcc )
            $mail->setBcc( $bcc );

        //return mail
        return $mail;
    }
}
