<?php

namespace HopitalNumerique\InterventionBundle\Controller\Admin\Form;

use HopitalNumerique\InterventionBundle\Form\InterventionDemande\AdminType;
use HopitalNumerique\InterventionBundle\Form\InterventionDemande\Edition\AdminType as EditAdminType;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;
use HopitalNumerique\InterventionBundle\Entity\InterventionDemande;
use HopitalNumerique\InterventionBundle\Entity\InterventionEtat;
use HopitalNumerique\InterventionBundle\Entity\InterventionEvaluationEtat;

/**
 * Contrôleur des formulaires de demandes d'intervention dans l'administration.
 */
class DemandeController extends Controller
{
    /**
     * @var InterventionDemande Demande d'intervention en cours
     */
    private $interventionDemande;

    /**
     * Création d'une demande d'intervention.
     *
     * @return Response La vue du formulaire de modification d'une demande d'intervention
     */
    public function nouveauAction()
    {
        $this->interventionDemande = new InterventionDemande();

        $user = $this->getUser();
        if ($user instanceof User) {
            $this->interventionDemande->setEmail($user->getEmail());
            $this->interventionDemande->setTelephone($user->getPhoneNumber());
        }

        $this->interventionDemande->setInterventionInitiateur(
            $this->container->get('hopitalnumerique_intervention.manager.intervention_initiateur')
                ->getInterventionInitiateurAnap()
        );

        $this->interventionDemande->setInterventionEtat(
            $this->container->get('hopitalnumerique_intervention.manager.intervention_etat')
                ->getInterventionEtatAcceptationCmsi()
        );

        $interventionDemandeFormulaire = $this->createForm(
            AdminType::class,
            $this->interventionDemande
        );

        if ($this->gereEnvoiFormulaireDemandeNouveau($interventionDemandeFormulaire)) {
            $do = $this->container->get('request')->request->get('do');

            return $this->redirect(
                $do == 'save-close'
                ? $this->generateUrl('hopital_numerique_intervention_admin_liste')
                : $this->generateUrl(
                    'hopital_numerique_intervention_admin_demande_edit',
                    ['id' => $this->interventionDemande->getId()]
                )
            );
        }

        return $this->render(
            'HopitalNumeriqueInterventionBundle:Admin/Demande:nouveau.html.twig',
            [
                'interventionDemandeFormulaireNouveau' => $interventionDemandeFormulaire->createView(),
                'interventionDemande' => $this->interventionDemande,
            ]
        );
    }

    /**
     * Gère l'enregistrement des données du formulaire de création d'une demande d'intervention.
     *
     * @param Form $interventionDemandeFormulaire Formulaire de la demande d'intervention
     *
     * @return bool VRAI ssi l'enregistrement s'est effectué
     */
    private function gereEnvoiFormulaireDemandeNouveau($interventionDemandeFormulaire)
    {
        if ($this->get('request')->isMethod('POST')) {
            $interventionDemandeFormulaire->bind($this->get('request'));

            if ($interventionDemandeFormulaire->isValid()) {
                $this->interventionDemande->setDateCreation(new \DateTime());
                //<-- CMSI de la région de l'ambassadeur
                $cmsi = $this->get('hopitalnumerique_user.manager.user')->getCmsi(
                    ['region' => $this->getUser()->getRegion(), 'enabled' => true]
                );

                if ($cmsi == null) {
                    $this->container->get('session')->getFlashBag()->add(
                        'danger',
                        'Un CMSI pour votre région doit exister pour créer une demande d\'intervention.'
                    );

                    return false;
                }

                $this->interventionDemande->setCmsi($cmsi);
                //-->
                $this->interventionDemande->setCmsiDateChoix(new \DateTime());

                $this->get('hopitalnumerique_intervention.manager.interventiondemande')->save(
                    $this->interventionDemande
                );

                // Message Flash
                $this->get('session')->getFlashBag()->add('success', 'La demande d\'intervention a été créée.');

                // Envoi des courriels
                $this->container->get('hopitalnumerique_intervention.manager.intervention_courriel')
                    ->envoiCourrielDemandeAcceptationAmbassadeur(
                        $this->interventionDemande->getAmbassadeur(),
                        $this->generateUrl(
                            'hopital_numerique_intervention_demande_voir',
                            ['id' => $this->interventionDemande->getId()],
                            true
                        )
                    )
                ;
                $this->container->get('hopitalnumerique_intervention.manager.intervention_courriel')
                    ->envoiCourrielAlerteReferent($this->interventionDemande->getReferent())
                ;

                return true;
            } else {
                $this->get('session')->getFlashBag()->add('danger', 'Le formulaire n\'est pas valide.');
            }
        }

        return false;
    }

    /**
     * Édition d'une demande d'intervention.
     *
     * @param InterventionDemande $id La demande d'intervention à éditer
     *
     * @return Response La vue du formulaire de modification d'une demande d'intervention
     */
    public function editAction(InterventionDemande $id)
    {
        $this->interventionDemande = $id;
        $interventionDemandeFormulaire = $this->createForm(
            EditAdminType::class,
            $this->interventionDemande
        );

        if ($this->gereEnvoiFormulaireDemandeEdition($interventionDemandeFormulaire)) {
            $do = $this->container->get('request')->request->get('do');

            return $this->redirect(
                $do == 'save-close'
                    ? $this->generateUrl('hopital_numerique_intervention_admin_liste')
                    : $this->generateUrl(
                        'hopital_numerique_intervention_admin_demande_edit',
                        ['id' => $this->interventionDemande->getId()]
                    )
            );
        }

        return $this->render(
            'HopitalNumeriqueInterventionBundle:Admin/Demande:edit.html.twig',
            [
                'interventionDemandeFormulaireEdition' => $interventionDemandeFormulaire->createView(),
                'interventionDemande' => $this->interventionDemande,
            ]
        );
    }

