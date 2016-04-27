<?php
namespace HopitalNumerique\ForumBundle\Controller;

use CCDNForum\ForumBundle\Controller\UserBoardController as BaseUserBoardController;

class UserBoardController extends BaseUserBoardController
{
    /**
     *
     * @access public
     * @param  string                          $forumName
     * @param  int                             $boardId
     * @return RedirectResponse|RenderResponse
     */
    public function showAction($forumName, $boardId)
    {
        $this->isFound($forum = $this->getForumModel()->findOneForumByName($forumName));
        $this->isFound($board = $this->getBoardModel()->findOneBoardByIdWithCategory($boardId));
        $this->isAuthorised($this->getAuthorizer()->canShowBoard($board, $forum));
        $itemsPerPage = $this->getPageHelper()->getTopicsPerPageOnBoards();
        $stickyTopics = $this->getTopicModel()->findAllTopicsStickiedByBoardId($boardId, true);
        $topicsPager = $this->getTopicModel()->findAllTopicsPaginatedByBoardId($boardId, $this->getQuery('page', 1), $itemsPerPage, true);

        if ($this->isGranted('ROLE_USER')) {
            if ($subscription = $this->getSubscriptionModel()->findOneSubscriptionForBoardAndUser($board, $this->getUser())) {
                $this->getSubscriptionModel()->markAsRead($subscription);
            }
        } else {
            $subscription = null;
        }
        return $this->renderResponse('CCDNForumForumBundle:User:Board/show.html.', array(
            'crumbs' => $this->getCrumbs()->addUserBoardShow($forum, $board),
            'forum' => $forum,
            'forumName' => $forumName,
            'board' => $board,
            'pager' => $topicsPager,
            'subscription' => $subscription,
            'posts_per_page' => $this->container->getParameter('ccdn_forum_forum.topic.user.show.posts_per_page'), // for working out last page per topic.
            'sticky_topics' => $stickyTopics,
        ));
    }
}
