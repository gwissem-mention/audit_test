<?php

namespace Nodevo\MailBundle\Manager;

use Doctrine\ORM\EntityManager;
use HopitalNumerique\NotificationBundle\Entity\Notification;
use HopitalNumerique\NotificationBundle\Service\Notifications;
use HopitalNumerique\ObjetBundle\Entity\Note;
use Nodevo\MailBundle\Entity\Mail;
use Nodevo\ToolsBundle\Tools\Chaine;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use Symfony\Component\Routing\RouterInterface;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use Symfony\Component\HttpFoundation\RequestStack;
use HopitalNumerique\UserBundle\Manager\UserManager;
use HopitalNumerique\ModuleBundle\Entity\Inscription;
use CCDNComponent\BBCodeBundle\Component\BBCodeEngine;
use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use HopitalNumerique\ExpertBundle\Entity\ActiviteExpert;
use HopitalNumerique\ExpertBundle\Entity\CourrielRegistre;
use HopitalNumerique\DomaineBundle\Manager\DomaineManager;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Fiche;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use Symfony\Component\Security\Core\SecurityContextInterface;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;
use HopitalNumerique\ExpertBundle\Manager\ActiviteExpertManager;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Commentaire;
use HopitalNumerique\ExpertBundle\Manager\CourrielRegistreManager;

/**
 * Manager de l'entité Mail.
 */
class MailManager extends BaseManager
{
    protected $class = 'Nodevo\MailBundle\Entity\Mail';

    /**
     * @var \Swift_Mailer Mailer
     */
    private $mailer;

    private $_allowAdd;
    private $_allowDelete;

    /**
     * Envoie du mail en CCI à l'expediteur aussi.
     *
     * @var bool
     */
    private $_expediteurEnCopie;
    private $_nomExpediteur;
    private $_mailExpediteur;
    private $_destinataire;
    private $_twig;

    /**
     * Adresses mails en Copie Caché de l'anap.
     *
     * @var array() Tableau clé: Nom affiché => valeur : Adresse mail
     */
    private $_mailAnap = '';

    /**
     * @var RouterInterface
     */
    private $_router;
    private $_requestStack;

    private $_session;
    private $_domaineManager;
    private $_userManager;

    /**
     * @var ReferenceManager ReferenceManager
     */
    private $referenceManager;

    /**
     * @var ActiviteExpertManager ActiviteExpertManager
     */
    private $activiteExpertManager;

    /**
     * @var CourrielRegistreManager CourrielRegistreManager
     */
    private $courrielRegistreManager;

    private $_optionsMail = [];

    /**
     * @var User|null Utilisateur connecté
     */
    private $user;
    /** @var BBCodeEngine $bbcodeEngine */
    protected $bbcodeEngine;

    /**
     * @var Notifications
     */
    protected $notificationService;

    /**
     * Constructeur du manager, on lui passe l'entity Manager de doctrine, un booléen si on peut ajouter des mails.
     *
     * @param EntityManager $em Entity      Manager de Doctrine
     * @param \Swift_Mailer $mailer
     * @param \Twig_Environment $twig
     * @param $router
     * @param SecurityContextInterface $securityContext
     * @param RequestStack $requestStack
     * @param $session
     * @param DomaineManager $domaineManager
     * @param UserManager $userManager
     * @param ReferenceManager $referenceManager
     * @param ActiviteExpertManager $activiteExpertManager
     * @param CourrielRegistreManager $courrielRegistreManager
     * @param array $options Tableau d'options
     * @param BBCodeEngine $BBCodeEngine
     * @param Notifications $notifications
     */
    public function __construct(
        EntityManager $em,
        \Swift_Mailer $mailer,
        \Twig_Environment $twig,
        $router,
        SecurityContextInterface $securityContext,
        RequestStack $requestStack,
        $session,
        DomaineManager $domaineManager,
        UserManager $userManager,
        ReferenceManager $referenceManager,
        ActiviteExpertManager $activiteExpertManager,
        CourrielRegistreManager $courrielRegistreManager,
        $options = [],
        BBCodeEngine $BBCodeEngine,
        Notifications $notifications
    ) {
        parent::__construct($em);

        $this->mailer = $mailer;

        $this->_twig = $twig;
        $this->_router = $router;
        $this->_requestStack = $requestStack;
        $this->_allowAdd = isset($options['allowAdd']) ? $options['allowAdd'] : true;
        $this->_allowDelete = isset($options['allowDelete']) ? $options['allowDelete'] : true;
        $this->_expediteurEnCopie = isset($options['expediteurEnCopie']) ? $options['expediteurEnCopie'] : false;
        $this->_nomExpediteur = isset($options['nomExpediteur']) ? $options['nomExpediteur'] : '';
        $this->_mailExpediteur = isset($options['mailExpediteur']) ? $options['mailExpediteur'] : '';
        $this->_destinataire = isset($options['destinataire']) ? $options['destinataire'] : '';

        $this->_session = $session;
        $this->_domaineManager = $domaineManager;
        $this->_userManager = $userManager;
        $this->referenceManager = $referenceManager;
        $this->activiteExpertManager = $activiteExpertManager;
        $this->courrielRegistreManager = $courrielRegistreManager;
        $this->bbcodeEngine = $BBCodeEngine;
        $this->notificationService = $notifications;

        $this->setOptions();

        $this->user = (
            null !== $securityContext->getToken()
                ? (
                    $securityContext->getToken()->getUser() instanceof User
                    ? $securityContext->getToken()->getUser()
                    : null
                ) : null
        );
    }

    /**
     * @return string
     */
    public function getDestinataire()
    {
        return $this->_destinataire;
    }

    /**
     * L'ajout de mail est-il autorisé ?
     *
     * @return bool
     */
    public function isAllowedToAdd()
    {
        return $this->_allowAdd;
    }

    /**
     * La suppression de mail est-elle autorisée ?
     *
     * @return bool
     */
    public function isAllowedToDelete()
    {
        return $this->_allowDelete;
    }

