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
}
