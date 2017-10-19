<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Repository\Discussion;

use Doctrine\ORM\Query\Expr\Join;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\FichierBundle\Entity\File;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Message;

/**
 * Class MessageRepository
 */
class MessageRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param Groupe $group
     * @param User $user
     *
     * @return Message[]
     */
    public function getMessageNotReaded(Groupe $group, User $user)
    {
        return $this->createQueryBuilder('message')
            ->join('message.discussion', 'discussion')
            ->join('discussion.groups', 'cdpGroup', Join::WITH, 'cdpGroup.id = :group')
            ->setParameter('group', $group)
            ->join('discussion.readings', 'reading', Join::WITH, 'reading.user = :user')
            ->setParameter('user', $user)

            ->andWhere('message.createdAt > reading.lastMessageDate')

            ->getQuery()->getResult()
        ;
    }

    /**
     * @param Domaine|null $domain
     *
     * @return integer
     */
    public function getPublicMessageCount(Domaine $domain = null)
    {
        $queryBuilder = $this->createQueryBuilder('message')
            ->select('count(message)')
            ->join('message.discussion', 'discussion', Join::WITH, 'discussion.public = TRUE')
        ;

        if ($domain) {
            $queryBuilder
                ->join('discussion.domains', 'domain', Join::WITH, 'domain.id = :domain')
                ->setParameter('domain', $domain)
            ;
        }

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @param Domaine|null $domain
     *
     * @return integer
     */
    public function getGroupMessageCount(Domaine $domain = null)
    {
        $queryBuilder = $this->createQueryBuilder('message')
            ->select('count(message)')
            ->join('message.discussion', 'discussion')
            ->join('discussion.groups', 'groups')
        ;

        if ($domain) {
            $queryBuilder
                ->join('discussion.domains', 'domain', Join::WITH, 'domain.id = :domain')
                ->setParameter('domain', $domain)
            ;
        }

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @param Domaine|null $domain
     *
     * @return integer
     */
    public function getMessageFileCount(Domaine $domain = null)
    {
        $queryBuilder = $this->createQueryBuilder('message')
            ->select('count(file)')
            ->join('message.discussion', 'discussion')
            ->join('message.files', 'file')
            ->join('discussion.groups', 'groups')
        ;

        if ($domain) {
            $queryBuilder
                ->join('discussion.domains', 'domain', Join::WITH, 'domain.id = :domain')
                ->setParameter('domain', $domain)
            ;
        }

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @param Domaine|null $domain
     * @param int $limit
     *
     * @return Message[]
     */
    public function getRecentPublicMessage(Domaine $domain = null, $limit = 20)
    {
        $queryBuilder = $this->createQueryBuilder('message')
            ->join('message.discussion', 'discussion', Join::WITH, 'discussion.public = TRUE AND discussion.createdAt != message.createdAt')
        ;

        if ($domain) {
            $queryBuilder
                ->join('discussion.domains', 'domain', Join::WITH, 'domain.id = :domain')
                ->setParameter('domain', $domain)
            ;
        }

        return $queryBuilder
            ->addOrderBy('message.createdAt', 'DESC')
            ->setMaxResults($limit)

            ->getQuery()->getResult()
        ;
    }

    /**
     * @param Domaine[] $domains
     *
     * @return integer
     */
    public function countRecentMessages(array $domains)
    {
        return $this->createQueryBuilder('message')
            ->select('COUNT(message.id)')
            ->join('message.discussion', 'discussion')
            ->join('discussion.domains', 'domain', Join::WITH, 'domain.id IN (:domains)')
            ->setParameter('domains', $domains)
            ->andWhere('message.createdAt >= :date')
            ->setParameter('date', (new \DateTime())->sub(new \DateInterval('P1M')))

            ->getQuery()->getSingleScalarResult()
        ;
    }
}
