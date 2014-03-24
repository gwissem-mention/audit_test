<?php
/**
 * Contrôleur des demandes d'intervention.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Controller;

use HopitalNumerique\InterventionBundle\Entity\InterventionRegroupementType;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\InterventionBundle\Entity\InterventionDemande;
use HopitalNumerique\InterventionBundle\Entity\InterventionEtat;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\Form\Form;
use HopitalNumerique\InterventionBundle\Exception\InterventionException;
use Nodevo\RoleBundle\Entity\Role;
use Symfony\Component\HttpFoundation\Response;

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
        $interventionDemande = $id;
        $utilisateurConnecte = $this->get('security.context')->getToken()->getUser();
        $interventionDemandeEstRegroupee = $this->get('hopitalnumerique_intervention.manager.intervention_regroupement')->estInterventionDemandeRegroupee($interventionDemande);
        
        if (!$this->get('hopitalnumerique_intervention.manager.intervention_demande')->peutVoir($interventionDemande))
        {
            $this->get('session')->getFlashBag()->add('danger', 'Vous n\'êtes pas autorisé à visualiser cette demande.');
            return $this->redirect($this->generateUrl('hopital_numerique_homepage'));
        }
        
        $interventionRegroupements = $this->get('hopitalnumerique_intervention.manager.intervention_regroupement')->findBy(array('interventionDemandePrincipale' => $interventionDemande));

        $vueParametres = array(
            'interventionDemande' => $interventionDemande,
            'interventionDemandeEstRegroupee' => $interventionDemandeEstRegroupee,
            'InterventionEtat' => new InterventionEtat(),
            'interventionRegroupements' => $interventionRegroupements,
            'etablissementsRattachesNonRegroupes' => $this->get('hopitalnumerique_intervention.manager.intervention_demande')->findEtablissementsRattachesNonRegroupes($interventionDemande, $interventionRegroupements)
        );
        
        if ($utilisateurConnecte->hasRoleAmbassadeur())
        {
            $vueParametres['ambassadeurs'] = $this->get('hopitalnumerique_user.manager.user')->getAmbassadeurs(array(
                'region' => $interventionDemande->getCmsi()->getRegion()
            ));
        }
        else if (
            $utilisateurConnecte->hasRoleCmsi()
            && ($interventionDemande->interventionEtatEstDemandeInitiale() || $interventionDemande->interventionEtatEstAttenteCmsi())
        )
        {
            if (!$interventionDemandeEstRegroupee)
            {
                $vueParametres['interventionsSimilairesParObjets'] = $this->get('hopitalnumerique_intervention.manager.intervention_demande')->getInterventionsSimilairesParObjets($interventionDemande);
                $vueParametres['interventionsSimilairesParAmbassadeur'] = $this->get('hopitalnumerique_intervention.manager.intervention_demande')->getInterventionsSimilairesParAmbassadeur($interventionDemande);
                $vueParametres['interventionRegroupementTypeObjetId'] = InterventionRegroupementType::getInterventionRegroupementTypeObjetId();
                $vueParametres['interventionRegroupementTypeAmbassadeurId'] = InterventionRegroupementType::getInterventionRegroupementTypeAmbassadeurId();
            }
        }
        
        return $this->render(
            'HopitalNumeriqueInterventionBundle:Demande:voir.html.twig',
            $vueParametres
        );
    }

    /**
     * Action pour la visualisation d'une liste de demandes d'intervention.
     *
     * @return \Symfony\Component\HttpFoundation\Response La vue de la liste de demandes d'intervention
     */
    public function listeAction()
    {
        $utilisateurConnecte = $this->get('security.context')->getToken()->getUser();
        
        if ($utilisateurConnecte->hasRoleCmsi())
            return $this->render('HopitalNumeriqueInterventionBundle:Demande:Listes/cmsi.html.twig');
        else if ($utilisateurConnecte->hasRoleAmbassadeur())
            return $this->render('HopitalNumeriqueInterventionBundle:Demande:Listes/ambassadeur.html.twig');
        else return $this->render('HopitalNumeriqueInterventionBundle:Demande:Listes/etablissement.html.twig');
    }
    /**
     * Action pour la visualisation des suivis de demandes d'intervention.
     *
     * @return \Symfony\Component\HttpFoundation\Response La vue de la liste des suivis de demandes d'intervention
     */
    public function suiviDemandesAction()
    {
        $utilisateurConnecte = $this->get('security.context')->getToken()->getUser();
    
        if ($utilisateurConnecte->hasRoleDirecteur())
            return $this->render('HopitalNumeriqueInterventionBundle:Demande:Listes/directeurSuivi.html.twig');

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

    /**
     * Action appelée par le CRON.
     * 
     * @param integer $id Identifiant de sécurité
     * @return \Symfony\Component\HttpFoundation\Response Vide
     */
    public function cronAction($id)
    {
        if ($id == 'leschiensnefontpasdeschats')
        {
            $this->get('hopitalnumerique_intervention.manager.intervention_demande')->majInterventionEtatsDesInterventionDemandes();
            $this->get('hopitalnumerique_intervention.manager.intervention_demande')->relanceInterventionDemandes();
        }
        
        return new Response();
    }
}