    /**
     * Gère l'enregistrement des données du formulaire d'édition d'une demande d'intervention.
     *
     * @param Form $interventionDemandeFormulaire Formulaire de la demande d'intervention
     *
     * @return bool VRAI ssi l'enregistrement s'est effectué
     */
    private function gereEnvoiFormulaireDemandeEdition($interventionDemandeFormulaire)
    {
        if ($this->get('request')->isMethod('POST')) {
            $interventionDemandeFormulaire->bind($this->get('request'));

            if ($interventionDemandeFormulaire->isValid()) {
                // YRO 09/02/2015 : si le champ "etat actuel" a été modifié, on envoi les mails conséquents
                if ($this->get('hopitalnumerique_intervention.manager.interventiondemande')
                    ->isEtatActuelUpdated($this->interventionDemande)
                ) {
                    $this->gereEnvoiMailChangementEtat($this->interventionDemande);
                }

                if (!is_null($this->interventionDemande->getEvaluationEtat())
                    && $this->interventionDemande->getEvaluationEtat()->getId()
                       === InterventionEvaluationEtat::getInterventionEvaluationEtatEvalueId()
                ) {
                    $this->interventionDemande->setRemboursementEtat(
                        $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(['id' => 5])
                    );

                    $this->get('hopitalnumerique_intervention.manager.intervention_demande')->changeEtat(
                        $this->interventionDemande,
                        $this->container->get('hopitalnumerique_intervention.manager.intervention_etat')
                                        ->getInterventionEtatTermine()
                    );
                    $this->get('hopitalnumerique_intervention.manager.intervention_demande')->changeEvaluationEtat(
                        $this->interventionDemande,
                        $this->container->get('hopitalnumerique_intervention.manager.intervention_evaluation_etat')
                                        ->getInterventionEvaluationEtatEvalue()
                    );
                }

                $this->get('hopitalnumerique_intervention.manager.interventiondemande')->save(
                    $this->interventionDemande
                );

                $this->get('session')->getFlashBag()->add('success', 'La demande d\'intervention a été modifiée.');

                return true;
            } else {
                $this->get('session')->getFlashBag()->add('danger', 'Le formulaire n\'est pas valide.');
            }
        }

        return false;
    }

    /**
     * Suppression d'une demande d'intervention.
     *
     * @param InterventionDemande|int $id ID de l'utilisateur
     *
     * @return Response
     */
    public function supprimeAction(InterventionDemande $id)
    {
        $interventionDemande = $id;
        $this->container->get('hopitalnumerique_intervention.manager.intervention_demande')->delete(
            $interventionDemande
        );
        $this->container->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.');

        $reponseJson = json_encode([
            'success' => true,
            'url' => $this->generateUrl('hopital_numerique_intervention_admin_liste'),
        ]);

        return new Response($reponseJson);
    }

    /**
     * Gère l'envoi des mails conséquents au changement d'état de la demande d'intervention.
     *
     * @TODO: Envoyer les mails
     *
     * @param InterventionDemande $interventionDemande
     */
    private function gereEnvoiMailChangementEtat(InterventionDemande $interventionDemande)
    {
        $etatId = $interventionDemande->getInterventionEtat()->getId();

        if ($etatId == InterventionEtat::getInterventionEtatRefusCmsiId()) {
            $this->container->get('hopitalnumerique_intervention.manager.intervention_courriel')
                ->envoiCourrielEstRefuseCmsi($interventionDemande)
            ;
        } elseif ($etatId == InterventionEtat::getInterventionEtatAcceptationCmsiId()) {
            $this->container->get('hopitalnumerique_intervention.manager.intervention_courriel')
                ->envoiCourrielDemandeAcceptationAmbassadeur(
                    $interventionDemande->getAmbassadeur(),
                    $this->generateUrl(
                        'hopital_numerique_intervention_demande_voir',
                        ['id' => $interventionDemande->getId()],
                        true
                    )
                )
            ;
        } elseif ($etatId == InterventionEtat::getInterventionEtatRefusAmbassadeurId()) {
            $this->container->get('hopitalnumerique_intervention.manager.intervention_courriel')
                ->envoiCourrielEstRefuseAmbassadeur(
                    $interventionDemande->getReferent(),
                    $this->generateUrl(
                        'hopital_numerique_intervention_demande_voir',
                        ['id' => $interventionDemande->getId()],
                        true
                    )
                )
            ;
        } elseif ($etatId == InterventionEtat::getInterventionEtatAcceptationAmbassadeurId()) {
            $this->container->get('hopitalnumerique_intervention.manager.intervention_courriel')
                ->envoiCourrielEstAccepteAmbassadeur(
                    $interventionDemande->getReferent(),
                    $this->generateUrl(
                        'hopital_numerique_intervention_demande_voir',
                        ['id' => $interventionDemande->getId()],
                        true
                    )
                )
            ;
        } elseif ($etatId == InterventionEtat::getInterventionEtatAnnulationEtablissementId()) {
            $this->container->get('hopitalnumerique_intervention.manager.intervention_courriel')
                ->envoiCourrielEstAnnuleEtablissement($interventionDemande)
            ;
        }
    }
}
