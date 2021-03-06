<?php
/**
 * Configuration du grid des demandes d'intervention.
 */

namespace HopitalNumerique\InterventionBundle\Grid;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Nodevo\GridBundle\Grid\Grid;
use Nodevo\GridBundle\Grid\GridInterface;
use Nodevo\GridBundle\Grid\Column;
use HopitalNumerique\InterventionBundle\Entity\InterventionInitiateur;
use HopitalNumerique\InterventionBundle\Entity\InterventionEvaluationEtat;

/**
 * Configuration du grid des demandes d'intervention.
 */
abstract class DemandesAbstractGrid extends Grid implements GridInterface
{
    protected $utilisateurConnecte;

    /**
     * Constructeur du grid des demandes d'intervention.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container Conteneur de services de l'application
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->utilisateurConnecte = $container->get('security.context')->getToken()->getUser();
    }

    /**
     * Set la config propre au Grid des demandes d'intervention (Source + config par défaut).
     */
    public function setConfig()
    {
        $this->setSource('hopitalnumerique_intervention.manager.intervention_demande');
        $this->setSourceType(self::SOURCE_TYPE_MANAGER);
        $this->setNoDataMessage('- Aucune intervention à afficher -');
    }

    /**
     * Ajoute les colonnes visibles du grid.
     */
    public function setColumns()
    {
        $colonneRegroupements = new Column\TextColumn('nombreRegroupements', '');
        $colonneRegroupements->setFilterable(false)->setSortable(false);
        $colonneRegroupements->manipulateRenderCell(
            function ($value, \APY\DataGridBundle\Grid\Row $row) {
                if (intval($row->getField('nombreDemandesRegroupees')) > 0) {
                    return '<img src="/bundles/hopitalnumeriqueintervention/img/regroupement_principale.png" width="16" height="14" title="Demande principale">';
                }
                if (intval($row->getField('nombreDemandesPrincipales')) > 0) {
                    return '<img src="/bundles/hopitalnumeriqueintervention/img/regroupement_groupee.png" width="16" height="14" title="Demande regroupée">';
                }

                return '';
            }
        );
        $this->addColonne($colonneRegroupements);
    }

    /**
     * Ajoute les boutons d'actions si nécessaire.
     */
    public function setActionsButtons()
    {
    }

    /**
     * Ajoute les actions de masses.
     */
    public function setMassActions()
    {
    }

    /**
     * Ignore plusieurs colonne (ne pas afficher dans le filtre).
     *
     * @param string[] $colonneLabels Identifiant des colonnes
     */
    protected function ignoreColonnes($colonneLabels)
    {
        foreach ($colonneLabels as $colonneLabel) {
            $this->ignoreColonne($colonneLabel);
        }
    }

    /**
     * Ignore une colonne (ne pas afficher dans le filtre).
     *
     * @param string $colonneLabel Identifiant de la colonne
     */
    private function ignoreColonne($colonneLabel)
    {
        $colonneAIgnorer = new Column\BlankColumn($colonneLabel);
        $colonneAIgnorer->setVisibleForSource(true);
        $this->addColonne($colonneAIgnorer);
    }

    /**
     * Ajoute la colonne Demandeur.
     */
    protected function addColonneDemandeur()
    {
        $colonneDemandeurInformations = new Column\TextColumn('demandeurInformations', 'Demandeur');
        $colonneDemandeurInformations->manipulateRenderCell(
            function ($value, $row, $router) {
                return DemandesAbstractGrid::renderCellReferent($value, $row, $router);
            }
        );
        $colonneDemandeurInformations->setFilterable(false)->setSortable(false);
        $this->addColonne($colonneDemandeurInformations);
    }

    /**
     * Ajoute la colonne Ambassadeur.
     */
    protected function addColonneAmbassadeur()
    {
        $colonneAmbassadeurInformations = new Column\TextColumn('ambassadeurInformations', 'Ambassadeur');
        $colonneAmbassadeurInformations->setFilterable(false)->setSortable(false);
        $this->addColonne($colonneAmbassadeurInformations);
    }

    /**
     * Ajoute la colonne InterventionInitiateurType.
     */
    protected function addColonneInterventionInitiateurType()
    {
        $colonneInterventionInitiateurType = new Column\TextColumn('interventionInitiateurType', 'Initiateur');
        $colonneInterventionInitiateurType->setFilterable(false)->setSortable(false);
        $this->addColonne($colonneInterventionInitiateurType);
    }

    /**
     * Ajoute la colonne DateCreation.
     */
    protected function addColonneDateCreation()
    {
        $colonneDateCreationLibelle = new Column\DateColumn('dateCreationLibelle', 'Création');
        $colonneDateCreationLibelle->setFilterable(false)->setSortable(false);
        $this->addColonne($colonneDateCreationLibelle);
    }

    /**
     * Ajoute la colonne InterventionEtat.
     */
    protected function addColonneInterventionEtat()
    {
        $colonneInterventionEtatLibelle = new Column\TextColumn('interventionEtatLibelle', 'État');
        $colonneInterventionEtatLibelle->setFilterable(false)->setSortable(false);
        $this->addColonne($colonneInterventionEtatLibelle);
    }

