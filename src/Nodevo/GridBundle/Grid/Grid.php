<?php 
namespace Nodevo\GridBundle\Grid;

use APY\DataGridBundle\Grid\Source;
use APY\DataGridBundle\Grid\Column;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

/**
 * Classe GRID de Nodevo
 *
 * @author Quentin SOMAZZI <qsomazzi@nodevo.com>
 */
class Grid
{
    // Constantes de class
    const SOURCE_TYPE_ENTITY   = 1;
    const SOURCE_TYPE_DOCUMENT = 2;
    const SOURCE_TYPE_ARRAY    = 3;
    const SOURCE_TYPE_MANAGER  = 4;

    //config
    protected $_grid                 = null;
    protected $_container            = null;
    protected $_source               = null;
    protected $_sourceType           = self::SOURCE_TYPE_ENTITY;
    protected $_sourceCondition      = null;
    protected $_maxResults           = null;
    protected $_limits               = array(10, 20, 50, 100);
    protected $_defaultLimit         = 20;
    protected $_noDataMessage        = ' - Aucun élément à afficher - ';
    protected $_noResultMessage      = ' - Aucun résultat à afficher - ';
    protected $_buttonSize           = 42;
    protected $_fieldParentRecursive = null;
    protected $_fieldLabelRecursive  = null;
    protected $_showIdColumn         = false;
    protected $_filterIdColumn       = false;
    protected $_functionName         = 'getDatasForGrid';
    protected $_persistence          = true;

    //colonnes
    protected $_colonnes         = array();
    protected $_colonnesVisibles = array();

    //actions button
    protected $_buttons = array();

    //mass actions
    protected $_massActions = array();

    /**
     * Récupère le service grid et initialise un object Nodevo Grid
     *
     * @param APY\DataGridBundle\Grid $grid L'objet grid APY
     */
    public function __construct( $container )
    {
        $this->_container = $container;
        $this->_grid      = $this->_container->get('_grid');

        return $this;
    }

    /**
     * Met en place toute la config et retourne l'objet grid
     *
     * @return APY\DataGridBundle\Grid
     */
    public function render( $vue, $params = array() )
    {
        //Initialize the grid with the conf
        $this->initConfig();
        $this->initColonnes();
        $this->initSource();
        $this->initMassActions();

        //return the grid object
        return $this->_grid->getGridResponse( $vue, $params );
    }

    //////////////////////////////////////////////////////////
    //              COLUMNS AND ACTIONS                     //
    //////////////////////////////////////////////////////////
    
        /**
         * Ajoute un action button
         *
         * @param RowAction $button Boutton d'action
         */
        protected function addActionButton( $button )
        {
            $this->_buttons[] = $button;
        }

        /**
         * Ajoute une colonne visible dans le grid
         *
         * @param Column $colonne Colonne
         */
        protected function addColonne( $colonne )
        {
            $this->_colonnes[] = $colonne;
        }

        /**
         * Ajoute une action de masse
         *
         * @param MassAction $massAction Mass Action
         */
        protected function addMassAction( $massAction )
        {
            $this->_massActions[] = $massAction;
        }

    //////////////////////////////////////////////////////////
    //                    CONFIG                            //
    //////////////////////////////////////////////////////////
    
        /**
         * Affiche la colonne ID
         *
         * @param boolean $show Is ID column showed
         */
        protected function showIdColumn( $show )
        {
            $this->_showIdColumn = $show;
        }

        /**
         * Active le filtre de la colonne ID
         *
         * @param boolean $filter Is ID column filterable
         */
        protected function setFilterIdColumn( $filter )
        {
            $this->_filterIdColumn = $filter;
        }

        /**
         * Met en place l'affichage récursif du grid
         *
         * @param string $fieldParent Field de l'Id parent
         * @param string $fieldLabel  Field label
         */
        protected function setAffichageRecursif( $fieldParent = 'idParent', $fieldLabel = 'libelle' )
        {
            $this->_fieldParentRecursive = $fieldParent;
            $this->_fieldLabelRecursive  = $fieldLabel;
        }

