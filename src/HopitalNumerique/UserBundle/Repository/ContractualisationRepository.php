<?php

namespace HopitalNumerique\UserBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * ContractualisationRepository
 */
class ContractualisationRepository extends EntityRepository
{
    /**
     * Récupère le nombre de contractualisation à renouveler depuis 45jours
     *
     * @return QueryBuilder
     */
    public function getContractualisationsARenouveler()
    {
        $in45Days = new \DateTime();
        $in45Days->add(new \DateInterval('P45D'));

        $qb = $this->_em->createQueryBuilder();
        $qb
            ->select('count(distinct user.id)')
            ->from('HopitalNumeriqueUserBundle:User', 'user')
            ->leftJoin('user.contractualisations', 'con', Join::WITH, 'con.archiver = 0')
            ->andWhere(
                $qb->expr()->orX(
                    'con.dateRenouvellement <= :in45Days',
                    'con.id IS NULL'
                )
            )
            ->andWhere(
                $qb->expr()->orX(
                    ...array_map(function ($role) use ($qb) {
                        return $qb->expr()->like(
                            'user.roles',
                            $qb->expr()->literal("%$role%")
                        );
                    }, User::getRolesContractualisationUpToDate())
                )
            )
            ->setParameters([
                'in45Days' => $in45Days,
            ])
        ;

        return $qb;
    }

    /**
     * Récupère les contractualisations pour un utilisateur donné
     *
     * @param null $condition
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getContractualisationForGrid($condition = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('contract.id,
            contract.nomDocument,
            contract.dateRenouvellement,
            contract.archiver,
            contract.path,
            typeDocument.libelle
            ')
            ->from('HopitalNumeriqueUserBundle:Contractualisation', 'contract')
            ->innerJoin('contract.user', 'user')
            ->innerJoin('contract.typeDocument', 'typeDocument')
            ->addOrderBy('user.username');

        if ($condition) {
            $qb->where('user.id = :id')
                ->setParameter('id', $condition->value);
        }

        return $qb;
    }
}
