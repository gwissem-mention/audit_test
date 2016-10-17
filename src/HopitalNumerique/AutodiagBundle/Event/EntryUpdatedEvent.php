<?php

namespace HopitalNumerique\AutodiagBundle\Event;

use HopitalNumerique\AutodiagBundle\Entity\AutodiagEntry;
use Symfony\Component\EventDispatcher\Event;

class EntryUpdatedEvent extends Event
{
    /**
     * @var AutodiagEntry
     */
    protected $entry;

    /**
     * @var AutodiagEntry\Value[]
     */
    protected $values;

    public function __construct(AutodiagEntry $entry, $values = [])
    {
        $this->entry = $entry;
        $this->values = $values;
    }

    public function getEntry()
    {
        return $this->entry;
    }

    public function getValues()
    {
        return $this->values;
    }
}
