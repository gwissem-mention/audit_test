<?php

namespace HopitalNumerique\ObjetBundle\Domain\Command;

use HopitalNumerique\ObjetBundle\Entity\Risk;

class DeleteRiskCommand
{
    /**
     * @var Risk $risk
     */
    public $risk;

    /**
     * DeleteRiskCommand constructor.
     *
     * @param Risk $risk
     */
    public function __construct(Risk $risk)
    {
        $this->risk = $risk;
    }
}
