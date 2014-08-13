<?php

namespace HopitalNumerique\UserBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\GridInterface;
use Nodevo\GridBundle\Grid\Column;
use Nodevo\GridBundle\Grid\Action;

/**
 * Configuration du grid RefusCandidature.
 */
class RefusCandidatureGrid extends Grid implements GridInterface
{
    /**
     * Définie la config spécifique au grid RefusCandidature.
     */
    public function setConfig()
    {
        $this->setSource( 'hopitalnumerique_user.manager.refuscandidature' );
        $this->setSourceType( self::SOURCE_TYPE_MANAGER );
        $this->setNoDataMessage('Aucun refus de candidature à afficher.');
        $this->setButtonSize(49);
    }

    /**
     * Ajoute les colonnes du grid RefusCandidature.
     */
    public function setColumns()
    {

        $prenomNomParticipantColumn = new Column\TextColumn('nomPrenom', 'Candidat');
        $prenomNomParticipantColumn->setSize( 150 );
        $this->addColonne( $prenomNomParticipantColumn );
        
        $regionParticipantColumn = new Column\TextColumn('userRegion', 'Sa région');
        $regionParticipantColumn->setSize( 150 );
        $this->addColonne( $regionParticipantColumn );
        
        $this->addColonne( new Column\TextColumn('motifRefus', 'Motif du refus') );

        $this->addColonne( new Column\DateColumn('dateRefus', 'Date de refus') );
    }

    /**
     * Ajoute les boutons d'action
     */
    public function setActionsButtons()
    {
        $actionFicheParticipant = new Action\ShowButton('hopital_numerique_user_show');
        $actionFicheParticipant->setAttributes( array(
                'class'=>'btn btn-primary fa fa-user-md',
                'title' => 'Fiche de l\'utilisateur',
                'target' => '_blank'
        ));
        $actionFicheParticipant->setRouteParametersMapping(array('userId' => 'id'));
        $actionFicheParticipant->setRouteParameters(array('userId'));
        $this->addActionButton( $actionFicheParticipant );
    }

    /**
     * Ajoute les actions de masses.
     */
    public function setMassActions()
    {
        $this->addMassAction( new Action\ActionMass('Export CSV - Refus candidature', 'HopitalNumeriqueUserBundle:RefusCandidature:exportCsv') );
    }
}