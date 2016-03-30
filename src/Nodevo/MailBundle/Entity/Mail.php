<?php

namespace Nodevo\MailBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

//Asserts Stuff
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Nodevo\ToolsBundle\Validator\Constraints as Nodevo;

/**
 * Mail
 *
 * @ORM\Table(name="core_mail")
 * @ORM\Entity(repositoryClass="Nodevo\MailBundle\Repository\MailRepository")
 * @UniqueEntity(fields="objet", message="Un mail avec cet objet existe déjà.")
 */
class Mail
{
    /**
     * @var integer ID du courriel de recommandation à un ami
     */
    const MAIL_RECOMMANDATION_AMI_ID = 63;


    /**
     * @var integer
     *
     * @ORM\Column(name="mail_id", type="integer", options = {"comment" = "ID du mail"})
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
     * @ORM\Column(name="mail_objet", type="string", length=255, options = {"comment" = "Objet du mail"})
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
     * @ORM\Column(name="mail_description", type="string", length=255, options = {"comment" = "Description du mail"})
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
     * @ORM\Column(name="mail_expediteur_mail", type="string", length=255, options = {"comment" = "Adresse mail de l expéditeur"})
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
     * @ORM\Column(name="mail_expediteur_name", type="string", length=255, options = {"comment" = "Nom de l expéditeur"})
     */
    private $expediteurName;

    /**
     * @var string
     * @Assert\NotBlank(message="Le contenu du mail ne peut pas être vide.")
     * @Nodevo\Javascript(class="validate[required]")
     * @ORM\Column(name="mail_body", type="text", options = {"comment" = "Contenu du mail"})
     */
    private $body;

    /**
     * @var boolean
     *
     * @ORM\Column(name="mail_notification_region_referent", type="boolean", nullable=false, options={"default"=false,"comment"="Indique si le référent de région est notifié"})
     */
    private $notificationRegionReferent;

    /**
     * @var array
     *
     * @ORM\Column(name="mail_params", type="json_array")
     */
    private $params;

    public function __construct()
    {
        $this->params = array();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set objet
     *
     * @param string $objet
     * @return Mail
     */
    public function setObjet($objet)
    {
        $this->objet = $objet;

        return $this;
    }

    /**
     * Get objet
     *
     * @return string 
     */
    public function getObjet()
    {
        return $this->objet;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Mail
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set expediteurMail
     *
     * @param string $expediteurMail
     * @return Mail
     */
    public function setExpediteurMail($expediteurMail)
    {
        $this->expediteurMail = $expediteurMail;

        return $this;
    }

    /**
     * Get expediteurMail
     *
     * @return string 
     */
    public function getExpediteurMail()
    {
        return $this->expediteurMail;
    }

    /**
     * Set expediteurName
     *
     * @param string $expediteurName
     * @return Mail
     */
    public function setExpediteurName($expediteurName)
    {
        $this->expediteurName = $expediteurName;

        return $this;
    }

    /**
     * Get expediteurName
     *
     * @return string 
     */
    public function getExpediteurName()
    {
        return $this->expediteurName;
    }

    /**
     * Set body
     *
     * @param string $body
     * @return Mail
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body
     *
     * @return string 
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Get params
     *
     * @return array $params
     */
    public function getParams()
    {
        return $this->params;
    }
    
    /**
     * Set params
     *
     * @param array $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * Set notificationRegionReferent
     *
     * @param boolean $notificationRegionReferent
     *
     * @return Mail
     */
    public function setNotificationRegionReferent($notificationRegionReferent)
    {
        $this->notificationRegionReferent = $notificationRegionReferent;

        return $this;
    }

    /**
     * Get notificationRegionReferent
     *
     * @return boolean
     */
    public function isNotificationRegionReferent()
    {
        return $this->notificationRegionReferent;
    }
}
