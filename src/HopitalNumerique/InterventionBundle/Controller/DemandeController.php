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

/**
 * Contrôleur des demandes d'intervention.
 */
class DemandeController extends Controller
{
    /**
     * Action pour la création d'une nouvelle demande d'intervention.
     * 
     * @return \Symfony\Component\HttpFoundation\Response La vue du formulaire de création d'une demande d'intervention
     */
    public function nouveauAction()
    {
        $utilisateurConnecte = $this->get('security.context')->getToken()->getUser();
        $interventionDemande = new InterventionDemande();

        // @todo TEST
        if (/*true || */$utilisateurConnecte->hasRoleCmsi())
        {
            $utilisateurFormulaire = $this->createForm('hopitalnumerique_interventionbundle_user_cmsi', $utilisateurConnecte);
            $interventionDemandeFormulaire = $this->createForm('hopitalnumerique_interventionbundle_interventiondemande_cmsi', $interventionDemande);
        }
        else
        {
            $utilisateurFormulaire = $this->createForm('hopitalnumerique_interventionbundle_user_etablissement', $utilisateurConnecte);
            $interventionDemandeFormulaire = $this->createForm('hopitalnumerique_interventionbundle_interventiondemande_etablissement', $interventionDemande);
        }

        /*if ($this->get('request')->getMethod() == 'POST')
        {
            $utilisateurFormulaire->bind($this->get('request'));
            $interventionDemandeFormulaire->bind($this->get('request'));
            
            if ($utilisateurFormulaire->isValid() && $interventionDemandeFormulaire->isValid())
            {
                $this->get('hopitalnumerique_user.manager.user')->save($utilisateurConnecte);
                $this->get('hopitalnumerique_intervention.manager.interventiondemande')->save($interventionDemande);
                $this->get('session')->getFlashBag()->add('success', 'La demande d\'intervention a été enregistrée.');
            }
            else $this->get('session')->getFlashBag()->add('danger', 'Le formulaire n\'est pas valide.');
        }*/

        return $this->render(
            'HopitalNumeriqueInterventionBundle:Demande:nouveau.html.twig',
            array(
                'utilisateurFormulaireNouveau' => $utilisateurFormulaire->createView(),
                'interventionDemandeFormulaireNouveau' => $interventionDemandeFormulaire->createView()
            )
        );
    }
}
