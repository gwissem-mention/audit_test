<?php

namespace HopitalNumerique\UserBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use HopitalNumerique\ReferenceBundle\Entity\Reference;

use HopitalNumerique\UserBundle\Entity\User;

/**
 * ConnaissanceAmbassadeurRepository
 */
class ConnaissanceAmbassadeurSIRepository extends EntityRepository
{
    public function findByAmbassadeur(User $ambassadeur)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb
            ->select('connaissanceAmbassadeur, connaissance, domaine')
            ->from('HopitalNumeriqueUserBundle:ConnaissanceAmbassadeurSi', 'connaissanceAmbassadeur')
            ->leftJoin('connaissanceAmbassadeur.connaissance', 'connaissance' , Expr\Join::WITH, 'connaissance.etat = :actif')
            ->innerJoin('connaissanceAmbassadeur.domaine', 'domaine' , Expr\Join::WITH, 'domaine.etat = :actif')
            ->setParameter('actif', Reference::STATUT_ACTIF_ID)
            ->where('connaissanceAmbassadeur.user = :ambassadeur')
            ->setParameter('ambassadeur', $ambassadeur)
        ;

        return $qb->getQuery()->getResult();
    }

}