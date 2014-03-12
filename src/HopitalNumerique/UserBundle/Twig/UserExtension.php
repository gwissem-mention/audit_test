<?php
namespace HopitalNumerique\UserBundle\Twig;

class UserExtension extends \Twig_Extension
{  
    /**
     * Vérifie que l'utilisateur a bien renseignés certains champs
     *
     * @param User   $user  User a vérifier
     *
     * @return boolean
     */
    public function informationsManquantes( $user )
    {
        $resultat = false;
        
        if( !empty($user->getTelephoneDirect()) 
            && !empty($user->getRegion())
            && !empty($user->getDepartement())
            && ( (!empty($user->getEtablissementRattachementSante())) || (!empty($user->getAutreStructureRattachementSante())))
            && !empty($user->getFonctionDansEtablissementSante())
            && !empty($user->getProfilEtablissementSante())
            && !empty($user->getRaisonInscriptionSante()) )
        {
            $resultat = true;
        }

        return $resultat;
    }

    /**
     * Retourne le nom de l'extension : utilisé dans les services
     *
     * @return string
     */
    public function getName()
    {
        return 'acl_extension';
    }
}