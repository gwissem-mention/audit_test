<?php

namespace HopitalNumerique\ObjetBundle\Manager;

use Nodevo\AdminBundle\Manager\Manager as BaseManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

/**
 * Manager de l'entité Objet.
 */
class ObjetManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\ObjetBundle\Entity\Objet';

    /**
     * Override : Récupère les données pour le grid sous forme de tableau
     *
     * @return array
     */
    public function getDatasForGrid( $condition = null )
    {
        $results = $this->getRepository()->getDatasForGrid( $condition );

        return $this->_rearangeForTypes( $results );
    }

    /**
     * Récupère les données du grid sous forme de tableau correctement formaté
     *
     * @return array
     */
    public function getDatasForGridAmbassadeur( $condition = null )
    {
        $results = $this->getRepository()->getDatasForGridAmbassadeur( $condition );
        
        return $this->_rearangeForTypes( $results );
    }

    /**
     * Vérouille un objet en accès
     *
     * @param Objet $objet Objet concerné
     * @param User  $user  User concerné
     *
     * @return Objet
     */
    public function lock( $objet, $user )
    {
        $objet->setLock( 1 );
        $objet->setLockedBy( $user );

        $this->save( $objet );

        return $objet;
    }

    /**
     * Dévérouille un objet en accès
     *
     * @param Objet $objet Objet concerné
     *
     * @return empty
     */
    public function unlock( $objet )
    {
        $objet->setLock( 0 );
        $objet->setLockedBy( null );

        $this->save( $objet );
    }

    /**
     * Formatte les références sous forme d'un unique tableau
     *
     * @param objet $objet      Objet concerné
     * @param array $references Liste des références de type dictionnaire
     *
     * @return array
     */
    public function getReferences($objet, $references)
    {
        $selectedReferences = $objet->getReferences();
        $disabledChilds     = array();

        //applique les références 
        foreach( $selectedReferences as $selected )
        {
            //on récupère l'élément que l'on va manipuler
            $ref = $references[ $selected->getReference()->getId() ];

            //on le met à jour 
            $ref->selected = true;
            $ref->primary  = $selected->getPrimary();

            //si on est un enfant et que l'on est présent dans le tableau disabled childs, on devient disabled (car notre parent est sélectionné)
            if( in_array($ref->id, $disabledChilds))
                $ref->disabled = true;

            //si y'a des enfants, on ajoute les ids dans les disabledChilds
            if( !is_null($ref->childs) ){
                $childs         = json_decode($ref->childs);
                $disabledChilds = array_unique( array_merge($disabledChilds, $childs) );
            }

            //on remet l'élément à sa place
            $references[ $selected->getReference()->getId() ] = $ref;
        }
        
        return $references;
    }
    
    /**
     * Retourne la liste des objets pour un ambassadeur donné
     * 
     * @param integer $idUser Id de l'ambassadeur
     */
    public function getObjetsByAmbassadeur( $idUser )
    { 
        return $this->getRepository()->getObjetsByAmbassadeur( $idUser );
    }

    /**
     * Retourne la liste des objets non maitrisés par l'ambassadeur
     * 
     * @param integer $id Id de l'ambassadeur
     */
    public function getObjetsNonMaitrises( $id )
    { 
        $results = $this->findAll();
        $objets  = array();

        foreach ($results as $one)
        {
            $add = true;
            $ambassadeurs = $one->getAmbassadeurs();
            if( count($ambassadeurs) >= 1 ){
                foreach($ambassadeurs as $ambassadeur){
                    if( $ambassadeur->getId() == $id ){
                        $add = false;
                        break;
                    }
                }
            }

            if( $add ) {
                $objet        = new \stdClass;
                $objet->id    = $one->getId();
                $objet->titre = $one->getTitre();
                $objets[]     = $objet;
            }
        }

        return $objets;
    }

    /**
     * Vérifie que le rôle ne fait pas partie de la liste des rôles exclus
     *
     * @param string $role  Rôle de l'user connecté
     * @param Objet  $objet L'entitée Objet
     *
     * @return boolean
     */
    public function checkAccessToObjet($role, $objet)
    {
        //on teste si le rôle de l'user connecté ne fait pas parti de la liste des restriction de l'objet
        $roles = $objet->getRoles();
        foreach($roles as $restrictedRole){
            //on "break" en retournant null, l'objet n'est pas ajouté
            if( $restrictedRole->getRole() == $role)
                return false;
        }

        return true;
    }














    /**
     * Réarrange les objets pour afficher correctement les types
     *
     * @param array $results Les résultats de la requete
     *
     * @return array
     */
    private function _rearangeForTypes( $results )
    {
        $objets  = array();

        foreach($results as $result)
        {
            if( isset( $objets[ $result['id'] ] ) )
                $objets[ $result['id'] ]['types'] .= ', ' . $result['types'];
            else
                $objets[ $result['id'] ] = $result;
        }

        return array_values($objets);
    }
}