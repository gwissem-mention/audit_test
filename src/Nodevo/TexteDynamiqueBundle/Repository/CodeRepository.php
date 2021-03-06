<?php

namespace Nodevo\TexteDynamiqueBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use Nodevo\TexteDynamiqueBundle\Entity\Code;

/**
 * CodeRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CodeRepository extends EntityRepository
{
    /**
     * Récupère les données du grid sous forme de tableau correctement formaté.
     *
     * @param      $domainesIds
     * @param null $condition
     *
     * @return QueryBuilder
     */
    public function getDatasForGrid($domainesIds, $condition = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('code.id, code.code, code.texte, domaine.nom as domaineNom')
            ->from('NodevoTexteDynamiqueBundle:Code', 'code')
            ->leftJoin('code.domaines', 'domaine')
                ->where($qb->expr()->orX(
                    $qb->expr()->in('domaine.id', ':domainesId'),
                    $qb->expr()->isNull('domaine.id')
                ))
                ->setParameter('domainesId', $domainesIds)
            ->orderBy('code.code')
            ->groupBy('code.id', 'domaine.id');

        return $qb;
    }

    /**
     * @param         $code
     * @param Domaine $domaine
     *
     * @return Code|null
     */
    public function findOneByCodeAndDomaine($code, Domaine $domaine)
    {
        $qb = $this->createQueryBuilder('code');

        $qb
            ->innerJoin('code.domaines', 'domaine', Expr\Join::WITH, $qb->expr()->eq('domaine.id', ':domaine'))
            ->where($qb->expr()->eq('code.code', ':code'))
            ->setParameters([
                'code' => $code,
                'domaine' => $domaine,
            ])
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }
}
