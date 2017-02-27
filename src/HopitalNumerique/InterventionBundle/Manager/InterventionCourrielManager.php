<?php
/**
 * Manager pour les envois de courriels concernant les interventions.
 *
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */

namespace HopitalNumerique\InterventionBundle\Manager;

use Nodevo\MailBundle\Entity\Mail;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\InterventionBundle\Entity\InterventionCourriel;
use HopitalNumerique\InterventionBundle\Entity\InterventionDemande;
use Nodevo\MailBundle\Manager\MailManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use HopitalNumerique\InterventionBundle\DependencyInjection\Demande\EnvoiCourrielsAffichageLogs;

/**
 * Manager pour les envois de courriels concernant les interventions.
 */
class InterventionCourrielManager
{
    /**
     * @var \Nodevo\MailBundle\Manager\MailManager Manager de Mail
     */
    private $mailManager;
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Routing\Router Router de l'application
     */
    private $router;
    /**
     * @var \Swift_Mailer Service d'envoi de courriels
     */
    private $mailer;
    /**
     * @var \HopitalNumerique\InterventionBundle\DependencyInjection\Demande\EnvoiCourrielsAffichageLogs Service de logs des envois de courriel
     */
    private $envoiCourrielsAffichageLogs;

    /**
     * @var array Contacts HN
     */
    private $contacts;

    /**
     * Constructeur du manager gérant les demandes d'intervention.
     *
     * @param \Nodevo\MailBundle\Manager\MailManager                                                       $mailManager                 Manager de Mail
     * @param \Swift_Mailer                                                                                $mailer                      Service d'envoi de courriels
     * @param \Symfony\Bundle\FrameworkBundle\Routing\Router                                               $router                      Router de l'application
     * @param \HopitalNumerique\InterventionBundle\DependencyInjection\Demande\EnvoiCourrielsAffichageLogs $envoiCourrielsAffichageLogs Service de logs des envois de courriel
     */
    public function __construct(MailManager $mailManager, \Swift_Mailer $mailer, Router $router, EnvoiCourrielsAffichageLogs $envoiCourrielsAffichageLogs, $contacts)
    {
        $this->mailManager = $mailManager;
        $this->mailer = $mailer;
        $this->router = $router;
        $this->envoiCourrielsAffichageLogs = $envoiCourrielsAffichageLogs;
        $this->contacts = $contacts;
    }

    /**
     * Envoi le courriel de création de demande d'intervention à l'établissement.
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $utilisateurEtablissement L'utilisateur demandeur
     */
    public function envoiCourrielCreation(User $utilisateurEtablissement)
    {
        $courriel = $this->mailManager->findOneById(InterventionCourriel::getInterventionCourrielCreationId());

        $this->envoiCourriel($courriel, $utilisateurEtablissement);
    }

    /**
     * Envoi le courriel de demande d'acceptation ou non d'une demande d'intervention à l'ambassadeur.
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $ambassadeur            L'ambassadeur de la demande
     * @param string                                   $interventionDemandeUrl Le chemin vers l'URL de la demande d'intervention
     */
    public function envoiCourrielDemandeAcceptationAmbassadeur(User $ambassadeur, $interventionDemandeUrl)
    {
        $courriel = $this->mailManager->findOneById(InterventionCourriel::getInterventionCourrielAcceptationAmbassadeurId());

        $this->envoiCourriel($courriel, $ambassadeur, ['l' => $interventionDemandeUrl]);
    }

    /**
     * Envoi le courriel d'acceptation ou non d'une demande d'intervention par le CMSI.
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $cmsi                   Le CMSI de la demande
     * @param string                                   $interventionDemandeUrl Le chemin vers l'URL de la demande d'intervention
     */
    public function envoiCourrielDemandeAcceptationCmsi(User $cmsi, $interventionDemandeUrl)
    {
        $courriel = $this->mailManager->findOneById(InterventionCourriel::getInterventionCourrielAcceptationCmsiId());

        $this->envoiCourriel($courriel, $cmsi, ['l' => $interventionDemandeUrl]);
    }

