<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * @ORM\Entity()
 * @ORM\Table(name="hn_communautepratique_discussion")
 */
class Discussion
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
     * @ORM\JoinColumn(referencedColumnName="usr_id", nullable=true)
     */
    protected $user;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $title;

    /**
     * @var Discussion
     *
     * @ORM\ManyToOne(targetEntity="Discussion", inversedBy="children")
     */
    protected $parent;

    /**
     * @var Discussion[]
     *
     * @ORM\OneToMany(targetEntity="Discussion", mappedBy="parent")
     */
    protected $children;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    protected $public = true;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    protected $recommended = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @var Groupe
     *
     * @ORM\ManyToMany(targetEntity="HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe")
     * @ORM\JoinTable(name="hn_communautepratique_discussion_group", inverseJoinColumns={@ORM\JoinColumn(referencedColumnName="group_id")})
     */
    protected $groups;

    /**
     * @var Read[]
     *
     * @ORM\OneToMany(targetEntity="Read", mappedBy="discussion")
     */
    protected $readings;

    /**
     * Discussion constructor.
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->readings = new ArrayCollection();
        $this->createdAt = new \DateTime();
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
     * @param User|null $user
     *
     * @return Discussion
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return Discussion
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return Discussion
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param Discussion|null $parent
     *
     * @return Discussion
     */
    public function setParent(Discussion $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Discussion[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @return bool
     */
    public function isPublic()
    {
        return $this->public;
    }

    /**
     * @param bool $public
     *
     * @return Discussion
     */
    public function setPublic($public)
    {
        $this->public = $public;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRecommended()
    {
        return $this->recommended;
    }

    /**
     * @param bool $recommended
     *
     * @return Discussion
     */
    public function setRecommended($recommended)
    {
        $this->recommended = $recommended;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     *
     * @return Discussion
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Groupe
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param Groupe $group
     *
     * @return Discussion
     */
    public function addGroup(Groupe $group)
    {
        if (!$this->groups->contains($group)) {
            $this->groups->add($group);
        }

        return $this;
    }
}
