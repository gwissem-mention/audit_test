<?php

namespace HopitalNumerique\ForumBundle\Model\Component\Repository;

use CCDNForum\ForumBundle\Model\Component\Repository\TopicRepository as CCDNTopicRepository;
use Doctrine\ORM\Query\Expr;

/**
 *
 * @category CCDNForum
 * @package  ForumBundle
 */
class TopicRepository extends CCDNTopicRepository
{
    /**
     * (non-PHPdoc)
     * @see \CCDNForum\ForumBundle\Model\Component\Repository\TopicRepository::findLastTopicForBoardByIdWithLastPost()
     */
    public function findLastTopicForBoardByIdWithLastPost($boardId)
    {
        if (null == $boardId || ! is_numeric($boardId) || $boardId == 0) {
            throw new \Exception('Board id "' . $boardId . '" is invalid!');
        }
    
        $params = array(':boardId' => $boardId, ':deleted' => false);
    
        $qb = $this->createSelectQuery(array('t', 'p', 'b'));
    
        $qb
        ->innerJoin('t.board', 'b')
        ->innerJoin('t.posts', 'p', Expr\Join::WITH, 'p.isDeleted = :deleted')
        ->where('b.id = :boardId')
        ->andWhere('t.isDeleted = FALSE')
        ->orderBy('p.createdDate', 'DESC')
        ->setMaxResults(1)
        ;
    
        return $this->gateway->findTopic($qb, $params);
    }
}