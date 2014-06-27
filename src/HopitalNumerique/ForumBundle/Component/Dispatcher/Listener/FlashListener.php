<?php

namespace HopitalNumerique\ForumBundle\Component\Dispatcher\Listener;

use Symfony\Component\EventDispatcher\Event;
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
 *
 * @category CCDNForum
 * @package  ForumBundle
 *
 * @author   Reece Fowell <reece@codeconsortium.com>
 * @license  http://opensource.org/licenses/MIT MIT
 * @version  Release: 2.0
 * @link     https://github.com/codeconsortium/CCDNForumForumBundle
 *
 */
class FlashListener implements EventSubscriberInterface
{
    /**
     *
     * @access private
     * @var \Symfony\Component\HttpFoundation\Session\Session $session
     */
    protected $session;

    /**
     *
     * @access public
     * @param \Symfony\Component\HttpFoundation\Session\Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     *
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            ForumEvents::ADMIN_FORUM_CREATE_COMPLETE          => 'onForumCreateComplete',
            ForumEvents::ADMIN_FORUM_EDIT_COMPLETE            => 'onForumEditComplete',
            ForumEvents::ADMIN_FORUM_DELETE_COMPLETE          => 'onForumDeleteComplete',
            ForumEvents::ADMIN_CATEGORY_CREATE_COMPLETE       => 'onCategoryCreateComplete',
            ForumEvents::ADMIN_CATEGORY_EDIT_COMPLETE         => 'onCategoryEditComplete',
            ForumEvents::ADMIN_CATEGORY_DELETE_COMPLETE       => 'onCategoryDeleteComplete',
            ForumEvents::ADMIN_BOARD_CREATE_COMPLETE          => 'onBoardCreateComplete',
            ForumEvents::ADMIN_BOARD_EDIT_COMPLETE            => 'onBoardEditComplete',
            ForumEvents::ADMIN_BOARD_DELETE_COMPLETE          => 'onBoardDeleteComplete',
            ForumEvents::MODERATOR_TOPIC_SOFT_DELETE_COMPLETE => 'onTopicDeleteComplete',
            ForumEvents::MODERATOR_TOPIC_RESTORE_COMPLETE     => 'onTopicRestoreComplete',
            ForumEvents::MODERATOR_TOPIC_STICKY_COMPLETE      => 'onTopicStickyComplete',
            ForumEvents::MODERATOR_TOPIC_UNSTICKY_COMPLETE    => 'onTopicUnstickyComplete',
            ForumEvents::MODERATOR_TOPIC_CLOSE_COMPLETE       => 'onTopicCloseComplete',
            ForumEvents::MODERATOR_TOPIC_REOPEN_COMPLETE      => 'onTopicReopenComplete',
            ForumEvents::MODERATOR_POST_RESTORE_COMPLETE      => 'onPostRestoreComplete',
            ForumEvents::MODERATOR_POST_UNLOCK_COMPLETE       => 'onPostUnlockComplete',
            ForumEvents::MODERATOR_POST_LOCK_COMPLETE         => 'onPostLockComplete',
            ForumEvents::USER_TOPIC_CREATE_COMPLETE           => 'onTopicCreateComplete',
            ForumEvents::USER_TOPIC_CREATE_FLOODED            => 'onTopicCreateFlooded',
            ForumEvents::USER_TOPIC_REPLY_COMPLETE            => 'onTopicReplyComplete',
            ForumEvents::USER_TOPIC_REPLY_FLOODED             => 'onTopicReplyFlooded',
            ForumEvents::USER_POST_EDIT_COMPLETE              => 'onPostEditComplete',
            ForumEvents::USER_POST_SOFT_DELETE_COMPLETE       => 'onPostSoftDeleteComplete',
        );
    }

    /**
     *
     * @access public
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
     *
     * @access public
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
     *
     * @access public
     * @param \CCDNForum\ForumBundle\Component\Dispatcher\Event\AdminForumEvent $event
     */
    public function onForumDeleteComplete(AdminForumEvent $event)
    {
        if ($event->getForum()) {
            if (! $event->getForum()->getId()) {
                $this->session->getFlashBag()->add('success', 'Forum "' . $event->getForum()->getName() . '" supprimé.');
            }
        }
    }

    /**
     *
     * @access public
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
     *
     * @access public
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
     *
     * @access public
     * @param \CCDNForum\ForumBundle\Component\Dispatcher\Event\AdminCategoryEvent $event
     */
    public function onCategoryDeleteComplete(AdminCategoryEvent $event)
    {
        if ($event->getCategory()) {
            if (! $event->getCategory()->getId()) {
                $this->session->getFlashBag()->add('success', 'Catégorie "' . $event->getCategory()->getName() . '" supprimée.');
            }
        }
    }

    /**
     *
     * @access public
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
     *
     * @access public
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
     *
     * @access public
     * @param \CCDNForum\ForumBundle\Component\Dispatcher\Event\AdminBoardEvent $event
     */
    public function onBoardDeleteComplete(AdminBoardEvent $event)
    {
        if ($event->getBoard()) {
            if (! $event->getBoard()->getId()) {
                $this->session->getFlashBag()->add('success', 'Thème "' . $event->getBoard()->getName() . '" supprimée.');
            }
        }
    }

    /**
     *
     * @access public
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
     *
     * @access public
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
     *
     * @access public
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
     *
     * @access public
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
     *
     * @access public
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
     *
     * @access public
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
     *
     * @access public
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
     *
     * @access public
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
     *
     * @access public
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
     *
     * @access public
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
     *
     * @access public
     * @param \CCDNForum\ForumBundle\Component\Dispatcher\Event\UserTopicFloodEvent $event
     */
    public function onTopicCreateFlooded(UserTopicFloodEvent $event)
    {
        $this->session->getFlashBag()->add('warning', 'Vous avez créé trop de fils de discussion en peu de temps, faîtes une pause.');
    }

    /**
     *
     * @access public
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
     *
     * @access public
     * @param \CCDNForum\ForumBundle\Component\Dispatcher\Event\UserTopicFloodEvent $event
     */
    public function onTopicReplyFlooded(UserTopicFloodEvent $event)
    {
        $this->session->getFlashBag()->add('warning', 'Vous avez posté trop de réponses en peu de temps, faîtes une pause.');
    }

    /**
     *
     * @access public
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
     *
     * @access public
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
