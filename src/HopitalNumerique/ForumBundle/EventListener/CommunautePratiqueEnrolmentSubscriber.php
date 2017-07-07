<?php

namespace HopitalNumerique\ForumBundle\EventListener;

use HopitalNumerique\CommunautePratiqueBundle\Events;
use HopitalNumerique\CommunautePratiqueBundle\Event\EnrolmentEvent;
use HopitalNumerique\ForumBundle\Model\FrontModel\SubscriptionModel;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CommunautePratiqueEnrolmentSubscriber implements EventSubscriberInterface
{
    /**
     * @var SubscriptionModel
     */
    protected $subscriptionModel;

    /**
     * @param SubscriptionModel $subscriptionManager
     */
    public function __construct(SubscriptionModel $subscriptionManager)
    {
        $this->subscriptionModel = $subscriptionManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::ENROLL_USER => 'enrollUser',
        ];
    }

    /**
     * Called when a user join communaute de pratique.
     * Automatically subscribe to all CDP boards of his domains
     *
     * @param EnrolmentEvent $event
     */
    public function enrollUser(EnrolmentEvent $event)
    {
        $user = $event->getUser();
        $boards = $this->getBoards($user);

        foreach ($boards as $board) {
            $this->subscriptionModel->subscribeBoard($board, $user);
        }
    }

    /**
     * @param User $user
     *
     * @return mixed
     */
    private function getBoards(User $user)
    {
        $boards = [];
        foreach ($user->getDomaines() as $domaine) {
            $categories = $domaine->getCommunautePratiqueForumCategories();
            foreach ($categories as $category) {
                $categoryBoards = $category->getBoards();
                foreach ($categoryBoards as $board) {
                    $boards[$board->getId()] = $board;
                }
            }
        }

        return $boards;
    }
}
