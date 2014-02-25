<?php 
namespace HopitalNumerique\UserBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\IGrid;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;
//Classe php externe dans le bundle Tools de nodevo
use Nodevo\ToolsBundle\Tools as NodevoTools;
use Nodevo\ToolsBundle\Tools\Chaine;

/**
 * Configuration du Grid User
 */
class UserGrid extends Grid implements IGrid
{
    private $arrayRolesDateContractualisation = array(
    	   'ambassadeur',
           'expert'
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
        //Récupération des roles
        $arrayRolesDateContractualisation = $this->arrayRolesDateContractualisation;
        
        $this->addColonne( new Column\TextColumn('username', 'Nom d\'utilisateur') );
        $this->addColonne( new Column\TextColumn('nom', 'Nom') );
        $this->addColonne( new Column\TextColumn('prenom', 'Prénom') );
        $this->addColonne( new Column\TextColumn('email', 'Adresse e-mail') );
        $this->addColonne( new Column\TextColumn('region', 'Région') );
        $this->addColonne( new Column\TextColumn('roles', 'Groupe associé') );
        
        $contractualisationColumn = new Column\TextColumn('contra', 'À jour');
        //$contractualisationColumn->setValues( array('true' => 'Document(s) à jour', 'false' => 'Document(s) dépassé(s) ', '' => 'Pas de document') );
        $contractualisationColumn->setSize( 75 );
        $contractualisationColumn->setAlign('center');
        //Affichage de l'icone uniquement si le role fait parti de $arrayRolesDateContractualisation
        $contractualisationColumn->manipulateRenderCell(
            function($value, $row, $router) use ($arrayRolesDateContractualisation) {
                $role = new Chaine($row->getField('roles'));       

                return in_array($role->minifie(), $arrayRolesDateContractualisation) ? ($value ? 'true' : 'false') : null;
            }
        );
        $this->addColonne( $contractualisationColumn );

        $expertColonne = new Column\BooleanColumn('expert', 'Expert');
        $expertColonne->setValues( array( 1 => 'Oui', 0 => 'Non') );
        $this->addColonne( $expertColonne->setSize(75) );

        $expertColonne = new Column\BooleanColumn('ambassadeur', 'Ambassadeur');
        $expertColonne->setValues( array( 1 => 'Oui', 0 => 'Non') );
        $this->addColonne( $expertColonne->setSize(115) );

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
    }
}