<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Entity\Member;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="HopitalNumerique\CommunautePratiqueBundle\Repository\Member\ViewedMemberRepository")
 * @ORM\Table(name="hn_communautepratique_viewed_member")
 */
class ViewedMember
{
    /**
     * @var User
     *
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\UserBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="usr_id", nullable=false, onDelete="CASCADE")
     * @Assert\NotNull()
     */
    protected $member;

    /**
     * @var User
     *
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\UserBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="usr_id", nullable=false, onDelete="CASCADE")
     * @Assert\NotNull()
     */
    protected $viewer;


    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $viewedAt;

    /**
     * ViewedMember constructor.
     * @param User $member
     * @param User $viewer
     * @param \DateTime $viewedAt
     */
    public function __construct(User $member, User $viewer, \DateTime $viewedAt)
    {
        $this->member = $member;
        $this->viewer = $viewer;
        $this->viewedAt = $viewedAt;
    }

    /**
     * @return User
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * @param User $member
     *
     * @return ViewedMember
     */
    public function setMember(User $member)
    {
        $this->member = $member;

        return $this;
    }

    /**
     * @return User
     */
    public function getViewer()
    {
        return $this->viewer;
    }

    /**
     * @param User $viewer
     *
     * @return ViewedMember
     */
    public function setViewer(User $viewer)
    {
        $this->viewer = $viewer;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getViewedAt()
    {
        return $this->viewedAt;
    }

    /**
     * @param \DateTime $viewedAt
     *
     * @return ViewedMember
     */
    public function setViewedAt($viewedAt)
    {
        $this->viewedAt = $viewedAt;

        return $this;
    }
}
