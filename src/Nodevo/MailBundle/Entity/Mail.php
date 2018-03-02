<?php

namespace Nodevo\MailBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Nodevo\ToolsBundle\Validator\Constraints as Nodevo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Mail.
 *
 * @ORM\Table(name="core_mail")
 * @ORM\Entity(repositoryClass="Nodevo\MailBundle\Repository\MailRepository")
 * @UniqueEntity(fields="objet", message="Un mail avec cet objet existe déjà.")
 */
class Mail
{
    /** @var int ID du courriel de recommandation à un ami */
    const MAIL_RECOMMANDATION_AMI_ID = 63;

    /** @var int ID du courriel de partage des résultats d'autodiag */
    const MAIL_SHARE_AUTODIAG_RESULT_ID = 68;

    /** @var int ID du courriel de recommandation à un ami */
    const MAIL_RECOMMANDATION_TOPIC_ID = 70;

    /** ID e-mail de nouveau commentaire sur un groupe de la communauté de pratique */
    const MAIL_CM_COMMENTAIRE_GROUPE = 71;

    /** ID e-mail de nouveau commentaire sur une fiche de la communauté de pratique */
    const MAIL_CM_COMMENTAIRE_FICHE = 72;

    /** @var int ID du courriel de partage des analyses du parcours guidé */
    const MAIL_SHARE_GUIDED_SEARCH_ID = 74;

    /** @var int Share email ID */
    const SHARE_SEARCH_EMAIL = 76;

    /** @var int Mail ID of notation */
    const MAIL_NOTED_COMMENT = 77;

    /** @var int Mail id notif publication */
    const MAIL_PUBLICATION_NOTIFIED = 29;

    /** @var int Mail id notif on publication commented */
    const MAIL_PUBLICATION_COMMENTED = 69;

    /** @var int Mail id forum post created */
    const MAIL_FORUM_POST_CREATED = 36;

    /** @var int Mail id forum topic created */
    const MAIL_FORUM_TOPIC_CREATED = 81;

    /** @var int */
    const MAIL_AUTODIAG_UPDATE = 75;

    /** @var int Mail id for reports */
    const MAIL_REPORT_SHARED_FOR_ME = 78;

    /** @var int Mail id for reports */
    const MAIL_REPORT_SHARED_FOR_OTHER = 79;

    /** @var int Mail id for reports */
    const MAIL_REPORT_COPIED_FOR_ME = 80;

    const MAIL_REPORT_UPDATED = 89;

    const MAIL_USER_ROLE_UPDATED = 86;

    const MAIL_SUGGESTION_ANAP_NEXT_SESSIONS = 87;

    const MAIL_CDP_GROUP_DOCUMENT = 82;

    const MAIL_CDP_GROUP_CREATED = 84;

    const MAIL_CDP_GROUP_USER_JOINED = 83;

    const MAIL_CDP_USER_JOINED = 85;

    const MAIL_GUIDED_SEARCH_NOTIF = 88;

    const MAIL_GROUPED_NOTIFS = 90;

    const MAIL_CDP_NEW_DISCUSSION_IN_GROUP = 102;

    /**
     * @var int
     *
     * @ORM\Column(name="mail_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(message="L'objet ne peut pas être vide.")
     * @Assert\Length(
     *      min = "1",
     *      max = "255",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans l'objet.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans l'objet."
     * )
     * @Nodevo\Javascript(class="validate[required,minSize[1],maxSize[255]]")
     * @ORM\Column(name="mail_objet", type="string", length=255)
     */
    private $objet;

    /**
     * @var string
     * @Assert\NotBlank(message="La description ne peut pas être vide.")
     * @Assert\Length(
     *      min = "1",
     *      max = "255",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans la description.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans la description."
     * )
     * @Nodevo\Javascript(class="validate[required,minSize[1],maxSize[255]]")
     * @ORM\Column(name="mail_description", type="string", length=255)
     */
    private $description;

    /**
     * @var string
     * @Assert\NotBlank(message="Le mail de l'expéditeur ne peut pas être vide.")
     * @Assert\Length(
     *      min = "1",
     *      max = "255",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans le mail de l'expéditeur.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans le mail de l'expéditeur."
     * )
     * @Nodevo\Javascript(class="validate[required,minSize[1],maxSize[255]]")
     * @ORM\Column(name="mail_expediteur_mail", type="string", length=255)
     */
    private $expediteurMail;

    /**
     * @var string
     * @Assert\NotBlank(message="Le nom de l'expéditeur ne peut pas être vide.")
     * @Assert\Length(
     *      min = "1",
     *      max = "255",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans le nom de l'expéditeur.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans le nom de l'expéditeur."
     * )
     * @Nodevo\Javascript(class="validate[required,minSize[1],maxSize[255]]")
     * @ORM\Column(name="mail_expediteur_name", type="string", length=255)
     */
    private $expediteurName;

    /**
     * @var string
     * @Assert\NotBlank(message="Le contenu du mail ne peut pas être vide.")
     * @Nodevo\Javascript(class="validate[required]")
     * @ORM\Column(name="mail_body", type="text")
     */
    private $body;

    /**
     * @var bool
     *
     * @ORM\Column(name="mail_notification_region_referent", type="boolean", nullable=false, options={"default"=false})
     */
    private $notificationRegionReferent;

    /**
     * @var array
     *
     * @ORM\Column(name="mail_params", type="json_array")
     */
    private $params;

    /**
     * Mail constructor.
     */
    public function __construct()
    {
        $this->params = [];
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set objet.
     *
     * @param string $objet
     *
     * @return Mail
     */
    public function setObjet($objet)
    {
        $this->objet = $objet;

        return $this;
    }

    /**
     * Get objet.
     *
     * @return string
     */
    public function getObjet()
    {
        return $this->objet;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return Mail
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set expediteurMail.
     *
     * @param string $expediteurMail
     *
     * @return Mail
     */
    public function setExpediteurMail($expediteurMail)
    {
        $this->expediteurMail = $expediteurMail;

        return $this;
    }

    /**
     * Get expediteurMail.
     *
     * @return string
     */
    public function getExpediteurMail()
    {
        return $this->expediteurMail;
    }

    /**
     * Set expediteurName.
     *
     * @param string $expediteurName
     *
     * @return Mail
     */
    public function setExpediteurName($expediteurName)
    {
        $this->expediteurName = $expediteurName;

        return $this;
    }

    /**
     * Get expediteurName.
     *
     * @return string
     */
    public function getExpediteurName()
    {
        return $this->expediteurName;
    }

    /**
     * Set body.
     *
     * @param string $body
     *
     * @return Mail
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body.
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Get params.
     *
     * @return array $params
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Set params.
     *
     * @param array $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * Set notificationRegionReferent.
     *
     * @param bool $notificationRegionReferent
     *
     * @return Mail
     */
    public function setNotificationRegionReferent($notificationRegionReferent)
    {
        $this->notificationRegionReferent = $notificationRegionReferent;

        return $this;
    }

    /**
     * Get notificationRegionReferent.
     *
     * @return bool
     */
    public function isNotificationRegionReferent()
    {
        return $this->notificationRegionReferent;
    }
}
