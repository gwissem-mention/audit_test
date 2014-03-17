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
        $resultat = array('ok' => array());       
        
        //Pour chacun des éléments ci-dessous, si sa valeur correspondante est nulle alors on créé un tableau contenant le label à afficher
        $resultat['telephoneDirect']       = (is_null($user->getTelephoneDirect())) ? array('label' => 'Téléphone direct') : array();
        $resultat['region']                = (is_null($user->getRegion())) ? array('label' => 'Région') : array();
        $resultat['departement']           = (is_null($user->getDepartement())) ? array('label' => 'Département') : array();
        //Si 'etablissement de rattachement' n'est pas renseigné on vérifie le 'autre structure' 
        $resultat['rattachementSante']     = (is_null($user->getEtablissementRattachementSante())) ? (is_null($user->getAutreStructureRattachementSante()) ? array('label' => 'Etablissement de rattachement / Autre structure de rattachement') : array()) : array();
        $resultat['fonctionEtablissement'] = (is_null($user->getFonctionDansEtablissementSante())) ? array('label' => 'Fonction dans l\'établissement') : array();
        $resultat['profilEtablissement']   = (is_null($user->getProfilEtablissementSante())) ? array('label' => 'Profil de l\'établissement') : array();
        $resultat['raisonInscription']     = (is_null($user->getRaisonInscriptionSante())) ? array('label' => 'Raison inscription') : array();
        
        //Si l'un des éléments ci-dessus est manquant
        foreach ($resultat as $res)
        {
            //Si au moins l'un des tableaux n'est pas vide alors il y a au moins un élément manquant
            if(!empty($res))
            {
                $resultat['ok'] = false;
                break; 
            }
            $resultat['ok'] = true; 
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