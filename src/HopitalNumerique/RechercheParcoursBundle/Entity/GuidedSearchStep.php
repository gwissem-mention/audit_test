<?php

namespace HopitalNumerique\RechercheParcoursBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\ObjetBundle\Entity\Risk;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * GuidedSearchStep
 *
 * @ORM\Table(name="hn_guided_search_step")
 * @ORM\Entity(repositoryClass="HopitalNumerique\RechercheParcoursBundle\Repository\GuidedSearchStepRepository")
 */
class GuidedSearchStep
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
     * @var GuidedSearch
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearch", inversedBy="steps")
     */
    protected $guidedSearch;

    /**
     * @var ArrayCollection|Risk[]
     *
     * @ORM\ManyToMany(targetEntity="HopitalNumerique\ObjetBundle\Entity\Risk")
     * @ORM\JoinTable(name="hn_guided_search_step_excluded_risk")
     */
    protected $excludedRisks;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $stepPath;

    /**
     * @var ArrayCollection|RiskAnalysis[]
     *
     * @ORM\OneToMany(targetEntity="HopitalNumerique\RechercheParcoursBundle\Entity\RiskAnalysis", mappedBy="step", cascade={"remove"}, orphanRemoval=true)
     */
    protected $risksAnalysis;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    protected $analyzed = false;

    /**
     * GuidedSearchStep constructor.
     *
     * @param GuidedSearch $guidedSearch
     * @param string $stepPath
     */
    public function __construct(GuidedSearch $guidedSearch, $stepPath)
    {
        $this->guidedSearch = $guidedSearch;
        $this->stepPath = $stepPath;
        $this->excludedRisks = new ArrayCollection();
        $this->risksAnalysis = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return GuidedSearch
     */
    public function getGuidedSearch()
    {
        return $this->guidedSearch;
    }

    /**
     * @param GuidedSearch $guidedSearch
     *
     * @return GuidedSearchStep
     */
    public function setGuidedSearch(GuidedSearch $guidedSearch)
    {
        $this->guidedSearch = $guidedSearch;

        return $this;
    }

    /**
     * @return ArrayCollection|Risk[]
     */
    public function getExcludedRisks()
    {
        return $this->excludedRisks;
    }

    /**
     * @param Risk $risk
     *
     * @return GuidedSearchStep
     */
    public function addExcludedRisk(Risk $risk)
    {
        if (!$this->excludedRisks->contains($risk)) {
            $this->excludedRisks->add($risk);
        }

        return $this;
    }

    /**
     * @param Risk $risk
     *
     * @return GuidedSearchStep
     */
    public function removeExcludedRisk(Risk $risk)
    {
        if ($this->excludedRisks->contains($risk)) {
            $this->excludedRisks->removeElement($risk);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getStepPath()
    {
        return $this->stepPath;
    }

    /**
     * @param string $stepPath
     *
     * @return GuidedSearchStep
     */
    public function setStepPath($stepPath)
    {
        $this->stepPath = $stepPath;

        return $this;
    }

    /**
     * @return int
     */
    public function getGuidedSearchParentReferenceId()
    {
        return (int) current(explode(':', $this->getStepPath()));
    }

    /**
     * @return int|null
     */
    public function getGuidedSearchSubReferenceId()
    {
        $steps = explode(':', $this->getStepPath());

        return isset($steps[1]) ? (int) $steps[1] : null;
    }

    /**
     * @return integer
     */
    public function getThinnestReferenceId()
    {
        $referenceId = $this
            ->getGuidedSearch()
            ->getGuidedSearchReference()
            ->getRecherchesParcoursDetails()
            ->filter(
                function (RechercheParcoursDetails $guidedSearchParent) {
                    return $guidedSearchParent->getId() === $this->getGuidedSearchParentReferenceId();
                }
            )
            ->first()
            ->getReference()
            ->getId()
        ;

        return $this->getGuidedSearchSubReferenceId() ?: $referenceId;
    }

    /**
     * @return ArrayCollection|RiskAnalysis[]
     */
    public function getRisksAnalysis()
    {
        return $this->risksAnalysis;
    }

    /**
     * @param RiskAnalysis $riskAnalysis
     *
     * @return GuidedSearchStep
     */
    public function removeRiskAnalysis(RiskAnalysis $riskAnalysis)
    {
        $this->risksAnalysis->removeElement($riskAnalysis);

        return $this;
    }

    /**
     * @return boolean
     */
    public function isAnalyzed()
    {
        return $this->analyzed;
    }

    /**
     * @param boolean $analyzed
     *
     * @return GuidedSearchStep
     */
    public function setAnalyzed($analyzed)
    {
        $this->analyzed = $analyzed;

        return $this;
    }
}
