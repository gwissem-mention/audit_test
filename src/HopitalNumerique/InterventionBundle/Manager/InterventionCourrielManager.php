<?php
/**
 * Manager pour les envois de courriels concernant les interventions.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Nodevo\MailBundle\Entity\Mail;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\InterventionBundle\Entity\InterventionCourriel;

/**
 * Manager pour les envois de courriels concernant les interventions.
 */
class InterventionCourrielManager
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface Container de l'application
     */
    private $container;
    /**
     * @var \Twig_Environment Environnement Twig de l'application
     */
    private $twig;

    /**
     * Constructeur du manager gérant les demandes d'intervention.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container Container de l'application
     * @param \Twig_Environment $twig L'environnement Twig de l'application
     * @return void
     */
    public function __construct(ContainerInterface $container, \Twig_Environment $twig)
    {
        $this->container = $container;
        $this->twig = $twig;
    }

    /**
     * Envoi le courriel de création de demande d'intervention à l'établissement.
     * 
     * @param \HopitalNumerique\UserBundle\Entity\User $utilisateurEtablissement L'utilisateur demandeur
     * @return void
     */
    public function envoiCourrielCreation(User $utilisateurEtablissement)
    {
        $courriel = $this->container->get('nodevo_mail.manager.mail')->findOneById(InterventionCourriel::getInterventionCourrielCreationId());

        $this->envoiCourriel($courriel, $utilisateurEtablissement);
    }
    /**
     * Envoi le courriel de demande d'acceptation ou non d'une demande d'intervention à l'ambassadeur.
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $ambassadeur L'ambassadeur de la demande
     * @param string $interventionDemandeUrl Le chemin vers l'URL de la demande d'intervention
     * @return void
     */
    public function envoiCourrielDemandeAcceptationAmbassadeur(User $ambassadeur, $interventionDemandeUrl)
    {
        $courriel = $this->container->get('nodevo_mail.manager.mail')->findOneById(InterventionCourriel::getInterventionCourrielAcceptationAmbassadeurId());

        $this->envoiCourriel($courriel, $ambassadeur, array('l' => $interventionDemandeUrl));
    }
    /**
     * Envoi le courriel d'acceptation ou non d'une demande d'intervention par le CMSI.
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $cmsi Le CMSI de la demande
     * @param string $interventionDemandeUrl Le chemin vers l'URL de la demande d'intervention
     * @return void
     */
    public function envoiCourrielDemandeAcceptationCmsi(User $cmsi, $interventionDemandeUrl)
    {
        $courriel = $this->container->get('nodevo_mail.manager.mail')->findOneById(InterventionCourriel::getInterventionCourrielAcceptationCmsiId());

        $this->envoiCourriel($courriel, $cmsi, array('l' => $interventionDemandeUrl));
    }
    /**
     * Envoi le courriel d'alerte de création de demande d'acceptation émise par un CMSI.
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $utilisateurEtablissement L'utilisateur demandeur
     * @return void
     */
    public function envoiCourrielAlerteReferent(User $referentEtablissement)
    {
        $courriel = $this->container->get('nodevo_mail.manager.mail')->findOneById(InterventionCourriel::getInterventionCourrielAlerteReferentId());

        $this->envoiCourriel($courriel, $referentEtablissement);
    }
    /**
     * Envoi le courriel d'acceptation d'une demande d'acceptation par un CMSI.
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $utilisateurEtablissement L'utilisateur demandeur
     * @return void
     */
    public function envoiCourrielEstAccepteCmsi(User $referentEtablissement)
    {
        $courriel = $this->container->get('nodevo_mail.manager.mail')->findOneById(InterventionCourriel::getInterventionCourrielEstAccepteeCmsiId());

        $this->envoiCourriel($courriel, $referentEtablissement);
    }
    /**
     * Envoi le courriel de refus d'une demande d'acceptation par un CMSI.
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $utilisateurEtablissement L'utilisateur demandeur
     * @param string $interventionDemandeUrl Le chemin vers l'URL de la demande d'intervention
     * @return void
     */
    public function envoiCourrielEstRefuseCmsi(User $referentEtablissement, $interventionDemandeUrl)
    {
        $courriel = $this->container->get('nodevo_mail.manager.mail')->findOneById(InterventionCourriel::getInterventionCourrielEstRefuseeCmsiId());

        $this->envoiCourriel($courriel, $referentEtablissement, array('l' => $interventionDemandeUrl));
    }
    /**
     * Envoi le courriel d'invitation du référent à évaluaer une intervention.
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $utilisateurEtablissement L'utilisateur demandeur
     * @param string $interventionEvaluationUrl Le chemin vers l'URL de l'évaluation de la demande d'intervention
     * @return void
     */
    public function envoiCourrielInvitationEvaluationReferent(User $referentEtablissement, $interventionEvaluationUrl)
    {
        $courriel = $this->container->get('nodevo_mail.manager.mail')->findOneById(InterventionCourriel::getInterventionCourrielInvitationEvaluationReferentId());
    
        $this->envoiCourriel($courriel, $referentEtablissement, array('l' => $interventionEvaluationUrl));
    }
    /**
     * Envoi le courriel de changement d'ambassadeur.
     *
     * @param \HopitalNumerique\UserBundle\Entity\User[] $destinataires Les destinataires du courriel
     * @param \HopitalNumerique\UserBundle\Entity\User $nouvelAmbassadeur Le nouvel ambassadeur
     * @param string $interventionDemandeUrl Le chemin vers l'URL de la demande d'intervention
     * @return void
     */
    public function envoiCourrielChangementAmbassadeur(array $destinataires, User $nouvelAmbassadeur, $interventionDemandeUrl)
    {
        $courriel = $this->container->get('nodevo_mail.manager.mail')->findOneById(InterventionCourriel::getInterventionCourrielChangementAmbassadeurId());
    
        foreach ($destinataires as $destinataire)
        {
            $this->envoiCourriel($courriel, $destinataire, array(
                'l' => $interventionDemandeUrl,
                'a' => $nouvelAmbassadeur->getAppellation()
            ));
        }
    }
    /**
     * Envoi le courriel d'acceptation d'une demande d'acceptation par un ambassadeur.
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $cmsi Le CMSI de la demande d'intervention
     * @param \HopitalNumerique\UserBundle\Entity\User $referent Le référent de la demande d'intervention
     * @param string $interventionDemandeUrl Le chemin vers l'URL de la demande d'intervention
     * @return void
     */
    public function envoiCourrielEstAccepteAmbassadeur(User $cmsi, User $referent, $interventionDemandeUrl)
    {
        $courriel = $this->container->get('nodevo_mail.manager.mail')->findOneById(InterventionCourriel::getInterventionCourrielEstAccepteeAmbassadeurId());
    
        $this->envoiCourriel($courriel, $cmsi, array('l' => $interventionDemandeUrl));
        $this->envoiCourriel($courriel, $referent, array('l' => $interventionDemandeUrl));
    }
    /**
     * Envoi le courriel de refus d'une demande d'acceptation par un ambassadeur.
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $cmsi Le CMSI de la demande d'intervention
     * @param \HopitalNumerique\UserBundle\Entity\User $referent Le référent de la demande d'intervention
     * @param string $interventionDemandeUrl Le chemin vers l'URL de la demande d'intervention
     * @return void
     */
    public function envoiCourrielEstRefuseAmbassadeur(User $cmsi, User $referent, $interventionDemandeUrl)
    {
        $courriel = $this->container->get('nodevo_mail.manager.mail')->findOneById(InterventionCourriel::getInterventionCourrielEstRefuseeAmbassadeurId());
    
        $this->envoiCourriel($courriel, $cmsi, array('l' => $interventionDemandeUrl));
        $this->envoiCourriel($courriel, $referent, array('l' => $interventionDemandeUrl));
    }
    /**
     * Envoi le courriel de relance 1 d'un ambassadeur.
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $ambassadeur L'ambassadeur à relancer
     * @param string $interventionDemandeUrl Le chemin vers l'URL de la demande d'intervention
     * @return void
     */
    public function envoiCourrielRelanceAmbassadeur1(User $ambassadeur, $interventionDemandeUrl)
    {
        $courriel = $this->container->get('nodevo_mail.manager.mail')->findOneById(InterventionCourriel::getInterventionCourrielRelanceAmbassadeur1Id());
    
        $this->envoiCourriel($courriel, $ambassadeur, array('l' => $interventionDemandeUrl));
    }
    /**
     * Envoi le courriel de relance 2 d'un ambassadeur.
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $ambassadeur L'ambassadeur à relancer
     * @param string $interventionDemandeUrl Le chemin vers l'URL de la demande d'intervention
     * @return void
     */
    public function envoiCourrielRelanceAmbassadeur2(User $ambassadeur, $interventionDemandeUrl)
    {
        $courriel = $this->container->get('nodevo_mail.manager.mail')->findOneById(InterventionCourriel::getInterventionCourrielRelanceAmbassadeur2Id());
    
        $this->envoiCourriel($courriel, $ambassadeur, array('l' => $interventionDemandeUrl));
    }
    /**
     * Envoi le courriel de clôture car sans nouvelle de l'ambassadeur.
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $cmsi Le CMSI de l'intervention
     * @param \HopitalNumerique\UserBundle\Entity\User $ambassadeur L'ambassadeur à relancer
     * @param \HopitalNumerique\UserBundle\Entity\User $referent Le référent de l'établissement
     * @param string $interventionDemandeUrl Le chemin vers l'URL de la demande d'intervention
     * @return void
     */
    public function envoiCourrielRelanceAmbassadeurCloture(User $cmsi, User $ambassadeur, User $referent, $interventionDemandeUrl)
    {
        $courriel = $this->container->get('nodevo_mail.manager.mail')->findOneById(InterventionCourriel::getInterventionCourrielRelanceAmbassadeurClotureId());
    
        $this->envoiCourriel($courriel, $cmsi, array('l' => $interventionDemandeUrl));
        $this->envoiCourriel($courriel, $ambassadeur, array('l' => $interventionDemandeUrl));
        $this->envoiCourriel($courriel, $referent, array('l' => $interventionDemandeUrl));
    }
    /**
     * Envoi le courriel de clôture car sans nouvelle de l'ambassadeur.
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $cmsi Le CMSI de l'intervention
     * @param \HopitalNumerique\UserBundle\Entity\User $ambassadeur L'ambassadeur à relancer
     * @param string $interventionEvaluationUrl Le chemin vers l'URL de l'évaluation de l'intervention
     * @return void
     */
    public function envoiCourrielEvaluationRemplie(User $cmsi, User $ambassadeur, $interventionEvaluationUrl)
    {
        $courriel = $this->container->get('nodevo_mail.manager.mail')->findOneById(InterventionCourriel::getInterventionCourrielEvaluationRemplieId());
    
        $this->envoiCourriel($courriel, $cmsi, array('l' => $interventionEvaluationUrl));
        $this->envoiCourriel($courriel, $ambassadeur, array('l' => $interventionEvaluationUrl));
    }
    
    
    /**
     * Envoi un courriel concernant les interventions.
     * 
     * @param \Nodevo\MailBundle\Entity\Mail $mail Le courriel à envoyer
     * @param \HopitalNumerique\UserBundle\Entity\User $destinataire Le destinataire du courriel
     * @param array $remplacements Les textes dynamiques
     * @return void
     */
    private function envoiCourriel(Mail $mail, User $destinataire, $remplacements = array())
    {
        $remplacements['u'] = $destinataire->getAppellation();
        $courrielCorps = $this->getCourrielCorps($mail, $remplacements);

        $courriel = \Swift_Message::newInstance()
            ->setSubject($mail->getObjet())
            ->setFrom(array($mail->getExpediteurMail() => $mail->getExpediteurName()))
            ->setTo($destinataire->getEmail())
            ->setBody($courrielCorps, 'text/html');
        $this->container->get('mailer')->send($courriel);
    }
    /**
     * Retourne le corps du courriel.
     * 
     * @param \Nodevo\MailBundle\Entity\Mail $mail Le courriel à envoyer
     * @param array $remplacements Les textes dynamiques
     * @return string Corps du courriel
     */
    private function getCourrielCorps(Mail $mail, $remplacements = array())
    {
        $courrielContenu = $mail->getBody();
        foreach ($remplacements as $texteRecherche => $remplacement)
            $courrielContenu = str_replace('%' . $texteRecherche, $remplacement, $courrielContenu);
        return $this->twig->loadTemplate('NodevoMailBundle::template.mail.html.twig')->render(array("content" => $courrielContenu));
    }
}
