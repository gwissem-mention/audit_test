<?php

namespace HopitalNumerique\ForumBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * RefTopicRepository
 */
class RefTopicRepository extends EntityRepository
{
    /**
     * Retourne la liste des références topics pour la recherche
     *
     * @param array $references Liste des références
     *
     * @return QueryBuilder
     */
    public function getTopicForRecherche( $references )
    {
        $qb = $this->_em->createQueryBuilder();

        return $qb->select('refO')
                        ->from('\HopitalNumerique\ForumBundle\Entity\RefTopic', 'refO')
                        ->leftJoin('refO.topic','top')
                        ->andWhere('refO.reference in (:references)','top.isClosed = false')
                        ->setParameter('references', $references )
                        ->orderBy('refO.primary', 'ASC');
    }
}