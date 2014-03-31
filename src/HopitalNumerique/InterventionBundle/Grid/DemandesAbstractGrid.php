<?php
/**
 * Configuration du grid des demandes d'intervention.
 */
namespace HopitalNumerique\InterventionBundle\Grid;

use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\IGrid;
use Nodevo\GridBundle\Grid\Column;
use HopitalNumerique\InterventionBundle\Entity\InterventionInitiateur;

/**
 * Configuration du grid des demandes d'intervention.
 */
abstract class DemandesAbstractGrid extends Grid implements IGrid
{
    /**
     * Set la config propre au Grid des demandes d'intervention (Source + config par défaut)
     */
    public function setConfig()
    {
        $this->setSource('hopitalnumerique_intervention.manager.intervention_demande');
        $this->setSourceType(self::SOURCE_TYPE_MANAGER);
        $this->setNoDataMessage('- Aucune intervention à afficher -');
        $this->setDefaultLimit(1000);
        $this->setLimits(array(5, 10, 15, 1000));
    }

    /**
     * Ajoute les colonnes visibles du grid
     */
    public function setColumns()
    {
        $colonneRegroupements = new Column\TextColumn('nombreRegroupements', '');
        $colonneRegroupements->setFilterable(false)->setSortable(false);
        $colonneRegroupements->manipulateRenderCell(
            function($value, $row, $router) {
                if (intval($row->getField('nombreDemandesRegroupees')) > 0)
                    return '<img src="/bundles/hopitalnumeriqueintervention/img/regroupement_principale.png" width="16" height="14" title="Demande principale">';
                if (intval($row->getField('nombreDemandesPrincipales')) > 0)
                    return '<img src="/bundles/hopitalnumeriqueintervention/img/regroupement_groupee.png" width="16" height="14" title="Demande regroupée">';
                return '';
            }
        );
        $this->addColonne($colonneRegroupements);
    }

    /**
     * Ajoute les boutons d'actions si nécessaire
     */
    public function setActionsButtons()
    {
        
    }

    /**
     * Ajoute les actions de masses
     */
    public function setMassActions()
    {
        
    }

    /**
     * Ignore plusieurs colonne (ne pas afficher dans le filtre).
     *
     * @param string[] $colonneLabels Identifiant des colonnes
     * @return void
     */
    protected function ignoreColonnes($colonneLabels)
    {
        foreach ($colonneLabels as $colonneLabel)
            $this->ignoreColonne($colonneLabel);
    }
    /**
     * Ignore une colonne (ne pas afficher dans le filtre).
     * 
     * @param string $colonneLabel Identifiant de la colonne
     * @return void
     */
    private function ignoreColonne($colonneLabel)
    {
        $colonneAIgnorer = new Column\BlankColumn($colonneLabel);
        $colonneAIgnorer->setVisibleForSource(true);
        $this->addColonne($colonneAIgnorer);
    }
    
    /**
     * Fonction de rendu de la cellule Référent (ou Demandeur).
     * 
     * @return string Le contenu de la cellule Référent
     */
    public static function renderCellReferent($value, $row, $router)
    {
        return
            '<strong>'.$row->getField('referent_nom').'</strong>'.
            ($row->getField('referentEtablissementNom') != null ? '<br>'.$row->getField('referentEtablissementNom').' - '.$row->getField('referentEtablissementFiness') : '').
            ($row->getField('referentRegionLibelle') != null ? '<br>'.$row->getField('referentRegionLibelle') : '')
        ;
    }
    /**
     * Fonction de rendu de la cellule Ambassadeur.
     * 
     * @return string Le contenu de la cellule Ambassadeur
     */
    public static function renderCellAmbassadeur($value, $row, $router)
    {
        return
            '<strong>'.$row->getField('ambassadeur_nom').'</strong>'.
            ($row->getField('ambassadeurRegionLibelle') != null ? '<br>'.$row->getField('ambassadeurRegionLibelle') : '')
        ;
    }
    /**
     * Fonction de rendu de la cellule CMSI.
     * 
     * @return string Le contenu de la cellule CMSI
     */
    public static function renderCellCmsi($value, $row, $router)
    {
        return
            '<strong>'.$row->getField('cmsi_nom').'</strong>'
        ;
    }
    /**
     * Fonction de rendu de la cellule Date choix.
     * 
     * @return string Le contenu de la cellule Date choix
     */
    public static function renderCellDateChoix($value, $row, $router)
    {
        $dateChoix = '';
        if ($row->getField('cmsiDateChoixLibelle') != null)
        {
            $dateChoixCmsi = new \DateTime($row->getField('cmsiDateChoixLibelle'));
            $dateChoix .= '<div>CMSI : '.$dateChoixCmsi->format('d/m/Y').'</div>';
        }
        if ($row->getField('ambassadeurDateChoixLibelle') != null)
        {
            $dateChoixAmbassadeur = new \DateTime($row->getField('ambassadeurDateChoixLibelle'));
            $dateChoix .= '<div>Ambassadeur : '.$dateChoixAmbassadeur->format('d/m/Y').'</div>';
        }
        return $dateChoix;
    }
    /**
     * Fonction de rendu de la cellule initiateur.
     *
     * @return string Le contenu de la cellule Date choix
     */
    public static function renderCellInitiateur($value, $row, $router)
    {
        if ($row->getField('interventionInitiateurId') == InterventionInitiateur::getInterventionInitiateurCmsiId())
        {
            return '<span class="glyphicon glyphicon-user" title="Initié par '.$row->getField('interventionInitiateurType').'"></span>';
        }
        else if ($row->getField('interventionInitiateurId') == InterventionInitiateur::getInterventionInitiateurEtablissementId())
        {
            return '<span class="glyphicon glyphicon-home" title="Initié par '.$row->getField('interventionInitiateurType').'"></span>';
        }
        return '';
    }
}
