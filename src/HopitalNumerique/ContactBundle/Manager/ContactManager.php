<?php

namespace HopitalNumerique\ContactBundle\Manager;

use Nodevo\ContactBundle\Manager\ContactManager as NodevoContactManager;

/**
 * Manager de l'entité Contractualisation.
 */
class ContactManager extends NodevoContactManager
{
    protected $_class = 'HopitalNumerique\ContactBundle\Entity\Contact';
    
    /**
     * Renvoie une chaine de caractère correspondant aux données du formulaire soumis
     *
     * @param Contact $contact
     *
     * @return string Affichage du formulaire
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
        //Fonction structure
        $candidature .= is_null($contact->getFonctionStructure()) ? '' : '<li><strong>Fonction structure :</strong> '. $contact->getFonctionStructure() .'</li>';
        //Message
        $candidature .= is_null($contact->getMessage()) ? '' : '<li><strong>Message :</strong> '. $contact->getMessage() .'</li>';
        $candidature .= '</ul>';
    
        return $candidature;
    }
}