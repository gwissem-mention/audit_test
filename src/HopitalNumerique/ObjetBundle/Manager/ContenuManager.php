<?php

namespace HopitalNumerique\ObjetBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use Nodevo\ToolsBundle\Tools\Chaine;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

use HopitalNumerique\UserBundle\Manager\UserManager;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;


/**
 * Manager de l'entité Contenu.
 */
class ContenuManager extends BaseManager
{
    protected $class = 'HopitalNumerique\ObjetBundle\Entity\Contenu';
    protected $_userManager;
    protected $_referenceManager;
    private $_refPonderees;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\Session Session
     */
    private $_session;

    /**
     * Construct 
     *
     * @param EntityManager  $em              Entity Mangager de doctrine
     *
     */
    public function __construct( EntityManager $em, UserManager $userManager, ReferenceManager $referenceManager)
    {
        parent::__construct($em);

        $this->_userManager      = $userManager;
        $this->_referenceManager = $referenceManager;
    }

    /**
     * [setRefPonderees description]
     *
     * @param [type] $refPonderees [description]
     */
    public function setRefPonderees( $refPonderees )
    {
        $this->_refPonderees = $refPonderees;
    }

    /**
     * Retourne l'arbo des contenu de l'objet (ou des objets)
     *
     * @param integer|array $id ID de(s) l'objet(s)
     *
     * @return array
     */
    public function getArboForObjet( $id, $domaineIds = array() )
    {
        $datas = new ArrayCollection( $this->getRepository()->getArboForObjet( $id, $domaineIds )->getQuery()->getResult() );

        //Récupère uniquement les premiers parents
        $criteria = Criteria::create()->where(Criteria::expr()->eq("parent", null) );
        $parents  = $datas->matching( $criteria );
        
        //call recursive function to handle all datas
        return $this->getArboRecursive($datas, $parents, array(), '' );
    }

    /**
     * Retourne le nombre des contenus ayant le même alias
     *
     * @param Contenu $contenu Objet contenu
     *
     * @return integer
     */
    public function countAlias( $contenu )
    {
        return $this->getRepository()->countAlias($contenu)->getQuery()->getSingleScalarResult();
    }

    /**
     * Compte le nombre de contenu parents lié à l'objet
     *
     * @param Objet $objet Objet
     *
     * @return integer
     */
    public function countContenu( $objet )
    {
        return $this->getRepository()->countContenu($objet)->getQuery()->getSingleScalarResult();
    }

    /**
     * Retourne le prefix du contenu
     *
     * @param Contenu $contenu Contenu
     *
     * @return string
     */
    public function getPrefix($contenu)
    {
        return $this->getPrefixRecursif( $contenu, '' );
    }

    /**
     * [parseCsv description]
     *
     * @param  [type] $csv   [description]
     * @param  [type] $objet [description]
     *
     * @return [type]
     */
    public function parseCsv( $csv, $objet )
    {
        //parse Str CSV and convert to array
        $lines    = explode("\n", $csv);
        if( empty($lines) )
            return false;

        //gestion des erreurs lors du parse du CSV
        $sommaire = array();
        foreach ($lines as $line) {
            $tmp = str_getcsv($line,';');
            if( isset($tmp[0]) && isset($tmp[1]) && !isset($tmp[2]) )
                $sommaire[] = $tmp;
            else
                return false;
        }

        //ajout des éléments parents only
        $parents = array();
        foreach($sommaire as $element)
        {
            $elem = &$parents;
            list($chapitre, $titre) = $element;
            $numeroChapitre = explode('.', $chapitre);
            foreach( $numeroChapitre as $key => $one ) {
                if ( $key == count($numeroChapitre) - 1 )
                    $elem = &$elem[ $one ];
                else
                    $elem = &$elem[ $one ]['childs'] ;
            }
            $elem['titre'] = $titre;
        }
        
        // clean elements sans titre
        $parents = $this->cleanSansTitre($parents);
        $this->saveContenusCSV($objet, $parents);

        return true;
    }

    /**
     * Retourne le contenu précédant immédiatemment le contenu $contenu dans la liste $contenus
     */
    public function getPrecedent( $contenus, $contenu )
    {
        reset($contenus);
        while ( current($contenus) !== false && current($contenus)->getId() !== $contenu->getId() ) next($contenus);        
        return prev($contenus) == false ? null : current($contenus);
    }

    /**
     * Retourne le contenu suivant immédiatemment le contenu $contenu dans la liste $contenus
     */
    public function getSuivant( $contenus, $contenu )
    {
        reset($contenus);
        while ( current($contenus) !== false && current($contenus)->getId() !== $contenu->getId() ) next($contenus);
        return next($contenus) == false ? null : current($contenus);
    }   

    /**
     * Retourne la liste des contenus qui ont un contenu non vide, triés par ordre (ex : Chapitre 1 - Chapitre 1.1 - Chapitre 1.2 - Chapitre 2 - Chapitre 2.1)
     */
    public function getContenusNonVidesTries( $objet )
    {
        $criteria = Criteria::create()->orderBy( array( "parent" => Criteria::ASC, "order" => Criteria::ASC ) );
        //Récupération de l'ensemble des contenus triés par Parent puis Order
        $contenus = $objet->getContenus()->matching( $criteria );

        //Récupération des contnus de premier niveau
        $contenusParent = array_filter( $contenus->toArray(), function($item) {
            return $item->getParent() == NULL;
        });

        $elements = array();

        foreach ($contenusParent as $key => $item) {
             $this->sortContenusRescursively( $item, $elements, $contenus );
        }

        $elements = array_filter( $elements, function($item) {
            return $item->getContenu() != "";
        });
        
        return $elements;
    }

