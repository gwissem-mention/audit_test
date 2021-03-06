<?php

namespace HopitalNumerique\UserBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\GridInterface;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

/**
 * Configuration du Grid User.
 */
class UserGrid extends Grid implements GridInterface
{
    /**
     * Set la config propre au Grid User (Source + config par défaut).
     */
    public function setConfig()
    {
        $this->setSource('hopitalnumerique_user.manager.user');
        $this->setSourceType(self::SOURCE_TYPE_MANAGER);
        $this->setSourceCondition('user', $this->_container->get('security.token_storage')->getToken()->getUser());
        $this->setNoDataMessage('Aucun utilisateur à afficher.');
        $this->setButtonSize(49);
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->_grid->setId($id);
    }

    /**
     * @param string $filtre
     * @param string|null $domain
     */
    public function setDefaultFiltreFromController($filtre, $domain = null)
    {
        $filtres = [];

        if (null !== $domain) {
            $filtres['domaines'] = $domain;
        }

        $this->setPersistence(false);

        switch ($filtre) {
            case 'Ambassadeur':
                $filtres['roles'] = 'ROLE_AMBASSADEUR_7';
                break;
            case 'Candidat-ambassadeur':
                $filtres['ambassadeur'] = 1;
                break;
            case 'Expert':
                $filtres['roles'] = 'ROLE_EXPERT_6';
                break;
            case 'Candidat-expert':
                $filtres['expert'] = 1;
                break;
            case 'Utilisateur-actif':
                $filtres['etat'] = 'Actif';
                //Not working
                $filtres['visitCount'] = ['operator' => 'gt', 'from' => '0'];
                break;
            //Not working
            case 'Ambassadeur-docs-a-renouvler':
                $filtres['contra'] = 'false';
                break;
        }

        $this->setDefaultFilters($filtres);
    }

    /**
     * Ajoute les colonnes visibles du grid.
     */
    public function setColumns()
    {
        $roles = $this->_container->get('nodevo_role.manager.role')->getRolesAsArray();

        //Retirer username et pseudonym des filtres
        $usernameColonne = new Column\DateColumn('username', 'Username');
        $usernameColonne->setVisible(false);
        $usernameColonne->setFilterable(false);
        $this->addColonne($usernameColonne);
        $pseudonymeColonne = new Column\DateColumn('pseudonym', 'Pseudonyme forum');
        $pseudonymeColonne->setVisible(false);
        $pseudonymeColonne->setFilterable(false);
        $this->addColonne($pseudonymeColonne);

        $this->addColonne(new Column\NumberColumn('idUser', 'ID'));
        $this->addColonne(new Column\DateColumn('registrationDate', 'Date d\'inscription'));
        $this->addColonne(new Column\NumberColumn('visitCount', 'Visites'));
        $this->addColonne(new Column\TextColumn('lastname', 'Nom'));
        $this->addColonne(new Column\TextColumn('firstname', 'Prénom'));
        $this->addColonne(new Column\TextColumn('email', 'Adresse e-mail'));

        $regionColumn = new Column\TextColumn('region', 'Région');
        $regionColumn->setFilterType('select');
        $regionColumn->setSelectFrom('source');
        $regionColumn->setOperatorsVisible(false);
        $regionColumn->setDefaultOperator(\APY\DataGridBundle\Grid\Column\Column::OPERATOR_EQ);
        $this->addColonne($regionColumn);

        $roleColumn = new Column\ArrayColumn('roles', 'Groupe associé');
        $roleColumn->manipulateRenderCell(
            function ($value, $row, $router) use ($roles) {
                return [$roles[$value[0]]];
            }
        );
        $roleColumn->setFilterType('select');
        $roleColumn->setSelectFrom('values');
        $roleColumn->setOperatorsVisible(false);
        $roleColumn->setValues($roles);
        $this->addColonne($roleColumn);

        $domaineColumn = new Column\TextColumn('domaines', 'Domaine(s) associé(s)');

        $this->addColonne($domaineColumn);

        $contractualisationColumn = new Column\TextColumn('contra', 'À jour');
        $contractualisationColumn->setSize(75);
        $contractualisationColumn->setAlign('center');
        $contractualisationColumn->setFilterType('select');
        $contractualisationColumn->setSelectFrom('values');
        $contractualisationColumn->setValues(['true' => 'Oui', 'false' => 'Non']);
        $contractualisationColumn->setOperatorsVisible(false);
        $contractualisationColumn->setDefaultOperator(\APY\DataGridBundle\Grid\Column\Column::OPERATOR_EQ);
        $this->addColonne($contractualisationColumn);

        $expertColumn = new Column\BooleanColumn('expert', 'Candidat expert');
        $expertColumn->setSize(90);
        $expertColumn->setSortable(true);
        $this->addColonne($expertColumn);

        $ambassadeurColumn = new Column\BooleanColumn('ambassadeur', 'Candidat ambassadeur');
        $ambassadeurColumn->setSize(115);
        $ambassadeurColumn->setSortable(true);
        $this->addColonne($ambassadeurColumn);

        $etatColonne = new Column\TextColumn('etat', 'Etat');
        $etatColonne->setSize(60);
        $etatColonne->setFilterType('select');
        $etatColonne->setSelectFrom('source');
        $etatColonne->setOperatorsVisible(false);
        $etatColonne->setDefaultOperator(\APY\DataGridBundle\Grid\Column\Column::OPERATOR_EQ);

        $this->addColonne($etatColonne);

        $activityNewsletterEnabledColumn = new Column\BooleanColumn('activityNewsletterEnabled', 'Newsletter');
        $activityNewsletterEnabledColumn->setSize(60);

        $this->addColonne($activityNewsletterEnabledColumn);

        if ($this->_container->get('security.authorization_checker')->isGranted('ROLE_ADMINISTRATEUR_1')) {
            $simulationColonne = new Column\TextColumn('usernameSimulated', 'Simuler');
            $simulationColonne->setFilterable(false)->setSortable(false);
            $this->addColonne($simulationColonne);
        }

        $this->addColonne(new Column\BlankColumn('lock'));
    }

