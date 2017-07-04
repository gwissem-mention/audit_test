<?php

namespace HopitalNumerique\RechercheParcoursBundle\Domain\Command;

use HopitalNumerique\ObjetBundle\Entity\Risk;
use Symfony\Component\Validator\Constraints as Assert;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearchStep;

class IncludeRiskCommand
{
    /**
     * @var GuidedSearchStep $guidedSearchStep
     */
    public $guidedSearchStep;

    /**
     * @var Risk $risk
     *
     * @Assert\NotNull
     */
    public $risk;

    /**
     * IncludeRiskCommand constructor.
     *
     * @param GuidedSearchStep $guidedSearchStep
     */
    public function __construct(GuidedSearchStep $guidedSearchStep)
    {
        $this->guidedSearchStep = $guidedSearchStep;
    }
}
