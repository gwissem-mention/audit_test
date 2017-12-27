<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * @ORM\Entity(repositoryClass="HopitalNumerique\CommunautePratiqueBundle\Repository\Discussion\ViewedRepository")
 * @ORM\Table(name="hn_communautepratique_discussion_viewed")
 */
class Viewed
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
     * @ORM\JoinColumn(referencedColumnName="usr_id", nullable=true, onDelete="CASCADE")
     */
    protected $user;

    /**
     * @var Discussion
     *
     * @ORM\ManyToOne(targetEntity="Discussion")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $discussion;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $viewDate;

    /**
     * View constructor.
     *
     * @param Discussion $discussion
     * @param User|null $user
     */
    public function __construct(Discussion $discussion, User $user = null)
    {
        $this->discussion = $discussion;
        $this->user = $user;
        $this->viewDate = new \DateTime();
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
    public function getViewDate()
    {
        return $this->viewDate;
    }

    /**
     * @param \DateTime $viewDate
     *
     * @return Viewed
     */
    public function setViewDate($viewDate)
    {
        $this->viewDate = $viewDate;

        return $this;
    }
}
