<?php

namespace HopitalNumerique\RechercheBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de la recherche
 */
class SearchManager extends BaseManager
{
    private $_production            = 175;
    private $_ressource             = 183;
    private $_pointDur              = 184;
    private $_refObjetManager       = null;
    private $_refsPonderees         = null;
    private $_ccdnAuthorizer        = null;
    private $_urlRechercheTextuelle = "";
    private $_activationExalead     = false;
    
    /**
     * Override du contrct d'un manager normal : ce manager n'est lié à aucune entitée
     *
     * @param RefObjetManager   $refObjetManager   Entitée RefObjetManager
     */
    public function __construct( $refObjetManager, $ccdnAuthorizer, $options = array() )
    {
        $this->_refObjetManager   = $refObjetManager;
        $this->_ccdnAuthorizer    = $ccdnAuthorizer;

        $this->_urlRechercheTextuelle = isset($options['urlRechercheTextuelle']) ? $options['urlRechercheTextuelle'] : '';
        $this->_activationExalead = isset($options['activationExalead']) ? $options['activationExalead'] : '';
    }

    /**
     * Permet de récuperer les options du parameter.yml
     *
     * @return [type]
     */
    public function getUrlRechercheTextuelle()
    {
        return $this->_urlRechercheTextuelle;
    }

