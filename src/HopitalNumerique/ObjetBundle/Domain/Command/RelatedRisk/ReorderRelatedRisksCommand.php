<?php

namespace HopitalNumerique\ObjetBundle\Domain\Command\RelatedRisk;

use HopitalNumerique\ObjetBundle\Entity\Objet;

/**
 * Class ReorderRelatedRisksCommand
 */
class ReorderRelatedRisksCommand
{
    /**
     * @var Objet
     */
    public $object;

    /**
     * @var array
     */
    public $risks;

    /**
     * ReorderRelatedRisksCommand constructor.
     *
     * @param Objet $object
     * @param array $risks
     */
    public function __construct(Objet $object, $risks)
    {
        $this->object = $object;
        $this->risks = $risks;
    }
}