    /**
     * Ajoute les boutons d'actions si nécessaire.
     */
    public function setActionsButtons()
    {
        $this->addActionButton(new Action\EditButton('hopital_numerique_user_edit'));
        $this->addActionButton(new Action\ShowButton('hopital_numerique_user_show'));
        // $this->addActionButton( new Action\DeleteButton('hopital_numerique_user_delete') );
    }

    /**
     * Ajoute les actions de masses.
     */
    public function setMassActions()
    {
        $this->addMassAction(new Action\DeleteMass('HopitalNumeriqueUserBundle:User:deleteMass'));

        /* Exports */
        $this->addMassAction(new Action\ActionMass('Export CSV - Caractérisation', 'HopitalNumeriqueUserBundle:User:exportCsv'));
        $this->addMassAction(new Action\ActionMass('Export CSV - Candidatures expert', 'HopitalNumeriqueUserBundle:User:exportCsvExperts'));
        $this->addMassAction(new Action\ActionMass('Export CSV - Candidatures ambassadeur', 'HopitalNumeriqueUserBundle:User:exportCsvAmbassadeurs'));
        $this->addMassAction(new Action\ActionMass('Export CSV - Productions maitrisées', 'HopitalNumeriqueUserBundle:User:exportCsvProductions'));
        $this->addMassAction(new Action\ActionMass('Export CSV - Connaissances métiers', 'HopitalNumeriqueUserBundle:User:exportCsvDomaines'));
        $this->addMassAction(new Action\ActionMass('Export CSV - Connaissances SI', 'HopitalNumeriqueUserBundle:User:exportCsvConnaissancesSI'));
        $this->addMassAction(new Action\ActionMass('Export CSV - Sessions Ambassadeurs', 'HopitalNumeriqueUserBundle:User:sessionsMass'));

        $this->addMassAction(new Action\ActionMass('Activer', 'HopitalNumeriqueUserBundle:User:activerMass'));
        $this->addMassAction(new Action\ActionMass('Désactiver', 'HopitalNumeriqueUserBundle:User:desactiverMass'));
        $this->addMassAction(new Action\ActionMass('Envoyer un mail', 'HopitalNumeriqueUserBundle:User:envoyerMailMass'));
    }
}
