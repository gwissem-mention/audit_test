<?php

namespace HopitalNumerique\InterventionBundle\Controller\Form;

use HopitalNumerique\InterventionBundle\Entity\InterventionDemande;
use HopitalNumerique\InterventionBundle\Entity\InterventionEtat;
use HopitalNumerique\InterventionBundle\Form\InterventionDemande\Edition\CmsiType;
use HopitalNumerique\InterventionBundle\Form\InterventionDemande\EtablissementType;
use HopitalNumerique\InterventionBundle\Form\InterventionDemande\RequiredUserDataType;
use HopitalNumerique\InterventionBundle\Service\InterventionDemandeBuilder;
use HopitalNumerique\InterventionBundle\Service\InterventionDemandeWorkflow;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Contrôleur des formulaires de demandes d'intervention.
 */
class DemandeController extends Controller
{
    /**
     * @var User Utilisateur connecté actuellement
     */
    private $utilisateurConnecte;

    /**
     * @var InterventionDemande Demande d'intervention en cours
     */
    private $interventionDemande;

    /**
     * Action pour la création d'une nouvelle demande d'intervention.
     *
     * @param User       $ambassadeur L'ambassadeur de la demande d'intervention
     * @param Objet|null $prod
     *
     * @return RedirectResponse|Response La vue du formulaire de création d'une demande d'intervention
     */
    public function nouveauAction(User $ambassadeur, Objet $prod = null)
    {
        if (!$ambassadeur->hasRoleAmbassadeur() || !$ambassadeur->isActif()) {
            $this->get('session')->getFlashBag()->add('danger', 'L\'utilisateur choisi n\'est pas un ambassadeur.');

            return $this->redirect($this->generateUrl('hopital_numerique_homepage'));
        }

        $this->utilisateurConnecte = $this->getUser();

        // Check if user define enough data to ask intervention.
        if (!$this->get(InterventionDemandeWorkflow::class)->userCanAskIntervention($this->utilisateurConnecte)) {
            return $this->redirectToRoute('hopital_numerique_intervention_demande_missing_data', [
                'ambassadeurId' => $ambassadeur->getId(),
                'prodId' => (null === $prod) ? 0 : $prod->getId(),
            ]);
        }

        $this->interventionDemande = $this->get(InterventionDemandeBuilder::class)->buildFromUser($this->utilisateurConnecte, $ambassadeur, $prod);

        if ($this->utilisateurConnecte->hasRoleCmsi()) {
            $interventionDemandeFormulaire = $this->createForm(\HopitalNumerique\InterventionBundle\Form\InterventionDemande\CmsiType::class, $this->interventionDemande);
        } else {
            $interventionDemandeFormulaire = $this->createForm(EtablissementType::class, $this->interventionDemande);
        }

        if ($this->gereEnvoiFormulaireDemandeNouveau($interventionDemandeFormulaire)) {
            return $this->redirect($this->generateUrl('hopital_numerique_intervention_demande_liste'));
        }

        return $this->render(
            'HopitalNumeriqueInterventionBundle:Demande:nouveau.html.twig',
            [
                'form' => $interventionDemandeFormulaire->createView(),
                'interventionDemande' => $this->interventionDemande,
                'ambassadeur' => $ambassadeur,
            ]
        );
    }