    /**
     * Envoi le courriel d'alerte de création de demande d'acceptation émise par un CMSI.
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $utilisateurEtablissement L'utilisateur demandeur
     */
    public function envoiCourrielAlerteReferent(User $referentEtablissement)
    {
        $courriel = $this->mailManager->findOneById(InterventionCourriel::getInterventionCourrielAlerteReferentId());

        $this->envoiCourriel($courriel, $referentEtablissement);
    }

    /**
     * Envoi le courriel d'acceptation d'une demande d'acceptation par un CMSI.
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $utilisateurEtablissement L'utilisateur demandeur
     */
    public function envoiCourrielEstAccepteCmsi(User $referentEtablissement)
    {
        $courriel = $this->mailManager->findOneById(InterventionCourriel::getInterventionCourrielEstAccepteeCmsiId());

        $this->envoiCourriel($courriel, $referentEtablissement);
    }

    /**
     * Envoi le courriel de refus d'une demande d'acceptation par un CMSI.
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $utilisateurEtablissement L'utilisateur demandeur
     * @param string                                   $interventionDemandeUrl   Le chemin vers l'URL de la demande d'intervention
     */
    public function envoiCourrielEstRefuseCmsi(InterventionDemande $interventionDemande)
    {
        $interventionDemandeUrl = $this->router->generate('hopital_numerique_intervention_demande_voir', ['id' => $interventionDemande->getId()], true);

        $courriel = $this->mailManager->findOneById(InterventionCourriel::getInterventionCourrielEstRefuseeCmsiId());

        $this->envoiCourriel($courriel, $interventionDemande->getReferent(), ['l' => $interventionDemandeUrl, 'm' => $interventionDemande->getCmsiCommentaire()]);
    }

    /**
     * Envoi le courriel d'invitation du référent à évaluaer une intervention.
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $utilisateurEtablissement  L'utilisateur demandeur
     * @param string                                   $interventionEvaluationUrl Le chemin vers l'URL de l'évaluation de la demande d'intervention
     */
    public function envoiCourrielInvitationEvaluationReferent(User $referentEtablissement, $interventionEvaluationUrl)
    {
        $courriel = $this->mailManager->findOneById(InterventionCourriel::getInterventionCourrielInvitationEvaluationReferentId());

        $this->envoiCourriel($courriel, $referentEtablissement, ['l' => $interventionEvaluationUrl]);
    }

    /**
     * Envoi le courriel de changement d'ambassadeur.
     *
     * @param \HopitalNumerique\UserBundle\Entity\User[] $destinataires          Les destinataires du courriel
     * @param \HopitalNumerique\UserBundle\Entity\User   $nouvelAmbassadeur      Le nouvel ambassadeur
     * @param string                                     $interventionDemandeUrl Le chemin vers l'URL de la demande d'intervention
     */
    public function envoiCourrielChangementAmbassadeur(array $destinataires, User $nouvelAmbassadeur, $interventionDemandeUrl)
    {
        $courriel = $this->mailManager->findOneById(InterventionCourriel::getInterventionCourrielChangementAmbassadeurId());

        foreach ($destinataires as $destinataire) {
            $this->envoiCourriel($courriel, $destinataire, [
                'l' => $interventionDemandeUrl,
                'a' => $nouvelAmbassadeur->getAppellation(),
            ]);
        }
    }

