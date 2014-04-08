<?php
/**
 * Contrôleur des formulaires de demandes d'intervention.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Controller\Form;

use HopitalNumerique\InterventionBundle\Entity\InterventionDemande;
use HopitalNumerique\InterventionBundle\Entity\InterventionEtat;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\ObjetBundle\Entity\Objet;

/**
 * Contrôleur des formulaires de demandes d'intervention.
 */
class DemandeController extends \HopitalNumerique\InterventionBundle\Controller\DemandeController
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
     * @param \HopitalNumerique\UserBundle\Entity\User $ambassadeur L'ambassadeur de la demande d'intervention
     * @return \Symfony\Component\HttpFoundation\Response La vue du formulaire de création d'une demande d'intervention
     */
    public function nouveauAction(User $ambassadeur, Objet $objet = null)
    {
        if (!$ambassadeur->hasRoleAmbassadeur() || !$ambassadeur->isActif())
        {
            $this->get('session')->getFlashBag()->add('danger', 'L\'utilisateur choisi n\'est pas un ambassadeur.');
            return $this->redirect($this->generateUrl('hopital_numerique_homepage'));
        }

        $this->utilisateurConnecte = $this->get('security.context')->getToken()->getUser();
        $this->interventionDemande = new InterventionDemande();
        $this->interventionDemande->setAmbassadeur($ambassadeur);
        if ($objet != null)
            $this->interventionDemande->addObjet($objet);

        if ($this->utilisateurConnecte->hasRoleCmsi())
            $interventionDemandeFormulaire = $this->createForm('hopitalnumerique_interventionbundle_interventiondemande_cmsi', $this->interventionDemande, array('interventionDemande' => $this->interventionDemande));
        else $interventionDemandeFormulaire = $this->createForm('hopitalnumerique_interventionbundle_interventiondemande_etablissement', $this->interventionDemande, array('interventionDemande' => $this->interventionDemande));

        if ($this->gereEnvoiFormulaireDemandeNouveau($interventionDemandeFormulaire))
            return $this->redirect($this->generateUrl('hopital_numerique_intervention_demande_liste'));

        return $this->render(
            'HopitalNumeriqueInterventionBundle:Demande:nouveau.html.twig',
            array(
                'interventionDemandeFormulaireNouveau' => $interventionDemandeFormulaire->createView(),
                'interventionDemande' => $this->interventionDemande,
                'ambassadeur' => $ambassadeur
            )
        );
    }
    /**
     * Gère l'enregistrement des données du formulaire de création d'une demande d'intervention.
     * 
     * @param \Symfony\Component\Form\Form $interventionDemandeFormulaire Formulaire de la demande d'intervention
     * @return boolean VRAI ssi le formulaire est validé
     */
    private function gereEnvoiFormulaireDemandeNouveau($interventionDemandeFormulaire)
    {
        if ($this->get('request')->isMethod('POST'))
        {
            $interventionDemandeFormulaire->bind($this->get('request'));

            if ($interventionDemandeFormulaire->isValid())
            {
                if (!$this->enregistreNouvelleDemande())
                    return false;

                $this->envoieCourrielsNouvelleDemande();

                $this->get('session')->getFlashBag()->add('success', 'La demande d\'intervention a été enregistrée et sera étudiée.');
                return true;
            }
            else
            {
                $this->get('session')->getFlashBag()->add('danger', 'Le formulaire n\'est pas valide.');
            }
        }
        return false;
    }
    /**
     * Enregistre une nouvelle demande d'intervention après soumission du formulaire.
     * 
     * @return boolean VRAI ssi la demande est enregistrée
     */
    private function enregistreNouvelleDemande()
    {
        $this->interventionDemande->setDateCreation(new \DateTime());

        $cmsi = null;
        if ($this->utilisateurConnecte->hasRoleCmsi())
        {
            $cmsi = $this->utilisateurConnecte;
            $this->interventionDemande->setCmsiDateChoix($this->interventionDemande->getDateCreation());
            $this->interventionDemande->setAmbassadeurDateDerniereRelance(new \DateTime());
            $this->interventionDemande->setInterventionInitiateur($this->get('hopitalnumerique_intervention.manager.intervention_initiateur')->getInterventionInitiateurCmsi());
            $this->interventionDemande->setInterventionEtat($this->get('hopitalnumerique_intervention.manager.intervention_etat')->getInterventionEtatAcceptationCmsi());
        }
        else // Établissement par défaut
        {
            $cmsi = $this->get('hopitalnumerique_user.manager.user')->getCmsi(array('region' => $this->interventionDemande->getAmbassadeur()->getRegion(), 'enabled' => true));
            if ($cmsi == null)
            {
                $this->get('session')->getFlashBag()->add('danger', 'Un CMSI pour la région de l\'ambassadeur choisi doit exister pour créer une demande d\'intervention.');
                return false;
            }

            $this->interventionDemande->setReferent($this->utilisateurConnecte);
            $this->interventionDemande->setInterventionInitiateur($this->get('hopitalnumerique_intervention.manager.intervention_initiateur')->getInterventionInitiateurEtablissement());
            $this->interventionDemande->setInterventionEtat($this->get('hopitalnumerique_intervention.manager.intervention_etat')->getInterventionEtatDemandeInitiale());
        }

        $this->interventionDemande->setCmsi($cmsi);
        
        if ($this->interventionDemande->getReferent()->getEtablissementRattachementSante() != null)
            $this->interventionDemande->setDirecteur($this->get('hopitalnumerique_user.manager.user')->getDirecteur(array('etablissementRattachementSante' => $this->interventionDemande->getReferent()->getEtablissementRattachementSante(), 'enabled' => true)));
        $this->get('hopitalnumerique_intervention.manager.interventiondemande')->save($this->interventionDemande);

        return true;
    }
    /**
     * Envoie les courriels nécessaires après l'enregistrement d'une nouvelle demande d'intervention.
     *
     * @return void
     */
    private function envoieCourrielsNouvelleDemande()
    {
        if ($this->utilisateurConnecte->hasRoleCmsi())
        {
            $this->get('hopitalnumerique_intervention.manager.intervention_courriel')->envoiCourrielDemandeAcceptationAmbassadeur($this->interventionDemande->getAmbassadeur(), $this->generateUrl('hopital_numerique_intervention_demande_voir', array('id' => $this->interventionDemande->getId()), true));
            $this->get('hopitalnumerique_intervention.manager.intervention_courriel')->envoiCourrielAlerteReferent($this->interventionDemande->getReferent());
        }
        else // Établissement par défaut
        {
            $this->get('hopitalnumerique_intervention.manager.intervention_courriel')->envoiCourrielCreation($this->utilisateurConnecte);
            $this->get('hopitalnumerique_intervention.manager.intervention_courriel')->envoiCourrielDemandeAcceptationCmsi($this->interventionDemande->getCmsi(), $this->generateUrl('hopital_numerique_intervention_demande_voir', array('id' => $this->interventionDemande->getId()), true));
        }
    }

    /**
     * Édition d'une demande d'intervention.
     *
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $id La demande d'intervention à éditer
     * @return \Symfony\Component\HttpFoundation\Response La vue du formulaire de modification d'une demande d'intervention
     */
    public function editAction(InterventionDemande $id)
    {
        $this->utilisateurConnecte = $this->get('security.context')->getToken()->getUser();
        $this->interventionDemande = $id;
        $interventionDemandeFormulaire = null;

        if (($this->utilisateurConnecte->hasRoleCmsi() && ($this->interventionDemande->getInterventionEtat()->getId() == InterventionEtat::getInterventionEtatDemandeInitialeId() || $this->interventionDemande->getInterventionEtat()->getId() == InterventionEtat::getInterventionEtatAttenteCmsiId())))
        {
            $interventionDemandeFormulaire = $this->createForm('hopitalnumerique_interventionbundle_interventiondemande_edition_cmsi', $this->interventionDemande, array('interventionDemande' => $this->interventionDemande));
        }

        if ($interventionDemandeFormulaire == null || !$this->get('hopitalnumerique_intervention.manager.intervention_demande')->peutEditer($this->interventionDemande))
        {
            $this->get('session')->getFlashBag()->add('danger', 'Vous n\'êtes pas autorisé à éditer cette demande d\'intervention.');
            return $this->redirect($this->generateUrl('hopital_numerique_homepage'));
        }

        $this->gereEnvoiFormulaireDemandeEdition($interventionDemandeFormulaire);

        return $this->render(
            'HopitalNumeriqueInterventionBundle:Demande:edit.html.twig',
            array(
                'interventionDemandeFormulaireEdition' => $interventionDemandeFormulaire->createView(),
                'interventionDemande' => $this->interventionDemande
            )
        );
    }
    /**
     * Gère l'enregistrement des données du formulaire d'édition d'une demande d'intervention.
     *
     * @param \Symfony\Component\Form\Form $interventionDemandeFormulaire Formulaire de la demande d'intervention
     * @return void
     */
    private function gereEnvoiFormulaireDemandeEdition($interventionDemandeFormulaire)
    {
        if ($this->get('request')->isMethod('POST'))
        {
            $interventionDemandeFormulaire->bind($this->get('request'));
    
            if ($interventionDemandeFormulaire->isValid())
            {
                $this->get('hopitalnumerique_intervention.manager.interventiondemande')->save($this->interventionDemande);
                $this->get('session')->getFlashBag()->add('success', 'La demande d\'intervention a été modifiée.');
            }
            else
            {
                $this->get('session')->getFlashBag()->add('danger', 'Le formulaire n\'est pas valide.');
            }
        }
    }
}
