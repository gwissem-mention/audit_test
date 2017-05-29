<?php

namespace HopitalNumerique\ObjetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\ForumBundle\Entity\Board;

/***
 * Class RelatedBoard
 *
 * @ORM\Entity
 * @ORM\Table(name="hn_related_board")
 */
class RelatedBoard
{
    /**
     * @var Objet
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\ObjetBundle\Entity\Objet", inversedBy="relatedBoards")
     */
    protected $object;

    /**
     * @var Board
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\ForumBundle\Entity\Board")
     */
    protected $board;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $order;

    /**
     * RelatedBoard constructor.
     *
     * @param Objet $object
     * @param Board $board
     * @param       $order
     */
    public function __construct(Objet $object, Board $board, $order)
    {
        $this->object = $object;
        $this->board = $board;
        $this->order = $order;
    }

    /**
     * @return Objet
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param Objet $object
     *
     * @return RelatedBoard
     */
    public function setObject($object)
    {
        $this->object = $object;

        return $this;
    }

    /**
     * @return Board
     */
    public function getBoard()
    {
        return $this->board;
    }

    /**
     * @param Board $board
     *
     * @return RelatedBoard
     */
    public function setBoard($board)
    {
        $this->board = $board;

        return $this;
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param int $order
     *
     * @return RelatedBoard
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }
}
