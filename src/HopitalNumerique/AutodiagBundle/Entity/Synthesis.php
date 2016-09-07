<?php

namespace HopitalNumerique\AutodiagBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * Synthesis
 *
 * @ORM\Table(name="ad_synthesis")
 * @ORM\Entity(repositoryClass="HopitalNumerique\AutodiagBundle\Repository\SynthesisRepository")
 */
class Synthesis
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
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $validatedAt;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @var Autodiag
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\AutodiagBundle\Entity\Autodiag")
     * @ORM\JoinColumn(name="autodiag_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $autodiag;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="usr_id", onDelete="CASCADE", nullable=true)
     */
    private $user;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(
     *     targetEntity="HopitalNumerique\AutodiagBundle\Entity\AutodiagEntry",
     *     mappedBy="syntheses",
     *     cascade={"persist"}
     * )
     */
    private $entries;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="HopitalNumerique\UserBundle\Entity\User")
     * @ORM\JoinTable(
     *     name="ad_synthesis_share",
     *     joinColumns={@ORM\JoinColumn(name="synthesis_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="usr_id")}
     * )
     */
    private $shares;

    private function __construct(Autodiag $autodiag, User $user = null)
    {
        $this->entries = new ArrayCollection();
        $this->autodiag = $autodiag;
        $this->user = $user;

        $now = new \DateTime();
        $this->name = $now->format('d/m/Y H:i');
    }

    /**
     * Create new Synthesis
     *
     * @param Autodiag $autodiag
     * @return Synthesis
     */
    public static function create(Autodiag $autodiag, User $user = null)
    {
        $synthesis = new self($autodiag, $user);

        return $synthesis;
    }

    /**
     * Create synthesis from other syntheses
     *
     * @param Autodiag $autodiag
     * @param Synthesis[] $syntheses
     * @return Synthesis
     */
    public static function createFromSynthesis(Autodiag $autodiag, $syntheses)
    {
        $synthesis = new self($autodiag);

        foreach ($syntheses as $synthesis) {
            foreach ($synthesis->getEntries() as $entry) {
                $synthesis->addEntry(
                    clone($entry)
                );
            }
        }

        return $synthesis;
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
     * Get name
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
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

    public function isValidated()
    {
        return $this->validatedAt !== null;
    }

    /**
     * Validate
     *
     * @return $this
     */
    public function validate()
    {
        $validatedAt = new \DateTime();
        $this->validatedAt = $validatedAt;
        foreach ($this->getEntries() as $entry) {
            $entry->setValidatedAt($validatedAt);
        }

        return $this;
    }

    /**
     * Unvalidate
     *
     * @return $this
     */
    public function unvalidate()
    {
        $this->validatedAt = null;
        foreach ($this->getEntries() as $entry) {
            $entry->setValidatedAt(null);
        }

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
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return Autodiag
     */
    public function getAutodiag()
    {
        return $this->autodiag;
    }

    /**
     * Get Entires
     *
     * @return Collection
     */
    public function getEntries()
    {
        return $this->entries;
    }

    /**
     * Add entry
     *
     * @param AutodiagEntry $entry
     * @return $this
     */
    public function addEntry(AutodiagEntry $entry)
    {
        if (!$this->entries->contains($entry)) {
            $this->entries->add($entry);

            if (null === $this->getUpdatedAt() || $entry->getUpdatedAt() > $this->getUpdatedAt()) {
                $this->setUpdatedAt($entry->getUpdatedAt());
            }
        }

        return $this;
    }

    /**
     * Remove entry
     *
     * @param AutodiagEntry $entry
     * @return $this
     */
    public function removeEntry(AutodiagEntry $entry)
    {
        $this->entries->removeElement($entry);

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
     * Get shares
     *
     * @return Collection
     */
    public function getShares()
    {
        return $this->shares;
    }

    /**
     * Set shares
     *
     * @param ArrayCollection $shares
     * @return $this
     */
    public function setShares(ArrayCollection $shares)
    {
        $this->shares = $shares;

        return $this;
    }

    /**
     * Add share
     *
     * @param User $user
     * @return $this
     */
    public function addShare(User $user)
    {
        if (!$this->shares->contains($user)) {
            $this->shares->add($user);
        }

        return $this;
    }

    /**
     * Remove share
     *
     * @param User $user
     * @return $this
     */
    public function removeShare(User $user)
    {
        $this->shares->removeElement($user);

        return $this;
    }
}

