<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * @ORM\Entity(repositoryClass="HopitalNumerique\CommunautePratiqueBundle\Repository\Discussion\MessageRepository")
 * @ORM\Table(name="hn_communautepratique_discussion_message")
 */
class Message
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
     * @ORM\JoinColumn(referencedColumnName="usr_id", nullable=false)
     */
    protected $user;

    /**
     * @var Discussion
     *
     * @ORM\ManyToOne(targetEntity="Discussion", inversedBy="messages")
     */
    protected $discussion;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    protected $content;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    protected $published = true;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    protected $helpful = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * Message constructor.
     *
     * @param Discussion $discussion
     * @param $content
     * @param User $author
     */
    public function __construct(Discussion $discussion, $content, User $author)
    {
        $this->discussion = $discussion;
        $this->content = $content;
        $this->user = $author;
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
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return Message
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Discussion
     */
    public function getDiscussion()
    {
        return $this->discussion;
    }

    /**
     * @param Discussion $discussion
     *
     * @return Message
     */
    public function setDiscussion(Discussion $discussion)
    {
        $this->discussion = $discussion;

        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     *
     * @return Message
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPublished()
    {
        return $this->published;
    }

    /**
     * @param bool $published
     *
     * @return Message
     */
    public function setPublished($published)
    {
        $this->published = $published;

        return $this;
    }

    /**
     * @return bool
     */
    public function isHelpful()
    {
        return $this->helpful;
    }

    /**
     * @param bool $helpful
     *
     * @return Message
     */
    public function setHelpful($helpful)
    {
        $this->helpful = $helpful;

        return $this;
    }

    /**
     * @return Message
     */
    public function toggleHelpful()
    {
        $this->setHelpful(!$this->isHelpful());

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
     * @return Message
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
