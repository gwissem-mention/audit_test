<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * @ORM\Entity(repositoryClass="HopitalNumerique\CommunautePratiqueBundle\Repository\Discussion\ReadRepository")
 * @ORM\Table(name="hn_communautepratique_discussion_read")
 */
class Read
{
    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\UserBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="usr_id")
     */
    protected $user;

    /**
     * @var Discussion
     *
     * @ORM\ManyToOne(targetEntity="Discussion", inversedBy="readings")
     */
    protected $discussion;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $lastMessageDate;


    /**
     * Read constructor.
     *
     * @param User $user
     * @param Discussion $discussion
     * @param \DateTime $lastMessageDate
     */
    public function __construct(User $user, Discussion $discussion, \DateTime $lastMessageDate)
    {
        $this-> user = $user;
        $this-> discussion = $discussion;
        $this-> lastMessageDate = $lastMessageDate;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return User|null
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return Discussion
     */
    public function getDiscussion()
    {
        return $this->discussion;
    }

    /**
     * @return \DateTime
     */
    public function getLastMessageDate()
    {
        return $this->lastMessageDate;
    }

    /**
     * @param \DateTime $lastMessageDate
     *
     * @return Read
     */
    public function setLastMessageDate($lastMessageDate)
    {
        $this->lastMessageDate = $lastMessageDate;

        return $this;
    }
}