    /**
     * Génération du mail avec le template NodevoMailBundle::template.mail.html.twig + envoie à l'user.
     *
     * @param User  $user
     * @param Mail  $mail
     * @param array $options Variables à remplacer dans le template : '%nomDansLeTemplate' => valeurDeRemplacement
     * @param int   $check
     *
     * @return \Swift_Message objet \Swift pour l'envoie du mail
     */
    private function generationMail($user, $mail, $options = [], $check = 0)
    {
        $options = $this->getAllOptions($options);

        $mailExpediteur = $this->replaceContent($mail->getExpediteurMail(), null, $options);
        $nameExpediteur = str_replace(
            'nodevoContactDomaineCurrent',
            $options['subjectDomaine'],
            $this->replaceContent($mail->getExpediteurName(), null, $options)
        );

        //prepare content
        $body = $this->replaceContent($mail->getBody(), $user, $options);
        $from = [$mailExpediteur => $nameExpediteur];

        if ($mail->getId() !== 1 && $mail->getId() !== 2) {
            $cci = $this->_expediteurEnCopie ? array_merge($this->getMailDomaine(), $from) : [$this->getMailDomaine()];

            if (null !== $user && $user instanceof User) {
                $regionReferentEmail = $this->getReferentRegionEmailForMailAndDestinataire($mail, $user);
                if (null !== $regionReferentEmail) {
                    $cci[] = $regionReferentEmail;
                }
            }
        } else {
            $cci = false;
        }

        $subject = $this->replaceContent($mail->getObjet(), $user, $options);

        return $this->sendMail($subject, $from, (null !== $user ? $user->getEmail() : null), $body, $cci, $check);
    }

    /**
     * Envoi un mail.
     *
     * @param string            $subject      Sujet du mail
     * @param string            $from         Expéditeur
     * @param string            $destinataire Destinataire
     * @param string            $body         Contenu du mail
     * @param array|bool|string $bcc          Copie(s) cachée(s)
     * @param int               $check
     *
     * @return \Swift_Message
     */
    public function sendMail($subject, $from, $destinataire = null, $body, $bcc = false, $check = 0)
    {
        $body = quoted_printable_decode($body);

        if (null !== $destinataire) {
            $user_mail = $this->_userManager->findOneBy(['email' => $destinataire]);

            if (($user_mail != null && !$user_mail->isActif()) || $check != 0) {
                return \Swift_Message::newInstance();
            }
        }

        //prepare content HTML
        $bodyHtml = str_replace(["\r\n", "\n"], '<br />', $body);
        $template = $this->_twig->loadTemplate('NodevoMailBundle::template.mail.html.twig');
        $bodyHtml = $template->render(['content' => $bodyHtml]);

        //prepare content TEXT
        $pattern = '/<a[^>]+href=([\'"])(.+?)\1[^>]*>(.*)<\/a>/i';
        if (preg_match_all($pattern, $body, $matches)) {
            foreach ($matches[1] as $key => $value) {
                $body = str_replace($matches[0][$key], '(' . $matches[2][$key] . ')' . $matches[3][$key], $body);
            }
        }
        $template = $this->_twig->loadTemplate('NodevoMailBundle::template.mail.txt.twig');
        $bodyTxt = $template->render(['content' => strip_tags($body)]);

        //prepare Mail
        $mail = \Swift_Message::newInstance();
        $mail
            ->setSubject($this->replaceContent($subject, null, []))
            ->setFrom($from)
            ->setBody($bodyTxt)
            ->addPart($bodyHtml, 'text/html')
        ;
        if (null !== $destinataire) {
            $mail->setTo($destinataire);
        }

        if ($bcc) {
            $mail->setBcc($bcc);
        }

        return $mail;
    }

    /**
     * Remplace les variables du mail par les vraies valeurs.
     *
     * @param string $content Contenu Templaté du mail
     * @param User   $user    User qui recevras l'email
     * @param array  $options Variables à remplacer dans le template : '%nomDansLeTemplate' => valeurDeRemplacement
     *
     * @return string
     */
    private function replaceContent($content, $user, $options)
    {
        $options = $this->getAllOptions($options);

        //Si il y a des variables spécifique dans le template courant
        if (!empty($options)) {
            foreach ($options as $key => $option) {
                //Récupération de la variable du template
                $variableARemplacer = '%' . $key;
                //Remplacement de la mise en forme
                $message = nl2br($option);
                //Mise à jour du contenu passé en arg
                $content = str_replace($variableARemplacer, $message, $content);
            }
        }

        if (!is_null($user)) {
            $content = str_replace('%u', $user->getFirstname() . ' ' . $user->getLastname(), $content);
            $content = str_replace('%p', $user->getPlainPassword(), $content);
        }

        $content = str_replace('%s', '<a href="' . $this->_requestStack->getCurrentRequest()->getUriForPath($this->_router->generate('hopital_numerique_homepage')) . '" target="_blank" >' . $this->_domaineManager->findOneById($this->_session->get('domaineId'))->getNom() . '</a>', $content);

        return $content;
    }

    /**
     * @return array
     */
    private function getMailDomaine()
    {
        if (null !== $this->_mailAnap && $this->_mailAnap === '') {
            $domaine = $this->_domaineManager->findOneById($this->_session->get('domaineId'));
            $this->_mailAnap = $domaine->getAdresseMailContact();
        }

        return $this->_mailAnap;
    }

    /**
     * @param array $options
     *
     * @return array
     */
    private function getAllOptions(array $options)
    {
        return array_merge($options, $this->_optionsMail);
    }

    /**
     * @return $this
     */
    private function setOptions()
    {
        $domaine = $this->_domaineManager->findOneById($this->_session->get('domaineId'));

        $this->_optionsMail = [
            'subjectDomaine' => $this->getDomaineSubjet(),
            'mailContactDomaineCurrent' => $domaine->getAdresseMailContact(),
            'nomContactDomaineCurrent' => $domaine->getNom(),
        ];

        return $this;
    }

    /**
     * Parse post body with BBCode, remove HTML, truncate to max $length characters and remove line endings.
     *
     * @param $body
     * @param int $length
     *
     * @return string
     */
    private function truncatePostBody($body, $length = 150)
    {
        $body = trim(strip_tags(html_entity_decode($this->bbcodeEngine->process($body))));

        if (strlen($body) > $length) {
            $body = substr($body, 0, strrpos(substr($body, 0, $length), ' ') ?: strrpos(substr($body, 0, $length), "\n") ?: $length);
        }

        $body = preg_replace("/\r\n|\r|\n/", " ", $body);

        return $body;
    }

    /**
     * Retourne le domaine courant sous forme de label pour le sujet du mail.
     *
     * @return mixed
     */
    private function getDomaineSubjet()
    {
        $chaine = new Chaine(
            is_null($this->_domaineManager->findOneById($this->_session->get('domaineId'))) ? 'Hopital Numérique'
                : $this->_domaineManager->findOneById($this->_session->get('domaineId'))->getNom()
        );

        return str_replace(' ', '', strtoupper($chaine->supprimeAccents()));
    }

    /**
     * Retourne l'adresse électronique du référent de région.
     *
     * @param Mail $mail         Mail
     * @param User $destinataire Destinataire
     *
     * @return null|string Adresse du référent
     */
    private function getReferentRegionEmailForMailAndDestinataire(Mail $mail, User $destinataire)
    {
        if ($mail->isNotificationRegionReferent() && null !== $destinataire->getRegion()) {
            $regionReferent = $this->_userManager->getRegionReferent($destinataire->getRegion());
            if (null !== $regionReferent) {
                return $regionReferent->getEmail();
            }
        }

        return null;
    }

