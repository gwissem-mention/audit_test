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
 * @ORM\Entity(repositoryClass="HopitalNumerique\AutodiagBundle\Repository\AutodiagEntryRepository")
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
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $validatedAt;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $copy = false;

    /**
     * @var Synthesis
     *
     * @ORM\ManyToMany(
     *     targetEntity="HopitalNumerique\AutodiagBundle\Entity\Synthesis",
     *     inversedBy="entries"
     * )
     * @ORM\JoinTable(name="ad_synthesis_entry",
     *      joinColumns={@ORM\JoinColumn(name="entry_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="synthesis_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    private $syntheses;

    /**
     * @var Collection
     * @ORM\OneToMany(
     *     targetEntity="HopitalNumerique\AutodiagBundle\Entity\AutodiagEntry\Value",
     *     mappedBy="entry",
     *     cascade={"persist", "remove"}
     * )
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
        $this->syntheses = new ArrayCollection([$synthesis]);
        $this->user = $user;
        $this->values = new ArrayCollection();
        $this->updatedAt = new \DateTime();

        $synthesis->addEntry($this);
    }

    public static function create(Autodiag $autodiag, User $user = null)
    {
        $entry = new self(Synthesis::create($autodiag, $user), $user);

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

    public function getSyntheses()
    {
        return $this->syntheses;
    }

    /**
     * @return Synthesis
     */
    public function getSynthesis()
    {
        return $this->syntheses->first();
    }

    /**
     * @param Synthesis $synthesis
     * @return $this
     */
    public function addSynthesis(Synthesis $synthesis)
    {
        if (!$this->syntheses->contains($synthesis)) {
            $this->syntheses->add($synthesis);
        }

        return $this;
    }

    public function removeSynthesis(Synthesis $synthesis)
    {
        if (!$this->syntheses->contains($synthesis)) {
            $this->syntheses->removeElement($synthesis);
        }

        return $this;
    }

    public function getValues()
    {
        return $this->values;
    }

    public function addValue($value)
    {
        $this->values->add($value);
    }

    public function removeValue($value)
    {
        if ($this->values->contains($value)) {
            $this->values->removeElement($value);
        }

        return $this;
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

    /**
     * Get validation datetime
     *
     * @return \DateTime
     */
    public function getValidatedAt()
    {
        return $this->validatedAt;
    }

    /**
     * Set validation datetime
     *
     * @param \DateTime $validatedAt
     *
     * @return $this
     */
    public function setValidatedAt($validatedAt)
    {
        $this->validatedAt = $validatedAt;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isCopy()
    {
        return $this->copy;
    }

    /**
     * @param boolean $copy
     */
    public function setCopy($copy)
    {
        $this->copy = $copy;
    }

    public function __clone()
    {
        $this->id = null;
        $this->syntheses = new ArrayCollection();
        $originalValues = $this->values;
        $this->values = new ArrayCollection();
        foreach ($originalValues as $value) {
            $clone = clone($value);
            $clone->setEntry($this);
        }
    }
}
