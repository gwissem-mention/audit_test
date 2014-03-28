<?php

namespace HopitalNumerique\ReferenceBundle\Manager;

use Nodevo\AdminBundle\Manager\Manager as BaseManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use HopitalNumerique\ReferenceBundle\Entity\Reference;

/**
 * Manager de l'entité Reference.
 */
class ReferenceManager extends BaseManager
{
    protected $_class       = 'HopitalNumerique\ReferenceBundle\Entity\Reference';
    private $_tabReferences = array();

    /**
     * Formate et retourne l'arborescence des références
     *
     * @param  boolean $unlockedOnly     Retourne que les éléments non vérouillés
     * @param  boolean $fromDictionnaire Retourne que les éléments présent dans le dictionnaire
     * @param  boolean $fromRecherche    Retourne que les éléments présent dans la recherche
     *
     * @return array
     */
    public function getArbo( $unlockedOnly = false, $fromDictionnaire = false, $fromRecherche = false )
    {
        //get All References, and convert to ArrayCollection
        $datas = new ArrayCollection( $this->getRepository()->getArbo( $unlockedOnly, $fromDictionnaire, $fromRecherche ) );

        //Récupère uniquement les premiers parents
        $criteria = Criteria::create()->where(Criteria::expr()->eq("parent", null) );
        $parents  = $datas->matching( $criteria );
        
        //call recursive function to handle all datas
        return $this->getArboRecursive($datas, $parents, array() );
    }
    
    /**
     * Retourne l'arbo formatée
     *
     * @param  boolean $unlockedOnly     Retourne que les éléments non vérouillés
     * @param  boolean $fromDictionnaire Retourne que les éléments présent dans le dictionnaire
     * @param  boolean $fromRecherche    Retourne que les éléments présent dans la recherche
     *
     * @return array
     */
    public function getArboFormat( $unlockedOnly = false, $fromDictionnaire = false, $fromRecherche = false )
    {
        $arbo = $this->getArbo($unlockedOnly, $fromDictionnaire, $fromRecherche );
        return $this->formatArbo($arbo);
    }
    
    /**
     * Override : Récupère les données pour le grid sous forme de tableau
     *
     * @return array
     */
    public function getDatasForGrid( $condition = null )
    {
        return $this->getRepository()->getDatasForGrid( $condition );
    }

    /**
     * Réordonne les options en mode parent |-- enfants
     *
     * @param array $options Tableau d'options
     *
     * @return array
     */
    public function reorder( $options )
    {
        $this->reorderChilds( $this->getArbo( true ) );
        $orderedOptions = array();

        foreach($this->_tabReferences as $orderedElement) {
            if( isset($options[ $orderedElement ]) )
                $orderedOptions[ $orderedElement ] = $options[ $orderedElement ];
        }

        return $orderedOptions;
    }

    /**
     * Mise à jour de l'order des référentiels
     *
     * @param $referentiel \HopitalNumerique\ReferenceBundle\Entity\Reference Référentiel à mettre à jour
     */
    public function updateOrder( Reference $referentiel )
    {
        
        //get All References
        $datas = new ArrayCollection($this->getRepository()->findBy(array('lock'=>0)));
        
        //Récupération des réferentiels
        $criteria = Criteria::create()
                                    ->where(Criteria::expr()->eq("parent", $referentiel->getParent()))
                                    ->andWhere(Criteria::expr()->gte("order", $referentiel->getOrder()))
                                    ->orderBy( array("order" => Criteria::ASC) );
        $childs = $datas->matching( $criteria );

        //On décale les référentiels dont l'order est supérieur ou égal au référentiel courant
        $newOrder = $referentiel->getOrder();
        foreach ($childs as $referentielAModifier) {
            if ($referentielAModifier === $referentiel)
                continue;

            $newOrder++;
            $referentielAModifier->setOrder( $newOrder );
            $this->save($referentielAModifier);
        }
    }

    /*
     * Raffraîchit l'ordre des referentiels
     */
    public function refreshOrder( $referentiel = null )
    {
        //get All References
        $datas = new ArrayCollection($this->getRepository()->findBy( array( 'lock' => 0 ) ) );
        
        $this->refreshReferentielOrder($datas, $referentiel);
    }

    /**
     * Retourne la liste des objets ordonnées pour les références des objets
     *
     * @return array
     */
    public function getRefsForGestionObjets()
    {
        $this->convertAsFlatArray( $this->getArbo(false, true), 1, array() );
        
        return $this->_tabReferences;
    }

    /**
     * Formatte la liste des domaines fonctionnels de l'user
     *
     * @param User $user User
     *
     * @return Array
     */
    public function getDomainesForUser ( $user )
    {
        $results = $this->findBy( array( 'code' => 'PERIMETRE_FONCTIONNEL_DOMAINES_FONCTIONNELS') );

        // on formatte les domaines de manière à les sélectionner plus simplement
        $domaines = array();
        foreach($results as $result){
            $tmp           = new \stdClass;
            $tmp->id       = $result->getId();
            $tmp->libelle  = $result->getLibelle();
            $tmp->selected = false;

            $domaines[ $result->getId() ] = $tmp;
        }

        //on met en place ceux attribué à l'user
        $userDomaines = $user->getDomaines();
        foreach( $userDomaines as $one){
            $tmp                       = $domaines[ $one->getId() ];
            $tmp->selected             = true;
            $domaines[ $one->getId() ] = $tmp;
        }

        return $domaines;
    }
















