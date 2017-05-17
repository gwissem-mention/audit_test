<?php

namespace HopitalNumerique\ForumBundle\Component\Dispatcher\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use CCDNForum\ForumBundle\Component\Dispatcher\ForumEvents;
use CCDNForum\ForumBundle\Component\Dispatcher\Event\AdminForumEvent;
use CCDNForum\ForumBundle\Component\Dispatcher\Event\AdminCategoryEvent;
use CCDNForum\ForumBundle\Component\Dispatcher\Event\AdminBoardEvent;
use CCDNForum\ForumBundle\Component\Dispatcher\Event\UserTopicEvent;
use CCDNForum\ForumBundle\Component\Dispatcher\Event\UserTopicFloodEvent;
use CCDNForum\ForumBundle\Component\Dispatcher\Event\UserPostEvent;
use CCDNForum\ForumBundle\Component\Dispatcher\Event\ModeratorTopicEvent;
use CCDNForum\ForumBundle\Component\Dispatcher\Event\ModeratorPostEvent;

/**
 * @category CCDNForum
 *
 * @author   Gaëtan MELCHILSEN
 * @license  Nodevo
 */
class FlashListener implements EventSubscriberInterface
{
    /**
     * @var \Symfony\Component\HttpFoundation\Session\Session
     */
    protected $session;

    /**
     * @param \Symfony\Component\HttpFoundation\Session\Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ForumEvents::ADMIN_FORUM_CREATE_COMPLETE => 'onForumCreateComplete',
            ForumEvents::ADMIN_FORUM_EDIT_COMPLETE => 'onForumEditComplete',
            ForumEvents::ADMIN_FORUM_DELETE_COMPLETE => 'onForumDeleteComplete',
            ForumEvents::ADMIN_CATEGORY_CREATE_COMPLETE => 'onCategoryCreateComplete',
            ForumEvents::ADMIN_CATEGORY_EDIT_COMPLETE => 'onCategoryEditComplete',
            ForumEvents::ADMIN_CATEGORY_DELETE_COMPLETE => 'onCategoryDeleteComplete',
            ForumEvents::ADMIN_BOARD_CREATE_COMPLETE => 'onBoardCreateComplete',
            ForumEvents::ADMIN_BOARD_EDIT_COMPLETE => 'onBoardEditComplete',
            ForumEvents::ADMIN_BOARD_DELETE_COMPLETE => 'onBoardDeleteComplete',
            ForumEvents::MODERATOR_TOPIC_SOFT_DELETE_COMPLETE => 'onTopicDeleteComplete',
            ForumEvents::MODERATOR_TOPIC_RESTORE_COMPLETE => 'onTopicRestoreComplete',
            ForumEvents::MODERATOR_TOPIC_STICKY_COMPLETE => 'onTopicStickyComplete',
            ForumEvents::MODERATOR_TOPIC_UNSTICKY_COMPLETE => 'onTopicUnstickyComplete',
            ForumEvents::MODERATOR_TOPIC_CLOSE_COMPLETE => 'onTopicCloseComplete',
            ForumEvents::MODERATOR_TOPIC_REOPEN_COMPLETE => 'onTopicReopenComplete',
            ForumEvents::MODERATOR_POST_RESTORE_COMPLETE => 'onPostRestoreComplete',
            ForumEvents::MODERATOR_POST_UNLOCK_COMPLETE => 'onPostUnlockComplete',
            ForumEvents::MODERATOR_POST_LOCK_COMPLETE => 'onPostLockComplete',
            ForumEvents::USER_TOPIC_CREATE_COMPLETE => 'onTopicCreateComplete',
            ForumEvents::USER_TOPIC_CREATE_FLOODED => 'onTopicCreateFlooded',
            ForumEvents::USER_TOPIC_REPLY_COMPLETE => 'onTopicReplyComplete',
            ForumEvents::USER_TOPIC_REPLY_FLOODED => 'onTopicReplyFlooded',
            ForumEvents::USER_POST_EDIT_COMPLETE => 'onPostEditComplete',
            ForumEvents::USER_POST_SOFT_DELETE_COMPLETE => 'onPostSoftDeleteComplete',
        ];
    }

    /**
     * @param \CCDNForum\ForumBundle\Component\Dispatcher\Event\AdminForumEvent $event
     */
    public function onForumCreateComplete(AdminForumEvent $event)
    {
        if ($event->getForum()) {
            if ($event->getForum()->getId()) {
                $this->session->getFlashBag()->add('success', 'Nouveau forum "' . $event->getForum()->getName() . '" créé.');
            }
        }
    }

    /**
     * @param \CCDNForum\ForumBundle\Component\Dispatcher\Event\AdminForumEvent $event
     */
    public function onForumEditComplete(AdminForumEvent $event)
    {
        if ($event->getForum()) {
            if ($event->getForum()->getId()) {
                $this->session->getFlashBag()->add('success', 'Forum "' . $event->getForum()->getName() . '" édité.');
            }
        }
    }

