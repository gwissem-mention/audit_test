<?php

namespace HopitalNumerique\RechercheParcoursBundle\DTO;

use Doctrine\Common\Collections\ArrayCollection;
use HopitalNumerique\DomaineBundle\Entity\Domaine;

class StepRiskDTO
{
    public $riskId;

    public $label;

    public $natureLabel;

    public $natureCode;

    public $probability;

    public $impact;

    public $initialSkillsRate;

    public $currentSkillsRate;

    public $comment;

    /**
     * @var array
     */
    public $relatedRisks = [];

    /**
     * @var array
     */
    public $excludedObjects = [];

    /**
     * StepRiskDTO constructor.
     */
    public function __construct()
    {
        $this->excludedObjects = new ArrayCollection();
    }

    public function relatedRisksByDomain(Domaine $domain)
    {
        $relatedRisks = [];
        foreach ($this->relatedRisks as $relatedRisk) {
            if ($relatedRisk->getObject()->getDomaines()->contains($domain)) {
                $relatedRisks[] = $relatedRisk;
            }
        }

        return $relatedRisks;
    }
}
