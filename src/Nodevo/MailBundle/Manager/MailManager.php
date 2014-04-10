<?php

namespace Nodevo\MailBundle\Manager;

use Nodevo\AdminBundle\Manager\Manager as BaseManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Manager de l'entité Mail.
 */
class MailManager extends BaseManager
{
    protected $_class = 'Nodevo\MailBundle\Entity\Mail';

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
    private $_mailAnap;
    private $_router;
    private $_requestStack;

    /**
     * Constructeur du manager, on lui passe l'entity Manager de doctrine, un booléen si on peut ajouter des mails
     *
     * @param EntityManager $em Entity      Manager de Doctrine
     * @param Array         $options        Tableau d'options
     */
    public function __construct(EntityManager $em, \Twig_Environment $twig, $router, RequestStack $requestStack, $options = array())
    {        
        parent::__construct($em);

        $this->_twig               = $twig;
        $this->_router             = $router;
        $this->_requestStack       = $requestStack;
        $this->_allowAdd           = isset($options['allowAdd'])          ? $options['allowAdd']          : true;
        $this->_allowDelete        = isset($options['allowDelete'])       ? $options['allowDelete']       : true;
        $this->_expediteurEnCopie  = isset($options['expediteurEnCopie']) ? $options['expediteurEnCopie'] : false;
        $this->_nomExpediteur      = isset($options['nomExpediteur'])     ? $options['nomExpediteur']     : '';
        $this->_mailExpediteur     = isset($options['mailExpediteur'])    ? $options['mailExpediteur']    : '';
        $this->_destinataire       = isset($options['destinataire'])      ? $options['destinataire']      : '';
        $this->_mailAnap           = isset($options['mailAnap'])          ? $options['mailAnap']          : array();
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
    public function sendAjoutUserFromAdminMail( $user )
    {
        $mail = $this->findOneById(1);
        
        return $this->generationMail($user, $mail);
    }  

    /**
     * Envoi un mail du type AjoutUser
     *
     * @param User $user Utilisateur qui recevras l'email
     *
     * @return Swift_Message
     */
    public function sendAjoutUserMail( $user )
    {
        $mail = $this->findOneById(2);
        
        return $this->generationMail($user, $mail);
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
     * Envoi un mail de confirmation de candidature expert
     *
     * @param User  $users   Utilisateurs qui recevront l'email
     * @param array $options Variables à remplacer dans le template : '%nomDansLeTemplate' => valeurDeRemplacement
     *
     * @return Swift_Message
     */
    public function sendCandidatureExpertAdminMail( $users, $options )
    {
        $mail = $this->findOneById(28);
    
        $toSend = array();
        foreach($users as $user)
            $toSend[] = $this->generationMail($user, $mail, $options);
        
        return $toSend;
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
     *
     * @return Swift_Message
     */
    public function sendValidationCandidatureAmbassadeurMail( $user )
    {
        $mail = $this->findOneById(12);
    
        return $this->generationMail($user, $mail);
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
     *
     * @return Swift_Message
     */
    public function sendRefusCandidatureAmbassadeurMail( $user, $options )
    {
        $mail = $this->findOneById(14);
    
        return $this->generationMail($user, $mail, $options);
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
     * Envoi un mail de contact (différent des autres envoie de mail)
     *
     * @param array $user    Utilisateurs qui recevras l'email (tableau configuré en config.yml)
     * @param array $options Variables à remplacer dans le template : '%nomDansLeTemplate' => valeurDeRemplacement
     *
     * @return Swift_Message
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
            
            //prepare content
            $content         = $this->replaceContent(str_replace(array("\r\n","\n"),'<br />',$mail->getBody()), NULL , $options);
            $expediteurMail  = $this->replaceContent(str_replace(array("\r\n","\n"),'<br />',$mail->getExpediteurMail()), NULL, $options);
            $expediteurName  = $this->replaceContent(str_replace(array("\r\n","\n"),'<br />',$mail->getExpediteurName()), NULL, $options);
            $content         = $this->replaceContent(str_replace(array("\r\n","\n"),'<br />',$mail->getBody()), NULL, $options);
            $templateFile    = "NodevoMailBundle::template.mail.html.twig";
            $templateContent = $this->_twig->loadTemplate($templateFile);
            
            // Render the whole template including any layouts etc
            $body = $templateContent->render( array("content" => $content) );

            $from = array($expediteurMail => $expediteurName );
            
            if($this->_expediteurEnCopie)
                $cci = array_merge( $this->_mailAnap, $from );
            else
                $cci = $this->_mailAnap;
            
            $mailsToSend[] = \Swift_Message::newInstance()
                                ->setSubject ( $mail->getObjet() )
                                ->setFrom ( $from )
                                ->setTo ( array($recepteurMail => $recepteurName) )
                                ->setBcc( $cci )
                                ->setBody ( $body, 'text/html' );
        }
        return $mailsToSend;
    }

    /**
     * Retourne un email de test
     *
     * @param  [type] $id   [description]
     * @param  [type] $user [description]
     *
     * @return Swift_Message
     */
    public function getMessageTest( $id, $user )
    {
        $mail = $this->findOneById($id);

        //prepare content
        $content = $mail->getBody();
        $content = str_replace( array("\r\n","\n"), '<br />', $content );

        $templateFile    = "NodevoMailBundle::template.mail.html.twig";
        $templateContent = $this->_twig->loadTemplate($templateFile);

        // Render the whole template including any layouts etc
        $body = $templateContent->render(array("content" => $content));

        // on remplace les champs par les valeurs récupérées si les champs sont vides
        $this->_mailExpediteur = ($this->_mailExpediteur == '') ? $mail->getExpediteurMail() : $this->_mailExpediteur;
        $this->_nomExpediteur  = ($this->_nomExpediteur == '')  ? $mail->getExpediteurName() : $this->_nomExpediteur;
        $this->_destinataire   = ($this->_destinataire == '')   ? $user->getEmail()          : $this->_destinataire;
        
        $from = array($this->_mailExpediteur => $this->_nomExpediteur );
        
        if($this->_expediteurEnCopie)
            $cci = array_merge( $this->_mailAnap, $from );
        else
            $cci = $this->_mailAnap;
        
        //return test email
        return \Swift_Message::newInstance()
                        ->setSubject( $mail->getObjet() )
                        ->setFrom( $from )
                        ->setTo( $this->_destinataire )
                        ->setBcc( $cci )
                        ->setBody( $body, 'text/html' );
    }

    public function getDestinataire()
    {
        return $this->_destinataire;
    }

    public function sendInterventionMail(\Nodevo\MailBundle\Entity\Mail $mail, \HopitalNumerique\UserBundle\Entity\User $destinataire, $options)
    {
        return $this->generationMail($destinataire, $mail, $options);
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
        //Si il y a des variables spécifique dans le template courant
        if(!empty($options))
        {
            foreach ($options as $key => $option)
            {
                //Récupération de la variable du template
                $variableARemplacer = '%' . $key;
                //Remplacement de la mise en forme
                $message = nl2br($option);
                //Mise à jour du body du mail
                $content = str_replace($variableARemplacer, $message, $content);
            }
        }
        
        if(!is_null($user))
        {
            $content = str_replace('%u', $user->getPrenom() . ' ' . $user->getNom(), $content);
            $content = str_replace('%p', $user->getPlainPassword(), $content);
        }
        
        $content = str_replace('%s', '<a href="'. $this->_requestStack->getCurrentRequest()->getUriForPath($this->_router->generate('hopital_numerique_homepage')) .'" target="_blank" >Hopital Numérique</a>', $content);

        return $content;
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
    private function generationMail( $user, $mail, $options = array() )
    {        
        //prepare content
        $content         = $this->replaceContent(str_replace(array("\r\n","\n"),'<br />',$mail->getBody()), $user, $options);
        $templateFile    = "NodevoMailBundle::template.mail.html.twig";
        $templateContent = $this->_twig->loadTemplate($templateFile);
        
        $from = array($mail->getExpediteurMail() => $mail->getExpediteurName() );
        
        if($this->_expediteurEnCopie)
            $cci = array_merge( $this->_mailAnap, $from );
        else
            $cci = $this->_mailAnap;
        
        var_dump($this->_expediteurEnCopie);die('die');
    
        // Render the whole template including any layouts etc
        $body = $templateContent->render( array("content" => $content) );
        //send email to users with new password
        return \Swift_Message::newInstance()
                            ->setSubject ( $mail->getObjet() )
                            ->setFrom ( $from )
                            ->setTo ( $user->getEmail() )
                            ->setBcc( $this->_mailAnap )
                            ->setBody ( $body, 'text/html' );
    }
}