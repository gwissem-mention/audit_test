<?php 
namespace HopitalNumerique\UserBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\GridInterface;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;
use APY\DataGridBundle\Grid\Row;

/**
 * Configuration du Grid User
 */
class UserGrid extends Grid implements GridInterface
{
    private $_arrayRolesDateContractualisation = array(
    	   'ROLE_AMBASSADEUR_7',
           'ROLE_EXPERT_6'
    );
    
    /**
    * Set la config propre au Grid User (Source + config par défaut)
    */
    public function setConfig()
    {
        $this->setSource( 'hopitalnumerique_user.manager.user' );
        $this->setSourceType( self::SOURCE_TYPE_MANAGER );
        $this->setNoDataMessage('Aucun utilisateur à afficher.');
        $this->setButtonSize(49);
    }

    public function setDefaultFiltreFromController($filtre)
    {
        $filtres = array();

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
            case 'Utilisateur-hopital-numerique':
                $filtres['domaines'] = 'Mon Hôpital Numérique';
                break;
            case 'Utilisateur-actif':
                $filtres['etat']      = 'Actif';
                $filtres['domaines']  = 'Mon Hôpital Numérique';
                //Not working
                $filtres['nbVisites'] = array('operator' => 'gt' , 'from' => '0');
                break;
            //Not working
            case 'Ambassadeur-docs-a-renouvler':
                $filtres['contra'] = 'false';
                break;
        }

