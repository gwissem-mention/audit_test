<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Repository;

use Doctrine\ORM\Query\Expr\Join;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\UserBundle\Entity\Document;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * Repository de Commentaire.
 */
class CommentaireRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * Retourne true si le document n'est appelé dans aucun commentaire.
     *
     * @return \Doctrine\ORM\QueryBuilder QueryBuilder
     */
    public function safeDelete($document)
    {
        $query = $this->createQueryBuilder('commentaire')
            ->where('commentaire.message LIKE :doc')
            ->setParameter(':doc', '%/' . $document . '/%');

        return $query;
    }

    /**
     * @param Domaine $domaine
     *
     * @return int
     */
    public function getLatestCommentsCount(Domaine $domaine)
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->join('c.groupe', 'g')
            ->join('g.domaine', 'd', Join::WITH, 'd.id = :domaine')
            ->setParameter('domaine', $domaine)
            ->andWhere('c.dateCreation >= :date')->setParameter('date', (new \DateTime())->sub(new \DateInterval('P30D')))

            ->getQuery()->getSingleScalarResult()
        ;
    }
}
