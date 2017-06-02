<?php

namespace HopitalNumerique\ObjetBundle\Domain\Command;

use HopitalNumerique\ObjetBundle\Entity\Objet;

/**
 * Class ReorderRelatedBoardsCommand
 */
class ReorderRelatedBoardsCommand
{
    /**
     * @var Objet
     */
    public $object;

    /**
     * @var array
     */
    public $boards;

    /**
     * ReorderRelatedBoardsCommand constructor.
     *
     * @param Objet $object
     * @param array $boards
     */
    public function __construct(Objet $object, $boards)
    {
        $this->object = $object;
        $this->boards = $boards;
    }
}
