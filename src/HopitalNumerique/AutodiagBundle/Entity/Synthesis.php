<?php

namespace HopitalNumerique\AutodiagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\Collection;
use HopitalNumerique\UserBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Synthesis.
 *
 * @ORM\Table(name="ad_synthesis")
 * @ORM\Entity(repositoryClass="HopitalNumerique\AutodiagBundle\Repository\SynthesisRepository")
 * @Gedmo\Loggable
 */
class Synthesis
{
    /**
     * @var int
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
     * @Gedmo\Versioned
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
    private $createdAt;

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
     *     joinColumns={@ORM\JoinColumn(name="synthesis_id", referencedColumnName="id", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="usr_id", onDelete="CASCADE")}
     * )
     */
    private $shares;

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    private $completion = 0;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $computeBeginning;

    /**
     * @var Synthesis
     * @ORM\ManyToOne(targetEntity="Synthesis")
     * @ORM\JoinColumn(name="created_from", referencedColumnName="id", nullable=true)
     */
    private $createdFrom = null;

    private function __construct(Autodiag $autodiag, User $user = null)
    {
        $this->entries = new ArrayCollection();
        $this->autodiag = $autodiag;
        $this->user = $user;

        $now = new \DateTime();
        $this->name = $now->format('d/m/Y H:i');
        $this->createdAt = $now;
    }

    /**
     * Create new Synthesis.
     *
     * @param Autodiag $autodiag
     *
     * @return Synthesis
     */
    public static function create(Autodiag $autodiag, User $user = null)
    {
        $synthesis = new self($autodiag, $user);

        return $synthesis;
    }

    /**
     * Create synthesis from other syntheses.
     *
     * @param Autodiag    $autodiag
     * @param Synthesis[] $syntheses
     *
     * @return Synthesis
     *
     * @deprecated
     */
    public static function createFromSynthesis(Autodiag $autodiag, $syntheses)
    {
        $new = new self($autodiag);

        foreach ($syntheses as $synthesis) {
            foreach ($synthesis->getEntries() as $entry) {
                $new->addEntry(
                    clone $entry
                );
            }
        }

        return $new;
    }

    /**
     * Get id.
     *
     * @return int
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
     * Get name.
     *
     * @param string $name
     *
     * @return Synthesis
     */
    public function setName($name)
    {
        if ($this->entries->count() === 1) {
            $this->entries->first()->setName($name);
        }

        $this->name = $name;

        return $this;
    }

    /**
     * Get validation datetime.
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

    public function canValidate()
    {
        return $this->getAutodiag()->isPartialResultsAuthorized() === true
            || $this->getCompletion() == 100
            || count($this->getEntries()) > 1
        ;
    }

    /**
     * Validate.
     *
     * @return Synthesis|false
     */
    public function validate()
    {
        if (!$this->canValidate()) {
            return false;
        }

        $validatedAt = new \DateTime();
        $this->validatedAt = $validatedAt;
        foreach ($this->getEntries() as $entry) {
            if (false === $entry->isCopy()) {
                $entry->setValidatedAt($validatedAt);
            }
        }

        return $this;
    }

    /**
     * Unvalidate.
     *
     * @return Synthesis
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
    public function getCreatedAt()
    {
        return $this->createdAt;
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
     * Get Entires.
     *
     * @return Collection
     */
    public function getEntries()
    {
        return $this->entries;
    }

    /**
     * Add entry.
     *
     * @param AutodiagEntry $entry
     *
     * @return Synthesis
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
     * Remove entry.
     *
     * @param AutodiagEntry $entry
     *
     * @return Synthesis
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
     *
     * @return Synthesis
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get shares.
     *
     * @return User[]|Collection
     */
    public function getShares()
    {
        return $this->shares;
    }

    /**
     * Set shares.
     *
     * @param ArrayCollection $shares
     *
     * @return Synthesis
     */
    public function setShares(ArrayCollection $shares)
    {
        $this->shares = $shares;

        return $this;
    }

    /**
     * Add share.
     *
     * @param User $user
     *
     * @return Synthesis
     */
    public function addShare(User $user)
    {
        if (!$this->shares->contains($user)) {
            $this->shares->add($user);
        }

        return $this;
    }

    /**
     * Remove share.
     *
     * @param User $user
     *
     * @return Synthesis
     */
    public function removeShare(User $user)
    {
        $this->shares->removeElement($user);

        return $this;
    }

    /**
     * @return float
     */
    public function getCompletion()
    {
        return $this->completion;
    }

    /**
     * @param float $completion
     */
    public function setCompletion($completion)
    {
        $this->completion = $completion;
    }

    public function setComputing()
    {
        $this->computeBeginning = time();
    }

    public function isComputing()
    {
        return $this->computeBeginning !== null && $this->computeBeginning + (12 * 60 * 60) > time();
    }

    public function getComputeBeginning()
    {
        return $this->computeBeginning;
    }

    public function stopComputing()
    {
        $this->computeBeginning = null;
    }

    /**
     * @return Synthesis|null
     */
    public function getCreatedFrom()
    {
        return $this->createdFrom;
    }

    /**
     * @param Synthesis $createdFrom
     *
     * @return Synthesis
     */
    public function setCreatedFrom(Synthesis $createdFrom)
    {
        $this->createdFrom = $createdFrom;

        return $this;
    }

    public function __clone()
    {
        $this->id = null;
    }
}
