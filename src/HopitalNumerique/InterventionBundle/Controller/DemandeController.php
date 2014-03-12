<?php
/**
 * Contrôleur des demandes d'intervention.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\InterventionBundle\Entity\InterventionDemande;
use HopitalNumerique\InterventionBundle\Entity\InterventionEtat;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\Form\Form;
use HopitalNumerique\InterventionBundle\Exception\InterventionException;
use Nodevo\RoleBundle\Entity\Role;

/**
 * Contrôleur des demandes d'intervention.
 */
class DemandeController extends Controller
{
    /**
     * Action pour la visualisation d'une demande d'intervention.
     *
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $id La demande d'intervention à visionner
     * @return \Symfony\Component\HttpFoundation\Response La vue de la visualisation d'une demande d'intervention
     */
    public function voirAction(InterventionDemande $id)
    {
        return $this->render(
            'HopitalNumeriqueInterventionBundle:Demande:voir.html.twig',
            array(
                'interventionDemande' => $id,
                'InterventionEtat' => new InterventionEtat()
            )
        );
    }

    /**
     * Action pour la visualisation d'une liste de demandes d'intervention.
     *
     * @return \Symfony\Component\HttpFoundation\Response La vue de la liste de demandes d'intervention
     */
    public function listeAction()
    {
        return $this->render(
            'HopitalNumeriqueInterventionBundle:Demande:liste.html.twig'
        );
    }
    /**
     * Action pour la liste des nouvelles demandes d'intervention (demandes en début de processus).
     *
     * @return \Symfony\Component\HttpFoundation\Response La vue de la liste des demandes d'intervention
     */
    public function listeDemandesNouvellesAction()
    {
        $interventionDemandesGrille = $this->get('hopitalnumerique_intervention.grid.intervention_demande_nouvelles');

        return $interventionDemandesGrille->render('HopitalNumeriqueInterventionBundle:Demande:Listes/demandesNouvelles.html.twig');
    }
    /**
     * Action pour la liste des demandes d'intervention traitées.
     *
     * @return \Symfony\Component\HttpFoundation\Response La vue de la liste des demandes d'intervention
     */
    public function listeDemandesTraiteesAction()
    {
        $interventionDemandesGrille = $this->get('hopitalnumerique_intervention.grid.intervention_demande_traitees');

        return $interventionDemandesGrille->render('HopitalNumeriqueInterventionBundle:Demande:Listes/demandesTraitees.html.twig');
    }
}