    /**
     * Ajoute la colonne DateChoix.
     */
    protected function addColonneDateChoix()
    {
        $colonneDateChoix = new Column\TextColumn('dateChoix', 'Dates');
        $colonneDateChoix->setFilterable(false)->setSortable(false);
        $colonneDateChoix->manipulateRenderCell(
            function ($value, \APY\DataGridBundle\Grid\Row $row) {
                return DemandesAbstractGrid::renderCellDateChoix($value, $row);
            }
        );
        $this->addColonne($colonneDateChoix);
    }

    /**
     * Ajoute la colonne DateChoix.
     */
    protected function addColonneEvaluationAvecEnvoiRelance()
    {
        $colonneEvaluation = new Column\TextColumn('evaluationEtatId', 'Éval.');
        $colonneEvaluation->setFilterable(false)->setSortable(false);
        $colonneEvaluation->setAlign('center');
        $colonneEvaluation->manipulateRenderCell(
            function ($value, \APY\DataGridBundle\Grid\Row $row, \Symfony\Component\Routing\Router $router) {
                if ($row->getField('evaluationEtatId') == InterventionEvaluationEtat::getInterventionEvaluationEtatAEvaluerId()) {
                    return '<button class="btn btn-warning btn-xs" data-evaluation-demande="' . $row->getField('id') . '" title="Envoyer une relance"><span class="glyphicon glyphicon-send"></span></button>';
                } elseif ($row->getField('evaluationEtatId') == InterventionEvaluationEtat::getInterventionEvaluationEtatEvalueId()) {
                    return '<a class="btn btn-info btn-xs" href="' . $router->generate('hopital_numerique_intervention_evaluation_voir', ['interventionDemande' => $row->getField('id')]) . '"><span class="glyphicon glyphicon-eye-open"></span></a>';
                }

                return '';
            }
        );
        $this->addColonne($colonneEvaluation);
    }

    /**
     * Fonction de rendu de la cellule Référent (ou Demandeur).
     *
     * @return string Le contenu de la cellule Référent
     */
    public static function renderCellReferent($value, $row)
    {
        echo
            '<strong>' . $row->getField('referent_nom') . '</strong>' .
            ($row->getField('referentEtablissementNom') != null ? '<br>' . $row->getField('referentEtablissementNom') . ' - ' . $row->getField('referentEtablissementFiness') : '') .
            ($row->getField('referentRegionLibelle') != null ? '<br>' . $row->getField('referentRegionLibelle') : '')
        ;
    }

    /**
     * Fonction de rendu de la cellule Ambassadeur.
     *
     * @return string Le contenu de la cellule Ambassadeur
     */
    public static function renderCellAmbassadeur($value, $row)
    {
        echo
            '<strong>' . $row->getField('ambassadeur_nom') . '</strong>' .
            ($row->getField('ambassadeurRegionLibelle') != null ? '<br>' . $row->getField('ambassadeurRegionLibelle') : '')
        ;
    }

    /**
     * Fonction de rendu de la cellule CMSI.
     *
     * @return string Le contenu de la cellule CMSI
     */
    public static function renderCellCmsi($value, $row)
    {
        return
            '<strong>' . $row->getField('cmsi_nom') . '</strong>'
        ;
    }

    /**
     * Fonction de rendu de la cellule Date choix.
     *
     * @return string Le contenu de la cellule Date choix
     */
    public static function renderCellDateChoix($value, $row)
    {
        $dateChoix = '';
        if ($row->getField('cmsiDateChoixLibelle') != null) {
            $dateChoixCmsi = new \DateTime($row->getField('cmsiDateChoixLibelle'));
            $dateChoix .= '<div><strong>CMSI :</strong> ' . $dateChoixCmsi->format('d/m/Y') . '</div>';
        }
        if ($row->getField('ambassadeurDateChoixLibelle') != null) {
            $dateChoixAmbassadeur = new \DateTime($row->getField('ambassadeurDateChoixLibelle'));
            $dateChoix .= '<div><strong>Ambassadeur :</strong> ' . $dateChoixAmbassadeur->format('d/m/Y') . '</div>';
        }
        if ($row->getField('evaluationEtatId') == InterventionEvaluationEtat::getInterventionEvaluationEtatEvalueId()) {
            //Récupérer la vrai date en fonction des réponses au questionnaire d'évaluation
            $dateChoixEvaluation = $row->getField('evaluationDateLibelle') === '' ? 'Date non communiquée' : (new \DateTime($row->getField('evaluationDateLibelle')))->format('d/m/Y');
            $dateChoix .= '<div><strong>Évaluation : </strong>' . $dateChoixEvaluation . '</div>';
        }
        echo $dateChoix;
    }

    /**
     * Fonction de rendu de la cellule initiateur.
     *
     * @return string Le contenu de la cellule initiateur
     */
    public static function renderCellInitiateur($value, $row)
    {
        if ($row->getField('interventionInitiateurId') == InterventionInitiateur::getInterventionInitiateurCmsiId()) {
            return '<span class="fa fa-user" title="Initié par CMSI"></span>';
        } elseif ($row->getField('interventionInitiateurId') == InterventionInitiateur::getInterventionInitiateurEtablissementId()) {
            return '<span class="fa fa-home" title="Initié par ES"></span>';
        } elseif ($row->getField('interventionInitiateurId') == InterventionInitiateur::getInterventionInitiateurAnapId()) {
            return '<span class="fa fa-briefcase" title="Initié par ANAP"></span>';
        }

        return '';
    }
}
