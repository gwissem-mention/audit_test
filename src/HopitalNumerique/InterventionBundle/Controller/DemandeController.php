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
        
        if (!$this->container->get('hopitalnumerique_intervention.manager.intervention_demande')->peutVoir($interventionDemande))
        {
            $this->get('session')->getFlashBag()->add('danger', 'Vous n\'êtes pas autorisé à visualiser cette demande.');
            return $this->redirect($this->generateUrl('hopital_numerique_homepage'));
        }

        $vueParametres = array(
            'interventionDemande' => $interventionDemande,
            'interventionDemandeEstRegroupee' => $interventionDemandeEstRegroupee,
            'InterventionEtat' => new InterventionEtat(),
            'etablissementsRattachesNonRegroupes' => $this->container->get('hopitalnumerique_intervention.manager.intervention_demande')->findEtablissementsRattachesNonRegroupes($interventionDemande),
            'etablissementPeutAnnulerDemande' => $this->container->get('hopitalnumerique_intervention.manager.intervention_demande')->etablissementPeutAnnulerDemande($interventionDemande, $utilisateurConnecte)
        );
        
        if ($utilisateurConnecte->hasRoleAmbassadeur())
        {
            $vueParametres['ambassadeurs'] = $this->get('hopitalnumerique_user.manager.user')->getAmbassadeurs(array(
                'region' => $interventionDemande->getCmsi()->getRegion()
            ));
        }
        else if ($this->container->get('hopitalnumerique_intervention.manager.intervention_regroupement')->utilisateurPeutRegrouperDemandes($interventionDemande, $utilisateurConnecte))
        {
            if (!$interventionDemandeEstRegroupee)
            {
                $vueParametres['interventionsSimilairesParObjets'] = $this->get('hopitalnumerique_intervention.manager.intervention_demande')->getInterventionsSimilairesParObjets($interventionDemande);
                $vueParametres['interventionsSimilairesParAmbassadeur'] = $this->get('hopitalnumerique_intervention.manager.intervention_demande')->getInterventionsSimilairesParAmbassadeur($interventionDemande);
                $vueParametres['interventionRegroupementTypeObjetId'] = InterventionRegroupementType::getInterventionRegroupementTypeObjetId();
                $vueParametres['interventionRegroupementTypeAmbassadeurId'] = InterventionRegroupementType::getInterventionRegroupementTypeAmbassadeurId();
            }
        }
        
        $this->container->get('hopitalnumerique_intervention.service.demande.etat_type_derniere_demande')->setDerniereDemandeOuverte($interventionDemande);
        
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
        {
            $derniereDemandeEstEtatTypeDemandeTraiteeCmsi = $this->container->get('hopitalnumerique_intervention.service.demande.etat_type_derniere_demande')->derniereDemandeEstEtatTypeDemandeTraiteeCmsi();
            
            return $this->render(
                'HopitalNumeriqueInterventionBundle:Demande:Listes/cmsi.html.twig',
                array(
                    'derniereDemandeEstEtatTypeDemandeTraiteeCmsi' => $derniereDemandeEstEtatTypeDemandeTraiteeCmsi
                )
            );
        }
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
     * Action appelée par le CRON qui peut être appelé n'importe quand.
     * 
     * @param string $id Identifiant de sécurité
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
    
    /**
     * CRON qui doit appelé une fois par jour.
     *
     * @param string $id Identifiant de sécurité
     * @return \Symfony\Component\HttpFoundation\Response Vide
     */
    public function cronQuotidienAction($id)
    {
        if ($id == 'ilnyapasdefumeesansfeu')
        {
            $this->get('hopitalnumerique_intervention.manager.intervention_demande')->relanceSimple();
        }
    
        return new Response();
    }
}
