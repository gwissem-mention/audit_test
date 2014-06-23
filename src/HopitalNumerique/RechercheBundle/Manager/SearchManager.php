<?php

namespace HopitalNumerique\RechercheBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

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
    private $_refTopicManager   = null;
    
    /**
     * Override du contrct d'un manager normal : ce manager n'est lié à aucune entitée
     *
     * @param RefObjetManager   $refObjetManager   Entitée RefObjetManager
     * @param RefContenuManager $refContenuManager Entitée RefContenuManager
     * @param RefTopicManager   $refTopicManager   Entitée RefTopicManager
     */
    public function __construct( $refObjetManager, $refContenuManager, $refTopicManager )
    {
        $this->_refObjetManager   = $refObjetManager;
        $this->_refContenuManager = $refContenuManager;
        $this->_refTopicManager   = $refTopicManager;
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
        $nbCateg              = 4;
        $objetsToIntersect    = array();
        $contenusToIntersect  = array();
        $filsForumToIntersect = array();

        //get objets from each Categ
        for ( $i = 1; $i <= $nbCateg; $i++ ) {
            //si on a filtré sur la catégorie
            if( isset($references['categ'.$i]) ) {
                //on récupères tous les objets, on les formate et on les ajoute à nos catégories
                $results = $this->_refObjetManager->getObjetsForRecherche( $references['categ'.$i] );
                if( $results ){
                    $tmp = array();
                    foreach( $results as $one) {
                        $objet = $this->formateObjet( $one, $role );
                        if( !is_null($objet) && $objet['categ'] != '' )
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
                        $contenu = $this->formateContenu( $one, $role );
                        if( !is_null($contenu) && $contenu['categ'] != '' )
                            $tmp[ $contenu['id'] ] = $contenu;
                    }

                    //il y'a eu des résultats pour cette catégorie, on place donc ces résultats dans le tableau d'intersection (analyse multi categ)
                    $contenusToIntersect[] = $tmp;
                }
                else
                    $contenusToIntersect[] = array();

                //on récupères tous les objets, on les formate et on les ajoute à nos catégories
                $results = $this->_refTopicManager->getTopicForRecherche( $references['categ'.$i] );                
                if( $results ){
                    $tmp = array();
                    foreach( $results as $one) {
                        $topic = $this->formateTopic( $one, $role );
                        if( !is_null($topic) && $topic['categ'] != '' )
                            $tmp[ $topic['id'] ] = $topic;
                    }

                    //il y'a eu des résultats pour cette catégorie, on place donc ces résultats dans le tableau d'intersection (analyse multi categ)
                    $filsForumToIntersect[] = $tmp;
                }else
                    $filsForumToIntersect[] = array();
            }
        }

        return $this->mergeDatas( $objetsToIntersect, $contenusToIntersect, $filsForumToIntersect );
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
     * Formatte les objets issues de la recherche : ne récupére que 10 résultats (autour de l'élément sélectionné)
     *
     * @param array         $objets      Liste des objets
     * @param Objet|Contenu $publication La publication (objet|contenu)
     *
     * @return array
     */
    public function formatForPublication($objets, $publication)
    {
        $results = array();
        foreach($objets as $item)
            $results[ $item['categ'] ][] = $item;

        $tabToReturn = array();
        foreach($results as $categ) {
            if( count($categ) > 10 ){
                $i         = 1;
                $maxResult = null;
                $toAdd     = array();

                foreach ($categ as $item) {
                    //objet Found here
                    if( (is_null($item['objet']) && $item['id'] == $publication->getId()) || (!is_null($item['objet']) && $item['id'] == $publication->getId()) ) {

                        //si i < 5 : on prend ceux d'avant, et on met à jour le max result
                        if( $i < 5 ){
                            $maxResult = $i + (10 - $i);

                            //on ajoute tous ceux d'avant
                            for($j = 1; $j <= $i; $j++)
                                $toAdd[] = $categ[$j];
                        }

                        //si i > 5 : on prend les 5 d'avants, et on met à jour le max result pour prendre les 5 suivants
                        if( $i >= 5 ){
                            $maxResult = $i + 5;

                            //on ajoute les 5 d'avant
                            for($j = ($i-5); $j < $i; $j++)
                                $toAdd[] = $categ[$j];
                        }
                    //Objet found before
                    }else if( !is_null($maxResult) ){
                        if ( $i <= $maxResult )
                            $toAdd[] = $item;

                        //on break une fois 10 atteint
                        if( $i == $maxResult )
                            break;
                    }

                    $i++;
                }

                //objet never found
                if( count($toAdd) != 10 ){
                    for($i = 0; $i < (10-count($toAdd)); $i++)
                        $toAdd[] = $categ[$i];
                }

                $tabToReturn = array_merge( $toAdd, $tabToReturn);
            }else
                $tabToReturn = array_merge( $categ, $tabToReturn);
        }

        return $tabToReturn;
    }

    /**
     * Retourne la liste des objets pour la question d'autodiag
     *
     * @param array $references Liste de références
     * @param Role  $role       Role de l'utilisateur
     *
     * @return array
     */
    public function getObjetsForAutodiag( $references, $role )
    {
        //prepare some vars
        $objetsToIntersect   = array();
        $contenusToIntersect = array();

        //on récupères tous les objets, on les formate et on les ajoute à nos catégories
        $results = $this->_refObjetManager->getObjetsForRecherche( $references );
        if( $results ){
            $tmp = array();
            foreach( $results as $one) {
                $objet = $this->formateObjet( $one, $role );
                if( !is_null($objet) && $objet['categ'] != '' )
                    $tmp[ $objet['id'] ] = $objet;
            }

            //il y'a eu des résultats pour cette catégorie, on place donc ces résultats dans le tableau d'intersection (analyse multi categ)
            $objetsToIntersect[] = $tmp;
        }else
            $objetsToIntersect[] = array();

        //on récupères tous les contenus (infradoc), on les formate et on les ajoute à nos catégories
        $results = $this->_refContenuManager->getContenusForRecherche( $references );
        if( $results ) {
            $tmp = array();
            foreach( $results as $one) {
                $contenu = $this->formateContenu( $one, $role );
                if( !is_null($contenu) && $contenu['categ'] != '' )
                    $tmp[ $contenu['id'] ] = $contenu;
            }

            //il y'a eu des résultats pour cette catégorie, on place donc ces résultats dans le tableau d'intersection (analyse multi categ)
            $contenusToIntersect[] = $tmp;
        }
        else
            $contenusToIntersect[] = array();

        return $this->mergeDatas( $objetsToIntersect, $contenusToIntersect, array() );
    }

    /**
     * Retourne la liste des objets pour le cron d'autodiag
     *
     * @param array $references Liste de références
     *
     * @return array
     */
    public function getObjetsForCronAutodiag( $references )
    {
        $objets = array();

        //on récupères tous les objets, on les formate et on les ajoute à nos catégories
        $results = $this->_refObjetManager->getObjetsForRecherche( $references );
        if( $results ){
            $tmp = array();
            foreach( $results as $one) {
                $objet = $this->formateObjet( $one );
                if( !is_null($objet) && $objet['categ'] != '' )
                    $tmp[ $objet['id'] ] = $objet;
            }

            $objets = array_merge($tmp, $objets);
        }

        return $objets;
    }











    /**
     * Merge les données objetds et les données contenus
     *
     * @param array $objetsToIntersect   Les objets
     * @param array $contenusToIntersect Les contenus
     *
     * @return array
     */
    private function mergeDatas( $objetsToIntersect, $contenusToIntersect, $filsForumToIntersect )
    {
        $objets    = array();
        $contenus  = array();
        $filsForum = array();

        //Si on a filtré sur plusieurs catégories, on récupère uniquement les objets commun à chaque catégorie (filtre ET)
        if( isset($objetsToIntersect[0]) )
            $objets = (count($objetsToIntersect) > 1) ? call_user_func_array('array_intersect_key',$objetsToIntersect) : $objetsToIntersect[0];
        
        //Si on a filtré sur plusieurs catégories, on récupère uniquement les contenus commun à chaque catégorie (filtre ET)
        if( isset($contenusToIntersect[0]) )
            $contenus = (count($contenusToIntersect) > 1) ? call_user_func_array('array_intersect_key',$contenusToIntersect) : $contenusToIntersect[0];

        //Si on a filtré sur plusieurs catégories, on récupère uniquement les fils du forum commun à chaque catégorie (filtre ET)
        if( isset($filsForumToIntersect[0]) )
            $filsForum = (count($filsForumToIntersect) > 1) ? call_user_func_array('array_intersect_key',$filsForumToIntersect) : $filsForumToIntersect[0];

        $fusion = array_merge( $objets, $contenus, $filsForum );

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
     * Formatte Correctement les refContenus
     *
     * @param RefContenu $one  L'entité RefContenu
     * @param string     $role Le rôle de l'user connecté
     *
     * @return stdClass
     */
    private function formateContenu( $one, $role )
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
        $item['resume'] = html_entity_decode(strip_tags($tab[0]), 2 | 0, 'UTF-8');
        $item['type']   = array();

        //get Categ and Type
        $tmp = $this->getTypeAndCateg( $objet );
        $item['type']  = $tmp['type'];
        $item['categ'] = $tmp['categ'];

        //status (new/updated/datecreation)
        $item['new']     = false;
        $item['updated'] = false;
        $item['created'] = $contenu->getDateCreation();

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
    private function formateObjet( $one, $role = null )
    {
        //Références
        $item            = array();
        $item['primary'] = $one->getPrimary();

        //objet
        $objet = $one->getObjet();
        
        if( !is_null($role) ) {
            //on teste si le rôle de l'user connecté ne fait pas parti de la liste des restriction de l'objet
            $roles = $objet->getRoles();
            foreach($roles as $restrictedRole){
                //on "break" en retournant null, l'objet n'est pas ajouté
                if( $restrictedRole->getRole() == $role)
                    return null;
            }    
        }
        
        $item['id']       = $objet->getId();
        $item['titre']    = $objet->getTitre();
        $item['nbRef']    = count($objet->getReferences());
        $item['objet']    = null;
        $item['alias']    = $objet->getAlias();
        $item['synthese'] = $objet->getSynthese() != '' ? $objet->getId() : null;

        //clean resume (pagebreak)
        $tab = explode('<!-- pagebreak -->', $objet->getResume() );
        $item['resume'] = html_entity_decode(strip_tags($tab[0]), 2 | 0, 'UTF-8');
        
        //get Categ and Type
        $tmp = $this->getTypeAndCateg( $objet );
        $item['type']  = $tmp['type'];
        $item['categ'] = $tmp['categ'];
        
        //status (new/updated/datecreation)
        $item['new']     = false;
        $item['updated'] = false;
        $item['created'] = $objet->getDateCreation();

        return $item;
    }

    /**
     * Formatte Correctement les refTopic
     *
     * @param RefTopic $one  L'entité RefTopic
     * 
     * @return stdClass
     */
    private function formateTopic( $one )
    {
        //Références
        $item            = array();
        $item['primary'] = $one->getPrimary();

        //topic
        $topic = $one->getTopic();
        
        $item['id']    = $topic->getId();
        $item['titre'] = $topic->getTitle();
        $item['nbRef'] = count($topic->getReferences());
        
        //clean resume (pagebreak)
        //$tab = explode('<!-- pagebreak -->', $topic->getResume() );
        //$item['resume'] = html_entity_decode(strip_tags($tab[0]), 2 | 0, 'UTF-8');
        
        //get Type
        $item['categ'] = 'forum';

        return $item;
    }

    /**
     * Extrait la catégorie et le(s) type(s) de l'objet
     *
     * @param Objet $objet Entitée objet
     *
     * @return array
     */
    private function getTypeAndCateg( $objet )
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
                }
            }
        }
        //reformatte proprement les types
        $type = implode(' ♦ ', $type);

        return array('categ' => $categ, 'type' => $type );
    }
}