    /**
     * Envoi un mail du type AjoutUser.
     *
     * @param User $user Utilisateur qui recevra l'email
     * @param      $options
     *
     * @return \Swift_Message
     */
    public function sendAjoutUserFromAdminMail($user, $options)
    {
        /** @var Mail $mail */
        $mail = $this->findOneById(1);

        return $this->generationMail($user, $mail, $options);
    }

    /**
     * Envoi un mail du type AjoutUser.
     *
     * @param User $user Utilisateur qui recevra l'email
     * @param      $options
     *
     * @return \Swift_Message
     */
    public function sendAjoutUserMail($user, $options)
    {
        /** @var Mail $mail */
        $mail = $this->findOneById(2);
        $url = $this->_router->generate('fos_user_registration_confirm', ['token' => $user->getConfirmationToken()], true);
        $options['url'] = $url;
        $check = 0;

        return $this->generationMail($user, $mail, $options, $check);
    }

    public function sendUserRoleUpdateNotification(User $user, $options)
    {
        $this->sendNotification($user, $options, Mail::MAIL_USER_ROLE_UPDATED);
    }

    /**
     * Envoi un mail de confirmation de candidature expert.
     *
     * @param User $user Utilisateur qui recevras l'email
     *
     * @return \Swift_Message
     */
    public function sendCandidatureExpertMail($user)
    {
        /** @var Mail $mail */
        $mail = $this->findOneById(8);

        return $this->generationMail($user, $mail);
    }

    /**
     * Envoi un mail de confirmation de candidature ambassadeur.
     *
     * @param User $user Utilisateur qui recevras l'email
     *
     * @return \Swift_Message
     */
    public function sendCandidatureAmbassadeurMail($user)
    {
        /** @var Mail $mail */
        $mail = $this->findOneById(9);

        return $this->generationMail($user, $mail);
    }

    /**
     * Envoi un mail de confirmation de candidature ambassadeur.
     *
     * @param User  $user    Utilisateur qui recevras l'email
     * @param array $options Variables à remplacer dans le template : '%nomDansLeTemplate' => valeurDeRemplacement
     *
     * @return \Swift_Message
     */
    public function sendCandidatureAmbassadeurCMSIMail($user, $options)
    {
        /** @var Mail $mail */
        $mail = $this->findOneById(24);

        return $this->generationMail($user, $mail, $options);
    }

    /**
     * Envoi un mail des réponses+question d'un questionnaire rempli par un utilisateur
     * (différent des autres envoie de mail).
     *
     * @param array $users    Utilisateurs qui recevront l'email (tableau configuré en config.yml/parameters.yml)
     * @param array $options Variables à remplacer dans le template : '%nomDansLeTemplate' => valeurDeRemplacement
     *
     * @return \Swift_Message[]
     */
    public function sendCandidatureExpertAdminMail($users, $options)
    {
        /** @var Mail $mail */
        $mail = $this->findOneById(28);

        //tableau de SwiftMessage a envoyé
        $mailsToSend = [];

        foreach ($users as $recepteurMail => $recepteurName) {
            $options['u'] = $recepteurName;
            $options = $this->getAllOptions($options);

            //prepare content
            $content = $this->replaceContent($mail->getBody(), null, $options);
            $expediteurMail = $this->replaceContent($mail->getExpediteurMail(), null, $options);
            $expediteurName = $this->replaceContent($mail->getExpediteurName(), null, $options);
            $content = $this->replaceContent($mail->getBody(), null, $options);
            $from = [$expediteurMail => $expediteurName];
            $subject = $this->replaceContent($mail->getObjet(), null, $options);

            $mailsToSend[] = $this->sendMail(
                $subject,
                $from,
                [$recepteurMail => $recepteurName],
                $content,
                $this->getMailDomaine()
            );
        }

        return $mailsToSend;
    }

    /**
     * Envoi un mail de validation de candidature expert.
     *
     * @param User $user Utilisateur qui recevras l'email
     *
     * @return \Swift_Message
     */
    public function sendValidationCandidatureExpertMail($user)
    {
        /** @var Mail $mail */
        $mail = $this->findOneById(11);

        return $this->generationMail($user, $mail);
    }

    /**
     * Envoi un mail de validation de candidature ambassadeur.
     *
     * @param User $user Utilisateur qui recevras l'email
     * @param User $CMSI CMSI qui recevras l'email en copie
     *
     * @return \Swift_Message
     */
    public function sendValidationCandidatureAmbassadeurMail($user, $CMSI = null)
    {
        /** @var Mail $mail */
        $mail = $this->findOneById(12);

        $mailValidation = $this->generationMail($user, $mail);

        if (!is_null($CMSI)) {
            $mailValidation->setCc([$CMSI->getEmail() => $CMSI->getNomPrenom()]);
        }

        return $mailValidation;
    }

    /**
     * Envoi un mail de refus de candidature expert.
     *
     * @param User  $user    Utilisateur qui recevras l'email
     * @param array $options Variables à remplacer dans le template : 'nomDansLeTemplate' => valeurDeRemplacement
     *
     * @return \Swift_Message
     */
    public function sendRefusCandidatureExpertMail($user, $options)
    {
        /** @var Mail $mail */
        $mail = $this->findOneById(13);

        return $this->generationMail($user, $mail, $options);
    }

    /**
     * Envoi un mail de validation de candidature ambassadeur.
     *
     * @param User  $user    Utilisateur qui recevras l'email
     * @param array $options Variables à remplacer dans le template : 'nomDansLeTemplate' => valeurDeRemplacement
     * @param User  $CMSI    CMSI qui recevras l'email en copie
     *
     * @return \Swift_Message
     */
    public function sendRefusCandidatureAmbassadeurMail($user, $options, $CMSI = null)
    {
        /** @var Mail $mail */
        $mail = $this->findOneById(14);

        $mailRefus = $this->generationMail($user, $mail, $options);

        if (!is_null($CMSI)) {
            $mailRefus->setCc([$CMSI->getEmail() => $CMSI->getNomPrenom()]);
        }

        return $mailRefus;
    }

    /**
     * Envoi un mail de recréation du mot de passe.
     *
     * @param User  $user    Utilisateur qui recevras l'email
     * @param array $options Variables à remplacer dans le template : 'nomDansLeTemplate' => valeurDeRemplacement
     *
     * @return \Swift_Message
     */
    public function sendResetPasswordMail($user, $options)
    {
        /** @var Mail $mail */
        $mail = $this->findOneById(26);

        return $this->generationMail($user, $mail, $options);
    }

