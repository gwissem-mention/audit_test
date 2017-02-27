<?php

namespace HopitalNumerique\ForumBundle\Model\Component\Repository;

use CCDNForum\ForumBundle\Model\Component\Repository\PostRepository as CCDNPostRepository;
use Doctrine\ORM\Query\Expr;

/**
 * @category CCDNForum
 */
class PostRepository extends CCDNPostRepository
{
    /**
     * @param int $topicId
     *
     * @return \CCDNForum\ForumBundle\Entity\Post
     */
    public function getLastPostForTopicById($topicId)
    {
        if (null == $topicId || !is_numeric($topicId) || $topicId == 0) {
            throw new \Exception('Topic id "' . $topicId . '" is invalid!');
        }

        $params = [':topicId' => $topicId, ':deleted' => false];

        $qb = $this->createSelectQuery(['p', 't']);

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

    /**
     * @return array
     */
    public function countGroupByUser()
    {
        $qb = $this->gateway->createSelectQuery();

        $qb->select('count(p.id) as nbPost, u.id as idUser')
            ->join('p.createdBy', 'u')
            ->groupBy('u.id')
        ;

        $results = $qb->getQuery()->getResult();

        foreach ($results as $key => $result) {
            $results[$result['idUser']] = intval($result['nbPost']);
            unset($results[$key]);
        }

        return $results;
    }
}
