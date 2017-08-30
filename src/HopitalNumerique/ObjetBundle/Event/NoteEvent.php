<?php

namespace HopitalNumerique\ObjetBundle\Event;


use HopitalNumerique\ObjetBundle\Entity\Note;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class NoteEvent
 */
class NoteEvent extends Event
{
    /**
     * @var Note
     */
    protected $note;

    /**
     * NoteEvent constructor.
     *
     * @param $note
     */
    public function __construct($note)
    {
        $this->note = $note;
    }

    /**
     * @return Note
     */
    public function getNote()
    {
        return $this->note;
    }
}