    /**
     * Envoi un mail de notification de la requete.
     *
     * @param User  $user    Utilisateur qui recevras l'email
     * @param array $options Variables à remplacer dans le template : '%nomDansLeTemplate' => valeurDeRemplacement
     *
     * @return \Swift_Message
     */
    public function sendNotificationRequete($user, $options)
    {
        /** @var Mail $mail */
        $mail = $this->findOneById(29);

        return $this->generationMail($user, $mail, $options);
    }

    /**
     * Envoi un mail d'acceptation de l'inscription à une session d'un module.
     *
     * @param User  $user    Utilisateur qui recevras l'email
     * @param array $options Variables à remplacer dans le template : '%nomDansLeTemplate' => valeurDeRemplacement
     *
     * @return \Swift_Message
     */
    public function sendAcceptationInscriptionMail($user, $options)
    {
        /** @var Mail $mail */
        $mail = $this->findOneById(31);

        return $this->generationMail($user, $mail, $options);
    }

    /**
     * Envoi un mail de refus de l'inscription à une session d'un module.
     *
     * @param User  $user    Utilisateur qui recevras l'email
     * @param array $options Variables à remplacer dans le template : '%nomDansLeTemplate' => valeurDeRemplacement
     *
     * @return \Swift_Message
     */
    public function sendRefusInscriptionMail($user, $options)
    {
        /** @var Mail $mail */
        $mail = $this->findOneById(32);

        return $this->generationMail($user, $mail, $options);
    }

    /**
     * Envoi un mail d'acceptation de l'inscription à une session d'un module.
     *
     * @param Inscription[] $inscriptions
     * @param array $options Variables à remplacer dans le template : '%nomDansLeTemplate' => valeurDeRemplacement
     *
     * @return \Swift_Message[]
     */
    public function sendAcceptationInscriptionMassMail($inscriptions, $options)
    {
        $mail = $this->findOneById(31);

        $toSend = [];
        foreach ($inscriptions as $key => $inscription) {
            if ($inscription->getSession()->getModule()->getMailConfirmationInscription()) {
                $toSend[] = $this->generationMail($inscription->getUser(), $mail, [
                    'date' => $inscription->getSession()->getDateSession()->format('d/m/Y'),
                    'module' => $inscription->getSession()->getModule()->getTitre(),
                ]);
            }
        }

        return $toSend;
    }

    /**
     * Envoi un mail de refus de l'inscription à une session d'un module.
     *
     * @param Inscription[] $inscriptions
     * @param array $options Variables à remplacer dans le template : '%nomDansLeTemplate' => valeurDeRemplacement
     *
     * @return \Swift_Message[]
     */
    public function sendRefusInscriptionMassMail($inscriptions, $options)
    {
        /** @var Mail $mail */
        $mail = $this->findOneById(32);

        $toSend = [];
        foreach ($inscriptions as $key => $inscription) {
            if ($inscription->getSession()->getModule()->getMailRefusInscription()) {
                $toSend[] = $this->generationMail($inscription->getUser(), $mail, [
                    'date' => $inscription->getSession()->getDateSession()->format('d/m/Y'),
                    'module' => $inscription->getSession()->getModule()->getTitre(),
                    'textRefus' => $options['textRefus'],
                ]);
            }
        }

        return $toSend;
    }

    /**
     * Envoi un mail d'acceptation de l'inscription à une session d'un module.
     *
     * @param Inscription[] $inscriptions
     * @param array         $options
     *
     * @return array
     */
    public function sendRappelInscriptionMail($inscriptions, $options)
    {
        /** @var Mail $mail */
        $mail = $this->findOneById(35);

        $toSend = [];
        foreach ($inscriptions as $key => $inscription) {
            if ($inscription->getSession()->getModule()->getMailRappelEvalution()) {
                $mailTemp = $this->generationMail($inscription->getUser(), $mail, [
                    'date' => $inscription->getSession()->getDateSession()->format('d/m/Y'),
                    'module' => $inscription->getSession()->getModule()->getTitre(),
                    'texteMail' => $inscription->getSession()->getTextMailRappel(),
                ]);

                if (!is_null($inscription->getSession()->getAbsolutePath())
                    && trim($inscription->getSession()->getAbsolutePath()) !== ''
                ) {
                    $mailTemp->attach(
                        \Swift_Attachment::fromPath($inscription->getSession()->getAbsolutePath()),
                        'application/octet-stream'
                    );
                } elseif (!is_null($inscription->getSession()->getModule()->getAbsolutePath())
                          && trim($inscription->getSession()->getModule()->getAbsolutePath()) !== ''
                ) {
                    $mailTemp->attach(
                        \Swift_Attachment::fromPath($inscription->getSession()->getModule()->getAbsolutePath()),
                        'application/octet-stream'
                    );
                }

                $toSend[] = $mailTemp;
            }
        }

        return $toSend;
    }

    /**
     * Envoie un mail pour acceder au formulaire d'évaluation à une session d'un module.
     *
     * @param Inscription[] $inscriptions
     * @param array $options Variables à remplacer dans le template : '%nomDansLeTemplate' => valeurDeRemplacement
     *
     * @return array
     */
    public function sendFormulaireEvaluationsMassMail($inscriptions, $options)
    {
        /** @var Mail $mail */
        $mail = $this->findOneById(33);

        $toSend = [];

        // Récupération du domaine de la session
        $domaines = $inscriptions[0]->getSession()->getModule()->getDomaines();
        $domaine = $domaines[0];

        foreach ($inscriptions as $key => $inscription) {
            if ($inscription->getSession()->getModule()->getMailAlerteEvaluation()) {
                $mailGenere = $this->generationMail($inscription->getUser(), $mail, [
                    'date' => $inscription->getSession()->getDateSession()->format('d/m/Y'),
                    'module' => $inscription->getSession()->getModule()->getTitre(),
                    'url' => '<a href="' . $this->_requestStack->getCurrentRequest()->getUriForPath($this->_router->generate('hopitalnumerique_module_evaluation_form_front', [
                            'id' => $inscription->getSession()->getId(),
                        ])) . '" target="_blank" >' . $domaine->getNom() . '</a>',
                ]);
                $mailGenere->setFrom($domaine->getAdresseMailContact());
                $objet = str_replace('%subjectDomaine', $domaine->getNom(), $mail->getObjet());
                $mailGenere->SetSubject($objet);
                $toSend[] = $mailGenere;
            }
        }

        return $toSend;
    }

    public function sendNextSessionsNotification(User $user, $options)
    {
        $this->sendNotification($user, $options, Mail::MAIL_SUGGESTION_ANAP_NEXT_SESSIONS);
    }

    /**
     * Envoie un courriel concernant les demandes d'intervention.
     *
     * @param Mail       $mail         Le courriel à envoyer
     * @param User|array $destinataire Le destinataire du message
     * @param array      $options      Les paramètres à remplacer
     *
     * @return \Swift_Message Le message prêt à être envoyé
     */
    public function sendInterventionMail(Mail $mail, $destinataire, $options)
    {
        return $this->generationMail($destinataire, $mail, $options);
    }

