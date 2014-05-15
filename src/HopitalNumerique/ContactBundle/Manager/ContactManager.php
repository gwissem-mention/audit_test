<?php

namespace HopitalNumerique\ContactBundle\Manager;

use Nodevo\ContactBundle\Manager\ContactManager as NodevoContactManager;

/**
 * Manager de l'entité Contractualisation.
 * 
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class ContactManager extends NodevoContactManager
{
    protected $_class = 'HopitalNumerique\ContactBundle\Entity\Contact';
    /**
     * Adresses mails en Copie Caché de l'anap
     * @var array() Tableau clé: Nom affiché => valeur : Adresse mail
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    protected $_mailsContact;
    
    /**
     * Constructeur du manager, on lui passe l'entity Manager de doctrine, un booléen si on peut ajouter des mails
     *
     * @param EntityManager $em Entity      Manager de Doctrine
     * @param Array         $options        Tableau d'options
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function __construct($em, $options = array())
    {
        parent::__construct($em);
        
        $this->_mailsContact = isset($options['mailsContact']) ? $options['mailsContact'] : array();
    }
    
    /**
     * Renvoie la liste des mails dans le config.yml
     * 
     * @return array(string)
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function getMailsContact()
    {
        return $this->_mailsContact;
    }
    
    /**
     * Renvoie une chaine de caractère correspondant aux données du formulaire soumis
     *
     * @param Contact $contact
     *
     * @return string Affichage du formulaire
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function getContactFormateMail($contact)
    {
        $candidature = '<ul>';
        //Civilité
        $candidature .= is_null($contact->getCivilite()) ? '' : '<li><strong>Civilité :</strong> '. $contact->getCivilite()->getLibelle() .'</li>';
        //Prénom
        $candidature .= is_null($contact->getPrenom()) ? '' : '<li><strong>Prénom :</strong> '. $contact->getPrenom() .'</li>';
        //Nom
        $candidature .= is_null($contact->getNom()) ? '' : '<li><strong>Nom :</strong> '. $contact->getNom() .'</li>';
        //Téléphone
        $candidature .= is_null($contact->getTelephone()) ? '' : '<li><strong>Téléphone :</strong> '. $contact->getTelephone() .'</li>';
        //Mail
        $candidature .= is_null($contact->getMail()) ? '' : '<li><strong>Mail :</strong> '. $contact->getMail() .'</li>';
        //Region
        $candidature .= is_null($contact->getRegion()) ? '' : '<li><strong>Région :</strong> '. $contact->getRegion()->getLibelle() .'</li>';
        //Département
        $candidature .= is_null($contact->getDepartement()) ? '' : '<li><strong>Département :</strong> '. $contact->getDepartement()->getLibelle() .'</li>';
        //Code postal
        $candidature .= is_null($contact->getCodepostal()) ? '' : '<li><strong>Code postal :</strong> '. $contact->getCodepostal() .'</li>';
        //Ville
        $candidature .= is_null($contact->getVille()) ? '' : '<li><strong>Ville :</strong> '. $contact->getVille() .'</li>';
        //Type établissement
        $candidature .= is_null($contact->getStatutEtablissementSante()) ? '' : '<li><strong>Type d\'établissement :</strong> '. $contact->getStatutEtablissementSante()->getLibelle() .'</li>';
        //Etablissement de rattachement ou Autre structure si établissement est null
        $candidature .= is_null($contact->getEtablissementRattachementSante()) ? (is_null($contact->getAutreStructureRattachementSante()) ? '' : '<li><strong>Nom de votre établissement si non disponible dans la liste précédente:</strong> '. $contact->getAutreStructureRattachementSante() .'</li>') : '<li><strong>Etablissement de rattachement :</strong> '. $contact->getEtablissementRattachementSante()->getAppellation() .'</li>';
        //Fonction structure
        $candidature .= is_null($contact->getFonctionStructure()) ? '' : '<li><strong>Fonction structure :</strong> '. $contact->getFonctionStructure() .'</li>';
        //Message
        $candidature .= is_null($contact->getMessage()) ? '' : '<li><strong>Message :</strong> '. $contact->getMessage() .'</li>';
        $candidature .= '</ul>';
        
        return $candidature;
    }
}