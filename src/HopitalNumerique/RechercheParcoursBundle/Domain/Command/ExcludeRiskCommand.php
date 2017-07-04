<?php

namespace HopitalNumerique\RechercheParcoursBundle\Domain\Command;

use HopitalNumerique\ObjetBundle\Entity\Risk;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearchStep;

class ExcludeRiskCommand
{
    /**
     * @var GuidedSearchStep $guidedSearchStep
     */
    public $guidedSearchStep;

    /**
     * @var Risk $risk
     */
    public $risk;

    /**
     * ExcludeRiskCommand constructor.
     *
     * @param GuidedSearchStep $guidedSearchStep
     * @param Risk $risk
     */
    public function __construct(GuidedSearchStep $guidedSearchStep, Risk $risk)
    {
        $this->guidedSearchStep = $guidedSearchStep;
        $this->risk = $risk;
    }
}
