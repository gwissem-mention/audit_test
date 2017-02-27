<?php
/**
 * Contrôleur des demandes d'intervention pour la console administrative.
 *
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */

namespace HopitalNumerique\InterventionBundle\Controller\Admin;

use HopitalNumerique\InterventionBundle\Entity\InterventionDemande;
use HopitalNumerique\InterventionBundle\Entity\InterventionEvaluationEtat;

/**
 * Contrôleur des demandes d'intervention pour la console administrative.
 */
class DemandeController extends \HopitalNumerique\InterventionBundle\Controller\DemandeController
{
    /**
     * Action pour la visualisation d'une demande d'intervention.
     *
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $id La demande d'intervention à visionner
     *
     * @return \Symfony\Component\HttpFoundation\Response La vue de la visualisation d'une demande d'intervention
     */
    public function voirAction(InterventionDemande $id)
    {
        $interventionDemande = $id;

        $this->container->get('hopitalnumerique_intervention.service.demande.etat_type_derniere_demande')->setDerniereDemandeOuverte($interventionDemande);

        return $this->render(
            'HopitalNumeriqueInterventionBundle:Admin/Demande:voir.html.twig',
            $this->getVueParametresVoir($interventionDemande)
        );
    }

    /**
     * Action pour la visualisation d'une liste de demandes d'intervention.
     *
     * @return \Symfony\Component\HttpFoundation\Response La vue de la liste de demandes d'intervention
     */
    public function listeAction($filtre = null)
    {
        $gridDemandes = $this->get('hopitalnumerique_intervention.grid.admin.intervention_demandes');

        if (!is_null($filtre)) {
            $gridDemandes->setDefaultFiltreFromController($filtre);
        }

        return $gridDemandes->render('HopitalNumeriqueInterventionBundle:Admin/Demande:liste.html.twig');
    }

    /**
     * Action pour la liste des demandes d'intervention.
     *
     * @return \Symfony\Component\HttpFoundation\Response La vue de la liste des demandes d'intervention
     */
    public function gridDemandesAction()
    {
        $interventionDemandesGrille = $this->get('hopitalnumerique_intervention.grid.admin.intervention_demandes');

        return $interventionDemandesGrille->render('HopitalNumeriqueInterventionBundle:Grid:Admin/demandes.html.twig');
    }

    /**
     * Suppression des demandes d'intervention de la liste.
     *
     * @param int[] $primaryKeys Les ID des demandes d'intervention sélectionnées par l'utilisateur
     *
     * @return \Component\HttpFoundation\RedirectResponse Redirection vers la liste
     */
    public function gridSupprimeMassAction(array $primaryKeys)
    {
        $utilisateurConnecte = $this->get('security.context')->getToken()->getUser();

        if ($this->container->get('nodevo_acl.manager.acl')->checkAuthorization($this->generateUrl('hopital_numerique_intervention_admin_demande_delete', ['id' => 0]), $utilisateurConnecte) != -1) {
            $interventionDemandes = $this->container->get('hopitalnumerique_intervention.manager.intervention_demande')->findBy(['id' => $primaryKeys]);
            $this->container->get('hopitalnumerique_intervention.manager.intervention_demande')->delete($interventionDemandes);

            $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.');
        } else {
            $this->get('session')->getFlashBag()->add('warning', 'Vous ne possédez pas les droits nécessaires pour supprimer des interventions.');
        }

        return $this->redirect($this->generateUrl('hopital_numerique_intervention_admin_liste'));
    }

    /**
     * Export de masse des demandes d'intervention.
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param bool  $allPrimaryKeys
     *
     * @return Redirect
     */
    public function exportMassAction($primaryKeys, $allPrimaryKeys)
    {
        if ($allPrimaryKeys == 1) {
            $rawDatas = $this->get('hopitalnumerique_intervention.manager.intervention_demande')->findAll();
            foreach ($rawDatas as $data) {
                $primaryKeys[] = $data->getId();
            }
        }

        return $this->get('hopitalnumerique_intervention.manager.intervention_demande')->getExportCsv($primaryKeys, $this->container->getParameter('kernel.charset'));
    }

    /**
     * Evaluer en masse les demandes d'intervention.
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param bool  $allPrimaryKeys
     *
     * @return Redirect
     */
    public function evaluationMassAction($primaryKeys, $allPrimaryKeys)
    {
        if ($allPrimaryKeys == 1) {
            $rawDatas = $this->get('hopitalnumerique_intervention.manager.intervention_demande')->findAll();
            foreach ($rawDatas as $data) {
                $primaryKeys[] = $data->getId();
            }
        }

        $interventionDemandes = $this->get('hopitalnumerique_intervention.manager.intervention_demande')->findBy(['id' => $primaryKeys]);

        //Lance les workflow de chaque passage à évaluer
        foreach ($interventionDemandes as $interventionDemande) {
            if (is_null($interventionDemande->getEvaluationEtat()) || $interventionDemande->getEvaluationEtat()->getId() !== InterventionEvaluationEtat::getInterventionEvaluationEtatEvalueId()) {
                $interventionDemande->setRemboursementEtat($this->get('hopitalnumerique_reference.manager.reference')->findOneBy(['id' => 5]));

                $this->get('hopitalnumerique_intervention.manager.intervention_demande')->changeEtat($interventionDemande, $this->container->get('hopitalnumerique_intervention.manager.intervention_etat')->getInterventionEtatTermine());
                $this->get('hopitalnumerique_intervention.manager.intervention_demande')->changeEvaluationEtat($interventionDemande, $this->container->get('hopitalnumerique_intervention.manager.intervention_evaluation_etat')->getInterventionEvaluationEtatEvalue());
            }
        }

        return $this->redirect($this->generateUrl('hopital_numerique_intervention_admin_liste'));
    }
}