    /**
     * Permet de récuperer les options du parameter.yml
     *
     * @return boolean
     */
    public function getActivationExalead()
    {
        return $this->_activationExalead;
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
        $meta['hasPageBreak'] = strpos($desc,'<!-- pagebreak -->') !== false;

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
                    if( (array_key_exists('objet', $item) && is_null($item['objet']) && $item['id'] == $publication->getId()) || (array_key_exists('objet', $item) && !is_null($item['objet']) && $item['id'] == $publication->getId()) ) {

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

        usort($tabToReturn, array($this, 'sortObjets'));

        return $tabToReturn;
    }

    private function sortObjets($objet1, $objet2)
    {
        if ($objet1['primary'] > $objet2['primary']) {
            return -1;
        } elseif ($objet1['primary'] < $objet2['primary']) {
            return 1;
        }

        return (($objet1['countRef'] > $objet2['countRef']) ? -1 : (($objet1['countRef'] < $objet2['countRef']) ? 1 : 0));
    }

    /**
     * Retourne la liste des objets pour la question d'autodiag
     *
     * @param array $references Liste de références
     * @param Role  $role       Role de l'utilisateur
     *
     * @return array
     */
    public function getObjetsForAutodiag( $domaineId, $references, $role )
    {
        //prepare some vars
        $objetsToIntersect   = array();
        $contenusToIntersect = array();

        //on récupères tous les objets, on les formate et on les ajoute à nos catégories
        $results = $this->_refObjetManager->getObjetsForRecherche( $references );

        if( $results )
        {
            $tmp = array();
            foreach( $results as $one) 
            {
                if(!in_array($domaineId, $one->getObjet()->getDomainesId()))
                {
                    continue;
                }
                $objet = $this->formateObjet( $one, $role );
                if( !is_null($objet) && $objet['categ'] != '' )
                {
                    $tmp[ $objet['id'] ] = $objet;
                }
            }

            //il y'a eu des résultats pour cette catégorie, on place donc ces résultats dans le tableau d'intersection (analyse multi categ)
            $objetsToIntersect[] = $tmp;
        }
        else
        {
            $objetsToIntersect[] = array();
        }

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
        if( $results )
        {
            $tmp = array();
            foreach( $results as $one) 
            {
                $objet = $this->formateObjet( $one );
                if( !is_null($objet) && $objet['categ'] != '' )
                {
                    $tmp[ $objet['id'] ] = $objet;
                }
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

        //**************************
        //******** OBJET ***********
        //**************************
        
        //Calcul de la somme des primary avant l'intersect
        $compteurPrimaryObjet = array();
        foreach ($objetsToIntersect as $categ) 
        {
            foreach ($categ as $idObjet => $objet) 
            {
                if(array_key_exists($idObjet, $compteurPrimaryObjet))
                {
                    $compteurPrimaryObjet[$idObjet] += $objet['primary'];
                }
                else
                {
                    $compteurPrimaryObjet[$idObjet] = $objet['primary'];
                }
            }
        }

        //Si on a filtré sur plusieurs catégories, on récupère uniquement les objets commun à chaque catégorie (filtre ET)
        if( isset($objetsToIntersect[0]) )
        {
            $objets = (count($objetsToIntersect) > 1) ? call_user_func_array('array_intersect_key',$objetsToIntersect) : $objetsToIntersect[0];
        }

        //Set le primary total pour chaque objet
        foreach ($objets as $key => $objet) 
        {
            if(array_key_exists($key, $compteurPrimaryObjet))
            {
                $objets[$key]['primary'] = $compteurPrimaryObjet[$key];
            }
        }

        //**************************
        //******** CONTENU *********
        //**************************
        
        //Calcul de la somme des primary avant l'intersect
        $compteurPrimaryContenu = array();
        foreach ($contenusToIntersect as $categ) 
        {
            foreach ($categ as $idContenu => $contenu) 
            {
                if(array_key_exists($idContenu, $compteurPrimaryContenu))
                {
                    $compteurPrimaryContenu[$idContenu] += $contenu['primary'];
                }
                else
                {
                    $compteurPrimaryContenu[$idContenu] = $contenu['primary'];
                }
            }
        }
        //Si on a filtré sur plusieurs catégories, on récupère uniquement les contenus commun à chaque catégorie (filtre ET)
        if( isset($contenusToIntersect[0]) )
        {
            $contenus = (count($contenusToIntersect) > 1) ? call_user_func_array('array_intersect_key',$contenusToIntersect) : $contenusToIntersect[0];
        }

        //Set le primary total pour chaque contenu
        foreach ($contenus as $key => $contenu) 
        {
            if(array_key_exists($key, $compteurPrimaryContenu))
            {
                $contenus[$key]['primary'] = $compteurPrimaryContenu[$key];
            }
        }

        //**************************
        //******** FORUM ***********
        //**************************
        
        //Calcul de la somme des primary avant l'intersect
        $compteurPrimaryFilForum = array();
        foreach ($filsForumToIntersect as $categ) 
        {
            foreach ($categ as $idFilForum => $filForum) 
            {
                if(array_key_exists($idFilForum, $compteurPrimaryFilForum))
                {
                    $compteurPrimaryFilForum[$idFilForum] += $filForum['primary'];
                }
                else
                {
                    $compteurPrimaryFilForum[$idFilForum] = $filForum['primary'];
                }
            }
        }
        //Si on a filtré sur plusieurs catégories, on récupère uniquement les fils du forum commun à chaque catégorie (filtre ET)
        if( isset($filsForumToIntersect[0]) )
        {
            $filsForum = (count($filsForumToIntersect) > 1) ? call_user_func_array('array_intersect_key',$filsForumToIntersect) : $filsForumToIntersect[0];
        }
        
        //Set le primary total pour chaque topic
        foreach ($filsForum as $key => $filForum) 
        {
            if(array_key_exists($key, $compteurPrimaryFilForum))
            {
                $filsForum[$key]['primary'] = $compteurPrimaryFilForum[$key];
            }
        }

        $fusion = array_merge( $objets, $contenus, $filsForum );

        if( empty($fusion) )
        {
            return $fusion;
        }

        //make a $sort array for multi-sort function
        $sort = array();
        foreach($fusion as $k=>$v) {
            $sort['primary'][$k]  = $v['primary'];
            $sort['countRef'][$k] = $v['countRef'];
            $sort['id'][$k]       = $v['id'];
        }
        //sort by primary desc and then countRef asc
        array_multisort($sort['primary'], SORT_DESC, $sort['countRef'], SORT_ASC,$sort['id'], SORT_DESC,$fusion);

        return $fusion;
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
        $item['primary'] = array_key_exists('primary', $item) ? ( $one->getPrimary() ? $item['primary']++ : $item['primary']) : ($one->getPrimary() ? 1 : 0) ;

        //objet
        $objet = $one->getObjet();

        if( !is_null($role) )
        {
            //on teste si le rôle de l'user connecté ne fait pas parti de la liste des restriction de l'objet
            $roles = $objet->getRoles();
            foreach($roles as $restrictedRole)
            {
                //on "break" en retournant null, l'objet n'est pas ajouté
                if( $restrictedRole->getRole() == $role)
                {
                    return null;
                }
            }
        }

        $item['id']       = $objet->getId();
        $item['titre']    = $objet->getTitre();
        $item['countRef'] = $this->getNoteReferencement($objet->getReferences());
        $item['objet']    = null;
        $item['alias']    = $objet->getAlias();
        $item['source']   = $objet->getSource();
        $item['synthese'] = $objet->getSynthese() != '' ? $objet->getId() : null;

        //clean resume (pagebreak)
        $tab                  = explode('<!-- pagebreak -->', $objet->getResume() );
        $item['resume']       = html_entity_decode(strip_tags($tab[0]), 2 | 0, 'UTF-8');
        $item['hasPageBreak'] = strpos($objet->getResume(),'<!-- pagebreak -->') !== false;

        //get Categ and Type
        $tmp = $this->getTypeAndCateg( $objet );
        $item['type']  = $tmp['type'];
        $item['categ'] = $tmp['categ'];

        //status (new/updated/datecreation)
        $item['new']      = false;
        $item['updated']  = false;
        $item['created']  = $objet->getDateCreation();
        $item['modified'] = $objet->getDateModification();

        return $item;
    }

    /**
     * Calcul la note de la publication/contenu basée sur ses références
     *
     * @param array $references Liste des références
     *
     * @return integer
     */
    private function getNoteReferencement( $references )
    {
        $note = 0;
        foreach($references as $reference)
        {
            $id = $reference->getReference()->getId();

            if( isset($this->_refsPonderees[ $id ]) )
            {
                $note += $this->_refsPonderees[ $id ]['poids'];
            }
        }
        
        return $note;
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
            if( is_null($one->getFirstParent()) ){
                if( $one->getId() == $this->_ressource ){
                    $categ  = 'ressource';
                    $type[] = $one->getLibelle();
                }elseif($one->getId() == $this->_pointDur ){
                    $categ  = 'point-dur';
                    $type[] = $one->getLibelle();
                }
            //parent : check production / forum
            }else{
                $parent = $one->getFirstParent();
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
