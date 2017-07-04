<?php

namespace HopitalNumerique\ObjetBundle\Domain\Command\RelatedRisk;

use HopitalNumerique\ObjetBundle\Entity\Risk;
use HopitalNumerique\ObjetBundle\Entity\Objet;

/**
 * Class UnlinkRiskToObjectCommand
 */
class UnlinkRiskToObjectCommand
{
    /**
     * @var Objet
     */
    public $object;

    /**
     * @var Risk
     */
    public $risk;

    /**
     * UnlinkRiskToObjectCommand constructor.
     *
     * @param Objet $object
     * @param Risk $risk
     */
    public function __construct(Objet $object, Risk $risk)
    {
        $this->object = $object;
        $this->risk = $risk;
    }
}
