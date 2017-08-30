<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Event;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Commentaire;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class CommentCreatedEvent.
 */
class CommentCreatedEvent extends Event
{

    /**
     * @var Commentaire $comment
     */
    protected $comment;

    /**
     * GroupCommentCreatedEvent constructor.
     *
     * @param Commentaire $comment
     */
    public function __construct(Commentaire $comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return Commentaire
     */
    public function getComment()
    {
        return $this->comment;
    }
}