    /**
     * [sendInscriptionSession description].
     *
     * @param [type] $user    [description]
     * @param [type] $options [description]
     *
     * @return [type]
     */
    public function sendInscriptionSession($user, $options)
    {
        /** @var Mail $mail */
        $mail = $this->findOneById(34);

        return $this->generationMail($user, $mail, $options);
    }

    /**
     * Envoie un mail pour notifier le changement de domaine.
     *
     * @param User  $user    Utilisateur qui recevras l'email
     * @param array $options Variables à remplacer dans le template : 'nomDansLeTemplate' => valeurDeRemplacement
     *
     * @return \Swift_Message
     */
    public function sendDomaineChanged($user, $options)
    {
        /** @var Mail $mail */
        $mail = $this->findOneById(41);

        return $this->generationMail($user, $mail, $options);
    }

    public function sendForumTopicCreated(User $user, $options)
    {
        /** @var Mail $mail */
        $mail = $this->findOneById(Mail::MAIL_FORUM_TOPIC_CREATED);

        $mail->setBody($this->replaceContent(
            $mail->getBody(),
            null,
            array_merge(
                $options,
                [
                    'prenomUtilisateur' => $user->getFirstname(),
                    'urlMessage' => $this->_router->generate('hopitalnumerique_forum_reference_topic', [
                        'id' => $options['topicId'],
                    ])
                ]
            )
        ));

        $mailsToSend = $this->generationMail($user, $mail);
        $mailsToSend->setTo($user->getEmail());

        $this->mailer->send($mailsToSend);
    }

    /**
     * @param User $user
     * @param array $options
     */
    public function sendForumPostCreatedNotification(User $user, $options)
    {
        $options['urlMessage'] = $this->_router->generate('hopitalnumerique_forum_reference_topic', [
            'id' => $options['id'],
        ]);
        $this->sendNotification($user, $options, Mail::MAIL_FORUM_POST_CREATED);
    }

    /**
     * @param $options
     * @param $topicId
     *
     * @return \Swift_Message
     */
    public function sendNouveauMessageForumAttenteModerationMail($options, $topicId)
    {
        /** @var Mail $mail */
        $mail = $this->findOneById(51);

        //Création du lien dans le mail
        $options['lienversmessage'] = '<a href="' . $this->_requestStack->getCurrentRequest()->getUriForPath($this->_router->generate('ccdn_forum_user_topic_show', [
                'forumName' => $options['forum'],
                'topicId' => $topicId,
            ])) . '" target="_blank" >%s</a>';

        $options['lienversmessage'] = sprintf(
            $options['lienversmessage'],
            $this->truncatePostBody($options['shortMessage'])
        );

        $domaine = $this->_domaineManager->findOneById($this->_session->get('domaineId'));
        $destinataire = $domaine->getAdresseMailContact();
        $options = $this->getAllOptions($options);

        $mailExpediteur = $this->replaceContent($mail->getExpediteurMail(), null, $options);
        $nameExpediteur = $this->replaceContent($mail->getExpediteurName(), null, $options);

        //prepare content
        $content = $this->replaceContent($mail->getBody(), null, $options);
        $from = [$mailExpediteur => $nameExpediteur];
        $cci = $this->_expediteurEnCopie ? array_merge($this->getMailDomaine(), $from) : $this->getMailDomaine();
        $cci = ($mail->getId() === 1 || $mail->getId() === 2) ? false : $cci;
        $subject = $this->replaceContent($mail->getObjet(), null, $options);

        $mailToSend = $this->sendMail($subject, $from, [$destinataire], $content, $this->getMailDomaine());

        return $mailToSend;
    }

    /**
     * [sendInscriptionSession description].
     *
     * @param [type] $users   [description]
     * @param [type] $options [description]
     *
     * @return \Swift_Message[]
     */
    public function sendNouveauRapportDeBugMail($users, $options)
    {
        //Création du lien dans le mail
        $mail = $this->findOneById(40);

        //tableau de SwiftMessage a envoyé
        $mailsToSend = [];

        foreach ($users as $recepteurMail) {
            $recepteurName = $recepteurMail;

            $options['nomdestinataire'] = $recepteurName;
            $options['maildestinataire'] = $recepteurMail;
            $options['u'] = $recepteurName;
            $options = $this->getAllOptions($options);

            //prepare content
            $content = $this->replaceContent($mail->getBody(), null, $options);
            $expediteurMail = $this->replaceContent($mail->getExpediteurMail(), null, $options);
            $expediteurName = $this->replaceContent($mail->getExpediteurName(), null, $options);
            $content = $this->replaceContent($mail->getBody(), null, $options);
            $from = [$expediteurMail => $expediteurName];
            $subject = $this->replaceContent($mail->getObjet(), null, $options);

            $mailsToSend[] = $this->sendMail(
                $subject,
                $from,
                [$recepteurMail => $recepteurName],
                $content,
                $this->getMailDomaine()
            );
        }

        return $mailsToSend;
    }

    /**
     * Envoie un mail de contact (différent des autres envois de mail).
     *
     * @param array $users    Utilisateurs qui recevront l'email (tableau configuré en config.yml)
     * @param array $options Variables à remplacer dans le template : '%nomDansLeTemplate' => valeurDeRemplacement
     *
     * @return \Swift_Message[]
     */
    public function sendContactMail($users, $options)
    {
        $mail = $this->findOneById(30);

        //tableau de SwiftMessage a envoyé
        $mailsToSend = [];

        foreach ($users as $recepteurMail => $recepteurName) {
            $options['nomdestinataire'] = $recepteurName;
            $options['maildestinataire'] = $recepteurMail;
            $options = $this->getAllOptions($options);

            //prepare content
            $content = $this->replaceContent($mail->getBody(), null, $options);
            $expediteurMail = $this->replaceContent($mail->getExpediteurMail(), null, $options);
            $expediteurName = $this->replaceContent($mail->getExpediteurName(), null, $options);
            $content = $this->replaceContent($mail->getBody(), null, $options);
            $from = [$expediteurMail => $expediteurName];
            $subject = $this->replaceContent($mail->getObjet(), null, $options);

            $mailsToSend[] = $this->sendMail(
                $subject,
                $from,
                [$recepteurMail => $recepteurName],
                $content,
                $this->getMailDomaine()
            );
        }

        return $mailsToSend;
    }

    /**
     * @return Mail|object
     */
    public function getGuidedSearchSynthesisMail()
    {
        return $this->findOneById(74);
    }

