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
    public function nouveauAction()
    {
        $this->utilisateurConnecte = $this->get('security.context')->getToken()->getUser();
        $this->interventionDemande = new InterventionDemande();

        // @todo TEST
        if (/*true || */$this->utilisateurConnecte->hasRoleCmsi())
        {
            $utilisateurFormulaire = $this->createForm('hopitalnumerique_interventionbundle_user_cmsi', $this->utilisateurConnecte);
            $interventionDemandeFormulaire = $this->createForm('hopitalnumerique_interventionbundle_interventiondemande_cmsi', $this->interventionDemande);
        }
        else
        {
            $utilisateurFormulaire = $this->createForm('hopitalnumerique_interventionbundle_user_etablissement', $this->utilisateurConnecte);
            $interventionDemandeFormulaire = $this->createForm('hopitalnumerique_interventionbundle_interventiondemande_etablissement', $this->interventionDemande);
        }
        
        $this->_gereEnvoiFormulaireDemandeNouveau($utilisateurFormulaire, $interventionDemandeFormulaire);

        return $this->render(
            'HopitalNumeriqueInterventionBundle:Demande:nouveau.html.twig',
            array(
                'utilisateurFormulaireNouveau' => $utilisateurFormulaire->createView(),
                'interventionDemandeFormulaireNouveau' => $interventionDemandeFormulaire->createView()
            )
        );
    }
    
    /**
     * Gère l'enregistrement des données du formulaire de création d'une demande d'intervention.
     * 
     * @param \Symfony\Component\Form\Form $utilisateurFormulaire Formulaire de l'utilisateur connecté
     * @param \Symfony\Component\Form\Form $interventionDemandeFormulaire Formulaire de la demande d'intervention
     */
    private function _gereEnvoiFormulaireDemandeNouveau($utilisateurFormulaire, $interventionDemandeFormulaire)
    {
        if ($this->get('request')->getMethod() == 'POST')
        {
            $utilisateurFormulaire->bind($this->get('request'));
            $interventionDemandeFormulaire->bind($this->get('request'));
        
            if ($utilisateurFormulaire->isValid() && $interventionDemandeFormulaire->isValid())
            {
                //@todo Enre date création
                $this->get('hopitalnumerique_user.manager.user')->save($this->utilisateurConnecte);
                $this->get('hopitalnumerique_intervention.manager.interventiondemande')->save($this->interventionDemande);
                $this->get('session')->getFlashBag()->add('success', 'La demande d\'intervention a été enregistrée.');
            }
            else $this->get('session')->getFlashBag()->add('danger', 'Le formulaire n\'est pas valide.');
        }
    }
}
