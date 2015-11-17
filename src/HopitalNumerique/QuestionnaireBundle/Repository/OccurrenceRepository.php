<?php
namespace HopitalNumerique\QuestionnaireBundle\Repository;

use HopitalNumerique\UserBundle\Entity\User;

/**
 * Repository de Occurrence.
 */
class OccurrenceRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * Retourne les occurrences d'un questionnaire d'un utilisateur avec les dates de création et de dernières modifications.
     * 
     * @param \HopitalNumerique\UserBundle\Entity\User                   $user          Utilisateur
     * @return array<\HopitalNumerique\QuestionnaireBundle\Entity\Occurrence> Occurrences
     */
    public function findByUserWithDates(User $user)
    {
        $query = $this->createQueryBuilder('occurrence');

        $query
            ->select('occurrence', 'MIN(reponse.dateCreation) AS dateCreation', 'MAX(reponse.dateUpdate) AS dernierUpdate')
            ->innerJoin('occurrence.reponses', 'reponse')
            ->andWhere('occurrence.user = :user')
            ->setParameter('user', $user)
        ;

        $query
            ->groupBy('occurrence.id')
            ->addOrderBy('occurrence.id', 'ASC')
        ;

        return $query->getQuery()->getResult();
    }
}
