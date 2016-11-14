<?php

namespace HopitalNumerique\AutodiagBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class DataEvent extends Event
{
    /**
     * @var AutodiagEntry
     */
    protected $data;

    public function __construct($data = [])
    {
        $this->values = $data;
    }

    public function getData()
    {
        return $this->data;
    }
}
