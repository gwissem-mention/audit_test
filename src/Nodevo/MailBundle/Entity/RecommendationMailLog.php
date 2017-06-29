<?php

namespace Nodevo\MailBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * @ORM\Entity(repositoryClass="Nodevo\MailBundle\Repository\RecommendationMailLogRepository")
 * @ORM\Table(name="core_mail_recommendation_log")
 */
class RecommendationMailLog
{

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $recipientEmail;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $sendedAt;

    /**
     * @var User $sendedBy
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\UserBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="usr_id")
     */
    protected $sendedBy;

    /**
     * MailLog constructor.
     *
     * @param string $recipient
     * @param User $sendedBy
     */
    public function __construct($recipient, User $sendedBy)
    {
        $this->recipientEmail = $recipient;
        $this->sendedAt = new \DateTime();
        $this->sendedBy = $sendedBy;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getRecipientEmail()
    {
        return $this->recipientEmail;
    }

    /**
     * @return \DateTime
     */
    public function getSendedAt()
    {
        return $this->sendedAt;
    }

    /**
     * @return User
     */
    public function getSendedBy()
    {
        return $this->sendedBy;
    }

}