        /**
         * Définie la taille par défaut des bouttons
         *
         * @param integer $size Taille par défaut
         */
        protected function setButtonSize( $size = 42 )
        {
            $this->_buttonSize = $size;
        }

        /**
         * Set le message affiché lorsque aucune donnée n'a été trouvée
         *
         * @param string $message Message affiché
         */
        protected function setNoDataMessage( $message = ' - Aucun élément à afficher - ')
        {
            $this->_noDataMessage = $message;
        }

        /**
         * Set le message affiché lorsqu'aucune donnée n'a été trouvée après Filtre
         *
         * @param string $message Message affiché
         */
        protected function setNoResultMessage( $message = ' - Aucun résultat à afficher - ' )
        {
            $this->_noResultMessage = $message;
        }

        /**
         * Limite par défaut de la pagination
         *
         * @param integer $default Taille par défault du la pagination
         */
        protected function setDefaultLimit( $default = 10 )
        {
            $this->_defaultLimit = $default;
        }

        /**
         * Active la persistence des filtres / tri
         *
         * @param boolean $persistence Est-ce que l'on conserve les filtres/tri ?
         */
        protected function setPersistence( $persistence = true )
        {
            $this->_persistence = $persistence;
        }
        
        /**
         * Set les limits de pagination
         *
         * @param array $limits Tableau des limits de la pagination
         */
        protected function setLimits( $limits = array(10, 20, 50, 100) )
        {
            $this->_limits = $limits;
        }

        /**
         * Set le nombre de résultats maximum dans le grid
         *
         * @param integer $maxResults Nombre de résultats maximum
         */
        protected function setMaxResults( $maxResults = 1000 )
        {
            $this->_maxResults = $maxResults;
        }

        /**
         * Set le type de la source des données
         *
         * @param integer $sourceType Type de la source
         */
        protected function setSourceType( $sourceType = self::SOURCE_TYPE_ENTITY )
        {
            $this->_sourceType = $sourceType;
        }

        /**
         * Set la source du grid (peut importe le type)
         *
         * @param string|array $source Source du grid (Entity, Document ou Vector)
         */
        protected function setSource( $source = null )
        {
            $this->_source = $source;
        }

        /**
         * Set le nom de la fonction à appeller pour charger le grid
         *
         * @param string $name Nom de la fonction à appeller
         */
        public function setFunctionName( $name )
        {
            $this->_functionName = $name;
        }

        /**
         * Set la condition de filtrage sur la source des données du grid (si nécessaire)
         * => Obligé de la mettre public car le paramètre est passé dans la Request (donc le controller)
         *
         * @param string $field Field concerné par la condition
         * @param string $value Value requise pour le field
         */
        public function setSourceCondition( $field, $value )
        {
            $condition = new \stdClass;
            $condition->field = $field;
            $condition->value = $value;

            $this->_sourceCondition = $condition;
        }

    //////////////////////////////////////////////////////////
    //             PRIVATE INITIALISATION                   //
    //////////////////////////////////////////////////////////
    
        /**
         * Initialise la configuration par rapport à la classe
         */
        private function initConfig()
        {
            //récupère la config utilisateur
            $this->setConfig();

            //max result
            $this->_grid->setMaxResults( $this->_maxResults );

            //Pagination
            $this->_grid->setLimits( $this->_limits );

            //Pagination par défaut
            $this->_grid->setDefaultLimit( $this->_defaultLimit );

            //Message lorsque vide
            $this->_grid->setNoDataMessage( $this->_noDataMessage );

            //Message lorsque filtrer no Results
            $this->_grid->setNoResultMessage( $this->_noResultMessage );

            //Active la persistence 
            $this->_grid->setPersistence( $this->_persistence );
        }