    /**
     * Envoi le courriel d'acceptation d'une demande d'acceptation par un ambassadeur.
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $cmsi                   Le CMSI de la demande d'intervention
     * @param \HopitalNumerique\UserBundle\Entity\User $referent               Le référent de la demande d'intervention
     * @param string                                   $interventionDemandeUrl Le chemin vers l'URL de la demande d'intervention
     */
    public function envoiCourrielEstAccepteAmbassadeur(User $referent, $interventionDemandeUrl)
    {
        $courriel = $this->mailManager->findOneById(InterventionCourriel::getInterventionCourrielEstAccepteeAmbassadeurId());

        // RLE 05/06/2014 : Suppression de l'envoi au CMSI
        $this->envoiCourriel($courriel, $referent, ['l' => $interventionDemandeUrl]);
    }

    /**
     * Envoi le courriel de refus d'une demande d'acceptation par un ambassadeur.
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $cmsi                   Le CMSI de la demande d'intervention
     * @param \HopitalNumerique\UserBundle\Entity\User $referent               Le référent de la demande d'intervention
     * @param string                                   $interventionDemandeUrl Le chemin vers l'URL de la demande d'intervention
     */
    public function envoiCourrielEstRefuseAmbassadeur(User $referent, $interventionDemandeUrl)
    {
        $courriel = $this->mailManager->findOneById(InterventionCourriel::getInterventionCourrielEstRefuseeAmbassadeurId());

        // RLE 05/06/2014 : Suppression de l'envoi au CMSI
        $this->envoiCourriel($courriel, $referent, ['l' => $interventionDemandeUrl]);
    }

    /**
     * Envoi le courriel de relance 1 d'un ambassadeur.
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $ambassadeur            L'ambassadeur à relancer
     * @param string                                   $interventionDemandeUrl Le chemin vers l'URL de la demande d'intervention
     */
    public function envoiCourrielRelanceAmbassadeur1(User $ambassadeur, $interventionDemandeUrl)
    {
        $courriel = $this->mailManager->findOneById(InterventionCourriel::getInterventionCourrielRelanceAmbassadeur1Id());

        $this->envoiCourriel($courriel, $ambassadeur, ['l' => $interventionDemandeUrl]);
    }

    /**
     * Envoi le courriel de relance 2 d'un ambassadeur.
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $ambassadeur            L'ambassadeur à relancer
     * @param string                                   $interventionDemandeUrl Le chemin vers l'URL de la demande d'intervention
     */
    public function envoiCourrielRelanceAmbassadeur2(User $ambassadeur, $interventionDemandeUrl)
    {
        $courriel = $this->mailManager->findOneById(InterventionCourriel::getInterventionCourrielRelanceAmbassadeur2Id());

        $this->envoiCourriel($courriel, $ambassadeur, ['l' => $interventionDemandeUrl]);
    }

    /**
     * Envoi le courriel de clôture car sans nouvelle de l'ambassadeur.
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $cmsi                   Le CMSI de l'intervention
     * @param \HopitalNumerique\UserBundle\Entity\User $ambassadeur            L'ambassadeur à relancer
     * @param \HopitalNumerique\UserBundle\Entity\User $referent               Le référent de l'établissement
     * @param string                                   $interventionDemandeUrl Le chemin vers l'URL de la demande d'intervention
     */
    public function envoiCourrielRelanceAmbassadeurCloture(User $cmsi, User $ambassadeur, User $referent, $interventionDemandeUrl)
    {
        $courriel = $this->mailManager->findOneById(InterventionCourriel::getInterventionCourrielRelanceAmbassadeurClotureId());

        $this->envoiCourriel($courriel, $cmsi, ['l' => $interventionDemandeUrl]);
        $this->envoiCourriel($courriel, $ambassadeur, ['l' => $interventionDemandeUrl]);
        $this->envoiCourriel($courriel, $referent, ['l' => $interventionDemandeUrl]);
    }

