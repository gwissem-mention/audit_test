<?php

namespace HopitalNumerique\AutodiagBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\UserBundle\Entity\User;

class SynthesisRepository extends EntityRepository
{
    /**
     * @param $id
     * @return Synthesis|null
     */
    public function getFullyLoadedSynthesis($id)
    {
        $qb = $this->createQueryBuilder('synthesis');
        $qb
            ->addSelect('autodiag', 'entries', 'values')
            ->leftJoin('synthesis.autodiag', 'autodiag')
            ->leftJoin('synthesis.entries', 'entries')
            ->leftJoin('entries.values', 'values')
            ->where('synthesis.id = :id')
            ->setParameters([
                'id' => $id
            ])
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Retourne les synthèses de l'utilisateur et les synthèses qui lui ont été partagées
     *
     * @param User $user
     * @return array
     */
    public function findByUser(User $user)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select('synthesis')
            ->from('HopitalNumeriqueAutodiagBundle:Synthesis', 'synthesis')
            ->leftJoin('synthesis.shares', 'shares', Join::WITH, 'shares = :user')
            ->orWhere('synthesis.user = :user')
            ->orWhere('shares.id IS NOT NULL')
            ->setParameters([
                'user' => $user,
            ])
        ;

        return $qb->getQuery()->getResult();
    }
}
