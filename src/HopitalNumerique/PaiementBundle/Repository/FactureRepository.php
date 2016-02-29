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
}
