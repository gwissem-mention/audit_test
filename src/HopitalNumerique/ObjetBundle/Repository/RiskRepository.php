<?php

namespace HopitalNumerique\ObjetBundle\Repository;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\EntityRepository;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ObjetBundle\Entity\Risk;

/**
 * Class RiskRepository
 */
class RiskRepository extends EntityRepository
{
    /**
     * @return Risk[]
     */
    public function getDatasForGrid()
    {
        $risks = $this
            ->createQueryBuilder('r')
            ->leftJoin('r.domains', 'd')->addSelect('d')
            ->leftJoin('r.nature', 'n')->addSelect('n')

            ->getQuery()->getResult()
        ;

        $results = [];
        /** @var Risk $risk */
        foreach ($risks as $risk) {
            $object = [];
            $object['id'] = $risk->getId();
            $object['createdAt'] = $risk->getCreatedAt();
            $object['label'] = $risk->getLabel();
            $object['nature'] = $risk->getNature()->getLibelle();
            $object['type'] = $risk->isPrivate();
            $object['archived'] = $risk->isArchived();
            $object['domains'] = [];
            foreach ($risk->getDomains() as $domain) {
                $object['domains'][] = $domain->getNom();
            }

            $results[] = $object;
        }

        return $results;

    }

    /**
     * @param Risk $risk
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createRelatedRisksQueryBuilder(Risk $risk)
    {
        $domainsId = [];
        foreach ($risk->getDomains() as $domain) {
            $domainsId[] = $domain->getId();
        }

        return $this
            ->createQueryBuilder('r')
            ->join('r.domains', 'd', Join::WITH, 'd.id IN (:domainsId)')
            ->join('r.nature', 'n')
            ->setParameter('domainsId', $domainsId)
            ->andWhere('r.id != :riskId')->setParameter('riskId', $risk->getId())

            ->addOrderBy('n.libelle', 'ASC')
            ->addOrderBy('r.label', 'ASC')
        ;
    }

    /**
     * @param Domaine $domain
     *
     * @return array|Risk[]
     */
    public function getPublicRisksForDomain(Domaine $domain)
    {
        return $this->createQueryBuilder('r', 'r.id')
            ->join('r.domains', 'd', Join::WITH, 'd.id = :domainId')
            ->setParameter('domainId', $domain->getId())
            ->andWhere('r.private = false')

            ->getQuery()->getResult()
        ;
    }
}
