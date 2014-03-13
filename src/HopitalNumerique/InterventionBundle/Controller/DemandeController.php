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
        $this->utilisateurConnecte = $this->get('security.context')->getToken()->getUser();
        
        if ($this->utilisateurConnecte->hasRoleCmsi())
            return $this->render('HopitalNumeriqueInterventionBundle:Demande:Listes/cmsi.html.twig');
        else if ($this->utilisateurConnecte->hasRoleAmbassadeur())
            return $this->render('HopitalNumeriqueInterventionBundle:Demande:Listes/ambassadeur.html.twig');
        
        $this->get('session')->getFlashBag()->add('danger', 'Vous n\'êtes pas autorisé à visualiser cette page.');
        return $this->redirect($this->generateUrl('hopital_numerique_homepage'));
    }
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
     * Action pour la liste des demandes d'intervention pour l'ambassadeur.
     *
     * @return \Symfony\Component\HttpFoundation\Response La vue de la liste des demandes d'intervention
     */
    public function gridAmbassadeurDemandesAction()
    {
        $interventionDemandesGrille = $this->get('hopitalnumerique_intervention.grid.ambassadeur.intervention_demandes');

        return $interventionDemandesGrille->render('HopitalNumeriqueInterventionBundle:Grid:Ambassadeur/demandes.html.twig');
    }
}
