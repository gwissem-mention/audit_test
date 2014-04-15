<?php
/**
 * Contrôleur des grids des demandes d'intervention.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Contrôleur des demandes d'intervention.
 */
class GridController extends Controller
{
    /**
     * Action pour la liste des nouvelles demandes d'intervention (demandes en début de processus) pour le CMSI.
     *
     * @return \Symfony\Component\HttpFoundation\Response La vue de la liste des demandes d'intervention
     */
    public function gridCmsiDemandesNouvellesAction()
    {
        $interventionDemandesGrille = $this->get('hopitalnumerique_intervention.grid.cmsi.intervention_demandes_nouvelles');

        return $interventionDemandesGrille->render('HopitalNumeriqueInterventionBundle:Grid:Cmsi/demandesNouvelles.html.twig');
    }
    /**
     * Action pour la liste des demandes d'intervention traitées pour le CMSI.
     *
     * @return \Symfony\Component\HttpFoundation\Response La vue de la liste des demandes d'intervention
     */
    public function gridCmsiDemandesTraiteesAction()
    {
        $interventionDemandesGrille = $this->get('hopitalnumerique_intervention.grid.cmsi.intervention_demandes_traitees');

        return $interventionDemandesGrille->render('HopitalNumeriqueInterventionBundle:Grid:Cmsi/demandesTraitees.html.twig');
    }
    /**
     * Action pour la liste des demandes d'intervention pour le directeur d'établissement.
     *
     * @return \Symfony\Component\HttpFoundation\Response La vue de la liste des demandes d'intervention
     */
    public function gridDirecteurSuiviDemandesAction()
    {
        $interventionDemandesGrille = $this->get('hopitalnumerique_intervention.grid.directeur.intervention_suivi_demandes');
    
        return $interventionDemandesGrille->render('HopitalNumeriqueInterventionBundle:Grid:Directeur/suiviDemandes.html.twig');
    }
    /**
     * Action pour la liste des demandes d'intervention pour l'ambassadeur.
     *
     * @return \Symfony\Component\HttpFoundation\Response La vue de la liste des demandes d'intervention
     */
    public function gridAmbassadeurDemandesAction()
    {
        $interventionDemandesGrille = $this->get('hopitalnumerique_intervention.grid.ambassadeur.intervention_demandes');

        return $interventionDemandesGrille->render('HopitalNumeriqueInterventionBundle:Grid:Ambassadeur/demandes.html.twig');
    }
    /**
     * Action pour la liste des demandes d'intervention pour l'établissement.
     *
     * @return \Symfony\Component\HttpFoundation\Response La vue de la liste des demandes d'intervention
     */
    public function gridEtablissementDemandesAction()
    {
        $interventionDemandesGrille = $this->get('hopitalnumerique_intervention.grid.etablissement.intervention_demandes');
    
        return $interventionDemandesGrille->render('HopitalNumeriqueInterventionBundle:Grid:Etablissement/demandes.html.twig');
    }
}
