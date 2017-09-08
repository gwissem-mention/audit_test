<?php

namespace HopitalNumerique\ObjetBundle\Domain\Command\RelatedRisk;

use HopitalNumerique\ObjetBundle\Entity\Objet;

/**
 * Class LinkBoardToObjectCommand
 */
class LinkRisksToObjectCommand
{
    /**
     * @var Objet $object
     */
    public $object;

    /**
     * @var array $risksId
     */
    public $risksId;

    /**
     * LinkRisksToObjectCommand constructor.
     *
     * @param Objet $object
     * @param array $risksId
     */
    public function __construct($object, $risksId)
    {
        $this->object = $object;
        $this->risksId = $risksId;
    }
}
