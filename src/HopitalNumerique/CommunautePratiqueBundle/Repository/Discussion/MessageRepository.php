<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Repository\Discussion;

use Doctrine\ORM\Query\Expr\Join;
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
}