    /**
     * @param string $subject
     * @param string $sender
     * @param string $recipient
     * @param string $content
     * @param string $filepath
     *
     * @return \Swift_Message
     */
    public function sendGuidedSearchSynthesis($subject, $sender, $recipient, $content, $filepath)
    {
        $mail = $this->sendMail($subject, $sender, $recipient, $content);
        $mail->attach(\Swift_Attachment::fromPath($filepath));

        return $mail;
    }

    /**
     * @param User $user
     * @param $options
     */
    public function sendGuidedSearchNotification(User $user, $options)
    {
        $options['urlParcoursGuides'] = $this->_router->generate('account_service');
        $this->sendNotification($user, $options, Mail::MAIL_GUIDED_SEARCH_NOTIF);
    }

    /**
     * @return object
     */
    public function getCartReportMail()
    {
        return $this->findOneById(73);
    }

    /**
     * @param string $subject
     * @param string $sender
     * @param string $recipient
     * @param string $content
     * @param string $filepath
     *
     * @return \Swift_Message
     */
    public function sendCartReport($subject, $sender, $recipient, $content, $filepath)
    {
        $mail = $this->sendMail($subject, $sender, $recipient, $content);
        $mail->attach(\Swift_Attachment::fromPath($filepath));

        return $mail;
    }

    /**
     * @param User $user
     * @param $options
     */
    public function sendReportSharedForMe(User $user, $options)
    {
        $mailsToSend = $this->buildReportMail($user, $options, Mail::MAIL_REPORT_SHARED_FOR_ME);
        $this->mailer->send($mailsToSend);
    }

    /**
     * @param User $user
     * @param $options
     */
    public function sendReportSharedForOther(User $user, $options)
    {
        $mailsToSend = $this->buildReportMail($user, $options, Mail::MAIL_REPORT_SHARED_FOR_OTHER);
        $this->mailer->send($mailsToSend);
    }

    /**
     * @param User $user
     * @param $options
     */
    public function sendReportCopiedForMe(User $user, $options)
    {
        $mailsToSend = $this->buildReportMail($user, $options, Mail::MAIL_REPORT_COPIED_FOR_ME);
        $this->mailer->send($mailsToSend);
    }

    /**
     * @param User $user
     * @param $options
     * @param $mailId
     *
     * @return \Swift_Message
     */
    public function buildReportMail(User $user, $options, $mailId)
    {
        /** @var Mail $mail */
        $mail = $this->findOneById($mailId);

        $mail->setBody($this->replaceContent(
            $mail->getBody(),
            null,
            array_merge(
                $options,
                [
                    'urlMonPanier' => $this->_router->generate('account_cart', [], RouterInterface::ABSOLUTE_URL),
                    'prenomUtilisateur' => $user->getFirstname(),
                ]
            )
        ));
        $mailsToSend = $this->generationMail($user, $mail);
        $mailsToSend->setTo($user->getEmail());

        return $mailsToSend;
    }

    /**
     * Envoie un mail des réponses+questions d'un questionnaire rempli par un utilisateur
     * (différent des autres envois de mail).
     *
     * @param array $users    Utilisateurs qui recevront l'email (tableau configuré en config.yml/parameters.yml)
     * @param array $options Variables à remplacer dans le template : '%nomDansLeTemplate' => valeurDeRemplacement
     *
     * @return \Swift_Message[]
     */
    public function sendReponsesQuestionnairesMail($users, $options)
    {
        /** @var Mail $mail */
        $mail = $this->findOneById(37);

        //tableau de SwiftMessage a envoyé
        $mailsToSend = [];

        foreach ($users as $recepteurMail => $recepteurName) {
            $options['u'] = $recepteurName;
            $options = $this->getAllOptions($options);

            //prepare content
            $content = $this->replaceContent($mail->getBody(), null, $options);
            $expediteurMail = $this->replaceContent($mail->getExpediteurMail(), null, $options);
            $expediteurName = $this->replaceContent($mail->getExpediteurName(), null, $options);
            $content = $this->replaceContent($mail->getBody(), null, $options);
            $from = [$expediteurMail => $expediteurName];
            $subject = $this->replaceContent($mail->getObjet(), null, $options);

            $mailsToSend[] = $this->sendMail(
                $subject,
                $from,
                [$recepteurMail => $recepteurName],
                $content,
                $this->getMailDomaine()
            );
        }

        return $mailsToSend;
    }

    /**
     * Retourne un email de test.
     *
     * @param int  $id   ID du mail
     * @param User $user Utilisateur destinataire
     *
     * @return \Swift_Message
     */
    public function sendMessageTest($id, $user)
    {
        /** @var Mail $mail */
        $mail = $this->findOneById($id);

        // on remplace les champs par les valeurs récupérées si les champs sont vides
        $this->_mailExpediteur = ($this->_mailExpediteur == '') ? $mail->getExpediteurMail() : $this->_mailExpediteur;
        $this->_nomExpediteur = ($this->_nomExpediteur == '') ? $mail->getExpediteurName() : $this->_nomExpediteur;
        $this->_destinataire = ($this->_destinataire == '') ? $user->getEmail() : $this->_destinataire;

        $options = $this->getAllOptions([$this->_mailExpediteur => $this->_nomExpediteur]);

        $expediteurMail = $this->replaceContent($this->_mailExpediteur, null, $options);
        $expediteurName = $this->replaceContent($this->_nomExpediteur, null, $options);

        $from = [$expediteurMail => $expediteurName];

        //return test email
        return $this->sendMail($mail->getObjet(), $from, $this->_destinataire, $mail->getBody());
    }

    /**
     * Envoie le courriel avec le modèle de contrat.
     *
     * @param ActiviteExpert $activiteExpert
     * @param string         $destinataireAdresseElectronique Adresse du destinataire
     */
    public function sendExpertActiviteContratMail(ActiviteExpert $activiteExpert, $destinataireAdresseElectronique)
    {
        /** @var Mail $courriel */
        $courriel = $this->findOneById(60);
        $contratModele = $this->referenceManager->findOneByCode('ACTIVITE_EXPERT_CONTRAT_MODELE');

        $csvFile = \Swift_Attachment::fromPath($this->activiteExpertManager->getContratCsv($activiteExpert));
        $csvFile->setFilename('activite.csv');
        $message =
            $this->generationMail(null, $courriel)
                ->setTo($destinataireAdresseElectronique)
                ->attach(\Swift_Attachment::fromPath('medias' . DIRECTORY_SEPARATOR . 'ActiviteExperts' . DIRECTORY_SEPARATOR . $contratModele->getLibelle()))
                ->attach($csvFile)
        ;

        $this->mailer->send($message);

        if (null !== $this->user) {
            $courrielRegistre = $this->courrielRegistreManager->createEmpty();
            $courrielRegistre->setDestinataire($destinataireAdresseElectronique);
            $courrielRegistre->setUser($this->user);
            $courrielRegistre->setType(CourrielRegistre::TYPE_CONTRAT);
            $this->courrielRegistreManager->save($courrielRegistre);
        }
    }