    /**
     * Converti l'arbo sous forme de tableau unique avec l'arbo représentée dans le nom de l'élément
     * On remplis ici un tableau global, pour écomiser des ressources via un array_merge et conserver les cléfs
     *
     * @param array   $childs Liste des éléments à ajouter au tableau
     * @param integer $level  Level de profondeur
     *
     * @return array
     */
    private function convertAsFlatArray( $childs, $level, $parent )
    {
        //affiche le level sous forme de |--
        $sep = '';
        if( $level > 1 ) {
            for( $i = 2; $i <= $level; $i++ )
                $sep .= '|--';
            $sep .= ' ';
        }

        //create Child Ids table : permet de lister les ID de tous les enfants de l'élément : selection récursive des références
        $childsIds = array();

        //pour chaque element, on le transforme on objet simple avec son niveau de profondeur affiché dans le nom
        foreach($childs as $child)
        {
            //populate childsIds
            $childsIds[] = $child->id;

            //create object
            $ref           = new \stdClass;
            $ref->id       = $child->id;
            $ref->nom      = $sep . $child->code . ' - ' . $child->libelle;
            $ref->selected = false;
            $ref->primary  = false;
            $ref->childs   = null;
            $ref->parents  = json_encode($parent);
            $ref->level    = $level;

            //add element parent first
            $this->_tabReferences[ $child->id ] = $ref;

            //if childs
            if ( !empty($child->childs) ){
                //met à jour le tab parent
                $tmp = $parent;
                $tmp[] = $child->id;

                //on met à jour sa liste d'enfants
                $newChilds = $this->convertAsFlatArray( $child->childs, $level + 1, $tmp );

                //récupère temporairement l'élément que l'on vien d'ajouter
                $tmp = $this->_tabReferences[ $child->id ];
                $tmp->childs = json_encode( $newChilds );

                //on remet l'élément parent à sa place
                $this->_tabReferences[ $child->id ] = $tmp;

                //merge les enfants
                $childsIds = array_merge($childsIds, $newChilds);
            }            
        }

        return $childsIds;
    }

    /**
     *  Retourne la liste des enfants de manière récursive
     *
     *  @param array  $childs Liste des elements à parcourir
     *
     *  @return empty
     */
    private function reorderChilds( $childs )
    {
        foreach($childs as $child)
        {
            $this->_tabReferences[ '0' . $child->id ] = $child->id;

            if ( !empty($child->childs) )
                $this->reorderChilds( $child->childs );
        }
    }

    /**
     * Fonction récursive qui parcourt l'ensemble des références en ajoutant l'element et recherchant les éventuels enfants
     *
     * @param ArrayCollection $items    Données d'origine
     * @param array           $elements Tableau d'élements à ajouter
     * @param array           $tab      Tableau de données vide à remplir puis retourner
     *
     * @return array
     */
    private function getArboRecursive( ArrayCollection $items, $elements, $tab)
    {        
        foreach($elements as $element) {
            //construction de l'element current
            $id = $element['id'];
            $item          = new \stdClass;
            $item->libelle = $element['libelle'];
            $item->code    = $element['code'];
            $item->id      = $id;

            //add childs : filter items with current element
            $criteria     = Criteria::create()->where(Criteria::expr()->eq("parent", $id))->orderBy(array("order"=>Criteria::ASC));
            $childs       = $items->matching( $criteria );
            $item->childs = $this->getArboRecursive($items, $childs, array() );

            //add current item to big table
            $tab[] = $item;
        }

        //return big table
        return $tab;
    }

    /**
     * Rafraîchit l'ordre des referentiels enfants d'un $referentiel
     *
     * @param ArrayCollection   $referentiels Les référentiels à ordonner
     * @param Reference         $referentiel  Le référentiel parent
     */
    private function refreshReferentielOrder( ArrayCollection $referentiels, Reference $referentiel = null)
    {
        $childs = $this->getReferentielChildsFromCollection($referentiels, $referentiel);
        
        for ($i=0; $i < count($childs); $i++) { 
            $childs[$i]->setOrder($i+1);
            $this->save($childs[$i]);
            $this->refreshReferentielOrder($referentiels, $childs[$i]);
        }
    }

    /**
     * Récupère dans une collection de $referentiels tous les referentiels qui ont pour parent $parent
     * 
     * @param ArrayCollection   $referentiels   Liste des référentiels
     * @param Reference         $parent         Réferentiel parent
     *
     * @return $childs Les référentiels enfants de $parent
     */
    private function getReferentielChildsFromCollection( ArrayCollection $referentiels, Reference $parent = null)
    {
        //si le parent n'existe pas, on filtre dans la collection pour récupérer TOUS les referentiels SANS parents
        if (null === $parent){
            $criteria = Criteria::create()
                                        ->where(Criteria::expr()->eq("parent", $parent))
                                        ->orderBy( array("order" => Criteria::ASC) );
            $childs = $referentiels->matching( $criteria );
        //si le parent existe, on récupère tous ses enfants
        } else 
            $childs = $parent->getChildsFromCollection( $referentiels );

        return $childs;
    }

    /**
     * Formatte de manière récursive l'arborescence des références
     *
     * @param array $arbo [description]
     *
     * @return array
     */
    private function formatArbo($arbo)
    {
        $retour = array();
        foreach( $arbo as $key => $ref ){
            $retour[ $ref->code ][ $key ]['libelle'] = $ref->libelle;
            $retour[ $ref->code ][ $key ]['id']      = $ref->id;
            if( $ref->childs ){
                $retour[ $ref->code ][ $key ]['childs'] = $this->formatArbo( $ref->childs );
            } else {
                $retour[ $ref->code ][ $key ]['childs'] = false;
            }
        }
        return $retour;
    }
}