<?php
/**
 * Contrôleur des demandes d'intervention.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\InterventionBundle\Entity\InterventionDemande;

/**
 * Contrôleur des demandes d'intervention.
 */
class DemandeController extends Controller
{
    /**
     * Action pour la création d'une nouvelle demande d'intervention.
     */
    public function nouveauAction()
    {
        $interventionDemande = new InterventionDemande();
        $interventionDemandeFormulaire = $this->createForm('hopitalnumerique_interventionbundle_interventiondemande', $interventionDemande);
        //$interventionDemandeFormulaire = $this->createForm('hopitalnumerique_interventionbundle_user', new \HopitalNumerique\UserBundle\Entity\User());

        return $this->render(
            'HopitalNumeriqueInterventionBundle:Demande:nouveau.html.twig',
            array(
                'interventionDemandeFormulaireNouveau' => $interventionDemandeFormulaire->createView()
            )
        );
    }
}
