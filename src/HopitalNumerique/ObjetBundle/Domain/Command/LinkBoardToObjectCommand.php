<?php

namespace HopitalNumerique\ObjetBundle\Domain\Command;

/**
 * Class LinkBoardToObjectCommand
 */
class LinkBoardToObjectCommand
{
    /**
     * @var integer
     */
    public $objectId;

    /**
     * @var array
     */
    public $boardsId;

    /**
     * LinkBoardToObjectCommand constructor.
     *
     * @param $objectId
     * @param $boardsId
     */
    public function __construct($objectId, $boardsId)
    {
        $this->objectId = $objectId;
        $this->boardsId = $boardsId;
    }
}