    public function missingDataAction(Request $request, $ambassadeurId, $prodId)
    {
        $loggedUser = $this->getUser();
        $form = $this->createForm(RequiredUserDataType::class, $loggedUser);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid() && $this->get(InterventionDemandeWorkflow::class)->userCanAskIntervention($loggedUser)) {
                $loggedUser->setDateLastUpdate(new \DateTime());
                $this->getDoctrine()->getManager()->flush();

                if (0 === $prodId) {
                    return $this->redirectToRoute('hopital_numerique_intervention_demande_nouveau', ['ambassadeur' => $ambassadeurId]);
                } else {
                    return $this->redirectToRoute('hopital_numerique_intervention_demande_nouveau_avec_objet', [
                        'ambassadeur' => $ambassadeurId,
                        'prod' => $prodId
                    ]);
                }
            } else {
                $this->get('session')->getFlashBag()->add('danger', 'Veuillez renseigner les champs demandés.');
            }
        }

        return $this->render('HopitalNumeriqueInterventionBundle:Demande:missing-data.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Gère l'enregistrement des données du formulaire de création d'une demande d'intervention.
     *
     * @param Form $interventionDemandeFormulaire Formulaire de la demande d'intervention
     *
     * @return bool vrai ssi le formulaire est validé
     */
    private function gereEnvoiFormulaireDemandeNouveau($interventionDemandeFormulaire)
    {
        if ($this->get('request')->isMethod('POST')) {
            $interventionDemandeFormulaire->handleRequest($this->get('request'));

            if ($interventionDemandeFormulaire->isValid()) {
                if (!$this->enregistreNouvelleDemande()) {
                    return false;
                }

                $this->envoieCourrielsNouvelleDemande();

                $this->addFlash(
                    'success',
                    'La demande d\'intervention a été enregistrée et sera étudiée.'
                );

                return true;
            } else {
                $this->addFlash('danger', 'Le formulaire n\'est pas valide.');
            }
        }

        return false;
    }

    /**
     * Enregistre une nouvelle demande d'intervention après soumission du formulaire.
     *
     * @return bool VRAI ssi la demande est enregistrée
     */
    private function enregistreNouvelleDemande()
    {
        $this->interventionDemande->setDateCreation(new \DateTime());

        $cmsi = null;
        if ($this->utilisateurConnecte->hasRoleCmsi()) {
            $cmsi = $this->utilisateurConnecte;
            $this->interventionDemande->setCmsiDateChoix($this->interventionDemande->getDateCreation());
            $this->interventionDemande->setAmbassadeurDateDerniereRelance(new \DateTime());
            $this->interventionDemande->setInterventionInitiateur(
                $this->get('hopitalnumerique_intervention.manager.intervention_initiateur')
                     ->getInterventionInitiateurCmsi()
            );
            $this->interventionDemande->setInterventionEtat(
                $this->get('hopitalnumerique_intervention.manager.intervention_etat')
                     ->getInterventionEtatAcceptationCmsi()
            );
        } else { // Établissement par défaut
            $cmsi = $this->get('hopitalnumerique_user.manager.user')->getCmsi(
                ['region' => $this->utilisateurConnecte->getRegion(), 'enabled' => true]
            );
            if ($cmsi == null) {
                $this->addFlash(
                    'danger',
                    'Un CMSI pour la région de l\'ambassadeur choisi doit exister pour créer une demande d\'intervention.'
                );

                return false;
            }

            $this->interventionDemande->setReferent($this->utilisateurConnecte);
            $this->interventionDemande->setInterventionInitiateur($this->get('hopitalnumerique_intervention.manager.intervention_initiateur')->getInterventionInitiateurEtablissement()
            );$this->interventionDemande->setInterventionEtat($this->get('hopitalnumerique_intervention.manager.intervention_etat')->getInterventionEtatDemandeInitiale());
        }

        $this->interventionDemande->setCmsi($cmsi);

        if ($this->interventionDemande->getReferent()->getOrganization() != null) {
            $this->interventionDemande->setDirecteur($this->get('hopitalnumerique_user.manager.user')->getDirecteur(['organization' => $this->interventionDemande->getReferent()->getOrganization(), 'enabled' => true,
                ])
            );
        }

        $this->get('hopitalnumerique_intervention.manager.interventiondemande')->save($this->interventionDemande);

        return true;
    }

    /**
     * Envoie les courriels nécessaires après l'enregistrement d'une nouvelle demande d'intervention.
     */
    private function envoieCourrielsNouvelleDemande()
    {
        if ($this->utilisateurConnecte->hasRoleCmsi()) {
            $this->get('hopitalnumerique_intervention.manager.intervention_courriel')->envoiCourrielDemandeAcceptationAmbassadeur($this->interventionDemande->getAmbassadeur(), $this->generateUrl('hopital_numerique_intervention_demande_voir', ['id' => $this->interventionDemande->getId()], true
            )
                )
            ;$this->get('hopitalnumerique_intervention.manager.intervention_courriel')->envoiCourrielAlerteReferent($this->interventionDemande->getReferent());
        } else { // Établissement par défaut
            $this->get('hopitalnumerique_intervention.manager.intervention_courriel')->envoiCourrielCreation($this->utilisateurConnecte
            );$this->get('hopitalnumerique_intervention.manager.intervention_courriel')->envoiCourrielDemandeAcceptationCmsi($this->interventionDemande->getCmsi(), $this->generateUrl('hopital_numerique_intervention_demande_voir', ['id' => $this->interventionDemande->getId()], true));
        }

        // Envoyer le courriel si le référent n'a pas d'ES
        if (null !== $this->interventionDemande->getReferent()
            && null === $this->interventionDemande->getReferent()->getOrganization()
        ) {
            $this->get('hopitalnumerique_intervention.manager.intervention_courriel')
                 ->envoiCourrielSollicitationSansEtablissement($this->interventionDemande->getReferent());
        }
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
        $this->utilisateurConnecte = $this->getUser();
        $this->interventionDemande = $id;
        $interventionDemandeFormulaire = null;

        if (($this->utilisateurConnecte->hasRoleCmsi() && ($this->interventionDemande->getInterventionEtat()->getId() == InterventionEtat::getInterventionEtatDemandeInitialeId() || $this->interventionDemande->getInterventionEtat()->getId() == InterventionEtat::getInterventionEtatAttenteCmsiId()))
        ) {    $interventionDemandeFormulaire = $this->createForm(CmsiType::class, $this->interventionDemande);
        }

        if ($interventionDemandeFormulaire == null
            || !$this->get(
                'hopitalnumerique_intervention.manager.intervention_demande'
            )
                ->peutEditer($this->interventionDemande)
        ) {
            $this->get('session')->getFlashBag()->add(
                'danger',
                'Vous n\'êtes pas autorisé à éditer cette demande d\'intervention.'
            );

            return $this->redirect($this->generateUrl('hopital_numerique_homepage'));
        }

        $this->gereEnvoiFormulaireDemandeEdition($interventionDemandeFormulaire);

        return $this->render(
            'HopitalNumeriqueInterventionBundle:Demande:edit.html.twig',
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
     */
    private function gereEnvoiFormulaireDemandeEdition($interventionDemandeFormulaire)
    {
        if ($this->get('request')->isMethod('POST')) {
            $interventionDemandeFormulaire->bind($this->get('request'));

            if ($interventionDemandeFormulaire->isValid()) {
                $this->get('hopitalnumerique_intervention.manager.interventiondemande')->save(
                    $this->interventionDemande
                );
                $this->get('session')->getFlashBag()->add('success', 'La demande d\'intervention a été modifiée.');
            } else {
                $this->get('session')->getFlashBag()->add('danger', 'Le formulaire n\'est pas valide.');
            }
        }
    }
}
