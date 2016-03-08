<?php
namespace HopitalNumerique\ForumBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use Doctrine\ORM\EntityManager;
use CCDNForum\ForumBundle\Model\FrontModel\TopicModel;
use CCDNForum\ForumBundle\Model\FrontModel\PostModel;

/**
 * Manager de l'entité Board.
 */
class BoardManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\ForumBundle\Entity\Board';
    
    /**
     * @var \CCDNForum\ForumBundle\Model\FrontModel\TopicModel TopicModel
     */
    private $topicModel;

    /**
     * @var \CCDNForum\ForumBundle\Model\FrontModel\PostModel PostModel
     */
    private $postModel;
    
    
    /**
     * Constructeur.
     */
    public function __construct(EntityManager $em, TopicModel $topicModel, PostModel $postModel)
    {
        parent::__construct($em);
        
        $this->topicModel = $topicModel;
        $this->postModel = $postModel;
    }
    
    
    /**
     * Recalcule les derniers messages de chaque board.
     */
    public function recalculateAllLastMessages()
    {
        $boards = $this->findAll();
        
        foreach ($boards as $board)
        {
            foreach ($board->getTopics() as $topic)
            {
                $topicLastPost = $this->postModel->getLastPostForTopicById($topic->getId());
                $topic->setLastPost($topicLastPost);
                $this->topicModel->saveTopic($topic);
            }

            $boardLastTopic = $this->topicModel->findLastTopicForBoardByIdWithLastPost($board->getId());
            if (null !== $boardLastTopic)
            {
                $boardLastTopicLastPost = $this->postModel->getLastPostForTopicById($boardLastTopic->getId());
                
                $board->setLastPost($boardLastTopicLastPost);
                $this->save($board);
            }
        }
    }

    /**
     * Retourne les boards classés par catégorie et forum.
     *
     * @return array Boards
     */
    public function findAllClassifiedByCategoryClassifiedByForum()
    {
        $boards = $this->getRepository()->findAll();

        $boardsClassifiedByCategoryAndForum = [];
        $forumIdFlag = $categoryIdFlag = null;

        foreach ($boards as $board) {
            if (null === $forumIdFlag || $forumIdFlag !== $board->getCategory()->getForum()->getId()) {
                $boardsClassifiedByCategoryAndForum[] = [
                    'forum' => $board->getCategory()->getForum(),
                    'categories' => []
                ];
                $forumIdFlag = $board->getCategory()->getForum()->getId();
            }
            $forumKey = count($boardsClassifiedByCategoryAndForum) - 1;
            if (null === $categoryIdFlag || $categoryIdFlag !== $board->getCategory()->getId()) {
                $boardsClassifiedByCategoryAndForum[$forumKey]['categories'][] = [
                    'category' => $board->getCategory(),
                    'boards' => []
                ];
                $categoryIdFlag = $board->getCategory()->getId();
            }
            $categoryKey = count($boardsClassifiedByCategoryAndForum[$forumKey]['categories']) - 1;
            $boardsClassifiedByCategoryAndForum[$forumKey]['categories'][$categoryKey]['boards'][] = $board;
        }

        return $boardsClassifiedByCategoryAndForum;
    }
}