        /**
         * Initialise les colonnes du grid
         */
        private function initColonnes()
        {
            //récupère les colonnes configurés par l'utilisateur et les boutons d'Action
            $this->setActionsButtons();
            $this->setColumns();

            //parcours des colonnes visibles ( colonnes ajoutées par l'utilisateur dans son grid seulement )
            foreach ($this->_colonnes as $one) {
                if( !($one instanceof \Nodevo\GridBundle\Grid\Column\BlankColumn) )
                    $this->_colonnesVisibles[] = $one->getField();
            }

            //Si il y a des boutons d'action, on ajoute la colonne
            if( count($this->_buttons) > 0 ) {
                $this->_colonnesVisibles[] = 'actions';

                //Ajout des actions au grid
                $actionsColumn = new Column\ActionsColumn('actions', '', $this->_buttons);
                $actionsColumn->setSize( ((count($this->_buttons) * $this->_buttonSize)) + 23 );
                $this->_colonnes[] = $actionsColumn;

                if($this->_sourceType == self::SOURCE_TYPE_ENTITY)
                    $this->_grid->addColumn( $actionsColumn );
            }

            //Ajout de la colonne ID
            $idColumn = new Column\NumberColumn(array('id' => 'id', 'title' => 'ID', 'size' => 50, 'field' => 'id', 'source' => true, 'primary' => true, 'filterable' => $this->_filterIdColumn, 'sortable' =>false));
            array_unshift($this->_colonnes, $idColumn);

            if ( $this->_showIdColumn )
                array_unshift($this->_colonnesVisibles, 'id');

            //met à jour le grid avec les colonnes visibles
            $this->_grid->setVisibleColumns( $this->_colonnesVisibles );
        }

        /**
         * Ajoute la source des données en fonction du type de la source $_sourceType
         */
        private function initSource()
        {
            switch ($this->_sourceType) {
                case self::SOURCE_TYPE_DOCUMENT:
                    $source = new Source\Document( $this->_source );
                    break;

                //Cas manager : utilsé pour récupérer les entitées avec leurs clé étrangères
                case self::SOURCE_TYPE_MANAGER:
                    //Edit : we can change the functionName called in the manager : default = getDatasForGrid
                    $manager = $this->_container->get( $this->_source );

                    //Récupération de la fonction a appellée pour la récupération des données du grid
                    $datas = call_user_func( array($manager, $this->_functionName), $this->_sourceCondition );

                    if( !empty($datas) && !is_null($datas[0]['id']) ){
                        if( !is_null($this->_fieldParentRecursive) && !is_null($this->_fieldLabelRecursive) )
                            $datas = $this->rearengeForRecursive( $datas );
                    }else
                        $datas = array();

                    $source = new Source\Vector( $datas, $this->_colonnes );
                    
                    $source->setId('id');
                    break;

                case self::SOURCE_TYPE_ARRAY:
                    $source = new Source\Vector( $this->_source );
                    break;

                case self::SOURCE_TYPE_ENTITY:
                default:
                    $source = new Source\Entity( $this->_source );
                    break;
            }

            $this->_grid->setSource($source);

            //Si on est en source Entity : on met à jour les colonnes au lieu de les ajouter
            if($this->_sourceType == self::SOURCE_TYPE_ENTITY)
                $this->manageColumnsForEntitySource();
        }

        /**
         * Initialise les actions de masse
         *
         * @return empty
         */
        private function initMassActions()
        {
            //Récupère les mass actions de la config du grid
            $this->setMassActions();

            foreach($this->_massActions as $massAction)
                $this->_grid->addMassAction($massAction);
        }

        /**
         * Manage colonne when datas come from an Entity Source
         *
         * @return empty
         */
        private function manageColumnsForEntitySource()
        {
            foreach($this->_colonnes as $colonne)
            {
                //get column with ID
                $column = $this->_grid->getColumn( $colonne->getId() );

                //repeat the configuration : Bad stuff but we need to do this because columns are already set (by entity) so it's just an update
                $column->setTitle( $colonne->getTitle() );
                $column->setSize( $colonne->getSize() );
                $column->setSortable( $colonne->isSortable() );
                $column->setAlign( $colonne->getAlign() );
                $column->setFilterable( $colonne->isFilterable() );
                $column->setValues( $colonne->getValues() );
                $column->setFilterType( $colonne->getFilterType() );
                $column->setSelectFrom( $colonne->getSelectFrom() );
                $column->setOperatorsVisible( $colonne->getOperatorsVisible() );
            }
        }

