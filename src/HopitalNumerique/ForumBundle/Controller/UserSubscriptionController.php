<?php

namespace HopitalNumerique\ForumBundle\Controller;

use CCDNForum\ForumBundle\Controller\UserSubscriptionController as CCDNUserSubscriptionController;
use CCDNForum\ForumBundle\Component\Dispatcher\ForumEvents;
use CCDNForum\ForumBundle\Component\Dispatcher\Event\UserTopicEvent;
use HopitalNumerique\ForumBundle\Entity\Board;

/**
 * UserSubscriptionController
 * 
 * @category CCDNForum
 * @package  ForumBundle
 *
 * @author   Gaëtan MELCHILSEN
 * @license  Nodevo
 *
 */
class UserSubscriptionController extends CCDNUserSubscriptionController
{
    /**
     *
     * @access public
     * @param  string         $forumName
     * @return RenderResponse
     */
    public function indexAction($forumName)
    {
        $this->isAuthorised('ROLE_USER');

        if ($forumName != '~') {
            $this->isFound($forum = $this->getForumModel()->findOneForumByName($forumName));
        } else {
            $forum = null;
        }

        $page = $this->getQuery('page', 1);
        $filter = $this->getQuery('filter', 'all');
        // Use this for the sidebar counters
        $subscriptionForums = $this->getSubscriptionModel()->findAllSubscriptionsForUserById($this->getUser()->getId(), true);
        $forumsSubscribed = array();
        $totalForumsSubscribed = array('count_read' => 0, 'count_unread' => 0, 'count_total' => 0);
        foreach ($subscriptionForums as $subscription) 
        {
            if( $subscription->isSubscribed() )
            {
                $forumSubscribed = $subscription->getForum();

                if ($forumSubscribed) {
                    $forumSubscribedId = $forumSubscribed->getId();

                    if (! array_key_exists($forumSubscribedId, $forumsSubscribed)) {
                        $forumsSubscribed[$forumSubscribedId] = array(
                            'forum' => $forumSubscribed,
                            'count_read' => 0,
                            'count_unread' => 0,
                            'count_total' => 0,
                        );
                    }

                    $forumsSubscribed[$forumSubscribedId]['count_total']++;
                    if ($subscription->isRead()) {
                        $forumsSubscribed[$forumSubscribedId]['count_read']++;
                    } else {
                        $forumsSubscribed[$forumSubscribedId]['count_unread']++;
                    }

                    if ($forum) {
                        if ($forum->getId() == $forumSubscribedId) {
                            $totalForumsSubscribed['count_total']++;
                            if ($subscription->isRead()) {
                                $totalForumsSubscribed['count_read']++;
                            } else {
                                $totalForumsSubscribed['count_unread']++;
                            }
                        }
                    } else {
                        $totalForumsSubscribed['count_total']++;
                        if ($subscription->isRead()) {
                            $totalForumsSubscribed['count_read']++;
                        } else {
                            $totalForumsSubscribed['count_unread']++;
                        }
                    }
                }
            }
        }

        // Use this for the ALL/READ/UNREAD tab
        $itemsPerPage = $this->getPageHelper()->getTopicsPerPageOnSubscriptions();
        if ($forumName == '~') {
            $subscriptionPager = $this->getSubscriptionModel()->findAllSubscriptionsPaginatedForUserById($this->getUser()->getId(), $page, $itemsPerPage, $filter, true);
        } else {
            $subscriptionPager = $this->getSubscriptionModel()->findAllSubscriptionsPaginatedForUserByIdAndForumById($forum->getId(), $this->getUser()->getId(), $page, $itemsPerPage, $filter, true);
        }

        return $this->renderResponse('CCDNForumForumBundle:User:Subscription/show.html.', array(
            'forum' => $forum,
            'forumName' => $forumName,
            'subscribed_forums' => $forumsSubscribed,
            'total_subscribed_forums' => $totalForumsSubscribed,
            'filter' => $filter,
            'pager' => $subscriptionPager,
            'posts_per_page' => $this->container->getParameter('ccdn_forum_forum.topic.user.show.posts_per_page')
        ));
    }
    
    /**
     *
     * @access public
     * @param  string           $forumName
     * @param  \HopitalNumerique\ForumBundle\Entity\Board $board
     * @return RedirectResponse
     */
    public function subscribeBoardAction($forumName, Board $board)
    {
        $this->isAuthorised('ROLE_USER');
    
        if ($forumName != '~') {
            $this->isFound($forum = $this->getForumModel()->findOneForumByName($forumName));
        } else {
            $forum = null;
        }
    
        $this->isAuthorised($this->getAuthorizer()->canSubscribeToBoard($board, $forum));
        $this->getSubscriptionModel()->subscribeBoard($board, $this->getUser())->flush();
        // Suppression du dispatch car seulement pour un topic

        return $this->redirectResponse($this->path('ccdn_forum_user_board_show', array(
            'forumName' => $forumName,
            'boardId' => $board->getId()
        )));
    }
    
    /**
     *
     * @access public
     * @param  string           $forumName
     * @param  \HopitalNumerique\ForumBundle\Entity\Board $board
     * @return RedirectResponse
     */
    public function unsubscribeBoardAction($forumName, Board $board)
    {
        $this->isAuthorised('ROLE_USER');
    
        if ($forumName != '~') {
            $this->isFound($forum = $this->getForumModel()->findOneForumByName($forumName));
        } else {
            $forum = null;
        }

        $subscription = $this->getSubscriptionModel()->findOneSubscriptionForBoardAndUser($board, $this->getUser());
        $this->isAuthorised($this->getAuthorizer()->canUnsubscribeFromBoard($board, $forum, $subscription));
        $this->getSubscriptionModel()->unsubscribeBoard($board, $this->getUser())->flush();
        // Suppression du dispatch car seulement pour un topic

        return $this->redirectResponse($this->path('ccdn_forum_user_board_show', array(
            'forumName' => $forumName,
            'boardId' => $board->getId()
        )));
    }
}
