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
        $referent = $this->get('security.context')->getToken()->getUser();
        $interventionDemande = new InterventionDemande();
        $interventionDemandeFormulaire = $this->createForm('hopitalnumerique_interventionbundle_interventiondemande', $interventionDemande);
        $referentFormulaire = $this->createForm('hopitalnumerique_interventionbundle_user', $referent);

        return $this->render(
            'HopitalNumeriqueInterventionBundle:Demande:nouveau.html.twig',
            array(
                'referentFormulaireNouveau' => $referentFormulaire->createView(),
                'interventionDemandeFormulaireNouveau' => $interventionDemandeFormulaire->createView()
            )
        );
    }
}
