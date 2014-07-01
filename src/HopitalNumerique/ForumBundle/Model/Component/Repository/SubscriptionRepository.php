<?php

namespace HopitalNumerique\ForumBundle\Model\Component\Repository;

use CCDNForum\ForumBundle\Model\Component\Repository\SubscriptionRepository as CCDNSubscriptionRepository;

/**
 *
 * @category CCDNForum
 * @package  ForumBundle
 */
class SubscriptionRepository extends CCDNSubscriptionRepository
{
    /**
     *
     * @access public
     * @param  int $topicId
     * @return int
     */
    public function countSubscriptionsForTopicById($topicId)
    {
        if (null == $topicId || ! is_numeric($topicId) || $topicId == 0) {
            throw new \Exception('Topic id "' . $topicId . '" is invalid!');
        }

        $qb = $this->createCountQuery();

        $qb->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('s.topic', ':topicId'),
                    $qb->expr()->eq('s.isSubscribed', true)
                )
            );

        return $this->gateway->countSubscriptions($qb, array(':topicId' => $topicId));
    }
}