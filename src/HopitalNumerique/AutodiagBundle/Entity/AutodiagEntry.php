<?php

namespace HopitalNumerique\AutodiagBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * Autodiag entry - a response to an autodiag
 *
 * @ORM\Table(name="ad_entry")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class AutodiagEntry
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @var Synthesis
     *
     * @ORM\ManyToOne(
     *     targetEntity="HopitalNumerique\AutodiagBundle\Entity\Synthesis",
     *     inversedBy="entries",
     *     fetch="EAGER"
     * )
     * @ORM\JoinColumn(name="synthesis_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $synthesis;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="HopitalNumerique\AutodiagBundle\Entity\AutodiagEntry\Value", mappedBy="entry", cascade={"persist"})
     */
    private $values;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="usr_id", onDelete="CASCADE", nullable=true)
     */
    private $user;

    public function __construct(Synthesis $synthesis, User $user = null)
    {
        $this->synthesis = $synthesis;
        $this->user = $user;
        $this->values = new ArrayCollection();
        $this->updatedAt = new \DateTime();

        $synthesis->addEntry($this);
    }

    public static function create(Autodiag $autodiag, User $user = null)
    {
        $entry = new self(Synthesis::create($autodiag), $user);

        return $entry;
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
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return Synthesis
     */
    public function getSynthesis()
    {
        return $this->synthesis;
    }

    public function getValues()
    {
        return $this->values;
    }

    public function addValue($value)
    {
        $this->values->add($value);
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
     * @return $this
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set updatedAt
     *
     * @ORM\PreUpdate
     * @ORM\PrePersist
     */
    public function setUpdatedAt()
    {
        $this->updatedAt = new \DateTime();
        $this->getSynthesis()->setUpdatedAt($this->updatedAt);
    }
}
