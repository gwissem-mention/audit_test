<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Grid;

use Nodevo\GridBundle\Grid\Column\TextColumn;
use Nodevo\GridBundle\Grid\Column\DateColumn;
use Nodevo\GridBundle\Grid\Column\BooleanColumn;
use Nodevo\GridBundle\Grid\Action\EditButton;
use Nodevo\GridBundle\Grid\Action\DeleteMass;

/**
 * Grid de Groupe.
 */
class GroupeGrid extends \Nodevo\GridBundle\Grid\Grid
{
    /**
     * @var \HopitalNumerique\UserBundle\Entity\User Utilisateur connecté
     */
    private $user;


    /**
     * {@inheritDoc}
     */
    public function __construct(\Symfony\Component\DependencyInjection\ContainerInterface $container)
    {
        parent::__construct($container);
        
        $this->user = $container->get('security.context')->getToken()->getUser();
        if (!($this->user instanceof \HopitalNumerique\UserBundle\Entity\User)) {
            throw new \Exception('Aucun utilisateur connecté.');
        }
    }


    /**
     * {@inheritDoc}
     */
    public function setConfig()
    {
        $this->setSource('hopitalnumerique_communautepratique.manager.groupe');
        $this->setSourceType(self::SOURCE_TYPE_MANAGER);
        $this->setFunctionName('getGridData');
        $this->showIdColumn( true  ); //Affiche la colonne ID; default
		$this->setFilterIdColumn( true ); //Active la possibilité de filtrer sur la colonne ID

        $filtre = array(
            'domaines' => $this->user->getDomaines()
        );
        $this->setSourceCondition('filtre', $filtre);
    }

    /**
     * {@inheritDoc}
     */
    public function setActionsButtons()
    {
        $this->addActionButton(new EditButton('hopitalnumerique_communautepratique_admin_groupe_edit'));
    }

    /**
     * {@inheritDoc}
     */
    public function setMassActions()
    {
        $utilisateurConnecte = $this->_container->get('security.context')->getToken()->getUser();
        
        if ($this->_container->get('nodevo_acl.manager.acl')->checkAuthorization($this->_container->get('router')->generate('hopitalnumerique_communautepratique_admin_groupe_deletemass'), $utilisateurConnecte) != -1)
        {
            $this->addMassAction( new DeleteMass('HopitalNumeriqueCommunautePratiqueBundle:Admin/Groupe:deleteMass') );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setColumns()
    {
        $this->addDomaineColumn();
        $this->addTitreColumn();
        $this->addDateInscriptionOuvertureColumn();
        $this->addDateDemarrageColumn();
        $this->addDateFinColumn();
        $this->addVedetteColumn();
        $this->addActifColumn();
    }

    /**
     * Ajoute la colonne Domaine.
     * 
     * @return void
     */
    private function addDomaineColumn()
    {
        $column = new TextColumn('domaineNom', 'Domaine');

        $column
            ->setFilterable(true)
            ->setSortable(false)
        ;

        $this->addColonne($column);
    }

    /**
     * Ajoute la colonne Titre.
     * 
     * @return void
     */
    private function addTitreColumn()
    {
        $column = new TextColumn('titre', 'Titre');

        $column
            ->setFilterable(true)
            ->setSortable(false)
        ;

        $this->addColonne($column);
    }

    /**
     * Ajoute la colonne Date d'ouverture des inscriptions.
     * 
     * @return void
     */
    private function addDateInscriptionOuvertureColumn()
    {
        $column = new DateColumn('dateInscriptionOuverture', 'Date d\'ouverture des inscriptions');

        $column
            ->setFilterable(true)
            ->setSortable(false)
        ;

        $this->addColonne($column);
    }

    /**
     * Ajoute la colonne Date de démarrage.
     * 
     * @return void
     */
    private function addDateDemarrageColumn()
    {
        $column = new DateColumn('dateDemarrage', 'Date de démarrage du groupe');

        $column
            ->setFilterable(true)
            ->setSortable(false)
        ;

        $this->addColonne($column);
    }

    /**
     * Ajoute la colonne Date de fin.
     * 
     * @return void
     */
    private function addDateFinColumn()
    {
        $column = new DateColumn('dateFin', 'Date de fin du groupe');

        $column
            ->setFilterable(true)
            ->setSortable(false)
        ;

        $this->addColonne($column);
    }

    /**
     * Ajoute la colonne En vedette.
     * 
     * @return void
     */
    private function addVedetteColumn()
    {
        $column = new BooleanColumn('vedette', 'En vedette');

        $column
            ->setFilterable(true)
            ->setSortable(false)
        ;

        $this->addColonne($column);
    }

    /**
     * Ajoute la colonne Actif.
     * 
     * @return void
     */
    private function addActifColumn()
    {
        $column = new BooleanColumn('actif', 'Actif');

        $column
            ->setFilterable(true)
            ->setSortable(false)
        ;

        $this->addColonne($column);
    }
}