    /**
     * Envoie le courriel avec le modèle de paiement.
     *
     * @param ActiviteExpert $activiteExpert
     * @param string         $destinataireAdresseElectronique Adresse du destinataire
     */
    public function sendExpertActivitePaimentMail(ActiviteExpert $activiteExpert, $destinataireAdresseElectronique)
    {
        /** @var Mail $courriel */
        $courriel = $this->findOneById(61);
        $paiementModele = $this->referenceManager->findOneByCode('ACTIVITE_EXPERT_PV_RECETTES_MODELE');

        $csvFile = \Swift_Attachment::fromPath($this->activiteExpertManager->getContratCsv($activiteExpert));
        $csvFile->setFilename('activite.csv');
        $message =
            $this->generationMail(null, $courriel)
                ->setTo($destinataireAdresseElectronique)
                ->attach(\Swift_Attachment::fromPath('medias' . DIRECTORY_SEPARATOR . 'ActiviteExperts' . DIRECTORY_SEPARATOR . $paiementModele->getLibelle()))
                ->attach($csvFile)
        ;

        $this->mailer->send($message);

        if (null !== $this->user) {
            $courrielRegistre = $this->courrielRegistreManager->createEmpty();
            $courrielRegistre->setDestinataire($destinataireAdresseElectronique);
            $courrielRegistre->setUser($this->user);
            $courrielRegistre->setType(CourrielRegistre::TYPE_PAIEMENT);
            $this->courrielRegistreManager->save($courrielRegistre);
        }
    }

    /**
     * @param User $expediteur
     * @param      $destinataires
     * @param      $nomGroupe
     */
    public function sendInvitationMail(User $expediteur, $destinataires, $nomGroupe)
    {
        /** @var Mail $courriel */
        $courriel = $this->findOneById(67);

        $message = $this->generationMail(null, $courriel, [
            'nomGroupe' => $nomGroupe,
            'u' => $expediteur->getNomPrenom(),
        ]);

        foreach ($destinataires as $destinataire) {
            $message->setFrom([$expediteur->getEmail() => $expediteur->getNom()]);
            $message->setTo($destinataire);
            $this->mailer->send($message);
        }
    }

    /**
     * @param        $destinataires
     * @param User   $user
     * @param Groupe $groupe
     * @param        $urlGroupe
     */
    public function sendAlerteInscriptionMail($destinataires, User $user, Groupe $groupe, $urlGroupe)
    {
        /** @var Mail $courriel */
        $courriel = $this->findOneById(65);

        $message = $this->generationMail(null, $courriel, [
            'g' => $groupe->getTitre(),
            'u' => $user->getNomPrenom(),
            'lienGroupe' => $urlGroupe,
        ]);

        foreach ($destinataires as $destinataire) {
            $message->setTo($destinataire);
            $this->mailer->send($message);
        }
    }

    /**
     * @param $destinataire
     * @param $nomGroupe
     * @param $urlGroupe
     */
    public function sendAlerteInscriptionValideMail($destinataire, $nomGroupe, $urlGroupe)
    {
        $courriel = $this->findOneById(64);

        $message = $this->generationMail(null, $courriel, [
            'nomGroupe' => $nomGroupe,
            'urlGroupe' => $urlGroupe,
        ]);

        $message->setTo($destinataire);

        $this->mailer->send($message);
    }

    /**
     * @param $expediteur
     * @param $destinataire
     * @param $objet
     * @param $message
     * @param $pdf
     */
    public function sendAutodiagResultMail($expediteur, $destinataire, $objet, $message, $pdf)
    {
        $email = $this->sendMail($objet, $expediteur, $destinataire, $message);
        $email->attach(\Swift_Attachment::newInstance($pdf, 'resultat.pdf'));

        $this->mailer->send($email);
    }

    /**
     * @param User $user
     * @param $options
     */
    public function sendAutodiagUpdateNotification(User $user, $options)
    {
        $options['urlAutodiagnostics'] = $this->_router->generate('hopitalnumerique_autodiag_entry_add', [
            'autodiag' => $options['autodiagId'],
        ]);
        $this->sendNotification($user, $options, Mail::MAIL_AUTODIAG_UPDATE);
    }

    /**
     * @param Objet $objet
     * @param $url
     */
    public function sendAlertePublicationCommentaireMail(Objet $objet, $url)
    {
        $mail = $this->findOneById(Mail::MAIL_PUBLICATION_COMMENTED);

        /** @var Domaine $domain */
        foreach ($objet->getDomaines() as $domain) {
            /** @var Mail $mail */
            $currentMail = clone $mail;

            $content = $this->replaceContent(
                $currentMail->getBody(),
                null,
                [
                    'titrePublication' => $objet->getTitre(),
                    'urlPublication' => $domain->getUrl() . $url,
                    'commentaire' => $objet->getListeCommentaires()->last()->getTexte(),
                    'prenomUtilisateur' => '',
                ]
            );

            $currentMail->setBody($content);
            $mailToSend = $this->generationMail(null, $currentMail);
            $mailToSend->setTo($domain->getAdresseMailContact());

            $this->mailer->send($mailToSend);
        }
    }

    /**
     * @param User $user
     * @param $options
     */
    public function sendPublicationCommentNotification(User $user, $options)
    {
        $options['urlPublication'] = $this->_router->generate('hopital_numerique_publication_publication_objet', [
            'id' => $options['idPublication'],
        ]);
        $this->sendNotification($user, $options, Mail::MAIL_PUBLICATION_COMMENTED);
    }

    /**
     * @param User $user
     * @param array $options
     */
    public function sendPublicationNotifiedNotification(User $user, $options)
    {
        $options['urlPublication'] = $this->_router->generate('hopital_numerique_publication_publication_objet', [
            'id' => $options['idPublication'],
        ]);
        $this->sendNotification($user, $options, Mail::MAIL_PUBLICATION_NOTIFIED);
    }

