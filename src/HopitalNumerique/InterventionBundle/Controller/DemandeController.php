<?php
/**
 * Contrôleur des demandes d'intervention.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\InterventionBundle\Entity\InterventionDemande;
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
     * @var \HopitalNumerique\UserBundle\Entity\User Utilisateur connecté actuellement
     */
    private $utilisateurConnecte;
    /**
     * @var \HopitalNumerique\InterventionBundle\Entity\InterventionDemande Demande d'intervention en cours
     */
    private $interventionDemande;

    /**
     * Action pour la création d'une nouvelle demande d'intervention.
     * 
     * @return \Symfony\Component\HttpFoundation\Response La vue du formulaire de création d'une demande d'intervention
     */
    public function nouveauAction(User $ambassadeur)
    {
        ini_set('memory_limit', '256M');

        $this->utilisateurConnecte = $this->get('security.context')->getToken()->getUser();
        $this->interventionDemande = new InterventionDemande();
        $this->interventionDemande->setAmbassadeur($ambassadeur);

        /*$formulaireOptions = array(
            'user' => $this->utilisateurConnecte
        );*/
        // @todo TEST
        if (true || $this->utilisateurConnecte->hasRoleCmsi())
        {
            //$utilisateurFormulaire = $this->createForm('hopitalnumerique_interventionbundle_user_cmsi', $this->utilisateurConnecte);
            $interventionDemandeFormulaire = $this->createForm('hopitalnumerique_interventionbundle_interventiondemande_cmsi', $this->interventionDemande);
        }
        else
        {
            //$utilisateurFormulaire = $this->createForm('hopitalnumerique_interventionbundle_user_etablissement', $this->utilisateurConnecte);
            $interventionDemandeFormulaire = $this->createForm('hopitalnumerique_interventionbundle_interventiondemande_etablissement', $this->interventionDemande);
        }

        $this->_gereEnvoiFormulaireDemandeNouveau($interventionDemandeFormulaire, $ambassadeur);

        return $this->render(
            'HopitalNumeriqueInterventionBundle:Demande:nouveau.html.twig',
            array(
                'interventionDemandeFormulaireNouveau' => $interventionDemandeFormulaire->createView()
            )
        );
    }
    /**
     * Gère l'enregistrement des données du formulaire de création d'une demande d'intervention.
     * 
     * @param \Symfony\Component\Form\Form $interventionDemandeFormulaire Formulaire de la demande d'intervention
     * @return void
     */
    private function _gereEnvoiFormulaireDemandeNouveau($interventionDemandeFormulaire, User $ambassadeur)
    {
        if ($this->get('request')->getMethod() == 'POST')
        {
            $interventionDemandeFormulaire->bind($this->get('request'));

            if ($interventionDemandeFormulaire->isValid())
            {
                $this->interventionDemande->setDateCreation(new \DateTime());
                
                $cmsi = null;
                if ($this->utilisateurConnecte->hasRoleCmsi())
                {
                    $cmsi = $this->get('hopitalnumerique_user.manager.user')->getCmsi(array('region' => $ambassadeur->getRegion(), 'enabled' => true));
                    if ($cmsi == null)
                        throw new InterventionException('Un CMSI pour la région choisie doit existé pour créer une demande d\'intervention.');

                    $this->interventionDemande->setCmsiDateChoix($this->interventionDemande->getDateCreation());
                    $this->interventionDemande->setInterventionInitiateur($this->get('hopitalnumerique_intervention.manager.intervention_initiateur')->getInterventionInitiateurCmsi());
                    $this->interventionDemande->setInterventionEtat($this->get('hopitalnumerique_intervention.manager.intervention_etat')->getInterventionEtatAcceptationCmsiId());
                }
                else // Établissement par défaut
                {
                    $cmsi = $this->utilisateurConnecte;
                    $this->interventionDemande->setReferent($this->utilisateurConnecte);
                    $this->interventionDemande->setInterventionInitiateur($this->get('hopitalnumerique_intervention.manager.intervention_initiateur')->getInterventionInitiateurEtablissement());
                    $this->interventionDemande->setInterventionEtat($this->get('hopitalnumerique_intervention.manager.intervention_etat')->getInterventionEtatDemandeInitiale());
                }

                $this->interventionDemande->setCmsi($cmsi);
                $this->interventionDemande->setDirecteur($this->get('hopitalnumerique_user.manager.user')->getDirecteur(array('region' => $this->utilisateurConnecte->getRegion(), 'enabled' => true)));
                $this->get('hopitalnumerique_intervention.manager.interventiondemande')->save($this->interventionDemande);

                if ($this->utilisateurConnecte->hasRoleCmsi())
                {
                    $this->get('hopitalnumerique_intervention.manager.intervention_courriel')->envoiCourrielDemandeAcceptationAmbassadeur($this->utilisateurConnecte, 'TOTOURLDEMANDEINTERVENTION');
                    $this->get('hopitalnumerique_intervention.manager.intervention_courriel')->envoiCourrielAlerteReferent($this->utilisateurConnecte);
                }
                else // Établissement par défaut
                {
                    $this->get('hopitalnumerique_intervention.manager.intervention_courriel')->envoiCourrielCreation($this->utilisateurConnecte);
                    $this->get('hopitalnumerique_intervention.manager.intervention_courriel')->envoiCourrielDemandeAcceptationCmsi($cmsi, 'TOTOURLDEMANDEINTERVENTION');
                }

                $this->get('session')->getFlashBag()->add('success', 'La demande d\'intervention a été enregistrée et sera étudiée.');
            }
            else
            {
                $this->get('session')->getFlashBag()->add('danger', 'Le formulaire n\'est pas valide.');
            }
        }
    }

    
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
                'interventionDemande' => $id
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
