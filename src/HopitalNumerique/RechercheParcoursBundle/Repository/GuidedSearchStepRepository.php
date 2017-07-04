<?php

namespace HopitalNumerique\RechercheParcoursBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearch;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearchStep;

/**
 * GuidedSearchStepRepository.
 */
class GuidedSearchStepRepository extends EntityRepository
{
    /**
     * @param GuidedSearch $guidedSearch
     * @param string $stepPath
     *
     * @return GuidedSearchStep|null
     */
    public function findOneByGuidedSearchAndPath(GuidedSearch $guidedSearch, $stepPath)
    {
        return $this->createQueryBuilder('gst')
            ->join('gst.guidedSearch', 'gs', Join::WITH, 'gs.id = :guidedSearchId')
            ->setParameter('guidedSearchId', $guidedSearch->getId())

            ->leftJoin('gst.risksAnalysis', 'ra')->addSelect('ra')

            ->andWhere('gst.stepPath = :stepPath')->setParameter('stepPath', $stepPath)

            ->getQuery()->getOneOrNullResult()
        ;
    }

    /**
     * @param GuidedSearch $guidedSearch
     * @param string $stepPath
     *
     * @return GuidedSearchStep|null
     */
    public function getByGuidedSearchAndStepPathOrCreate(GuidedSearch $guidedSearch, $stepPath)
    {
        $guidedSearchStep = $this->findOneByGuidedSearchAndPath($guidedSearch, $stepPath);

        if (is_null($guidedSearchStep)) {
            $guidedSearchStep = new GuidedSearchStep($guidedSearch, $stepPath);

            $this->_em->persist($guidedSearchStep);
        }

        return $guidedSearchStep;
    }
}