    /**
     * @param \CCDNForum\ForumBundle\Component\Dispatcher\Event\AdminForumEvent $event
     */
    public function onForumDeleteComplete(AdminForumEvent $event)
    {
        if ($event->getForum()) {
            if (!$event->getForum()->getId()) {
                $this->session->getFlashBag()->add('success', 'Forum "' . $event->getForum()->getName() . '" supprimé.');
            }
        }
    }

    /**
     * @param \CCDNForum\ForumBundle\Component\Dispatcher\Event\AdminCategoryEvent $event
     */
    public function onCategoryCreateComplete(AdminCategoryEvent $event)
    {
        if ($event->getCategory()) {
            if ($event->getCategory()->getId()) {
                $this->session->getFlashBag()->add('success', 'Nouvelle catégorie "' . $event->getCategory()->getName() . '" créée.');
            }
        }
    }

    /**
     * @param \CCDNForum\ForumBundle\Component\Dispatcher\Event\AdminCategoryEvent $event
     */
    public function onCategoryEditComplete(AdminCategoryEvent $event)
    {
        if ($event->getCategory()) {
            if ($event->getCategory()->getId()) {
                $this->session->getFlashBag()->add('success', 'Catégorie "' . $event->getCategory()->getName() . '" éditée.');
            }
        }
    }

    /**
     * @param \CCDNForum\ForumBundle\Component\Dispatcher\Event\AdminCategoryEvent $event
     */
    public function onCategoryDeleteComplete(AdminCategoryEvent $event)
    {
        if ($event->getCategory()) {
            if (!$event->getCategory()->getId()) {
                $this->session->getFlashBag()->add('success', 'Catégorie "' . $event->getCategory()->getName() . '" supprimée.');
            }
        }
    }

    /**
     * @param \CCDNForum\ForumBundle\Component\Dispatcher\Event\AdminBoardEvent $event
     */
    public function onBoardCreateComplete(AdminBoardEvent $event)
    {
        if ($event->getBoard()) {
            if ($event->getBoard()->getId()) {
                $this->session->getFlashBag()->add('success', 'Nouvelle thème "' . $event->getBoard()->getName() . '" créée.');
            }
        }
    }

    /**
     * @param \CCDNForum\ForumBundle\Component\Dispatcher\Event\AdminBoardEvent $event
     */
    public function onBoardEditComplete(AdminBoardEvent $event)
    {
        if ($event->getBoard()) {
            if ($event->getBoard()->getId()) {
                $this->session->getFlashBag()->add('success', 'Thème "' . $event->getBoard()->getName() . '" éditée.');
            }
        }
    }

    /**
     * @param \CCDNForum\ForumBundle\Component\Dispatcher\Event\AdminBoardEvent $event
     */
    public function onBoardDeleteComplete(AdminBoardEvent $event)
    {
        if ($event->getBoard()) {
            if (!$event->getBoard()->getId()) {
                $this->session->getFlashBag()->add('success', 'Thème "' . $event->getBoard()->getName() . '" supprimé.');
            }
        }
    }

    /**
     * @param \CCDNForum\ForumBundle\Component\Dispatcher\Event\ModeratorTopicEvent $event
     */
    public function onTopicDeleteComplete(ModeratorTopicEvent $event)
    {
        if ($event->getTopic()) {
            if ($event->getTopic()->getId()) {
                $this->session->getFlashBag()->add('success', 'Fil de discussion "' . $event->getTopic()->getId() . '" supprimé.');
            }
        }
    }

    /**
     * @param \CCDNForum\ForumBundle\Component\Dispatcher\Event\ModeratorTopicEvent $event
     */
    public function onTopicRestoreComplete(ModeratorTopicEvent $event)
    {
        if ($event->getTopic()) {
            if ($event->getTopic()->getId()) {
                $this->session->getFlashBag()->add('success', 'Fil de discussion "' . $event->getTopic()->getId() . '" réstoré.');
            }
        }
    }

    /**
     * @param \CCDNForum\ForumBundle\Component\Dispatcher\Event\ModeratorTopicEvent $event
     */
    public function onTopicStickyComplete(ModeratorTopicEvent $event)
    {
        if ($event->getTopic()) {
            if ($event->getTopic()->getId()) {
                $this->session->getFlashBag()->add('success', 'Vous suivez désormais le fil de discussion "' . $event->getTopic()->getId() . '".');
            }
        }
    }

    /**
     * @param \CCDNForum\ForumBundle\Component\Dispatcher\Event\ModeratorTopicEvent $event
     */
    public function onTopicUnstickyComplete(ModeratorTopicEvent $event)
    {
        if ($event->getTopic()) {
            if ($event->getTopic()->getId()) {
                $this->session->getFlashBag()->add('success', 'Vous ne suivez plus le fil de discussion "' . $event->getTopic()->getId() . '".');
            }
        }
    }

