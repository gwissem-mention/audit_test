<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Event\Discussion;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Message;
use Symfony\Component\EventDispatcher\Event;

class MessageCreatedEvent extends MessagePostedEvent
{
}
