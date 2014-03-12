<?php

namespace Nodevo\MailBundle\Manager;

use Nodevo\AdminBundle\Manager\Manager as BaseManager;
use Doctrine\ORM\EntityManager;

/**
 * Manager de l'entité Mail.
 */
class MailManager extends BaseManager
{
    protected $_class = 'Nodevo\MailBundle\Entity\Mail';

    private $_allowAdd;
    private $_allowDelete;
    private $_nomExpediteur;
    private $_mailExpediteur;
    private $_destinataire;
    private $_twig;

    /**
     * Constructeur du manager, on lui passe l'entity Manager de doctrine, un booléen si on peut ajouter des mails
     *
     * @param EntityManager $em Entity      Manager de Doctrine
     * @param Array         $options        Tableau d'options
     */
    public function __construct(EntityManager $em, \Twig_Environment $twig, $options = array())
    {
        parent::__construct($em);

        $this->_twig           = $twig;
        $this->_allowAdd       = isset($options['allowAdd'])        ? $options['allowAdd']          : true;
        $this->_allowDelete    = isset($options['allowDelete'])     ? $options['allowDelete']       : true;
        $this->_nomExpediteur  = isset($options['nomExpediteur'])   ? $options['nomExpediteur']     : '';        
        $this->_mailExpediteur = isset($options['mailExpediteur'])  ? $options['mailExpediteur']    : '';
        $this->_destinataire   = isset($options['destinataire'])    ? $options['destinataire']      : '';
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
        
        return $this->_generationMail($user, $mail);
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
        
        return $this->_generationMail($user, $mail);
    }
    
    /**
     * Envoi un mail du type AjoutUser
     *
     * @param User $user Utilisateur qui recevras l'email
     *
     * @return Swift_Message
     */
    public function sendCandidatureMail( $user )
    {
        $mail = $this->findOneById(8);
        
        return $this->_generationMail($user, $mail);
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
        
        //return test email
        return \Swift_Message::newInstance()
                        ->setSubject( $mail->getObjet() )
                        ->setFrom( array($this->_mailExpediteur => $this->_nomExpediteur ) )
                        ->setTo( $this->_destinataire )
                        ->setBody( $body, 'text/html' );
    }

    public function getDestinataire()
    {
        return $this->_destinataire;
    }








    /**
     * Remplace les variables du mail par les vrais valeurs
     *
     * @param string $content Contenu Templaté du mail
     * @param User   $user    User qui recevras l'email
     *
     * @return string
     */
    private function _replaceContent( $content, $user )
    {
        $content = str_replace('%u', $user->getPrenom() . ' ' . $user->getNom(), $content);
        $content = str_replace('%p', $user->getPlainPassword(), $content);
   
        return $content;
    }
    
    /**
     * Génération du mail avec le template NodevoMailBundle::template.mail.html.twig + envoi à l'user
     * 
     * @param \HopitalNumerique\UserBundle\Entity\User $user
     * @param \Nodevo\MailBundle\Entity\Mail           $mail
     * 
     * @return Swift_Message objet \Swift pour l'envoie du mail
     */
    private function _generationMail( $user, $mail )
    {
        //prepare content
        $content         = $this->_replaceContent(str_replace(array("\r\n","\n"),'<br />',$mail->getBody()), $user);
        $templateFile    = "NodevoMailBundle::template.mail.html.twig";
        $templateContent = $this->_twig->loadTemplate($templateFile);
    
        // Render the whole template including any layouts etc
        $body = $templateContent->render( array("content" => $content) );
    
        //send email to users with new password
        return \Swift_Message::newInstance()
                            ->setSubject ( $mail->getObjet() )
                            ->setFrom ( array($mail->getExpediteurMail() => $mail->getExpediteurName() ) )
                            ->setTo ( $user->getEmail() )
                            ->setBody ( $body, 'text/html' );
    }
}