        $this->setDefaultFilters($filtres);
    }

    /**
     * Ajoute les colonnes visibles du grid
     */
    public function setColumns()
    {
        $roles = $this->_container->get('nodevo_role.manager.role')->getRolesAsArray();

        //Récupération des roles
        $arrayRolesDateContractualisation = $this->_arrayRolesDateContractualisation;

        //Retirer username et pseudonymeForum des filtres
        $usernameColonne = new Column\DateColumn('username', 'Username');
        $usernameColonne->setVisible(false);
        $usernameColonne->setFilterable(false);
        $this->addColonne($usernameColonne);
        $pseudonymeColonne = new Column\DateColumn('pseudonymeForum', 'Pseudonyme forum');
        $pseudonymeColonne->setVisible(false);
        $pseudonymeColonne->setFilterable(false);
        $this->addColonne($pseudonymeColonne);

        $this->addColonne( new Column\NumberColumn('idUser', 'ID') );
        $this->addColonne( new Column\DateColumn('dateInscription', 'Date d\'inscription') );                      
        $this->addColonne( new Column\NumberColumn('nbVisites', 'Visites') );
        $this->addColonne( new Column\TextColumn('nom', 'Nom') );
        $this->addColonne( new Column\TextColumn('prenom', 'Prénom') );
        $this->addColonne( new Column\TextColumn('email', 'Adresse e-mail') );


        $regionColumn = new Column\TextColumn('region', 'Région');
        $regionColumn->setFilterType('select');
        $regionColumn->setSelectFrom('source');
        $regionColumn->setOperatorsVisible( false );
        $regionColumn->setDefaultOperator( \APY\DataGridBundle\Grid\Column\Column::OPERATOR_EQ );
        $this->addColonne( $regionColumn );

        $roleColumn = new Column\ArrayColumn('roles', 'Groupe associé');
        $roleColumn->manipulateRenderCell(
            function($value, $row, $router) use ($roles){
                return array($roles[$value[0]]);
            }
        );
        $roleColumn->setFilterType('select');
        $roleColumn->setSelectFrom('values');
        $roleColumn->setOperatorsVisible( false );
        $roleColumn->setValues( $roles );
        $this->addColonne( $roleColumn );

        $domaineColumn = new Column\TextColumn('domaines', 'Domaine(s) associé(s)');
        $this->addColonne( $domaineColumn );
        
        $contractualisationColumn = new Column\TextColumn('contra', 'À jour');
        $contractualisationColumn->setSize( 75 );
        $contractualisationColumn->setAlign('center');
        $contractualisationColumn->setFilterType('select');
        $contractualisationColumn->setSelectFrom('values');
        $contractualisationColumn->setOperatorsVisible( false )->setSortable(false)->setFilterable(false);
        //Affichage de l'icone uniquement si le role fait parti de $arrayRolesDateContractualisation
        $contractualisationColumn->manipulateRenderCell(
            function($value, \APY\DataGridBundle\Grid\Row $row) use ($arrayRolesDateContractualisation) {
                $roles = $row->getField('roles');
                return in_array( reset($roles), $arrayRolesDateContractualisation, true ) ? ($value ? 'true' : 'false') : null;
            }
        );
        $this->addColonne( $contractualisationColumn );

        $expertColumn = new Column\BooleanColumn('expert', 'Candidat expert');
        $expertColumn->setSize(90);
        $expertColumn->setSortable(true);
        $this->addColonne( $expertColumn );

        $ambassadeurColumn = new Column\BooleanColumn('ambassadeur', 'Candidat ambassadeur');
        $ambassadeurColumn->setSize(115);
        $ambassadeurColumn->setSortable(true);
        $this->addColonne( $ambassadeurColumn );

        $etatColonne = new Column\TextColumn('etat', 'Etat');
        $etatColonne->setSize( 60 );
        $etatColonne->setFilterType('select');
        $etatColonne->setSelectFrom('source');
        $etatColonne->setOperatorsVisible( false );
        $etatColonne->setDefaultOperator( \APY\DataGridBundle\Grid\Column\Column::OPERATOR_EQ );

        $this->addColonne( $etatColonne );

        if ($this->_container->get('security.authorization_checker')->isGranted('ROLE_ADMINISTRATEUR_1') ||
            $this->_container->get('security.authorization_checker')->isGranted('ROLE_ADMINISTRATEUR_DU_DOMAINE_HN_107') ||
            $this->_container->get('security.authorization_checker')->isGranted('ROLE_ADMINISTRATEUR_DE_DOMAINE_106')
        ) {
            $simulationColonne = new Column\TextColumn('usernameSimulated', 'Simuler');
            $simulationColonne->setFilterable(false)->setSortable(false);
            $this->addColonne($simulationColonne);
        }

        $this->addColonne( new Column\BlankColumn('lock') );
    }

    /**
     * Ajoute les boutons d'actions si nécessaire
     */
    public function setActionsButtons()
    {
        $this->addActionButton( new Action\EditButton('hopital_numerique_user_edit') );
        $this->addActionButton( new Action\ShowButton('hopital_numerique_user_show') );
        // $this->addActionButton( new Action\DeleteButton('hopital_numerique_user_delete') );
    }

    /**
     * Ajoute les actions de masses
     */
    public function setMassActions()
    {
        $this->addMassAction( new Action\DeleteMass('HopitalNumeriqueUserBundle:User:deleteMass') );

        /* Exports */
        $this->addMassAction( new Action\ActionMass('Export CSV - Caractérisation', 'HopitalNumeriqueUserBundle:User:exportCsv') );
        $this->addMassAction( new Action\ActionMass('Export CSV - Candidatures expert', 'HopitalNumeriqueUserBundle:User:exportCsvExperts') );
        $this->addMassAction( new Action\ActionMass('Export CSV - Candidatures ambassadeur', 'HopitalNumeriqueUserBundle:User:exportCsvAmbassadeurs') );
        $this->addMassAction( new Action\ActionMass('Export CSV - Productions maitrisées', 'HopitalNumeriqueUserBundle:User:exportCsvProductions') );
        $this->addMassAction( new Action\ActionMass('Export CSV - Connaissances métiers', 'HopitalNumeriqueUserBundle:User:exportCsvDomaines') );
        $this->addMassAction( new Action\ActionMass('Export CSV - Connaissances SI', 'HopitalNumeriqueUserBundle:User:exportCsvConnaissancesSI') );
        $this->addMassAction( new Action\ActionMass('Export CSV - Sessions Ambassadeurs','HopitalNumeriqueUserBundle:User:sessionsMass') );

        $this->addMassAction( new Action\ActionMass('Activer','HopitalNumeriqueUserBundle:User:activerMass') );
        $this->addMassAction( new Action\ActionMass('Désactiver','HopitalNumeriqueUserBundle:User:desactiverMass') );
        $this->addMassAction( new Action\ActionMass('Envoyer un mail','HopitalNumeriqueUserBundle:User:envoyerMailMass') );
        
    }
}
