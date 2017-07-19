<?php

namespace HopitalNumerique\RechercheParcoursBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\ObjetBundle\Entity\Risk;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * GuidedSearch
 *
 * @ORM\Table(name="hn_guided_search")
 * @ORM\Entity(repositoryClass="HopitalNumerique\RechercheParcoursBundle\Repository\GuidedSearchRepository")
 */
class GuidedSearch
{

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var User|null
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\UserBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="usr_id")
     */
    protected $owner;
    /**
     * @var User[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="HopitalNumerique\UserBundle\Entity\User")
     * @ORM\JoinTable(
     *     name="hn_guided_search_share",
     *     inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="usr_id", onDelete="CASCADE")}
     * )
     */
    protected $shares;

    /**
     * @var ArrayCollection|GuidedSearchStep[]
     *
     * @ORM\OneToMany(targetEntity="HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearchStep", mappedBy="guidedSearch", cascade={"remove"})
     */
    protected $steps;

    /**
     * @var ArrayCollection|Risk[]
     *
     * @ORM\ManyToMany(targetEntity="HopitalNumerique\ObjetBundle\Entity\Risk", inversedBy="guidedSearches")
     * @ORM\JoinTable(name="hn_guided_search_risk")
     */
    protected $privateRisks;

    /**
     * @var RechercheParcours $guidedSearchReference
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcours", inversedBy="guidedSearches")
     * @ORM\JoinColumn(referencedColumnName="rrp_id", onDelete="REMOVE")
     */
    protected $guidedSearchReference;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * GuidedSearch constructor.
     */
    public function __construct()
    {
        $this->steps = new ArrayCollection();
        $this->shares = new ArrayCollection();
        $this->privateRisks = new ArrayCollection();
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
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function isOwner(User $user)
    {
        return $this->owner->getId() === $user->getId();
    }

    /**
     * @param User|null $owner
     *
     * @return GuidedSearch
     */
    public function setOwner(User $owner = null)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return User[]|null|ArrayCollection
     */
    public function getShares()
    {
        return $this->shares;
    }

    /**
     * @param User $user
     *
     * @return GuidedSearch
     */
    public function addShare(User $user)
    {
        if (!$this->shares->contains($user)) {
            $this->shares->add($user);
        }

        return $this;
    }

    /**
     * @param User $user
     *
     * @return GuidedSearch
     */
    public function removeShare(User $user)
    {
        if ($this->shares->contains($user)) {
            $this->shares->removeElement($user);
        }

        foreach ($this->steps as $step) {
            foreach ($step->getRisksAnalysis() as $riskAnalysis) {
                if ($riskAnalysis->getOwner()->getId() === $user->getId()) {
                    $step->removeRiskAnalysis($riskAnalysis);
                }
            }
        }

        return $this;
    }

    /**
     * @return ArrayCollection|GuidedSearchStep[]
     */
    public function getSteps()
    {
        return $this->steps;
    }

    /**
     * @param mixed $steps
     *
     * @return GuidedSearch
     */
    public function setSteps($steps)
    {
        $this->steps = $steps;

        return $this;
    }

    /**
     * @return ArrayCollection|Risk[]
     */
    public function getPrivateRisks()
    {
        return $this->privateRisks;
    }

    /**
     * @param ArrayCollection|Risk[] $privateRisks
     *
     * @return GuidedSearch
     */
    public function setPrivateRisks($privateRisks)
    {
        $this->privateRisks = $privateRisks;

        return $this;
    }

    /**
     * @param Risk $privateRisk
     *
     * @return GuidedSearch
     */
    public function addPrivateRisk(Risk $privateRisk)
    {
        if (!$this->privateRisks->contains($privateRisk)) {
            $this->privateRisks->add($privateRisk);
            $privateRisk->addGuidedSearch($this);
        }

        return $this;
    }

    /**
     * @return RechercheParcours
     */
    public function getGuidedSearchReference()
    {
        return $this->guidedSearchReference;
    }

    /**
     * @param RechercheParcours $guidedSearchReference
     *
     * @return GuidedSearch
     */
    public function setGuidedSearchReference(RechercheParcours $guidedSearchReference)
    {
        $this->guidedSearchReference = $guidedSearchReference;

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
     * @return GuidedSearch
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
