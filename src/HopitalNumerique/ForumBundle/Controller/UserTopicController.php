<?php
namespace HopitalNumerique\ForumBundle\Controller;

use CCDNForum\ForumBundle\Controller\UserTopicController as UserTopicControllerCCDN;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 * @category CCDNForum
 * @package  ForumBundle
 *
 * @author   Gaëtan MELCHILSEN
 * @license  Nodevo
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

        //Récupération de l'ensemble des boards pour le déplacement des posts
        $boards = array();
        foreach ($forum->getCategories() as $tempCategory) 
        {
            foreach ($tempCategory->getBoards() as $tempBoard) 
            {
                $boards[] = $tempBoard;
            }
        }

        //get ponderations
        $refsPonderees = $this->container->get('hopitalnumerique_reference.manager.reference')->getReferencesPonderees();
        $note          = $this->container->get('hopitalnumerique_objet.manager.objet')->getNoteReferencement( $topic->getReferences(), $refsPonderees );

        $subscriberCount = $this->getSubscriptionModel()->countSubscriptionsForTopicById($topicId);
        $this->getTopicModel()->incrementViewCounter($topic);
        $response = $this->renderResponse('CCDNForumForumBundle:User:Topic/show.html.', array(
            'crumbs'             => $this->getCrumbs()->addUserTopicShow($forum, $topic), 
            'forum'              => $forum, 
            'topic'              => $topic,
            'forumName'          => $forumName,
            'pager'              => $postsPager, 
            'subscription'       => $subscription, 
            'subscription_count' => $subscriberCount,
            'note'               => $note,
            'boards'             => $boards
        ));

        return $response;
    }
    
    /**
     *
     * @access public
     * @param  string                          $forumName
     * @param  int                             $boardId
     * @return RedirectResponse|RenderResponse
     */
    public function createAction($forumName, $boardId)
    {
        //<-- Alerte si on n'est pas connecté
        if (!$this->isGranted('ROLE_USER'))
            $this->container->get('session')->getFlashBag()->add('warning', 'Vous devez vous identifier pour créer un fil de discussion.');
        //-->
    
        return parent::createAction($forumName, $boardId);
    }
    
    /**
     *
     * @access public
     * @param  string                          $forumName
     * @param  int                             $topicId
     * @return RedirectResponse|RenderResponse
     */
    public function replyAction($forumName, $topicId)
    {
        //<-- Alerte si on n'est pas connecté
        if (!$this->isGranted('ROLE_USER'))
            $this->container->get('session')->getFlashBag()->add('warning', 'Vous devez vous identifier pour poster une réponse.');
        //-->
        
        return parent::replyAction($forumName, $topicId);
    }

    /**
     * Permet de déplacer un post vers un autre topic
     *
     * @param Request $request [description]
     * @param int     $postId [description]
     *
     * @return Response
     */
    public function deplacerTopicAction(Request $request, $topicId)
    {
        $topic              = $this->getTopicModel()->findOneTopicByIdWithBoardAndCategory($topicId, true);
        $boardDestinationId = $request->request->get('boardId');
        $boardDestination   = $this->getBoardModel()->findOneBoardById($boardDestinationId);
        $boardOrigine       = $topic->getBoard();
        $topic->setBoard($boardDestination);
        $this->container->get('hopitalnumerique_forum.manager.topic')->save( $topic );

        //Mise à jour du Board de destination
        $stats = $this->getTopicModel()->getTopicAndPostCountForBoardById($boardDestination->getId());
        // set the board topic / post count
        $boardDestination->setCachedTopicCount($stats['topicCount']);
        $this->container->get('hopitalnumerique_forum.manager.board')->save( $boardDestination );

        //Mise à jour du Board d'origine
        $stats = $this->getTopicModel()->getTopicAndPostCountForBoardById($boardOrigine->getId());
        // set the board topic / post count
        $boardOrigine->setCachedTopicCount($stats['topicCount']);
        $this->container->get('hopitalnumerique_forum.manager.board')->save( $boardOrigine );

        return new Response('{"success":true}', 200);
    }
}
