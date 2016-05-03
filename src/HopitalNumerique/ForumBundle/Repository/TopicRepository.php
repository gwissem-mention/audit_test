<?php

namespace HopitalNumerique\ForumBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use HopitalNumerique\ForumBundle\Entity\Forum;

/**
 * TopicRepository
 */
class TopicRepository extends EntityRepository
{
  /**
   * Récupère les derniers topics commentés par type de forum
   *
   * @return  QueryBuilder
   */
  public function getLastTopicsForum($id, $limit = null) {
    $qb = $this->_em->createQueryBuilder();

    $qb->select('topic')
       ->from('\HopitalNumerique\ForumBundle\Entity\Topic', 'topic')
       ->innerJoin('topic.lastPost', 'post')
       ->innerJoin('topic.board', 'board')
       ->innerJoin('board.category', 'cat')
       ->innerJoin('cat.forum', 'forum', Join::WITH, 'forum.id = :idForum')
       ->setParameter('idForum', $id)
       ->groupBy('topic.id')
       ->orderBy('post.createdDate', 'DESC');

    if($limit != null) {
      $qb->setMaxResults($limit);
    }

    return $qb;
  }

  /**
   * Récupère les derniers topics commentés par type de forum
   *
   * @return  QueryBuilder
   */
  public function getLastTopicsForumEpingle($id, $limit = null, $epingle, $idCat) {
  	$qb = $this->_em->createQueryBuilder();
  
  	$qb->select('topic')
  	->from('\HopitalNumerique\ForumBundle\Entity\Topic', 'topic')
  	->innerJoin('topic.lastPost', 'post')
  	->innerJoin('topic.board', 'board')
  	->innerJoin('board.category', 'cat')
  	->innerJoin('cat.forum', 'forum', Join::WITH, 'forum.id = :idForum')
  	->andWhere('cat.id = :idCategorie')
  	->andWhere('topic.isSticky = :sticky')
  	->setParameter('idForum', $id)
  	->setParameter('sticky', $epingle)
  	->setParameter('idCategorie', $idCat)
  	->groupBy('topic.id')
  	->orderBy('post.createdDate', 'DESC');
  
  	if($limit != null) {
  		$qb->setMaxResults($limit);
  	}
  
  	return $qb;
  }
  
    /**
     * Retourne les topics d'un forum.
     *
     * @param \HopitalNumerique\ForumBundle\Entity\Forum $forum Forum
     * @return array<\HopitalNumerique\ForumBundle\Entity\Topic> Topics
     */
    public function findByForum(Forum $forum)
    {
        $query = $this->createQueryBuilder('topic');

        $query
            ->addSelect(array('board', 'category'))
            ->innerJoin('topic.board', 'board')
            ->innerJoin('board.category', 'category', Join::WITH, 'category.forum = :forum')
            ->setParameter('forum', $forum)
            ->orderBy('category.listOrderPriority')
            ->addOrderBy('board.listOrderPriority')
            ->addOrderBy('topic.lastPost', 'DESC')
        ;

        return $query->getQuery()->getResult();
    }
}
