<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\CoreBundle\Entity\ObjectIdentity\ObjectIdentityDisplayableInterface;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="HopitalNumerique\CommunautePratiqueBundle\Repository\Discussion\DiscussionRepository")
 * @ORM\Table(name="hn_communautepratique_discussion")
 */
class Discussion implements ObjectIdentityDisplayableInterface
{
    const CREATED_IN_GROUP = 1;
    const CREATED_AS_PUBLIC = 0;

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
     * @Assert\NotBlank()
     * @Assert\Length(max=255, min=5)
     */
    protected $title;

    /**
     * @var Discussion
     *
     * @ORM\ManyToOne(targetEntity="Discussion", inversedBy="children")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    protected $parent;

    /**
     * @var Discussion[]
     *
     * @ORM\OneToMany(targetEntity="Discussion", mappedBy="parent")
     */
    protected $children;

    /**
     * @var Message[]
     *
     * @ORM\OneToMany(targetEntity="Message", mappedBy="discussion", orphanRemoval=true)
     */
    protected $messages;

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
     * @var Groupe[]
     *
     * @ORM\ManyToMany(targetEntity="HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe", inversedBy="discussions")
     * @ORM\JoinTable(name="hn_communautepratique_discussion_group", inverseJoinColumns={@ORM\JoinColumn(referencedColumnName="group_id")})
     */
    protected $groups;

    /**
     * @var Read[]
     *
     * @ORM\OneToMany(targetEntity="Read", mappedBy="discussion", orphanRemoval=true)
     */
    protected $readings;

    /**
     * @var Domaine[]
     *
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\DomaineBundle\Entity\Domaine")
     * @ORM\JoinTable(name="hn_communautepratique_discussion_domain",
     *     inverseJoinColumns={ @ORM\JoinColumn(referencedColumnName="dom_id")}
     * )
     */
    protected $domains;

    /**
     * @var Objet
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\ObjetBundle\Entity\Objet")
     * @ORM\JoinColumn(referencedColumnName="obj_id", nullable=true, onDelete="SET NULL")
     */
    protected $relatedObject;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $creationPosition = self::CREATED_AS_PUBLIC;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    protected $active = true;

    /**
     * Discussion constructor.
     *
     * @param $title
     * @param User|null $author
     * @param array $domains
     */
    public function __construct($title, User $author = null, array $domains)
    {
        $this->title = $title;
        $this->user = $author;
        $this->domains = $domains;
        $this->children = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->readings = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->messages = new ArrayCollection();
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
     * @return Message[]|ArrayCollection
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @return ArrayCollection
     */
    public function getMessagesFiles()
    {
        $files = new ArrayCollection();

        foreach ($this->getMessages() as $message) {
            foreach ($message->getFiles() as $file) {
                $files->add($file);
            }
        }

        return $files;
    }

    /**
     * @return Read[]
     */
    public function getReadings()
    {
        return $this->readings;
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
     * @return Groupe[]
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @return Discussion
     */
    public function resetGroups()
    {
        $this->groups = new ArrayCollection();

        return $this;
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

    /**
     * @return Domaine[]|ArrayCollection
     */
    public function getDomains()
    {
        return $this->domains;
    }

    /**
     * @param User $user
     *
     * @return int
     */
    public function getNewMessageCount(User $user)
    {
        return $this->getNewMessages($user)->count();
    }

    /**
     * @param User $user
     *
     * @return int
     */
    public function getNewMessageFileCount(User $user)
    {
        $count = 0;
        foreach ($this->getNewMessages($user) as $message) {
            $count += $message->getFiles()->count();
        }

        return $count;
    }

    /**
     * @return string
     */
    public function getFirstMessageContent()
    {
        return $this->getMessages()->first() ? $this->getMessages()->first()->getContent() : null;
    }

    /**
     * Return user new messages
     *
     * @param User $user
     *
     * @return ArrayCollection|Message[]
     */
    private function getNewMessages(User $user)
    {
        /** @var Read $read */
        $read = $this->getReadings()->filter(function (Read $read) use ($user) {
            return $read->getUser()->getId() === $user->getId();
        })->first();

        if (!$read) {
            return new ArrayCollection();
        }

        return $this->getMessages()->filter(function (Message $message) use ($read) {
            return $message->getCreatedAt() > $read->getLastMessageDate();
        });
    }

    public function isNewDiscussion(User $user)
    {
        return 0 === $this->getReadings()->filter(function (Read $read) use ($user) {
            return $read->getUser()->getId() === $user->getId();
        })->count() && $this->getCreatedAt() >= $user->getCommunautePratiqueEnrollmentDate();
    }

    /**
     * @param User $user
     * @param Message $message
     *
     * @return bool
     */
    public function isNewMessage(User $user, Message $message)
    {
        /** @var Read $read */
        $read = $this->getReadings()->filter(function (Read $read) use ($user) {
            return $read->getUser()->getId() === $user->getId();
        })->first();

        if (!$read) {
            return $this->getCreatedAt() > $user->getCommunautePratiqueEnrollmentDate();
        }

        return $read->getLastMessageDate() < $message->getCreatedAt();
    }

    /**
     * @return bool
     */
    public function hasHelpfulMessage()
    {
        foreach ($this->getMessages() as $message) {
            if ($message->isHelpful()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Objet|null
     */
    public function getRelatedObject()
    {
        return $this->relatedObject;
    }

    /**
     * @param Objet|null $object
     *
     * @return Discussion
     */
    public function setRelatedObject(Objet $object = null)
    {
        $this->relatedObject = $object;

        return $this;
    }

    /**
     * @return string
     */
    public function getObjectIdentityTitle()
    {
        return $this->getTitle();
    }

    /**
     * @return array
     */
    public function getObjectIdentityCategories()
    {
        return [];
    }

    /**
     * @return null
     */
    public function getObjectIdentityDescription()
    {
        return null;
    }

    /**
     * @return string
     */
    public function getObjectIdentityType()
    {
        return 'discussion';
    }

    /**
     * @return int
     */
    public function getCreationPosition()
    {
        return $this->creationPosition;
    }

    /**
     * @param int $creationPosition
     *
     * @return Discussion
     */
    public function setCreationPosition($creationPosition)
    {
        $this->creationPosition = $creationPosition;

        return $this;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     *
     * @return $this
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }
}
