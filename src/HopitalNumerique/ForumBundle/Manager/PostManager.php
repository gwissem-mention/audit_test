<?php

namespace HopitalNumerique\ForumBundle\Manager;

use HopitalNumerique\ForumBundle\Entity\Post;
use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use Doctrine\ORM\EntityManager;

/**
 * Manager de l'entité Post.
 */
class PostManager extends BaseManager
{
    protected $class = 'HopitalNumerique\ForumBundle\Entity\Post';

    protected $managerTopic;
    protected $managerBoard;

    /**
     * Constructeur du manager.
     *
     * @param EntityManager $em Entity Manager de Doctrine
     * @param               $managerTopic
     * @param               $managerBoard
     */
    public function __construct(EntityManager $em, $managerTopic, $managerBoard)
    {
        parent::__construct($em);
        $this->managerTopic  = $managerTopic;
        $this->managerBoard = $managerBoard;
    }

    public function delete($posts)
    {
        /** @var Post $post */
        foreach ($posts as $post) {
            //Récupération du topic du post à supprimer
            $topic = $post->getTopic();
            if (null === $topic) {
                continue;
            }
            $isLastPostTopic = is_null($topic->getLastPost()) ? false
                : $topic->getLastPost()->getId() === $post->getId();
            //Récupération du board du post à supprimer
            $board = $topic->getBoard();
            $isLastPostBoard = is_null($board->getLastPost()) ? false
                : $board->getLastPost()->getId() === $post->getId();

            // Vérification si le post courant est le premier post du topic,
            // si c'est le cas il faut supprimer tout les autres posts liés au topic
            if (is_null($topic->getFirstPost())) {
                $postsADelete = $topic->getPosts();

                foreach ($postsADelete as $postADelete) {
                    //Suppression du post
                    parent::delete($postADelete);
                }
            } else {
                //Suppression du post
                parent::delete($post);
            }

            //Récupération du dernier post après suppression (dans le cas où le post supprimé était le dernier du topic)
            if (count($topic->getPosts()) != 0 && $isLastPostTopic) {
                $posts = $topic->getPosts();

                $lastPost = null;
                foreach ($posts as $postTemp) {
                    if (is_null($lastPost)) {
                        $lastPost = $postTemp;
                    }

                    if ($postTemp->getCreatedDate() > $lastPost->getCreatedDate()) {
                        $lastPost = $postTemp;
                    }
                }

                $topic->setLastPost($lastPost);
                $this->managerTopic->save($topic);
            }

            //Récupération du dernier post après suppression (dans le cas où le post supprimé était le dernier du topic)
            if (count($board->getTopics()) != 0 && $isLastPostBoard) {
                $topics = $board->getTopics();
                $lastPost = null;
                foreach ($topics as $topic) {
                    $posts = $topic->getPosts();

                    foreach ($posts as $postTemp) {
                        if (is_null($lastPost)) {
                            $lastPost = $postTemp;
                        }

                        if ($postTemp->getCreatedDate() > $lastPost->getCreatedDate()) {
                            $lastPost = $postTemp;
                        }
                    }
                }

                $board->setLastPost($lastPost);
                $this->managerBoard->save($board);
            }
        }
    }

    /**
     * Retrieves the first post of each board topic.
     *
     * @param int $boardId
     *
     * @return array
     */
    public function getFirstPostsFromBoard($boardId)
    {
        return $this->getRepository()->getFirstPostsFromBoard($boardId);
    }
}
