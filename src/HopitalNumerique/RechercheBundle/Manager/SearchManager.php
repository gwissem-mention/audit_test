<?php

namespace HopitalNumerique\RechercheBundle\Manager;

use Nodevo\AdminBundle\Manager\Manager as BaseManager;

/**
 * Manager de la recherche
 */
class SearchManager extends BaseManager
{
    private $_production        = 175;
    private $_ressource         = 183;
    private $_pointDur          = 184;
    private $_forum             = 188;
    private $_refObjetManager   = null;
    private $_refContenuManager = null;
    
    /**
     * Override du contrct d'un manager normal : ce manager n'est lié à aucune entitée
     *
     * @param RefObjetManager   $refObjetManager   Entitée RefObjetManager
     * @param RefContenuManager $refContenuManager Entitée RefContenuManager
     */
    public function __construct( $refObjetManager, $refContenuManager )
    {
        $this->_refObjetManager   = $refObjetManager;
        $this->_refContenuManager = $refContenuManager;
    }
    
    /**
     * Retourne la liste des objets concernés par la requete de recherche
     *
     * @param array  $references Liste des références sélectionées
     * @param string $role       Role de l'user connecté
     *
     * @return array
     */
    public function getObjetsForRecherche( $references, $role )
    {
        //prepare some vars
        $nbCateg             = 4;
        $objets              = array();
        $contenus            = array();
        $objetsToIntersect   = array();
        $contenusToIntersect = array();

        //get objets from each Categ
        for ( $i = 1; $i <= $nbCateg; $i++ ) {
            //si on a filtré sur la catégorie
            if( isset($references['categ'.$i]) ) {
                //on récupères tous les objets, on les formate et on les ajoute à nos catégories
                $results = $this->_refObjetManager->getObjetsForRecherche( $references['categ'.$i] );
                if( $results ){
                    $tmp = array();
                    foreach( $results as $one) {
                        $objet = $this->_formateObjet( $one, $role );
                        if( !is_null($objet) )
                            $tmp[ $objet['id'] ] = $objet;
                    }

                    //il y'a eu des résultats pour cette catégorie, on place donc ces résultats dans le tableau d'intersection (analyse multi categ)
                    $objetsToIntersect[] = $tmp;
                }else
                    $objetsToIntersect[] = array();

                //on récupères tous les contenus (infradoc), on les formate et on les ajoute à nos catégories
                $results = $this->_refContenuManager->getContenusForRecherche( $references['categ'.$i] );
                if( $results ) {
                    $tmp = array();
                    foreach( $results as $one) {
                        $contenu = $this->_formateContenu( $one, $role );
                        if( !is_null($contenu) )
                            $tmp[ $contenu['id'] ] = $contenu;
                    }

                    //il y'a eu des résultats pour cette catégorie, on place donc ces résultats dans le tableau d'intersection (analyse multi categ)
                    $contenusToIntersect[] = $tmp;
                }
                else
                    $contenusToIntersect[] = array();
            }
        }

        //Si on a filtré sur plusieurs catégories, on récupère uniquement les objets commun à chaque catégorie (filtre ET)
        if( isset($objetsToIntersect[0]) )
            $objets = (count($objetsToIntersect) > 1) ? call_user_func_array('array_intersect_key',$objetsToIntersect) : $objetsToIntersect[0];
        
        //Si on a filtré sur plusieurs catégories, on récupère uniquement les contenus commun à chaque catégorie (filtre ET)
        if( isset($contenusToIntersect[0]) )
            $contenus = (count($contenusToIntersect) > 1) ? call_user_func_array('array_intersect_key',$contenusToIntersect) : $contenusToIntersect[0];

        $fusion = array_merge( $objets, $contenus );

        if( empty($fusion) )
            return $fusion;

        //make a $sort array for multi-sort function
        $sort = array();
        foreach($fusion as $k=>$v) {
            $sort['primary'][$k] = $v['primary'];
            $sort['nbRef'][$k]   = $v['nbRef'];
            $sort['id'][$k]      = $v['id'];
        }
        //sort by primary desc and then nbRef asc
        array_multisort($sort['primary'], SORT_DESC, $sort['nbRef'], SORT_ASC,$sort['id'], SORT_DESC,$fusion);

        return $fusion;
    }

