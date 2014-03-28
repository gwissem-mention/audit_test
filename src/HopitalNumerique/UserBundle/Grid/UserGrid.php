<?php 
namespace HopitalNumerique\UserBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\IGrid;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

/**
 * Configuration du Grid User
 */
class UserGrid extends Grid implements IGrid
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
    }

    /**
     * Ajoute les colonnes visibles du grid
     */
    public function setColumns()
    {
        $roles = $this->_container->get('nodevo_role.manager.role')->getRolesAsArray();

        //Récupération des roles
        $arrayRolesDateContractualisation = $this->_arrayRolesDateContractualisation;
        
        $this->addColonne( new Column\DateColumn('dateInscription', 'Date d\'inscription') );        
        $this->addColonne( new Column\TextColumn('username', 'Nom du compte') );
        $this->addColonne( new Column\TextColumn('nom', 'Nom') );
        $this->addColonne( new Column\TextColumn('prenom', 'Prénom') );
        $this->addColonne( new Column\TextColumn('email', 'Adresse e-mail') );
        $this->addColonne( new Column\TextColumn('region', 'Région') );

        $roleColumn = new Column\ArrayColumn('roles', 'Groupe associé');
        $roleColumn->manipulateRenderCell(
            function($value, $row, $router) use ($roles){
                return array($roles[$value[0]]);
            }
        );
        $this->addColonne( $roleColumn );
        
        $contractualisationColumn = new Column\TextColumn('contra', 'À jour');
        $contractualisationColumn->setSize( 75 );
        $contractualisationColumn->setAlign('center');
        //Affichage de l'icone uniquement si le role fait parti de $arrayRolesDateContractualisation
        $contractualisationColumn->manipulateRenderCell(
            function($value, $row) use ($arrayRolesDateContractualisation) {
                $roles = $row->getField('roles');
                return in_array( reset($roles), $arrayRolesDateContractualisation, true ) ? ($value ? 'true' : 'false') : null;
            }
        );
        $this->addColonne( $contractualisationColumn );

        $expertColumn = new Column\BooleanColumn('expert', 'Candidat expert');
        $expertColumn->setValues( array( 1 => 'Oui', 0 => 'Non') );
        $expertColumn->setSize(90);
        $this->addColonne( $expertColumn );

        $ambassadeurColumn = new Column\BooleanColumn('ambassadeur', 'Candidat ambassadeur');
        $ambassadeurColumn->setValues( array( 1 => 'Oui', 0 => 'Non') );
        $ambassadeurColumn->setSize(115);
        $this->addColonne( $ambassadeurColumn );

        $etatColonne = new Column\TextColumn('etat', 'Etat');
        $etatColonne->setSize( 60 );
        $this->addColonne( $etatColonne );

        $this->addColonne( new Column\BlankColumn('lock') );
    }

    /**
     * Ajoute les boutons d'actions si nécessaire
     */
    public function setActionsButtons()
    {
        $this->addActionButton( new Action\EditButton('hopital_numerique_user_edit') );
        $this->addActionButton( new Action\ShowButton('hopital_numerique_user_show') );
        $this->addActionButton( new Action\DeleteButton('hopital_numerique_user_delete') );
    }

    /**
     * Ajoute les actions de masses
     */
    public function setMassActions()
    {
        $this->addMassAction( new Action\DeleteMass('HopitalNumeriqueUserBundle:User:DeleteMass') );
        $this->addMassAction( new Action\Export\CsvMass('HopitalNumeriqueUserBundle:User:ExportCsv') );

        $this->addMassAction( new Action\ActionMass('Activer','HopitalNumeriqueUserBundle:User:ActiverMass') );
        $this->addMassAction( new Action\ActionMass('Désactiver','HopitalNumeriqueUserBundle:User:DesactiverMass') );
		
		$this->addMassAction( new Action\ActionMass('Envoyer un mail','HopitalNumeriqueUserBundle:User:EnvoyerMailMass') );
    }
}
