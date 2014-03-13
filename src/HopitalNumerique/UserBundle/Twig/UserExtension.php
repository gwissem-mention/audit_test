<?php
namespace HopitalNumerique\UserBundle\Twig;

class UserExtension extends \Twig_Extension
{
    /**
     * Retourne la liste des filtres custom pour cette extension
     *
     * @return array
     */
    public function getFilters()
    {
        return array(
            'informationsManquantes' => new \Twig_Filter_Method($this, 'informationsManquantes')
        );
    }

    /**
     * Vérifie que l'utilisateur a bien renseignés certains champs
     *
     * @param User $user User a vérifier
     *
     * @return boolean
     */
    public function informationsManquantes( $user )
    {
        $resultat = false;
        
        if( !is_null($user->getTelephoneDirect())
            && !is_null($user->getRegion())
            && !is_null($user->getDepartement())
            && ( (!is_null($user->getEtablissementRattachementSante())) || (!is_null($user->getAutreStructureRattachementSante())))
            && !is_null($user->getFonctionDansEtablissementSante())
            && !is_null($user->getProfilEtablissementSante())
            && !is_null($user->getRaisonInscriptionSante()) )
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
        return 'user_extension';
    }
}