    /**
     * GetMeta (desc+keywords) : for référencement
     *
     * @param array  $references Liste des références
     * @param string $desc       Resume|contenu
     *
     * @return array
     */
    public function getMetas($references, $desc )
    {
        $meta = array();

        //description
        $tab          = explode('<!-- pagebreak -->', $desc);
        $meta['desc'] = html_entity_decode(strip_tags($tab[0]));

        //keywords
        $meta['keywords'] = array();
        foreach ($references as $reference) {
            $ref                = $reference->getReference();
            $meta['keywords'][] = $ref->getLibelle();
        }

        return $meta;
    }














    /**
     * Formatte Correctement les refContenus
     *
     * @param RefContenu $one  L'entité RefContenu
     * @param string     $role Le rôle de l'user connecté
     *
     * @return stdClass
     */
    private function _formateContenu( $one, $role )
    {
        //Références
        $item            = array();
        $item['primary'] = $one->getPrimary();

        //contenu
        $contenu = $one->getContenu();
        $objet   = $contenu->getObjet();

        //on teste si le rôle de l'user connecté ne fait pas parti de la liste des restriction de l'objet
        $roles = $objet->getRoles();
        foreach($roles as $restrictedRole){
            //on "break" en retournant null, l'objet n'est pas ajouté
            if( $restrictedRole->getRole() == $role)
                return null;
        }

        $item['id']       = $contenu->getId();
        $item['titre']    = $contenu->getTitre();
        $item['nbRef']    = count($contenu->getReferences());
        $item['objet']    = $objet->getId();
        $item['aliasO']   = $objet->getAlias();
        $item['aliasC']   = $contenu->getAlias();
        $item['synthese'] = $objet->getSynthese() != '' ? $objet->getId() : null;

        //clean resume (pagebreak)
        $tab = explode('<!-- pagebreak -->', $contenu->getContenu());
        $item['resume'] = html_entity_decode(strip_tags($tab[0]));
        $item['type']   = array();

        //get Categ and Type
        $tmp = $this->_getTypeAndCateg( $objet );
        $item['type']  = $tmp['type'];
        $item['categ'] = $tmp['categ'];

        return $item;
    }

    /**
     * Formatte Correctement les refObjets
     *
     * @param Refobjet $one  L'entité RefObjet
     * @param string   $role Le rôle de l'user connecté
     * 
     * @return stdClass
     */
    private function _formateObjet( $one, $role )
    {
        //Références
        $item          = array();
        $item['primary'] = $one->getPrimary();

        //objet
        $objet = $one->getObjet();
        
        //on teste si le rôle de l'user connecté ne fait pas parti de la liste des restriction de l'objet
        $roles = $objet->getRoles();
        foreach($roles as $restrictedRole){
            //on "break" en retournant null, l'objet n'est pas ajouté
            if( $restrictedRole->getRole() == $role)
                return null;
        }

        $item['id']       = $objet->getId();
        $item['titre']    = $objet->getTitre();
        $item['nbRef']    = count($objet->getReferences());
        $item['objet']    = null;
        $item['alias']    = $objet->getAlias();
        $item['synthese'] = $objet->getSynthese() != '' ? $objet->getId() : null;

        //clean resume (pagebreak)
        $tab = explode('<!-- pagebreak -->', $objet->getResume() );
        $item['resume'] = html_entity_decode(strip_tags($tab[0]));
        
        //get Categ and Type
        $tmp = $this->_getTypeAndCateg( $objet );
        $item['type']  = $tmp['type'];
        $item['categ'] = $tmp['categ'];
        
        return $item;
    }

    /**
     * Extrait la catégorie et le(s) type(s) de l'objet
     *
     * @param Objet $objet Entitée objet
     *
     * @return array
     */
    private function _getTypeAndCateg( $objet )
    {
        $type  = array();
        $categ = '';
        $types = $objet->getTypes();

        foreach ($types as $one) {
            //pas de parent : check ressource / point dur
            if( is_null($one->getParent()) ){
                if( $one->getId() == $this->_ressource ){
                    $categ  = 'ressource';
                    $type[] = $one->getLibelle();
                }elseif($one->getId() == $this->_pointDur ){
                    $categ  = 'point-dur';
                    $type[] = $one->getLibelle();
                }
            //parent : check production / forum
            }else{
                $parent = $one->getParent();
                if( $parent->getId() == $this->_production ){
                    $categ  = 'production';
                    $type[] = $one->getLibelle();
                }elseif($parent->getId() == $this->_forum ){
                    $categ  = 'forum';
                    $type[] = $one->getLibelle();
                }
            }
        }
        //reformatte proprement les types
        $type = implode(' ♦ ', $type);

        return array('categ' => $categ, 'type' => $type );
    }
}