        /**
         * Réarrange les données pour les afficher en mode Récursif
         *
         * @param array $datas Tableau de données
         *
         * @return array
         */
        private function rearengeForRecursive( $datas )
        {
            //Création d'un tableau ayant pour clé le véritable id de l'item
            foreach ($datas as $key => $data)
                $datasById[$data['id']] = $data;
            
            //Tri + affichage '|---' (affiché X fois selon la profondeur du fils) devant le nom si l'élément est un fils (TODO : récursivité à faire !)
            foreach ($datas as $key => $data)
            {
                if(NULL !== $data[$this->_fieldParentRecursive])
                    $datas = $this->gestionAffichageSousItems( $key, $datas, $datasById, $datasById[$data['id']] );
            }
            
            return $this->regroupementParentEnfant($datas);
        }

        /**
         * Fonction récursive permettant d'ajouter le préfixe '|---' le nombre de fois qu'il y a de parent pour un item donné
         * 
         * @param int $key          Clé de l'index courant
         * @param array $datas      Tableau des données à modifier
         * @param array $datasById  Tableau des permettant de recupérer un item en fonction de son Id
         * @param array $parentData Tableau contenant les données de l'item parent de l'item courant
         */
        private function gestionAffichageSousItems( $key, $datas, $datasById, $parentData )
        {    
            if( NULL != $parentData[$this->_fieldParentRecursive])
            {
                $datas[$key][$this->_fieldLabelRecursive] = '|--- ' . $datas[$key][$this->_fieldLabelRecursive];

                $parentData = array_key_exists($this->_fieldParentRecursive,$parentData)  && array_key_exists($parentData[$this->_fieldParentRecursive], $datasById)? $datasById[$parentData[$this->_fieldParentRecursive]] : null;
                $datas      = $this->gestionAffichageSousItems( $key, $datas, $datasById, $parentData );
            }
            
            return $datas;
        }
        
        /**
         * Fonction permettant de regrouper les parents/enfants ensemble.
         * 
         * @param array $datas      Tableau des données à regrouper
         * @return array            Tableau des données regroupées
         */
        private function regroupementParentEnfant( $datas )
        {
            //Récupère l'ensemble des données dans un ArrayCollection
            $datasArrayCollection = new ArrayCollection( $datas );
            
            //Récupère que les éléments qui ont des parents
            $criteria = Criteria::create()->where(Criteria::expr()->eq($this->_fieldParentRecursive, null) );
            $parents  = $datasArrayCollection->matching( $criteria )->toArray();
            
            return $this->rangeEnfants($parents, $datasArrayCollection );
        }
        
        /**
         * Fonction récursive permettant de trier tout les niveaux d'enfants et les regrouper
         * 
         * @param array           $items                Tableau d'un niveau de parent/enfant
         * @param ArrayCollection $datasArrayCollection Tableau de l'ensemble des données permettant de faire de tri ou récupération de groupe d'enfant d'un parent donné
         * 
         * @return array Tableau du niveau parent/enfant courant trié
         */
        private function rangeEnfants( $items, $datasArrayCollection)
        {
            $tab = array();

            foreach ($items as $key => $enfant)
            {
                $tab[] = $enfant;

                //Récupère que les éléments qui ont des parents
                $criteria = Criteria::create()->where(Criteria::expr()->eq($this->_fieldParentRecursive, $enfant['id']) );
                $enfants  = $datasArrayCollection->matching( $criteria )->toArray();

                //range les éléments
                $tab = array_merge( $tab, $this->rangeEnfants($enfants, $datasArrayCollection) );
            }
            
            return $tab;
        }
}