    /**
     * Retourne l'ordre complet d'un contenu (ex : retourne 2.1.1 si le contenu est le premier enfant du premier enfant du deuxième élément)
     */
    public function getFullOrder($contenu)
    {
        if (!isset($contenu))
            return null;

        $order  = $contenu->getOrder();
        $parent = $contenu;

        while ( ($parent = $parent->getParent()) != NULL )
        {
            $order = $parent->getOrder() . '.' . $order;
        }

        return $order;
    }















    /**
     * Retourne le prefix du contenu
     *
     * @param Contenu $contenu Contenu
     * @param string  $prefix  Prefix
     *
     * @return string
     */
    private function getPrefixRecursif( $contenu, $prefix )
    {
        if(is_null($contenu))
        {
            return $prefix;
        }
        
        $prefix = $contenu->getOrder() . '.' . $prefix;

        if( !is_null($contenu->getParent()) )
            $prefix = $this->getPrefixRecursif($contenu->getParent(), $prefix);
        
        return $prefix;
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
    private function getArboRecursive( ArrayCollection $items, $elements, $tab, $numeroChapitre )
    {
        foreach($elements as $element)
        {
            //creation du mero de chapitre
            $chapitre = $numeroChapitre . $element->getOrder() . '.';

            //construction de l'element current
            $item             = new \stdClass;
            $item->entity = $element;
            $item->titre      = $element->getTitre();
            $item->alias      = $element->getAlias();
            $item->id         = $element->getId();
            $item->nbVue      = $element->getNbVue();
            $item->order      = $chapitre;
            $item->hasContent = $element->getContenu() == ''   ? false : true;
            $item->objet      = $element->getParent()  == null ? $element->getObjet()->getId() : NULL;

            //add childs : filter items with current element
            $criteria     = Criteria::create()->where(Criteria::expr()->eq("parent", $element ))->orderBy( array( "order" => Criteria::ASC ) );
            $childs       = $items->matching( $criteria );
            $item->childs = $this->getArboRecursive($items, $childs, array(), $chapitre );

            //add current item to big table
            $tab[] = $item;
        }

        //return big table
        return $tab;
    }

    /**
     * [getChilds description]
     *
     * @param  [type] $retour [description]
     * @param  [type] $elem   [description]
     *
     * @return [type]
     */
    private function getChilds ( &$retour, $elem )
    {
        if( isset( $elem['childs'] ) && count($elem['childs']) ) {
            $childs = array();
            foreach( $elem["childs"] as $key => $one ) {
                $childs[ $one ] = $retour[ $one ];
                $petitsEnfants  = $this->getChilds($retour, $childs[ $one ]);
                if( $petitsEnfants ){
                    $childs[ $one ]['childs'] = $petitsEnfants;
                    unset( $retour[ $one ] );
                } else
                    unset( $retour[ $one ] );
            }

            return $childs;

        } else
            return false;
    }
    
    /**
     * Fonction qui renvoie uniquement les éléments du tableau ayant l'index "titre" qui existe, de manière récursive
     * 
     * @param array $elements
     * 
     * @return array 
     */
    private function cleanSansTitre($elements)
    {
        $retour = array();
        foreach( $elements as $cle => $elem ){
            if( isset($elem['childs']) )
                $elem['childs'] = $this->cleanSansTitre( $elem['childs'] );
            
            if( isset($elem['titre']) )
                $retour[$cle] = $elem;
        }

        return $retour;
    }
    
    /**
     * Fonctionne qui sauvegarde tous les éléments du sommaire
     * 
     * @param type $objet
     * @param type $contenus
     * @param type $objects
     * @param type $parent
     * @param boolean doit-on sauvegarder $objects
     * 
     * @return void
     */
    private function saveContenusCSV($objet, $contenus, &$objects = array(), $parent = null, $save = true)
    {
        foreach( $contenus as $ordre => $content ){
            //créer un contenu (set titre, generate alias, set objet)
            $contenu = $this->createEmpty();
            $contenu->setObjet( $objet );
            $contenu->setTitre( $content['titre'] );
            $tool = new Chaine( $content['titre'] );
            $contenu->setAlias( $tool->minifie() );
            $contenu->setOrder($ordre);

            if( $parent )
                $contenu->setParent($parent);
            
            $objects[] = $contenu;
            if( isset($content['childs']) )
                $this->saveContenusCSV($objet, $content['childs'], $objects, $contenu, false);
        }
        
        if( $save )
            $this->save($objects);
    }    

    /**
     * Fonction pour trier les contenus récursivement
     */
    private function sortContenusRescursively( $parent, &$elements, $contenus )
    {
        $elements[] = $parent;

        $childs = array_filter( $contenus->toArray(), function($item) use ($parent) {
            return $item->getParent() == $parent;            
        });

        foreach ($childs as $key => $child) {
             $this->sortContenusRescursively( $child, $elements, $contenus );
        } 
    }
}