    /**
     * Envoi le courriel de clôture car sans nouvelle de l'ambassadeur.
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $cmsi                      Le CMSI de l'intervention
     * @param \HopitalNumerique\UserBundle\Entity\User $ambassadeur               L'ambassadeur à relancer
     * @param string                                   $interventionEvaluationUrl Le chemin vers l'URL de l'évaluation de l'intervention
     */
    public function envoiCourrielEvaluationRemplie(User $cmsi, User $ambassadeur, $interventionEvaluationUrl)
    {
        $courriel = $this->mailManager->findOneById(InterventionCourriel::getInterventionCourrielEvaluationRemplieId());

        $this->envoiCourriel($courriel, $cmsi, ['l' => $interventionEvaluationUrl]);
        $this->envoiCourriel($courriel, $ambassadeur, ['l' => $interventionEvaluationUrl]);
    }

    /**
     * Envoi le courriel d'annulation d'une demande par un établissement.
     *
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $interventionDemande La demande d'intervention annulée
     */
    public function envoiCourrielEstAnnuleEtablissement(InterventionDemande $interventionDemande)
    {
        $courriel = $this->mailManager->findOneById(InterventionCourriel::getInterventionCourrielEstAnnuleeEtablissementId());

        $interventionDemandeUrl = $this->router->generate('hopital_numerique_intervention_demande_voir', ['id' => $interventionDemande->getId()], true);
        $this->envoiCourriel($courriel, $interventionDemande->getCmsi(), ['l' => $interventionDemandeUrl]);
        if ($interventionDemande->interventionEtatEstAcceptationCmsi()) {
            $this->envoiCourriel($courriel, $interventionDemande->getAmbassadeur(), ['l' => $interventionDemandeUrl]);
        }
    }

    /**
     * Envoi le courriel de relance d'une demande d'intervention en attente CMSI.
     *
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $interventionDemande La demande d'intervention en attente CMSI
     */
    public function envoiCourrielRelanceAttenteCmsi(InterventionDemande $interventionDemande)
    {
        $courriel = $this->mailManager->findOneById(InterventionCourriel::getInterventionCourrielRelanceAttenteCmsiId());

        $interventionDemandeUrl = $this->router->generate('hopital_numerique_intervention_demande_voir', ['id' => $interventionDemande->getId()], true);
        $this->envoiCourriel($courriel, $interventionDemande->getCmsi(), ['l' => $interventionDemandeUrl]);
    }

    /**
     * Envoie le courriel quand le référent n'a pas d'ES rattaché.
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $referent Référent
     */
    public function envoiCourrielSollicitationSansEtablissement(User $referent)
    {
        $courriel = $this->mailManager->findOneById(InterventionCourriel::COURRIEL_SOLLICITATION_SANS_ETABLISSEMENT_ID);

        $referentUrl = $this->router->generate('hopital_numerique_user_edit', ['id' => $referent->getId()], true);
        $this->envoiCourriel($courriel, $this->contacts, ['url' => $referentUrl]);
    }

    /**
     * Envoi un courriel concernant les interventions.
     *
     * @param \Nodevo\MailBundle\Entity\Mail                 $mail          Le courriel à envoyer
     * @param \HopitalNumerique\UserBundle\Entity\User|array $destinataire  Le destinataire du courriel
     * @param array                                          $remplacements Les textes dynamiques
     */
    private function envoiCourriel(Mail $mail, $destinataire, $remplacements = [])
    {
        if ($destinataire instanceof User) {
            $remplacements['u'] = $destinataire->getAppellation();
            $courriel = $this->mailManager->sendInterventionMail($mail, $destinataire, $remplacements);
        } else {
            $courriel = $this->mailManager->sendInterventionMail($mail, null, $remplacements);
            foreach ($destinataire as $destinataireAdresseElectronique => $destinataireNom) {
                $courriel->addTo($destinataireAdresseElectronique, $destinataireNom);
            }
        }

        $this->mailer->send($courriel);

        $this->envoiCourrielsAffichageLogs->addLog('Courriel "' . $mail->getObjet() . '" envoyé à ' . ($destinataire instanceof User ? $destinataire->getAppellation() : print_r($destinataire, true)) . "\n");
    }
}
