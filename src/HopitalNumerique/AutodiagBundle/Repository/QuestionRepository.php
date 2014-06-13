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

    /**
     * Retourne la liste des questions dans le bon ordre
     *
     * @param Chapitre $chapitre chapitre
     *
     * @return integer
     */
    public function getQuestionsOrdered( $chapitre )
    {
        return $this->_em->createQueryBuilder()
                         ->select('que')
                         ->from('\HopitalNumerique\AutodiagBundle\Entity\Question', 'que')
                         ->andWhere('que.chapitre = :chapitre')
                         ->setParameter('chapitre', $chapitre )
                         ->orderBy('que.order', 'ASC');
    }

}
