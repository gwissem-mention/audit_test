<?php

namespace HopitalNumerique\RechercheParcoursBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\ObjetBundle\Entity\Risk;

/**
 * RiskAnalysis
 *
 * @ORM\Table(name="hn_guided_search_risk_analysis")
 * @ORM\Entity(repositoryClass="HopitalNumerique\RechercheParcoursBundle\Repository\RiskAnalysisRepository")
 */
class RiskAnalysis
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
     * @var GuidedSearchStep
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearchStep", inversedBy="risksAnalysis")
     */
    protected $step;

    /**
     * @var Risk
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\ObjetBundle\Entity\Risk")
     */
    protected $risk;

    /**
     * @var integer|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $probability;

    /**
     * @var integer|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $impact;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $initialSkillsRate;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $currentSkillsRate;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $comment;

    /**
     * @var Objet[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="HopitalNumerique\ObjetBundle\Entity\Objet")
     * @ORM\JoinTable(name="hn_guided_search_risk_analysis_excluded_object",
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="obj_id", referencedColumnName="obj_id")
     *   }
     * )
     */
    protected $excludedObjects;

    /**
     * RiskAnalysis constructor.
     */
    public function __construct()
    {
        $this->excludedObjects = new ArrayCollection();
    }

    /**
     * Clone
     */
    public function __clone()
    {
        $this->id = null;
        $this->owner = null;
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
     * @param User|null $owner
     *
     * @return RiskAnalysis
     */
    public function setOwner(User $owner = null)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return GuidedSearchStep
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * @param GuidedSearchStep $step
     *
     * @return RiskAnalysis
     */
    public function setStep(GuidedSearchStep $step)
    {
        $this->step = $step;

        return $this;
    }

    /**
     * @return Risk
     */
    public function getRisk()
    {
        return $this->risk;
    }

    /**
     * @param Risk $risk
     *
     * @return RiskAnalysis
     */
    public function setRisk(Risk $risk)
    {
        $this->risk = $risk;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getProbability()
    {
        return $this->probability;
    }

    /**
     * @param int|null $probability
     *
     * @return RiskAnalysis
     */
    public function setProbability($probability)
    {
        $this->probability = $probability;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getImpact()
    {
        return $this->impact;
    }

    /**
     * @param int|null $impact
     *
     * @return RiskAnalysis
     */
    public function setImpact($impact)
    {
        $this->impact = $impact;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getCriticality()
    {
        return $this->getImpact() * $this->getProbability();
    }

    /**
     * @return int
     */
    public function getInitialSkillsRate()
    {
        return $this->initialSkillsRate;
    }

    /**
     * @param int $initialSkillsRate
     *
     * @return RiskAnalysis
     */
    public function setInitialSkillsRate($initialSkillsRate)
    {
        $this->initialSkillsRate = $initialSkillsRate;

        return $this;
    }

    /**
     * @return int
     */
    public function getCurrentSkillsRate()
    {
        return $this->currentSkillsRate;
    }

    /**
     * @param int $currentSkillsRate
     *
     * @return RiskAnalysis
     */
    public function setCurrentSkillsRate($currentSkillsRate)
    {
        $this->currentSkillsRate = $currentSkillsRate;

        return $this;
    }

    /**
     * @return int
     */
    public function getSkillsRate()
    {
        return $this->getCurrentSkillsRate() ?: $this->getInitialSkillsRate();
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     *
     * @return RiskAnalysis
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return Objet[]|ArrayCollection
     */
    public function getExcludedObjects()
    {
        return $this->excludedObjects;
    }

    /**
     * @param Objet $object
     *
     * @return RiskAnalysis
     */
    public function addExcludedObject(Objet $object)
    {
        if (!$this->excludedObjects->contains($object)) {
            $this->excludedObjects->add($object);
        }

        return $this;
    }
}
