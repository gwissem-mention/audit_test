<?php

namespace HopitalNumerique\ObjetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\ForumBundle\Entity\Board;

/**
 * Class RelatedBoard
 *
 * @ORM\Entity(repositoryClass="HopitalNumerique\ObjetBundle\Repository\RelatedBoardRepository")
 * @ORM\Table(name="hn_related_board")
 */
class RelatedBoard
{
    /**
     * @var Objet
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Objet", inversedBy="relatedBoards", cascade={"persist"})
     * @ORM\JoinColumn(referencedColumnName="obj_id")
     */
    protected $object;

    /**
     * @var Board
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\ForumBundle\Entity\Board", cascade={"persist"})
     */
    protected $board;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $position;

    /**
     * RelatedBoard constructor.
     *
     * @param Objet $object
     * @param Board $board
     * @param       $position
     */
    public function __construct(Objet $object, Board $board, $position = null)
    {
        $this->object = $object;
        $this->board = $board;
        $this->position = $position;
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
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     *
     * @return RelatedBoard
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }
}
