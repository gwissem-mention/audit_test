<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Manager;

use Doctrine\ORM\EntityManager;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Commentaire;
use HopitalNumerique\CommunautePratiqueBundle\Event\CommentCreatedEvent;
use HopitalNumerique\CommunautePratiqueBundle\Events;
use Nodevo\MailBundle\Manager\MailManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Manager de Commentaire.
 */
class CommentaireManager extends \Nodevo\ToolsBundle\Manager\Manager
{
    protected $class = 'HopitalNumerique\CommunautePratiqueBundle\Entity\Commentaire';

    protected $mailManager;

    /**
     * @var EventDispatcherInterface $eventDispatcher
     */
    protected $eventDispatcher;

    /**
     * CommentaireManager constructor.
     *
     * @param EntityManager            $em
     * @param MailManager              $mailManager
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EntityManager $em, MailManager $mailManager, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($em);

        $this->mailManager = $mailManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Retourne true si le document n'est appelé dans aucun commentaire.
     *
     * @return bool
     */
    public function safeDelete($document)
    {
        $result = $this->getRepository()->safeDelete($document)->getQuery()->getResult();

        if (!empty($result)) {
            return false;
        }

        return true;
    }

    /**
     * @param Commentaire $commentaire
     */
    public function save($commentaire)
    {
        // Send email only if creation
        $sendmail = (null !== $commentaire->getId()) ? false : true;

        parent::save($commentaire);

        /**
         * Fire 'GROUP_COMMENT_CREATED' or 'FORM_COMMENT_CREATED' event
         */
        if (null === $commentaire->getFiche()) {
            $eventCode = Events::GROUP_COMMENT_CREATED;
        } else {
            $eventCode = Events::FORM_COMMENT_CREATED;
        }

        $event = new CommentCreatedEvent($commentaire);
        $this->eventDispatcher->dispatch($eventCode, $event);

        if ($sendmail) {
            $this->mailManager->sendCMCommentaireMail($commentaire);
        }
    }
}
