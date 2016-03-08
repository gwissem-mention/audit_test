<?php
namespace HopitalNumerique\ForumBundle\Controller;

use CCDNForum\ForumBundle\Controller\UserTopicController as UserTopicControllerCCDN;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use HopitalNumerique\ForumBundle\Entity\Board;
use Symfony\Component\Security\Core\User\UserInterface;

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

        $subscription = null;
        if ($this->isGranted('ROLE_USER'))
        {
            if ($subscription = $this->getSubscriptionModel()->findOneSubscriptionForTopicByIdAndUserById($topicId, $this->getUser()->getId()))
            {
                $this->getSubscriptionModel()->markAsRead($subscription);
            }
        }

        //Récupération de l'ensemble des boards pour le déplacement des posts
        $boards = $this->container->get('hopitalnumerique_forum.manager.board')->findAllClassifiedByCategoryClassifiedByForum();

        //get ponderations
        $domainesDuForum = $this->container->get('hopitalnumerique_domaine.manager.domaine')->getDomaineForForumId($forum->getId());
        $domainesIds     = array();
        foreach ($domainesDuForum as $domaine)
        {
            $domainesIds[] = $domaine->getId();
        }
        $refsPonderees = $this->container->get('hopitalnumerique_reference.manager.reference')->getReferencesPonderees($domainesIds);
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
            'isSubscriptionBoard' => $this->isSubscriptionBoard($topic->getBoard()),
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
    
    public function createProcessAction($forumName, $boardId)
    {
        $this->container->get('hopitalnumerique_forum.service.piece_jointe')->verifyPieceJointe();
    
        return parent::createProcessAction($forumName, $boardId);
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

    public function replyProcessAction($forumName, $topicId)
    {
        $this->container->get('hopitalnumerique_forum.service.piece_jointe')->verifyPieceJointe();

        return parent::replyProcessAction($forumName, $topicId);
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


        //<-- Màj des derniers POST
        $boardDestinationDernierTopic = $this->getTopicModel()->findLastTopicForBoardByIdWithLastPost($boardDestination->getId());
        if (null !== $boardDestinationDernierTopic)
        {
            $boardDestinationDernierTopicDernierPost = $this->getPostModel()->getLastPostForTopicById($boardDestinationDernierTopic->getId());
            $boardDestination->setLastPost($boardDestinationDernierTopicDernierPost);
        }
        $boardOrigineDernierTopic = $this->getTopicModel()->findLastTopicForBoardByIdWithLastPost($boardOrigine->getId());
        if (null !== $boardOrigineDernierTopic)
        {
            $boardOrigineDernierTopicDernierPost = $this->getPostModel()->getLastPostForTopicById($boardOrigineDernierTopic->getId());
            $boardOrigine->setLastPost($boardOrigineDernierTopicDernierPost);
        }
        //-->

        //Mise à jour du Board de destination
        $stats = $this->getTopicModel()->getTopicAndPostCountForBoardById($boardDestination->getId());
        // set the board topic / post count
        $boardDestination->setCachedTopicCount($stats['topicCount']);
        $boardDestination->setCachedPostCount($stats['postCount']);
        $this->container->get('hopitalnumerique_forum.manager.board')->save( $boardDestination );

        //Mise à jour du Board d'origine
        $stats = $this->getTopicModel()->getTopicAndPostCountForBoardById($boardOrigine->getId());
        // set the board topic / post count
        $boardOrigine->setCachedTopicCount($stats['topicCount']);
        $boardOrigine->setCachedPostCount($stats['postCount']);
        $this->container->get('hopitalnumerique_forum.manager.board')->save( $boardOrigine );

        return new JsonResponse([
            'success' => true,
            'url' => $this->getRouter()->generate('ccdn_forum_user_topic_show', ['topicId' => $topicId, 'forumName' => $topic->getBoard()->getCategory()->getForum()->getName()])
        ], 200);
    }

    /**
     * Retourne le PDF de la charte d'utilisation
     */
    public function pdfCharteUtilisationAction( Request $request )
    {
        $fileName = __ROOT_DIRECTORY__ . '/web/medias/Forum/charte_utilisation_forum.pdf';
        $options  = array(
            'serve_filename' => 'charte_utilisation_forum.pdf',
            'absolute_path'  => false,
            'inline'         => false,
        );

        return $this->container->get('igorw_file_serve.response_factory')->create( $fileName , 'application/pdf', $options);
    }

    /**
     * Retourne si l'utilisateur est abonné au Board.
     */
    public function isSubscriptionBoard(Board $board)
    {
        if (!($this->getUser() instanceof UserInterface))
            return false;
        
        $subscription = $this->getSubscriptionModel()->findOneSubscriptionForBoardAndUser($board, $this->getUser());
        
        if (null === $subscription)
            return false;
        
        return $subscription->isSubscribed();
    }
}
