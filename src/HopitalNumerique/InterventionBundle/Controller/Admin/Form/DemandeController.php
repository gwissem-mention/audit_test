<?php
/**
 * Contrôleur des formulaires de demandes d'intervention dans l'administration.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Controller\Admin\Form;

use Symfony\Component\HttpFoundation\Response;
use HopitalNumerique\InterventionBundle\Entity\InterventionDemande;
use HopitalNumerique\InterventionBundle\Entity\InterventionEtat;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * Contrôleur des formulaires de demandes d'intervention dans l'administration.
 */
class DemandeController extends \HopitalNumerique\InterventionBundle\Controller\Form\DemandeController
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

        $interventionDemandeFormulaire = $this->createForm('hopitalnumerique_interventionbundle_interventiondemande_edition_admin', $this->interventionDemande, array('interventionDemande' => $this->interventionDemande));

        $this->gereEnvoiFormulaireDemandeEdition($interventionDemandeFormulaire);

        return $this->render(
            'HopitalNumeriqueInterventionBundle:Admin/Demande:edit.html.twig',
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

    /**
     * Suppression d'une demande d'intervention.
     *
     * @param integer $id ID de l'utilisateur
     */
    public function supprimeAction(InterventionDemande $id)
    {
        $interventionDemande = $id;
        $this->container->get('hopitalnumerique_intervention.manager.intervention_demande')->delete($interventionDemande);
        $this->container->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.');
    
        $reponseJson = json_encode(array(
            'success' => true,
            'url' => $this->generateUrl('hopital_numerique_intervention_admin_liste')
        ));
    
        return new Response($reponseJson);
    }
}
