<?php

namespace HopitalNumerique\ObjetBundle\EventListener;

use HopitalNumerique\ObjetBundle\Events;
use HopitalNumerique\ObjetBundle\Event\NoteEvent;
use Nodevo\MailBundle\Manager\MailManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NotedSubscriber implements EventSubscriberInterface
{
    /**
     * @var MailManager
     */
    private $mailManager;

    /**
     * @var int
     */
    const LOWER_RATE = 3;

    /**
     * NotedSubscriber constructor.
     *
     * @param $mailManager
     */
    public function __construct(MailManager $mailManager)
    {
        $this->mailManager = $mailManager;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::OBJECT_NOTED => 'notifyDomainMaster'
        ];
    }

    /**
     * @param NoteEvent $note
     */
    public function notifyDomainMaster(NoteEvent $note)
    {
        if ($note->getNote()->getNote() < $this::LOWER_RATE) {
            $this->mailManager->sendNoteCommentaire($note->getNote());
        }
    }
}