<?php

namespace HopitalNumerique\ObjetBundle\Repository;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\EntityRepository;
use HopitalNumerique\CoreBundle\DependencyInjection\Entity;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ObjetBundle\Entity\Risk;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursDetails;
use HopitalNumerique\ReferenceBundle\Entity\EntityHasReference;

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
    public function getPublicRisksForDomain(Domaine $domain, $referenceId = null)
    {
        return $this
            ->createRiskForDomainAndReferenceQueryBuilder($domain, $referenceId)
            ->andWhere('r.private = false')

            ->getQuery()->getResult()
        ;
    }

    /**
     * @param Domaine $domain
     * @param integer $referenceId
     *
     * @return Risk[]
     */
    public function getRisksForDomainAndReference(Domaine $domain, $referenceId)
    {
        return $this
            ->createRiskForDomainAndReferenceQueryBuilder($domain, $referenceId)

            ->getQuery()->getResult()
        ;
    }

    /**
     * @param Domaine $domain
     * @param integer|null $referenceId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function createRiskForDomainAndReferenceQueryBuilder(Domaine $domain, $referenceId = null)
    {
        $queryBuilder =  $this->createQueryBuilder('r', 'r.id')
            ->join('r.domains', 'd', Join::WITH, 'd.id = :domainId')
            ->setParameter('domainId', $domain->getId())
        ;

        if ($referenceId) {
            $queryBuilder
                ->join(
                    EntityHasReference::class,
                    'entityHasReference',
                    Join::WITH,
                    '
                        entityHasReference.entityType = :entityType AND
                        entityHasReference.entityId = r.id AND
                        entityHasReference.reference = :referenceId
                    '
                )
                ->setParameter('entityType', Entity::ENTITY_TYPE_RISK)
                ->setParameter('referenceId', $referenceId)
            ;
        }

        return $queryBuilder;
    }
}
