<?php

namespace HopitalNumerique\ForumBundle\Entity;

use CCDNForum\ForumBundle\Entity\Subscription as BaseSubscription;

/**
 * 
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class Subscription extends BaseSubscription
{
    /**
     * @var \HopitalNumerique\ForumBundle\Entity\Board Board
     */
    private $board = null;

    /**
     * Get board
     *
     * @return Board
     */
    public function getBoard()
    {
        return $this->board;
    }
    
    /**
     * Set board
     *
     * @param  Board        $board
     * @return Subscription
     */
    public function setBoard(Board $board = null)
    {
        $this->board = $board;
    
        return $this;
    }
    
}
