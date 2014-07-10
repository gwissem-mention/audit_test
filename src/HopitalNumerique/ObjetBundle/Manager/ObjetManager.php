<?php

namespace HopitalNumerique\ObjetBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use Doctrine\ORM\EntityManager;
use \Nodevo\ToolsBundle\Tools\Chaine;

/**
 * Manager de l'entité Objet.
 */
class ObjetManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\ObjetBundle\Entity\Objet';
    protected $_contenuManager;

    /**
     * Construct 
     *
     * @param EntityManager  $em      Entity Mangager de doctrine
     * @param ContenuManager $manager ContenuManager
     */
    public function __construct( EntityManager $em, ContenuManager $manager )
    {
        parent::__construct($em);

        $this->_contenuManager = $manager;
    }

    /**
     * Override : Récupère les données pour le grid sous forme de tableau
     *
     * @return array
     */
    public function getDatasForGrid( $condition = null )
    {
        $results = $this->getRepository()->getDatasForGrid( $condition )->getQuery()->getResult();

        return $this->rearangeForTypes( $results );
    }

    /**
     * Récupère les objets pour l'export
     *
     * @return array
     */
    public function getDatasForExport( $ids, $refsPonderees )
    {
        $objets  = $this->getRepository()->getDatasForExport( $ids )->getQuery()->getResult();
        $results = array();

        foreach($objets as $objet) {
            $row = array();

            //simple stuff
            $row['id']           = $objet->getId();
            $row['titre']        = $objet->getTitre();
            $row['alias']        = $objet->getAlias();
            $row['commentaires'] = $objet->getCommentaires() ? 'Oui' : 'Non';
            $row['notes']        = $objet->getNotes()        ? 'Oui' : 'Non';
            $row['type']         = $objet->isArticle()       ? 'Article' : 'Objet';
            $row['nbVue']        = $objet->getNbVue();
            $row['etat']         = $objet->getEtat() ? $objet->getEtat()->getLibelle() : '';
            $row['fichier1']     = $objet->getPath();
            $row['fichier2']     = $objet->getPath2();
            $row['fichierEdit']  = $objet->getPathEdit();
            $row['vignette']     = $objet->getVignette();
            $row['note']         = number_format($this->getNoteReferencement($objet->getReferences(), $refsPonderees), 0);

            //quelques Dates
            $row['dateCreation']         = !is_null($objet->getDateCreation())         ? $objet->getDateCreation()->format('d/m/Y')         : '';
            $row['dateDebutPublication'] = !is_null($objet->getDateDebutPublication()) ? $objet->getDateDebutPublication()->format('d/m/Y') : '';
            $row['dateFinPublication']   = !is_null($objet->getDateFinPublication())   ? $objet->getDateFinPublication()->format('d/m/Y')   : '';
            $row['dateModification']     = !is_null($objet->getDateModification())     ? $objet->getDateModification()->format('d/m/Y')     : '';
            
            //handle Roles
            $roles        = $objet->getRoles();
            $row['roles'] = array();
            foreach($roles as $role)
                $row['roles'][] = $role->getName();
            $row['roles'] = implode(', ', $row['roles']);
                
            //handle types (catégories)
            $types        = $objet->getTypes();
            $row['types'] = array();
            foreach($types as $type)
                $row['types'][] = $type->getLibelle();
            $row['types'] = implode(', ', $row['types']);

            //handle Ambassadeurs concernés
            $ambassadeurs        = $objet->getAmbassadeurs();
            $row['ambassadeurs'] = array();
            foreach($ambassadeurs as $ambassadeur)
                $row['ambassadeurs'][] = $ambassadeur->getPrenomNom();
            $row['ambassadeurs'] = implode(', ', $row['ambassadeurs']);

            //set empty values for objet (infra doc)
            $row['idC'] = $row['titreC'] = $row['aliasC'] = $row['orderC'] = $row['contenuC'] = $row['dateCreationC'] = $row['dateModificationC'] = $row['nbVueC'] = $row['noteC'] = '';

            //add Object To Results
            $results[] = $row;

            if( $objet->isInfraDoc() ) {
                $contenus = $objet->getContenus();
                if( $contenus ){
                    foreach($contenus as $contenu) {                        
                        $row = array();

                        //init empty for infra doc
                        $row['id'] = $row['titre'] = $row['alias'] = $row['synthese'] = $row['resume'] = $row['commentaires'] = $row['notes'] = $row['type'] = $row['nbVue'] = $row['etat'] = '';
                        $row['dateCreation'] = $row['dateDebutPublication'] = $row['dateFinPublication'] = $row['dateModification'] = $row['roles'] = $row['types'] = $row['ambassadeurs'] = '';
                        $row['fichier1'] = $row['fichier2'] = $row['fichierEdit'] = $row['vignette'] = $row['note'] = '';

                        //Infra doc values
                        $row['idC']               = $contenu->getId();
                        $row['titreC']            = $contenu->getTitre();
                        $row['aliasC']            = $contenu->getAlias();
                        $row['orderC']            = $contenu->getOrder();
                        $row['dateCreationC']     = !is_null($contenu->getDateCreation()) ? $contenu->getDateCreation()->format('d/m/Y') : '';
                        $row['dateModificationC'] = !is_null($contenu->getDateModification()) ? $contenu->getDateModification()->format('d/m/Y') : '';
                        $row['nbVueC']            = $contenu->getNbVue();
                        $row['noteC']             = number_format($this->getNoteReferencement($contenu->getReferences(), $refsPonderees), 0);

                        //add Infra-doc To Results
                        $results[] = $row;
                    }
                }
            }
        }

        return $results;
    }

    /**
     * Retourne la liste des objets selon le/les types
     *
     * @param array $types Les types à filtrer
     *
     * @return array
     */
    public function getObjetsByTypes( $types, $limit = 0 )
    {
        return $this->getRepository()->getObjetsByTypes( $types, $limit )->getQuery()->getResult();
    }
    
    /**
     * Retourne l'ensemble des productions actives
     */
    public function getProductionsActive()
    {
        return $this->getRepository()->getProductionsActive()->getQuery()->getResult();
    }

    /**
     * Récupère les données du grid sous forme de tableau correctement formaté
     *
     * @return array
     */
    public function getDatasForGridAmbassadeur( $condition = null )
    {
        $results = $this->getRepository()->getDatasForGridAmbassadeur( $condition )->getQuery()->getResult();
        
        return $this->rearangeForTypes( $results );
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

        //applique les références 
        foreach( $selectedReferences as $selected )
        {
            //on récupère l'élément que l'on va manipuler
            $ref = $references[ $selected->getReference()->getId() ];

            //on le met à jour 
            $ref->selected = true;
            $ref->primary  = $selected->getPrimary();

            //on remet l'élément à sa place
            $references[ $selected->getReference()->getId() ] = $ref;
        }
        
        return $references;
    }

    /**
     * Formatte les références sous forme d'un unique tableau
     *
     * @param objet $objet      Objet concerné
     * @param array $references Liste des références de type dictionnaire
     *
     * @return array
     */
    public function getReferencesOwn($objet)
    {
        $return = array();
        $selectedReferences = $objet->getReferences();

        //applique les références 
        foreach( $selectedReferences as $selected ){
            $reference = $selected->getReference();

            //on remet l'élément à sa place
            $return[ $reference->getId() ]['nom']     = $reference->getCode() . " - " . $reference->getLibelle();
            $return[ $reference->getId() ]['primary'] = $selected->getPrimary();
            
            if( $reference->getParent() )
                $return[ $reference->getParent()->getId() ]['childs'][] = $reference->getId();
        }
        
        $this->formatReferencesOwn( $return );
        
        return $return;
    }
    
    /**
     * Retourne la liste des objets pour un ambassadeur donné
     * 
     * @param integer $idUser Id de l'ambassadeur
     */
    public function getObjetsByAmbassadeur( $idUser )
    { 
        return $this->getRepository()->getObjetsByAmbassadeur( $idUser )->getQuery()->getResult();
    }
    
    /**
     * Retourne la liste des objets non maitrisés par l'ambassadeur
     *
     * @param integer $id    Id de l'ambassadeur
     * @param array   $types Liste des types
     *
     * @return array
     */
    public function getObjetsNonMaitrises( $id, $types )
    {
        $results = $this->getProductions( $types );
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
     * Retourne la liste des productions
     *
     * @param array   $types Liste des types
     *
     * @return array
     */
    public function getProductions( $types )
    {
        //Remove Points Dur et Ressources Externes
        foreach($types as $key => $type){
            if( $type->getId() == 183 || $type->getId() == 184)
                unset($types[$key]);
        }

        return $this->getObjetsByTypes( $types );
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
     * Formatte les types d'objet sous forme d'une chaine de caractère avec le séparateur $sep
     *
     * @param array  $types Types de l'objet
     * @param string $sep   Séparateur pour l'implode
     *
     * @return string
     */
    public function formatteTypes( $types, $sep = ' ♦ ' )
    {
        $tabType  = array();
        foreach ($types as $type)
            $tabType[] = $type->getLibelle();

        return implode($sep, $tabType);
    }

    /**
     * [testAliasExist description]
     *
     * @param  [type] $objet [description]
     * @param  [type] $new   [description]
     *
     * @return [type]
     */
    public function testAliasExist( $objet, $new )
    {
        $alias = $this->findOneBy( array( 'alias'=>$objet->getAlias() ) );

        if( $alias && $new === true )
            return true;
        elseif( $alias && $new === false && $alias->getId() != $objet->getId() )
            return true;
        
        return false;
    }

    /**
     * Retourne l'arbo Objets -> contenus
     *
     * @return array
     */
    public function getObjetsAndContenuArbo( $types = null )
    {
        //get objets and IDS
        $objets = is_null($types) ? $this->findAll() : $this->getObjetsByTypes( $types );
        $ids    = array();
        foreach( $objets as $one )
            $ids[] = $one->getId();

        //get Contenus
        $datas    = $this->_contenuManager->getArboForObjet($ids);
        $contenus = array();
        foreach( $datas as $one ) {
            if( $one->objet != null )
                $contenus[ $one->objet ][] = $one;
        }

        //formate datas
        foreach( $objets as $one ) 
        {
            //Traitement pour Article
            if($one->isArticle())
            {
                $results[] = array(
                        "text"  => $one->getTitre(),
                        "value" => "ARTICLE:" . $one->getId()
                );
            }
            //Traitement pour Publication et Infradoc
            else 
            {
                $results[] = array(
                        "text"  => $one->getTitre(),
                        "value" => "PUBLICATION:" . $one->getId()
                );
                
                if( !isset($contenus[ $one->getId() ]) || count( $contenus[ $one->getId() ] ) <= 0 )
                    continue;
                
                foreach( $contenus[ $one->getId() ] as $content ){
                    $results[] = array(
                            "text"  => "|--" . $content->titre,
                            "value" => "INFRADOC:" . $content->id
                    );
                    $this->getObjetsChilds($results, $content, 2);
                }
            }
            
        }

        return $results;
    }

    /**
     * Retorune l'arbo des articles
     *
     * @return array
     */
    public function getArticlesArbo( $types )
    {
        //get objets
        $objets = $this->getObjetsByTypes( $types );

        //formate datas
        foreach( $objets as $one ) {
            $results[] = array(
                "text" => $one->getTitre(), "value" => "ARTICLE:" . $one->getId()
            );
        }

        return $results;
    }

    /**
     * Retourne la liste des actualités des catégories passées en paramètre
     *
     * @param array $categories Les catégories
     *
     * @return array
     */
    public function getActualitesByCategorie( $categories, $role, $limit = 0 )
    {
        $articles   = $this->getObjetsByTypes( $categories, $limit );
        $actualites = array();

        foreach($articles as $article) {
            if( $this->checkAccessToObjet($role, $article) ) {
                $actu = new \stdClass;

                $actu->id    = $article->getId();
                $actu->titre = $article->getTitre();
                $actu->alias = $article->getAlias();
                $actu->image = $article->getVignette() ? $article->getVignette() : false;

                //resume
                $tab = explode('<!-- pagebreak -->', $article->getResume());
                //$actu->resume = html_entity_decode(strip_tags($tab[0]), 2 | 0, 'UTF-8');
                $actu->resume = $tab[0];

                //types / catégories
                $types            = $article->getTypes();
                $actu->types      = $this->formatteTypes( $types );
                $actu->categories = $this->getCategorieForUrl( $article->getTypes() );

                $actualites[] = $actu;
            }
        }

        return $actualites;
    }
    
    /**
     * Retourne les catégories qui ont des articles
     *
     * @param array $allCategories Liste des catégories
     *
     * @return array
     */
    public function getCategoriesWithArticles( $allCategories )
    {
        $categories = array();
        foreach($allCategories as $one) {
            $articles = $this->getObjetsByTypes( array($one) );
            if( count($articles) > 0)
            {
                $categ          = new \stdClass;
                $categ->id      = $one->getId();
                $categ->libelle = $one->getLibelle();

                $categories[] = $categ;
            }
        }

        return $categories;
    }

    /**
     * Retourne l'objet article pour la page d'accueil
     *
     * @return stdClass
     */
    public function getArticleHome()
    {
        $article = $this->findOneBy( array('id' => 1) );
        $item    = new \stdClass;
        
        $item->id         = $article->getId();
        $item->titre      = $article->getTitre();
        $item->alias      = $article->getAlias();
        $item->image      = $article->getVignette() ? $article->getVignette() : false;
        $item->categories = $this->getCategorieForUrl( $article->getTypes() );

        //resume
        $tab = explode('<!-- pagebreak -->', $article->getResume());
        $item->resume = $tab[0];

        return $item;
    }

    /**
     * Retourne la note de l'objet
     *
     * @param array $references   Tableau des références
     * @param array $ponderations Tableau des pondérations
     *
     * @return integer
     */
    public function getNoteReferencement( $references, $ponderations )
    {
        $note = 0;
        foreach($references as $reference){
            $id = $reference->getReference()->getId();

            if( isset($ponderations[ $id ]) )
                $note += $ponderations[ $id ]['poids'];
        }
        
        return $note;
    }













    /**
     * Formatte les types de l'objet pour les URLS (catégorie param)
     *
     * @param array $types Les types de l'objet
     *
     * @return string
     */
    private function getCategorieForUrl( $types )
    {
        $type      = $types[0];
        $categorie = '';

        if( $parent = $type->getParent() )
            $categorie .= $parent->getLibelle().'-';
        $categorie .= $type->getLibelle();

        $tool = new Chaine( $categorie );

        return $tool->minifie();
    }

    /**
     * Ajoute les enfants de $objet dans $return, formatées en fonction de $level
     * 
     * @param array    $return
     * @param stdClass $objet
     * @param integer  $level
     * 
     * @return void
     */
    private function getObjetsChilds( &$return, $objet, $level = 1 )
    {
        if( count($objet->childs) > 0 ){
            foreach( $objet->childs as $child ){
                $texte = str_pad($child->titre, strlen($child->titre) + ($level*3), "|--", STR_PAD_LEFT);
                $return[] = array(
                    "text" => $texte, "value" => "INFRADOC:" . $child->id
                );
                $this->getObjetsChilds($return, $child, $level + 1);
            }
        }
    }

    /**
     * Réarrange les objets pour afficher correctement les types
     *
     * @param array $results Les résultats de la requete
     *
     * @return array
     */
    private function rearangeForTypes( $results )
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
    
    /**
     * [formatReferencesOwn description]
     *
     * @param  [type] $retour [description]
     *
     * @return [type]
     */
    private function formatReferencesOwn( &$retour )
    {
        foreach( $retour as $key => $one ){
            $retour[ $key ]['childs'] = $this->getChilds($retour, $one);
        }
    }
    
    /**
     * [getChilds description]
     *
     * @param  [type] $retour [description]
     * @param  [type] $elem   [description]
     *
     * @return [type]
     */
    private function getChilds(&$retour, $elem)
    {
        if( isset( $elem['childs'] ) && count($elem['childs']) ){
            $childs = array();
            foreach( $elem["childs"] as $key => $one ){
                $childs[ $one ] = $retour[ $one ];
                $petitsEnfants  = $this->getChilds($retour, $childs[ $one ]);
                if( $petitsEnfants ){
                    $childs[ $one ]['childs'] = $petitsEnfants;
                    unset( $retour[ $one ] );
                } else {
                    unset( $retour[ $one ] );
                }
            }
            return $childs;
        } else {
            return false;
        }
    }
}
