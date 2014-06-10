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
        
        if (!$this->container->get('hopitalnumerique_intervention.manager.intervention_demande')->peutVoir($interventionDemande))
        {
            $this->get('session')->getFlashBag()->add('danger', 'Vous n\'êtes pas autorisé à visualiser cette demande.');
            return $this->redirect($this->generateUrl('hopital_numerique_homepage'));
        }

        $this->container->get('hopitalnumerique_intervention.service.demande.etat_type_derniere_demande')->setDerniereDemandeOuverte($interventionDemande);
        
        return $this->render(
            'HopitalNumeriqueInterventionBundle:Demande:voir.html.twig',
            $this->getVueParametresVoir($interventionDemande)
        );
    }
    /**
     * Retourne les paramètres de la vue Voir.
     * 
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $interventionDemande
     * @return array Paramètres de la vue Voir
     */
    protected function getVueParametresVoir(InterventionDemande $interventionDemande)
    {
        $utilisateurConnecte = $this->get('security.context')->getToken()->getUser();
        $interventionDemandeEstRegroupee = $this->get('hopitalnumerique_intervention.manager.intervention_regroupement')->estInterventionDemandeRegroupee($interventionDemande);
        
        $vueParametres = array(
                'interventionDemande'                 => $interventionDemande,
                'interventionDemandeEstRegroupee'     => $interventionDemandeEstRegroupee,
                'InterventionEtat'                    => new InterventionEtat(),
                'etablissementsRattachesNonRegroupes' => $this->container->get('hopitalnumerique_intervention.manager.intervention_demande')->findEtablissementsRattachesNonRegroupes($interventionDemande),
                'etablissementPeutAnnulerDemande'     => $this->container->get('hopitalnumerique_intervention.manager.intervention_demande')->etablissementPeutAnnulerDemande($interventionDemande, $utilisateurConnecte)
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
                $vueParametres['interventionsSimilairesParObjets']          = $this->get('hopitalnumerique_intervention.manager.intervention_demande')->getInterventionsSimilairesParObjets($interventionDemande);
                $vueParametres['interventionsSimilairesParAmbassadeur']     = $this->get('hopitalnumerique_intervention.manager.intervention_demande')->getInterventionsSimilairesParAmbassadeur($interventionDemande);
                $vueParametres['interventionRegroupementTypeObjetId']       = InterventionRegroupementType::getInterventionRegroupementTypeObjetId();
                $vueParametres['interventionRegroupementTypeAmbassadeurId'] = InterventionRegroupementType::getInterventionRegroupementTypeAmbassadeurId();
            }
        }
        
        return $vueParametres;
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
     * Action appelée par le CRON qui peut être appelé n'importe quand.
     * 
     * @param string $id Identifiant de sécurité
     * @return \Symfony\Component\HttpFoundation\Response Vide
     */
    public function cronAction($id)
    {
        if ($id == 'FHFURJYIHOLPMFKVIDUESQGEUDRCTUFT')
        {
            $this->container->get('hopitalnumerique_intervention.manager.intervention_demande')->majInterventionEtatsDesInterventionDemandes();
            $this->container->get('hopitalnumerique_intervention.manager.intervention_demande')->relanceInterventionDemandes();
            return new Response($this->container->get('hopitalnumerique_intervention.service.demande.envoi_courriels_affichage_logs')->getHtml().'<p>Fin du traitement : OK.</p>');
        }
        
        return new Response('Clef invalide.');
    }

    /**
     * CRON qui doit appelé une fois par jour.
     *
     * @param string $id Identifiant de sécurité
     * @return \Symfony\Component\HttpFoundation\Response Vide
     */
    public function cronQuotidienAction($id)
    {
        if ($id == 'FLFTRJYPVGLPMMVGIDUEOFCEUDCVBUPA')
        {
            $this->container->get('hopitalnumerique_intervention.manager.intervention_demande')->relanceSimple();
            return new Response($this->container->get('hopitalnumerique_intervention.service.demande.envoi_courriels_affichage_logs')->getHtml().'<p>Fin du traitement : OK.</p>');
        }
    
        return new Response('Clef invalide.');
    }

    /**
     * Action appelée lors qu'une requête s'est effectuée avec succès en AJAX.
     * 
     * @return \Symfony\Component\HttpFoundation\Response La vue du succès AJAX
     */
	public function ajaxSuccesAction()
	{
		return $this->render('HopitalNumeriqueInterventionBundle:Ajax:succes.html.twig');
	}
    /**
     * Action appelée lors qu'une requête s'est effectuée avec erreur en AJAX.
     * 
     * @return \Symfony\Component\HttpFoundation\Response La vue de l'erreur AJAX
     */
	public function ajaxErreurAction()
	{
		return $this->render('HopitalNumeriqueInterventionBundle:Ajax:erreur.html.twig');
	}
}
