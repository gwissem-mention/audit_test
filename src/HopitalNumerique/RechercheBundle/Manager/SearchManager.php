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
    private $_refContenuManager     = null;
    private $_refsPonderees         = null;
    private $_refTopicManager       = null;
    private $_ccdnAuthorizer        = null;
    private $_urlRechercheTextuelle = "";
    private $_activationExalead     = false;
    
    /**
     * Override du contrct d'un manager normal : ce manager n'est lié à aucune entitée
     *
     * @param RefObjetManager   $refObjetManager   Entitée RefObjetManager
     * @param RefContenuManager $refContenuManager Entitée RefContenuManager
     * @param RefTopicManager   $refTopicManager   Entitée RefTopicManager
     */
    public function __construct( $refObjetManager, $refContenuManager, $refTopicManager, $ccdnAuthorizer, $options = array() )
    {
        $this->_refObjetManager   = $refObjetManager;
        $this->_refContenuManager = $refContenuManager;
        $this->_refTopicManager   = $refTopicManager;
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
     * Récupération des objets formatés pour la recherche textuelle uniquement
     *
     * @param Array(Objet)   $objetsRecherche   Tableau des objets retournés par l'exalead
     * @param Array(Contenu) $contenusRecherche Tableau des contenus retournés par l'exalead
     * @param String         $role              Nom du role de l'utilsateur accedant à la requête pour la vérif d'autorisation d'accès aux différents objets
     *
     * @return Array(array()) Retourne un tableau des objets formatés en tableaux pour l'affichage de la recherche
     */
    public function getObjetsForRechercheTextuelle( $objetsRecherche , $contenusRecherche, $role)
    {
        $results = array();

        //Parcourt les objets pour les ajouter (si la date de publication est renseignée et respectée) formaté au tableau des résults en vérifiant l'accès
        foreach ($objetsRecherche as $objet) 
        {
            //Objet actif
            if($objet->getEtat()->getId() !== 3)
            {
                continue;
            }

            if( !is_null($objet->getDateDebutPublication()) )
            {
                $today = new \DateTime();

                //L'objet n'est pas encore publié on ne le prend pas en compte
                if($today < $objet->getDateDebutPublication())
                {
                    continue;
                }
            }
            if( !is_null($objet->getDateFinPublication()) )
            {
                $today = new \DateTime();

                //L'objet n'est plus publié on ne le prend pas en compte
                if($today > $objet->getDateFinPublication())
                {
                    continue;
                }
            }

            //Gestion des catégories
            if($objet->isArticle())
            {
                continue;
            }

            $bonneCategorie = true;
            $types          = $objet->getTypes();
            if($objet->isArticle())
            {
                foreach ($types as $type)
                {
                    if($type->getId() === 175)
                    {
                        $bonneCategorie = false;
                        break;
                    }
                    if($type->getCode() !== "CATEGORIE_OBJET")
                    {
                        $bonneCategorie = false;
                        break;
                    }
                }
            }

            if(!$bonneCategorie)
            {
                continue;
            }

            //Formatage de l'objet courant
            if( !is_null($role) ) 
            {
                $notAllowed = false;
                //on teste si le rôle de l'user connecté ne fait pas parti de la liste des restriction de l'objet
                $roles = $objet->getRoles();
                foreach($roles as $restrictedRole)
                {
                    //on "break" en retournant null, l'objet n'est pas ajouté
                    if( $restrictedRole->getRole() == $role)
                    {
                        $notAllowed = true;
                        break;
                    }
                }
                //L'utilisateur n'a pas le droit de voir l'objet, on ne le prend pas en compte
                if($notAllowed)
                {
                    continue;
                }    
            }

            $item = array();

            $item['primary']  = 0;
            
            $item['id']       = $objet->getId();
            $item['titre']    = $objet->getTitre();
            $item['countRef'] = $this->getNoteReferencement($objet->getReferences());
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
            $item['new']      = false;
            $item['updated']  = false;
            $item['created']  = $objet->getDateCreation();
            $item['modified'] = $objet->getDateModification();

            $results[] = $item;
        }

        //Parcourt les contenus pour les ajouter (si la date de publication de l'objet lié est renseignée et respectée) formaté au tableau des résults en vérifiant l'accès
        foreach ($contenusRecherche as $contenu) 
        {
            $objet = $contenu->getObjet();

            if( !is_null($objet->getDateDebutPublication()) )
            {
                $today = new \DateTime();

                if($today < $objet->getDateDebutPublication())
                {
                    continue;
                }
            }
            if( !is_null($objet->getDateFinPublication()) )
            {
                $today = new \DateTime();

                if($today > $objet->getDateFinPublication())
                {
                    continue;
                }
            }

            //on teste si le rôle de l'user connecté ne fait pas parti de la liste des restriction de l'objet
            $roles = $objet->getRoles();
            foreach($roles as $restrictedRole){
                //on "break" en retournant null, l'objet n'est pas ajouté
                if( $restrictedRole->getRole() == $role)
                    return null;
            }

            $item = array();

            $item['primary']  = 0;

            $item['id']       = $contenu->getId();
            $item['titre']    = $contenu->getTitre();
            $item['countRef'] = $this->getNoteReferencement($contenu->getReferences());
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
            $item['new']      = false;
            $item['updated']  = false;
            $item['created']  = $contenu->getDateCreation();
            $item['modified'] = $contenu->getDateModification();

            $results[] = $item;
        }

        return $results;
    }
    
    /**
     * Retourne la liste des objets concernés par la requete de recherche
     *
     * @param array  $references Liste des références sélectionées
     * @param string $role       Role de l'user connecté
     *
     * @return array
     */
    public function getObjetsForRecherche( $references, $role, $refsPonderees )
    {
        //prepare some vars
        $nbCateg              = 4;
        $objetsToIntersect    = array();
        $contenusToIntersect  = array();
        $this->_refsPonderees = $refsPonderees;
        $filsForumToIntersect = array();

        //get objets from each Categ
        for ( $i = 1; $i <= $nbCateg; $i++ ) 
        {
            //si on a filtré sur la catégorie
            if( isset($references['categ'.$i]) )
            {
                //on récupères tous les objets, on les formate et on les ajoute à nos catégories
                $results = $this->_refObjetManager->getObjetsForRecherche( $references['categ'.$i] );
                if( $results )
                {
                    $tmp = array();
                    foreach( $results as $one) 
                    {
                        $objet = $this->formateObjet( $one, $role );
                        if( !is_null($objet) && $objet['categ'] != '' )
                        {
                            //Dans le cas où on est déjà sur une ref d'un objet qui existe déjà, on ajout les primary
                            if(array_key_exists($objet['id'], $tmp))
                            {
                                $primary                        = $tmp[ $objet['id'] ]['primary'];
                                $tmp[ $objet['id'] ]            = $objet;
                                $tmp[ $objet['id'] ]['primary'] = $tmp[ $objet['id'] ]['primary'] + $primary;
                            }
                            else
                            {
                                $tmp[ $objet['id'] ] = $objet;
                            }
                        }
                    }
                    //il y'a eu des résultats pour cette catégorie, on place donc ces résultats dans le tableau d'intersection (analyse multi categ)
                    $objetsToIntersect[] = $tmp;
                }
                else
                {
                    $objetsToIntersect[] = array();
                }

                //on récupères tous les contenus (infradoc), on les formate et on les ajoute à nos catégories
                $results = $this->_refContenuManager->getContenusForRecherche( $references['categ'.$i] );
                if( $results ) {
                    $tmp = array();
                    foreach( $results as $one) {
                        $contenu = $this->formateContenu( $one, $role );
                        if( !is_null($contenu) && $contenu['categ'] != '' )
                        {
                            //Dans le cas où on est déjà sur une ref d'un contenu qui existe déjà, on ajout les primary
                            if(array_key_exists($contenu['id'], $tmp))
                            {
                                $primary                          = $tmp[ $contenu['id'] ]['primary'];
                                $tmp[ $contenu['id'] ]            = $contenu;
                                $tmp[ $contenu['id'] ]['primary'] = $tmp[ $contenu['id'] ]['primary'] + $primary;
                            }
                            else
                            {
                                $tmp[ $contenu['id'] ] = $contenu;
                                $tmp[ $contenu['id'] ]['primary'] = intval($contenu['primary'] ? 1 : 0);
                            }
                        }
                    }

                    //il y'a eu des résultats pour cette catégorie, on place donc ces résultats dans le tableau d'intersection (analyse multi categ)
                    $contenusToIntersect[] = $tmp;
                }
                else
                {
                    $contenusToIntersect[] = array();
                }

                //on récupères tous les objets, on les formate et on les ajoute à nos catégories
                $results = $this->_refTopicManager->getTopicForRecherche( $references['categ'.$i] );                
                if( $results ){
                    $tmp = array();
                    foreach( $results as $one) {
                        $topic = $this->formateTopic( $one, $role );
                        if( !is_null($topic) && $topic['categ'] != '' )
                        {
                            //Dans le cas où on est déjà sur une ref d'un topic qui existe déjà, on ajout les primary
                            if(array_key_exists($topic['id'], $tmp))
                            {
                                $primary                        = $tmp[ $topic['id'] ]['primary'];
                                $tmp[ $topic['id'] ]            = $topic;
                                $tmp[ $topic['id'] ]['primary'] = $tmp[ $topic['id'] ]['primary'] + $primary;
                            }
                            else
                            {
                                $tmp[ $topic['id'] ] = $topic;
                                $tmp[ $topic['id'] ]['primary'] = intval($topic['primary'] ? 1 : 0);
                            }
                        }
                    }

                    //il y'a eu des résultats pour cette catégorie, on place donc ces résultats dans le tableau d'intersection (analyse multi categ)
                    $filsForumToIntersect[] = $tmp;
                }
                else
                {
                    $filsForumToIntersect[] = array();
                }
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
                    $compteurPrimaryObjet[$idObjet] += $objet['primary'];
                else
                    $compteurPrimaryObjet[$idObjet] = $objet['primary'];
            }
        }

        //Si on a filtré sur plusieurs catégories, on récupère uniquement les objets commun à chaque catégorie (filtre ET)
        if( isset($objetsToIntersect[0]) )
            $objets = (count($objetsToIntersect) > 1) ? call_user_func_array('array_intersect_key',$objetsToIntersect) : $objetsToIntersect[0];

        //Set le primary total pour chaque objet
        foreach ($objets as $key => $objet) 
        {
            if(array_key_exists($key, $compteurPrimaryObjet))
                $objets[$key]['primary'] = $compteurPrimaryObjet[$key];
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
                    $compteurPrimaryContenu[$idContenu] += $contenu['primary'];
                else
                    $compteurPrimaryContenu[$idContenu] = $contenu['primary'];
            }
        }
        //Si on a filtré sur plusieurs catégories, on récupère uniquement les contenus commun à chaque catégorie (filtre ET)
        if( isset($contenusToIntersect[0]) )
            $contenus = (count($contenusToIntersect) > 1) ? call_user_func_array('array_intersect_key',$contenusToIntersect) : $contenusToIntersect[0];

        //Set le primary total pour chaque contenu
        foreach ($contenus as $key => $contenu) 
        {
            if(array_key_exists($key, $compteurPrimaryContenu))
                $contenus[$key]['primary'] = $compteurPrimaryContenu[$key];
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
                    $compteurPrimaryFilForum[$idFilForum] += $filForum['primary'];
                else
                    $compteurPrimaryFilForum[$idFilForum] = $filForum['primary'];
            }
        }
        //Si on a filtré sur plusieurs catégories, on récupère uniquement les fils du forum commun à chaque catégorie (filtre ET)
        if( isset($filsForumToIntersect[0]) )
            $filsForum = (count($filsForumToIntersect) > 1) ? call_user_func_array('array_intersect_key',$filsForumToIntersect) : $filsForumToIntersect[0];
        
        //Set le primary total pour chaque topic
        foreach ($filsForum as $key => $filForum) 
        {
            if(array_key_exists($key, $compteurPrimaryFilForum))
                $filsForum[$key]['primary'] = $compteurPrimaryFilForum[$key];
        }

        $fusion = array_merge( $objets, $contenus, $filsForum );

        if( empty($fusion) )
            return $fusion;

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
        $item['primary'] = array_key_exists('primary', $item) ? ( $one->getPrimary() ? $item['primary']++ : $item['primary']) : ($one->getPrimary() ? 1 : 0) ;

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
        $item['countRef'] = $this->getNoteReferencement($contenu->getReferences());
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
        $item['new']      = false;
        $item['updated']  = false;
        $item['created']  = $contenu->getDateCreation();
        $item['modified'] = $contenu->getDateModification();

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
        $item['primary'] = array_key_exists('primary', $item) ? ( $one->getPrimary() ? $item['primary']++ : $item['primary']) : ($one->getPrimary() ? 1 : 0) ;

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
        $item['countRef'] = $this->getNoteReferencement($objet->getReferences());
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
        foreach($references as $reference){
            $id = $reference->getReference()->getId();

            if( isset($this->_refsPonderees[ $id ]) )
                $note += $this->_refsPonderees[ $id ]['poids'];
        }
        
        return $note;
    }
   
    /*
     * Formatte Correctement les refTopic
     *
     * @param RefTopic $one  L'entité RefTopic
     * 
     * @return stdClass
     */
    private function formateTopic( $one, $role = null )
    {
        //Références
        $item            = array();
        $item['primary'] = array_key_exists('primary', $item) ? ( $one->getPrimary() ? $item['primary']++ : $item['primary']) : ($one->getPrimary() ? 1 : 0) ;

        //topic
        $topic = $one->getTopic();
        $forum = $topic->getBoard()->getCategory()->getForum();

        if( !is_null($role) ) {
            //si on à pas accès au topic, on retourne null
            if( !$this->_ccdnAuthorizer->canShowTopic( $topic, $forum ) )
                return null;
        }

        $item['id']       = $topic->getId();
        $item['titre']    = $topic->getTitle();
        $item['countRef'] = $this->getNoteReferencement($topic->getReferences());
        
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
