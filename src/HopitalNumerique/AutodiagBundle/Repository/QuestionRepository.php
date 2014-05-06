<?php

namespace HopitalNumerique\AutodiagBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * QuestionRepository
 */
class QuestionRepository extends EntityRepository
{
    /**
     * Compte le nombre de questions lié au chapitre
     *
     * @param Chapitre $chapitre chapitre
     *
     * @return QueryBuilder
     */
    public function countQuestions( $chapitre )
    {
        return $this->_em->createQueryBuilder()
                         ->select('count(que)')
                         ->from('\HopitalNumerique\AutodiagBundle\Entity\Question', 'que')
                         ->andWhere('que.chapitre = :chapitre')
                         ->setParameter('chapitre', $chapitre );
    }
}