    /**
     * @param \CCDNForum\ForumBundle\Component\Dispatcher\Event\ModeratorTopicEvent $event
     */
    public function onTopicCloseComplete(ModeratorTopicEvent $event)
    {
        if ($event->getTopic()) {
            if ($event->getTopic()->getId()) {
                $this->session->getFlashBag()->add('success', 'Fil de discussion "' . $event->getTopic()->getId() . '" fermé.');
            }
        }
    }

    /**
     * @param \CCDNForum\ForumBundle\Component\Dispatcher\Event\ModeratorTopicEvent $event
     */
    public function onTopicReopenComplete(ModeratorTopicEvent $event)
    {
        if ($event->getTopic()) {
            if ($event->getTopic()->getId()) {
                $this->session->getFlashBag()->add('success', 'Fil de discussion "' . $event->getTopic()->getId() . '" ré-ouvert.');
            }
        }
    }

    /**
     * @param \CCDNForum\ForumBundle\Component\Dispatcher\Event\ModeratorPostEvent $event
     */
    public function onPostUnlockComplete(ModeratorPostEvent $event)
    {
        if ($event->getPost()) {
            if ($event->getPost()->getId()) {
                $this->session->getFlashBag()->add('success', 'Fil de discussion "' . $event->getPost()->getId() . '" dévérouillé.');
            }
        }
    }

    /**
     * @param \CCDNForum\ForumBundle\Component\Dispatcher\Event\ModeratorPostEvent $event
     */
    public function onPostRestoreComplete(ModeratorPostEvent $event)
    {
        if ($event->getPost()) {
            if ($event->getPost()->getId()) {
                $this->session->getFlashBag()->add('success', 'Fil de discussion "' . $event->getPost()->getId() . '" réstoré.');
            }
        }
    }

    /**
     * @param \CCDNForum\ForumBundle\Component\Dispatcher\Event\ModeratorPostEvent $event
     */
    public function onPostLockComplete(ModeratorPostEvent $event)
    {
        if ($event->getPost()) {
            if ($event->getPost()->getId()) {
                $this->session->getFlashBag()->add('success', 'Fil de discussion "' . $event->getPost()->getId() . '" vérouillé.');
            }
        }
    }

    /**
     * @param \CCDNForum\ForumBundle\Component\Dispatcher\Event\UserTopicEvent $event
     */
    public function onTopicCreateComplete(UserTopicEvent $event)
    {
        if ($event->getTopic()) {
            if ($event->getTopic()->getId()) {
                $this->session->getFlashBag()->add('success', 'Nouveau fil de discussion "' . $event->getTopic()->getTitle() . '" créé.');
            }
        }
    }

    /**
     * @param \CCDNForum\ForumBundle\Component\Dispatcher\Event\UserTopicFloodEvent $event
     */
    public function onTopicCreateFlooded(UserTopicFloodEvent $event)
    {
        $this->session->getFlashBag()->add('warning', 'Vous avez créé trop de fils de discussion en peu de temps, faîtes une pause.');
    }

    /**
     * @param \CCDNForum\ForumBundle\Component\Dispatcher\Event\UserTopicEvent $event
     */
    public function onTopicReplyComplete(UserTopicEvent $event)
    {
        if ($event->getTopic()) {
            if ($event->getTopic()->getId()) {
                $this->session->getFlashBag()->add('success', 'Nouvelle réponse au fil de discussion "' . $event->getTopic()->getTitle() . '".');
            }
        }
    }

    /**
     * @param \CCDNForum\ForumBundle\Component\Dispatcher\Event\UserTopicFloodEvent $event
     */
    public function onTopicReplyFlooded(UserTopicFloodEvent $event)
    {
        $this->session->getFlashBag()->add('warning', 'Vous avez posté trop de réponses en peu de temps, faîtes une pause.');
    }

    /**
     * @param \CCDNForum\ForumBundle\Component\Dispatcher\Event\UserPostEvent $event
     */
    public function onPostEditComplete(UserPostEvent $event)
    {
        if ($event->getPost()) {
            if ($event->getPost()->getId()) {
                $this->session->getFlashBag()->add('success', 'Message "' . $event->getPost()->getId() . '" édité.');
            }
        }
    }

    /**
     * @param \CCDNForum\ForumBundle\Component\Dispatcher\Event\UserPostEvent $event
     */
    public function onPostSoftDeleteComplete(UserPostEvent $event)
    {
        if ($event->getPost()) {
            if ($event->getPost()->getId()) {
                $this->session->getFlashBag()->add('success', 'Message "' . $event->getPost()->getId() . '" supprimé.');
            }
        }
    }
}
