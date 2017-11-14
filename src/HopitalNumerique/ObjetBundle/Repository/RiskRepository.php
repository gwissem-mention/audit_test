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
     * @param integer[] $referencesId
     *
     * @return array|Risk[]
     */
    public function getPublicRisksForDomain(Domaine $domain, $referencesId = [])
    {
        return $this
            ->createRiskForDomainAndReferenceQueryBuilder($domain, $referencesId)
            ->andWhere('risk.private = false')

            ->getQuery()->getResult()
        ;
    }

    /**
     * @param Domaine $domain
     * @param array $referencesId
     *
     * @return Risk[]
     */
    public function getRisksForDomainAndReference(Domaine $domain, $referencesId)
    {
        return $this
            ->createRiskForDomainAndReferenceQueryBuilder($domain, $referencesId)

            ->getQuery()->getResult()
        ;
    }

    /**
     * @param Domaine $domain
     * @param array $referencesId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function createRiskForDomainAndReferenceQueryBuilder(Domaine $domain, $referencesId = [])
    {
        $queryBuilder =  $this->createQueryBuilder('risk', 'risk.id')
            ->join('risk.domains', 'domain', Join::WITH, 'domain.id = :domainId')
            ->setParameter('domainId', $domain->getId())
        ;

        if ($referencesId) {
            foreach ($referencesId as $referenceId) {
                $queryBuilder
                    ->join(
                        EntityHasReference::class,
                        sprintf('entityHasReference%d', $referenceId),
                        Join::WITH,
                        sprintf('
                                entityHasReference%d.entityType = :entityType AND
                                entityHasReference%d.entityId = risk.id AND
                                %s
                                
                            ',
                            $referenceId,
                            $referenceId,
                            sprintf(is_array($referenceId) ? 'entityHasReference%d.reference IN (:referenceId%d)' : 'entityHasReference%d.reference = :referenceId%d',$referenceId, $referenceId)
                        )
                    )
                    ->setParameter(sprintf('referenceId%d', $referenceId), $referenceId)
                ;
            }

            $queryBuilder->setParameter('entityType', Entity::ENTITY_TYPE_RISK);
        }

        return $queryBuilder;
    }
}
