<?php

namespace HopitalNumerique\AutodiagBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\ActionPlan;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container\Chapter;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Preset;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Gabarit d'autodiag
 *
 * @ORM\Table(name="ad_autodiag")
 * @ORM\Entity(repositoryClass="HopitalNumerique\AutodiagBundle\Repository\AutodiagRepository")
 */
class Autodiag
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
     * Autodiag title
     *
     * @var string
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank()
     */
    private $title;

    /**
     * Autodiag instructions
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $instructions;

    /**
     * Authorize to show results if all questions aren't answered.
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    private $partialResultsAuthorized;

    /**
     * Autorize synthesis
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    private $synthesisAuthorized;

    /**
     * Public update date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $publicUpdatedDate;

    /**
     * Creation date and time
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * Autodiag algorithm
     *
     * @var string
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $algorithm;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="HopitalNumerique\DomaineBundle\Entity\Domaine")
     * @ORM\JoinTable(
     *     name="ad_autodiag_domain",
     *     joinColumns={@ORM\JoinColumn(name="autodiag_id", referencedColumnName="id", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="domaine_id", referencedColumnName="dom_id")}
     * )
     *
     * @Assert\Count(min="1", minMessage="ad.autodiag.domaines.limit.min")
     */
    private $domaines;

    /**
     * @var Questionnaire
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire")
     * @ORM\JoinColumn(name="questionnaire_id", referencedColumnName="qst_id", onDelete="SET NULL")
     */
    private $questionnaire;

    /**
     * @var Collection
     * @ORM\OneToMany(
     *     targetEntity="HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container",
     *     mappedBy="autodiag",
     *     cascade={"persist"},
     *     orphanRemoval=true
     * )
     * @ORM\OrderBy({"order" = "ASC"})
     */
    private $containers;

    /**
     * @var Collection
     * @ORM\OneToMany(
     *     targetEntity="HopitalNumerique\AutodiagBundle\Entity\Autodiag\Attribute",
     *     mappedBy="autodiag",
     *     cascade={"persist"},
     *     orphanRemoval=true
     * )
     */
    public $attributes;

    /**
     * @var Collection
     * @ORM\OneToMany(
     *     targetEntity="HopitalNumerique\AutodiagBundle\Entity\Autodiag\Preset",
     *     mappedBy="autodiag",
     *     fetch="EAGER"
     * )
     */
    private $presets;

    /**
     * @var Restitution
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\AutodiagBundle\Entity\Restitution", cascade={"persist"})
     * @ORM\JoinColumn(name="restitution_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $restitution;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="HopitalNumerique\AutodiagBundle\Entity\Autodiag\ActionPlan", mappedBy="autodiag")
     */
    private $actionPlans;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="HopitalNumerique\AutodiagBundle\Entity\Autodiag\Reference", mappedBy="autodiag")
     */
    private $references;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $computeBeginning;

    public function __construct()
    {
        $this->domaines = new ArrayCollection();
        $this->chapters = new ArrayCollection();
        $this->publicUpdatedDate = new \DateTime();
        $this->createdAt = new \DateTime();
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
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get instructions
     *
     * @return string
     */
    public function getInstructions()
    {
        return $this->instructions;
    }

    /**
     * Set instructions
     *
     * @param string $instructions
     * @return $this
     */
    public function setInstructions($instructions)
    {
        $this->instructions = $instructions;

        return $this;
    }

    /**
     * Is show results if all questions aren't answered authorized
     *
     * @return boolean
     */
    public function isPartialResultsAuthorized()
    {
        return $this->partialResultsAuthorized;
    }

    /**
     * Set partial results authorization
     *
     * @param boolean $partialResultsAuthorized
     * @return $this
     */
    public function setPartialResultsAuthorized($partialResultsAuthorized)
    {
        $this->partialResultsAuthorized = $partialResultsAuthorized;

        return $this;
    }

    /**
     * Is synthesis authorized
     *
     * @return boolean
     */
    public function isSynthesisAuthorized()
    {
        return $this->synthesisAuthorized;
    }

    /**
     * Set synthesis authorization
     *
     * @param boolean $synthesisAuthorized
     * @return $this
     */
    public function setSynthesisAuthorized($synthesisAuthorized)
    {
        $this->synthesisAuthorized = $synthesisAuthorized;

        return $this;
    }

    /**
     * Get public updated date
     *
     * @return \DateTime
     */
    public function getPublicUpdatedDate()
    {
        return $this->publicUpdatedDate;
    }

    /**
     * Set public updated date
     *
     * @param \DateTime $publicUpdatedDate
     * @return $this
     */
    public function setPublicUpdatedDate(\DateTime $publicUpdatedDate)
    {
        $this->publicUpdatedDate = $publicUpdatedDate;

        return $this;
    }

    /**
     * Get creation datetime
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set creation datetime
     *
     * @param \DateTime $createdAt
     * @return $this
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return string
     */
    public function getAlgorithm()
    {
        return $this->algorithm;
    }

    /**
     * @param string $algorithm
     * @return $this
     */
    public function setAlgorithm($algorithm)
    {
        $this->algorithm = $algorithm;

        return $this;
    }

    /**
     * Get Domaine
     *
     * @return Collection
     */
    public function getDomaines()
    {
        return $this->domaines;
    }

    /**
     * Add domaine
     *
     * @param Domaine $domaine
     * @return $this
     */
    public function addDomaine(Domaine $domaine)
    {
        $this->domaines->add($domaine);

        return $this;
    }

    /**
     * Remove domaine
     *
     * @param Domaine $domaine
     * @return $this
     */
    public function removeDomaine(Domaine $domaine)
    {
        $this->domaines->removeElement($domaine);

        return $this;
    }

    public function setDomaines(Collection $domaines)
    {
        $this->domaines = $domaines;
        return $this;
    }

    /**
     * Get prior questionnaire
     *
     * @return Questionnaire
     */
    public function getQuestionnaire()
    {
        return $this->questionnaire;
    }

    /**
     * Set prior questionnaire
     *
     * @param Questionnaire $questionnaire
     * @return $this
     */
    public function setQuestionnaire($questionnaire = null)
    {
        $this->questionnaire = $questionnaire;
        return $this;
    }

    public function getContainers()
    {
        return $this->containers;
    }

    /**
     * Get chapters
     *
     * @return Collection
     */
    public function getChapters()
    {
        return $this->containers->filter(function (Container $container) {
            return $container instanceof Chapter && $container->getParent() === null;
        });
    }

    /**
     * Add chapter
     *
     * @param Chapter $chapter
     * @return $this
     */
    public function addChapter(Chapter $chapter)
    {
        $chapter->setAutodiag($this);
        $this->containers->add($chapter);

        return $this;
    }

    /**
     * Remove chapter
     *
     * @param Chapter $chapter
     * @return $this
     */
    public function removeChapter(Chapter $chapter)
    {
        $this->containers->removeElement($chapter);

        return $this;
    }

    /**
     * Get presets
     *
     * @return Collection
     */
    public function getPresets()
    {
        return $this->presets;
    }

    /**
     * @return Restitution
     */
    public function getRestitution()
    {
        return $this->restitution;
    }

    /**
     * @param Restitution $restitution
     * @return $this
     */
    public function setRestitution(Restitution $restitution)
    {
        $this->restitution = $restitution;

        return $this;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Get ActionPlans
     *
     * @return Collection
     */
    public function getActionPlans()
    {
        return $this->actionPlans;
    }

    /**
     * Add ActionPlan
     *
     * @param ActionPlan $actionPlan
     * @return $this
     */
    public function addActionPlan(ActionPlan $actionPlan)
    {
        $this->actionPlans->add($actionPlan);

        return $this;
    }

    /**
     * Remove actionPlan
     *
     * @param ActionPlan $actionPlan
     * @return $this
     */
    public function removeActionPlan(ActionPlan $actionPlan)
    {
        $this->actionPlans->removeElement($actionPlan);

        return $this;
    }

    /**
     * Get references
     *
     * @return Collection
     */
    public function getReferences()
    {
        return $this->references;
    }

    public function setComputing()
    {
        $this->computeBeginning = time();
        return $this->computeBeginning;
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
}
