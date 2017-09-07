<?php

namespace HopitalNumerique\RechercheParcoursBundle\DTO;

class RiskSynthesisDTO
{
    /**
     * @var RiskSynthesisRiskDTO[]
     */
    public $global;

    /**
     * @var RiskSynthesisRiskDTO[]
     */
    public $parents;

    /**
     * @var RiskSynthesisRiskDTO[][]
     */
    public $subReferences;
}
