<?php

namespace HopitalNumerique\ForumBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

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
}