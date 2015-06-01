<?php

namespace HopitalNumerique\ForumBundle\Model\Component\Repository;

use CCDNForum\ForumBundle\Model\Component\Repository\PostRepository as CCDNPostRepository;
use Doctrine\ORM\Query\Expr;

/**
 *
 * @category CCDNForum
 * @package  ForumBundle
 */
class PostRepository extends CCDNPostRepository
{
    /**
     *
     * @access public
     * @param  int                                $topicId
     * @return \CCDNForum\ForumBundle\Entity\Post
     */
    public function getLastPostForTopicById($topicId)
    {
        if (null == $topicId || ! is_numeric($topicId) || $topicId == 0) {
            throw new \Exception('Topic id "' . $topicId . '" is invalid!');
        }
    
        $params = array(':topicId' => $topicId, ':deleted' => false);
    
        $qb = $this->createSelectQuery(array('p', 't'));
    
        $qb
            ->innerJoin('p.topic', 't', Expr\Join::WITH, 't.isDeleted = :deleted')
            ->where(
                $qb->expr()->eq('t.id', ':topicId')
            )
            ->andWhere('p.isDeleted = :deleted')
            ->orderBy('p.createdDate', 'DESC')
            ->setMaxResults(1)
        ;
        
        $dernierPost = $this->gateway->findPost($qb, $params);

        return $dernierPost;
    }
}