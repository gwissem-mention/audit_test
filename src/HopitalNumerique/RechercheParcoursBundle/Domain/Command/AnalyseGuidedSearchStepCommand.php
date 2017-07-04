<?php

namespace HopitalNumerique\RechercheParcoursBundle\Domain\Command;

use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearchStep;

class AnalyseGuidedSearchStepCommand
{
    /**
     * @var GuidedSearchStep
     */
    public $guidedSearchStep;

    /**
     * AnalyseGuidedSearchStepCommand constructor.
     *
     * @param GuidedSearchStep $guidedSearchStep
     */
    public function __construct(GuidedSearchStep $guidedSearchStep)
    {
        $this->guidedSearchStep = $guidedSearchStep;
    }
}
