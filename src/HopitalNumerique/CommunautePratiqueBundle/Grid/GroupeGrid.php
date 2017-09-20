<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Grid;

use Nodevo\GridBundle\Grid\Column\ArrayColumn;
use Nodevo\GridBundle\Grid\Grid;
use HopitalNumerique\UserBundle\Entity\User;
use APY\DataGridBundle\Grid\Action\RowAction;
use Nodevo\GridBundle\Grid\Column\TextColumn;
use Nodevo\GridBundle\Grid\Column\DateColumn;
use Nodevo\GridBundle\Grid\Action\EditButton;
use Nodevo\GridBundle\Grid\Action\DeleteMass;
use Nodevo\GridBundle\Grid\Column\BooleanColumn;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Grid de Groupe.
 */
class GroupeGrid extends Grid
{
    /**
     * @var User Utilisateur connecté
     */
    private $user;

    /**
     * {@inheritdoc}
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->user = $container->get('security.token_storage')->getToken()->getUser();
        if (!($this->user instanceof User)) {
            throw new \Exception('Aucun utilisateur connecté.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setConfig()
    {
        $this->setSource('hopitalnumerique_communautepratique.manager.groupe');
        $this->setSourceType(self::SOURCE_TYPE_MANAGER);
        $this->setFunctionName('getGridData');
        $this->showIdColumn(true); //Affiche la colonne ID; default
        $this->setFilterIdColumn(true); //Active la possibilité de filtrer sur la colonne ID

        $filtre = [
            'domaines' => $this->user->getDomaines(),
        ];
        $this->setSourceCondition('filtre', $filtre);
    }

    /**
     * {@inheritdoc}
     */
    public function setActionsButtons()
    {
        $this->addActionButton(new EditButton('hopitalnumerique_communautepratique_admin_groupe_edit'));

        $referencesButton = new RowAction('', 'hopitalnumerique_communautepratique_admin_groupe_edit');
        $referencesButton->setRouteParameters(['id', 'toRef' => 1]);
        $referencesButton->setAttributes(
            ['class' => 'btn btn-primary fa fa-cog', 'title' => 'Accès direct aux références']
        );

        $this->addActionButton($referencesButton);
    }

    /**
     * {@inheritdoc}
     */
    public function setMassActions()
    {
        $utilisateurConnecte = $this->_container->get('security.token_storage')->getToken()->getUser();

        if ($this->_container->get('nodevo_acl.manager.acl')->checkAuthorization($this->_container->get('router')->generate('hopitalnumerique_communautepratique_admin_groupe_deletemass'), $utilisateurConnecte) != -1) {
            $this->addMassAction(new DeleteMass('HopitalNumeriqueCommunautePratiqueBundle:Admin/Groupe:deleteMass'));
        }
    }

    /**
     * {@inheritdoc}
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
     */
    private function addDomaineColumn()
    {
        $column = new TextColumn('domaines', 'Domaines');

        $column
            ->setFilterable(true)
            ->setSortable(false)
        ;

        $this->addColonne($column);
    }

    /**
     * Ajoute la colonne Titre.
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
