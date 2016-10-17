<?php

namespace HopitalNumerique\PaiementBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * FactureRepository
 */
class FactureRepository extends EntityRepository
{
    /**
     * Retourne la liste des factures ordonnées par date
     *
     * @return QueryBuilder
     */
    public function getFacturesOrdered($user, $onlyValid)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb
            ->select('fac')
            ->from('HopitalNumeriquePaiementBundle:Facture', 'fac')
            ->where('fac.user = :user')
        ;
        if ($onlyValid) {
            $qb
                ->leftJoin('fac.factureAnnulee', 'factureAnnulee')
                ->andWhere($qb->expr()->isNull('factureAnnulee.id'))
            ;
        }
        $qb
            ->setParameter('user', $user)
            ->orderBy('fac.dateCreation', 'DESC')
        ;
        return $qb;
    }

    /**
     * @param $year
     *
     * @return integer
     */
    public function getTotalAmountForYear($year)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb
            ->select('SUM(f.total)')
            ->from('HopitalNumeriquePaiementBundle:Facture', 'f')
            ->where('f.payee = 1')
            ->andWhere('f.annulee = 0')
            ->andWhere('f.dateCreation BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', new \DateTime(sprintf('00-00-%d 00:00:00', $year)))
            ->setParameter('endDate', new \DateTime(sprintf('31-12-%d 23:59:59', $year)))
        ;

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $year
     *
     * @return integer
     */
    public function getTotalNotPayedAmountForYear($year = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb
            ->select('SUM(f.total)')
            ->from('HopitalNumeriquePaiementBundle:Facture', 'f')
            ->where('f.payee = 0')
            ->andWhere('f.annulee = 0')
        ;

        if (!is_null($year)) {
            $qb
                ->andWhere('f.dateCreation BETWEEN :startDate AND :endDate')
                ->setParameter('startDate', new \DateTime(sprintf('00-00-%d 00:00:00', $year)))
                ->setParameter('endDate', new \DateTime(sprintf('31-12-%d 23:59:59', $year)))
            ;
        }

        return $qb->getQuery()->getSingleScalarResult();
    }


}