    /**
     * Envoi un e-mail de notification lors du dépôt de commentaire sur la communauté de pratique.
     *
     * @param Commentaire $commentaire
     *
     * @return bool
     */
    public function sendCMCommentaireMail(Commentaire $commentaire)
    {
        $group = $commentaire->getGroupe();
        // Commentaire groupe
        if (null !== $group) {
            /** @var Groupe $group */
            $courriel = $this->findOneById(Mail::MAIL_CM_COMMENTAIRE_GROUPE);
            $parameters = [
                'nomGroupe' => $group->getTitre(),
                'urlGroupe' => $this->_router->generate('hopitalnumerique_communautepratique_groupe_view', [
                    'groupe' => $group->getId(),
                ], RouterInterface::ABSOLUTE_URL),
            ];
            // Commentaire fiche
        } elseif (null !== $fiche = $commentaire->getFiche()) {
            /** @var Fiche $fiche */
            $courriel = $this->findOneById(Mail::MAIL_CM_COMMENTAIRE_FICHE);
            $group = $commentaire->getFiche()->getGroupe();
            $parameters = [
                'nomFiche' => $fiche->getQuestionPosee(),
                'urlFiche' => $this->_router->generate('hopitalnumerique_communautepratique_fiche_view', [
                    'fiche' => $fiche->getId(),
                ], RouterInterface::ABSOLUTE_URL),
            ];
        } else {
            return false;
        }

        foreach ($group->getUsers() as $recipient) {
            $concerned = $recipient->isActifInGroupe($group)
                || $group->hasAnimateur($recipient)
                || $recipient->hasRoleAdmin()
                || $recipient->hasRoleAdminHn()
            ;

            if (!$concerned) {
                continue;
            }

            /** @var Mail $currentCourriel */
            $currentCourriel = clone $courriel;

            $content = $this->replaceContent(
                $currentCourriel->getBody(),
                null,
                array_merge($parameters, [
                    'nomUtilisateur' => $commentaire->getUser()->getLastname(),
                    'prenomUtilisateur' => $commentaire->getUser()->getFirstname(),
                ])
            );

            $currentCourriel->setBody($content);
            $message = $this->generationMail(null, $currentCourriel);
            $message->setTo($recipient->getEmail());

            $this->mailer->send($message);
        }
    }

    /**
     * @param User $user
     * @param $options
     */
    public function sendCdpGroupCommentNotification(User $user, $options)
    {
        $options['urlGroupe'] = $this->_router->generate('hopitalnumerique_communautepratique_groupe_view', [
            'groupe' => $options['groupId'],
        ]);
        $this->sendNotification($user, $options, Mail::MAIL_CDP_GROUP_COMMENT);
    }

    /**
     * @param User $user
     * @param $options
     */
    public function sendCdpGroupDocumentNotification(User $user, $options)
    {
        $options['urlGroupe'] = $this->_router->generate('hopitalnumerique_communautepratique_groupe_view', [
            'groupe' => $options['groupId'],
        ]);
        $this->sendNotification($user, $options, Mail::MAIL_CDP_GROUP_DOCUMENT);
    }

    /**
     * @param User $user
     * @param $options
     */
    public function sendCdpGroupCreatedNotification(User $user, $options)
    {
        $options['urlCommunaute'] = $this->_router->generate('hopitalnumerique_communautepratique_groupe_view', [
            'groupe' => $options['groupId'],
        ]);
        $this->sendNotification($user, $options, Mail::MAIL_CDP_GROUP_USER_JOINED);
    }

    /**
     * @param User $user
     * @param $options
     */
    public function sendCdpGroupUserJoinedNotification(User $user, $options)
    {
        $options['urlGroupe'] = $this->_router->generate('hopitalnumerique_communautepratique_groupe_view', [
            'groupe' => $options['groupId'],
        ]);
        $this->sendNotification($user, $options, Mail::MAIL_CDP_GROUP_USER_JOINED);
    }

    /**
     * @param User $user
     * @param $options
     */
    public function sendCdpUserJoinedNotification(User $user, $options)
    {
        $this->sendNotification($user, $options, Mail::MAIL_CDP_USER_JOINED);
    }

    /**
     * @param User $user
     * @param $options
     */
    public function sendCdpFormCommentNotification(User $user, $options)
    {
        $options['urlFiche'] = $this->_router->generate('hopitalnumerique_communautepratique_fiche_view', [
            'fiche' => $options['ficheId'],
        ]);
        $this->sendNotification($user, $options, Mail::MAIL_CM_COMMENTAIRE_FICHE);
    }

    /**
     * @param $sender
     * @param $recipient
     * @param $subject
     * @param $body
     * @param $excelFile
     * @param $csvFile
     */
    public function sendGuidedSearchAnalyzes($sender, $recipient, $subject, $body, $excelFile, $csvFile)
    {
        $message = $this->sendMail($subject, $sender, $recipient, $body);

        $message->attach(\Swift_Attachment::newInstance($excelFile, 'export.xlsx'));
        $message->attach(\Swift_Attachment::newInstance($csvFile, 'export.csv'));

        $this->mailer->send($message);
    }

    /**
     * @param $expediteur
     * @param $destinataire
     * @param $objet
     * @param $message
     */
    public function sendSearch($expediteur, $destinataire, $objet, $message)
    {
        $email = $this->sendMail($objet, $expediteur, $destinataire, $message);

        $this->mailer->send($email);
    }

    /**
     * @param Note $note
     */
    public function sendNoteCommentaire(Note $note)
    {
        /** @var Mail $mail */
        $mail = $this->findOneById(Mail::MAIL_NOTED_COMMENT);
        $options = [
            'urlDocument' => $this->_router->generate('hopital_numerique_publication_publication_objet', [
                'id' => $note->getObjet()->getId(),
            ], RouterInterface::ABSOLUTE_URL),
            'note' => $note->getNote(),
            'comment' => $note->getComment(),
            'nomUtilisateur' => $note->getUser()->getLastname(),
            'prenomUtilisateur' => $note->getUser()->getFirstname(),
            'subjectdomain' => $this->getDomaineSubjet(),
        ];

        $expediteurMail = $this->replaceContent($mail->getExpediteurMail(), null, $options);
        $expediteurName = $this->replaceContent($mail->getExpediteurName(), null, $options);
        $content = $this->replaceContent($mail->getBody(), null, $options);
        $from = [$expediteurMail => $expediteurName];

        $mailsToSend = $this->sendMail(
            $mail->getObjet(),
            $from,
            $this->getMailDomaine(),
            $content
        );

        $this->mailer->send($mailsToSend);
    }

    /**
     * @param User $user
     * @param $options
     * @param $mailId
     */
    public function sendNotification(User $user, $options, $mailId)
    {
        /** @var Mail $mail */
        $mail = $this->findOneById($mailId);
        $cloneMail = clone $mail;

        $options['prenomUtilisateur'] = $user->getFirstname();

        $mailsToSend = $this->generationMail($user, $cloneMail, $options);
        $mailsToSend->setTo($user->getEmail());

        $this->mailer->send($mailsToSend);
    }

    /**
     * @param Notification[] $groupedNotifications
     */
    public function sendGroupedNotification($groupedNotifications)
    {
        array_multisort($groupedNotifications);
        $content = "";
        foreach ($groupedNotifications as $section) {
            foreach ($section as $notifications) {
                $provider = $this->notificationService->getProvider($notifications[0]->getNotificationCode());
                $content .= $this->_twig->render($provider->getTemplatePath(), ['notifications' => $notifications]);
            }
        }
        dump($content);
        die;
    }
}
