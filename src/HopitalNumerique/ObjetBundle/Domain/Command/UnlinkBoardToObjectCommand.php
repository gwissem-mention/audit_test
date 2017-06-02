<?php

namespace HopitalNumerique\ObjetBundle\Domain\Command;

use HopitalNumerique\ForumBundle\Entity\Board;
use HopitalNumerique\ObjetBundle\Entity\Objet;

/**
 * Class UnlinkBoardToObjectCommand
 */
class UnlinkBoardToObjectCommand
{
    /**
     * @var Objet
     */
    public $object;

    /**
     * @var Board
     */
    public $board;

    /**
     * UnlinkBoardToObjectCommand constructor.
     *
     * @param Objet $object
     * @param Board $board
     */
    public function __construct(Objet $object, Board $board)
    {
        $this->object = $object;
        $this->board = $board;
    }
}
