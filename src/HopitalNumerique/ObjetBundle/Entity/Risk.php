<?php

namespace HopitalNumerique\ObjetBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearch;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ReferenceBundle\Entity\Reference;

/**
 * @ORM\Entity(repositoryClass="HopitalNumerique\ObjetBundle\Repository\RiskRepository")
 * @ORM\Table(name="hn_objet_risk")
 */
class Risk
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $label;

    /**
     * @var Reference
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\ReferenceBundle\Entity\Reference")
     * @ORM\JoinColumn(referencedColumnName="ref_id")
     */
    protected $nature;

    /**
     * @var Domaine[]
     *
     * @ORM\ManyToMany(targetEntity="HopitalNumerique\DomaineBundle\Entity\Domaine")
     * @ORM\JoinTable(
     *     name="hn_objet_risk_domain",
     *     joinColumns={@ORM\JoinColumn(name="risk_id", referencedColumnName="id", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="domain_id", referencedColumnName="dom_id")}
     * )
     */
    protected $domains;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $private = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $archived = false;

    /**
     * @var User[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="HopitalNumerique\UserBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinTable(
     *     name="hn_objet_risk_owner",
     *     joinColumns={@ORM\JoinColumn(name="risk_id", referencedColumnName="id", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="usr_id", onDelete="CASCADE")}
     * )
     */
    protected $owners;

    /**
     * @var ArrayCollection|RelatedRisk[]
     *
     * @ORM\OneToMany(targetEntity="RelatedRisk", mappedBy="risk", cascade={"remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"position": "ASC"})
     */
    protected $relatedRisks;

    /**
     * @var ArrayCollection|GuidedSearch[]
     *
     * @ORM\ManyToMany(targetEntity="HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearch", mappedBy="privateRisks")
     */
    protected $guidedSearches;

    /**
     * Risk constructor.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->domains = new ArrayCollection();
        $this->owners = new ArrayCollection();
        $this->guidedSearches = new ArrayCollection();
    }

    /**
     * @return Risk
     */
    public static function createPrivate()
    {
        $risk = new self;

        $risk->setPrivate(true);

        return $risk;
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
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     *
     * @return Risk
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     *
     * @return Risk
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return Reference
     */
    public function getNature()
    {
        return $this->nature;
    }

    /**
     * @param Reference $nature
     *
     * @return Risk
     */
    public function setNature($nature)
    {
        $this->nature = $nature;

        return $this;
    }

    /**
     * @return Domaine[]
     */
    public function getDomains()
    {
        return $this->domains;
    }

    /**
     * @param Domaine[] $domains
     *
     * @return Risk
     */
    public function setDomains($domains)
    {
        $this->domains = $domains;

        return $this;
    }

    /**
     * @param Domaine $domain
     *
     * @return Risk
     */
    public function addDomain(Domaine $domain)
    {
        if (!$this->domains->contains($domain)) {
            $this->domains->add($domain);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isPrivate()
    {
        return $this->private;
    }

    /**
     * @param bool $private
     *
     * @return Risk
     */
    public function setPrivate($private)
    {
        $this->private = $private;

        return $this;
    }

    /**
     * @return bool
     */
    public function isArchived()
    {
        return $this->archived;
    }

    /**
     * @param bool $archived
     *
     * @return Risk
     */
    public function setArchived($archived)
    {
        $this->archived = $archived;

        return $this;
    }

    /**
     * @return ArrayCollection|User[]
     */
    public function getOwners()
    {
        return $this->owners;
    }

    /**
     * @param User $user
     *
     * @return Risk
     */
    public function addOwner(User $user)
    {
        if (!$this->owners->contains($user)) {
            $this->owners->add($user);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getLabel();
    }

    /**
     * @return string
     */
    public function getLongLabel()
    {
        return sprintf('%s - %s', $this->getNature()->getLibelle(), $this->getLabel());
    }

    /**
     * Return Entity between Object and Risk
     *
     * @return ArrayCollection|RelatedRisk[]
     */
    public function getRelatedRisks()
    {
        return $this->relatedRisks;
    }

    /**
     * Count related risks object type
     *
     * @return array
     */
    public function countRelatedRisksObjectType()
    {
        $result = [];
        foreach ($this->getRelatedRisks() as $relatedRisk) {
            foreach ($relatedRisk->getObject()->getTypeLabels() as $typeLabel) {
                if (!isset($result[$typeLabel])) {
                    $result[$typeLabel] = 0;
                }

                $result[$typeLabel]++;
            }
        }

        return $result;
    }

    /**
     * @return ArrayCollection|GuidedSearch[]
     */
    public function getGuidedSearches()
    {
        return $this->guidedSearches;
    }

    /**
     * @param GuidedSearch $guidedSearch
     *
     * @return Risk
     */
    public function addGuidedSearch(GuidedSearch $guidedSearch)
    {
        if (!$this->guidedSearches->contains($guidedSearch)) {
            $this->guidedSearches->add($guidedSearch);
            $guidedSearch->addPrivateRisk($this);
        }

        return $this;
    }
}
