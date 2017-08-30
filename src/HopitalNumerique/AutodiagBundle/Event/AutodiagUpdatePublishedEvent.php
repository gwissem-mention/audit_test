<?php

namespace HopitalNumerique\AutodiagBundle\Event;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class AutodiagUpdatePublishedEvent.
 */
class AutodiagUpdatePublishedEvent extends Event
{

    /**
     * @var Autodiag $autodiag
     */
    protected $autodiag;

    /**
     * @var string $reason
     */
    protected $reason;

    /**
     * AutodiagUpdatePublishedEvent constructor.
     *
     * @param Autodiag $autodiag
     * @param string   $reason
     */
    public function __construct(Autodiag $autodiag, $reason)
    {
        $this->autodiag = $autodiag;
        $this->reason = $reason;
    }

    /**
     * @return Autodiag
     */
    public function getAutodiag()
    {
        return $this->autodiag;
    }

    /**
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }
}
