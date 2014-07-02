<?php
namespace HopitalNumerique\ForumBundle\Controller;

use CCDNForum\ForumBundle\Controller\UserTopicController as UserTopicControllerCCDN;

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
class UserTopicController extends UserTopicControllerCCDN
{
    /**
     *
     * @access public
     * @param  string                          $forumName
     * @param  int                             $topicId
     * @return RedirectResponse|RenderResponse
     */
    public function showAction($forumName, $topicId)
    {
        $this->isFound($forum = $this->getForumModel()->findOneForumByName($forumName));
        $this->isFound($topic = $this->getTopicModel()->findOneTopicByIdWithBoardAndCategory($topicId, true));
        $this->isAuthorised($this->getAuthorizer()->canShowTopic($topic, $forum));
        $postsPager = $this->getPostModel()->findAllPostsPaginatedByTopicId($topicId, $this->getQuery('page', 1), $this->getPageHelper()->getPostsPerPageOnTopics(), true);

        if ($this->isGranted('ROLE_USER')) {
            if ($subscription = $this->getSubscriptionModel()->findOneSubscriptionForTopicByIdAndUserById($topicId, $this->getUser()->getId())) {
                $this->getSubscriptionModel()->markAsRead($subscription);
            }
        } else {
            $subscription = null;
        }

        //get ponderations
        $refsPonderees = $this->container->get('hopitalnumerique_reference.manager.reference')->getReferencesPonderees();
        $note          = $this->container->get('hopitalnumerique_objet.manager.objet')->getNoteReferencement( $topic->getReferences(), $refsPonderees );

        $subscriberCount = $this->getSubscriptionModel()->countSubscriptionsForTopicById($topicId);
        $this->getTopicModel()->incrementViewCounter($topic);
        $response = $this->renderResponse('CCDNForumForumBundle:User:Topic/show.html.', array(
            'crumbs'    => $this->getCrumbs()->addUserTopicShow($forum, $topic), 'forum' => $forum, 'topic' => $topic,
            'forumName' => $forumName,
            'pager'     => $postsPager, 'subscription' => $subscription, 'subscription_count' => $subscriberCount,
            'note'      => $note
        ));

        return $response;
    }
}
