<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use HopitalNumerique\DomaineBundle\Entity\Domaine;

/**
 * Repository de Commentaire.
 */
class CommentaireRepository extends EntityRepository
{
    /**
     * Retourne true si le document n'est appelé dans aucun commentaire.
     *
     * @param $document
     *
     * @return QueryBuilder QueryBuilder
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

    /**
     * @param Domaine[] $domains
     *
     * @return array
     */
    public function getLatestCommentsCount($domains)
    {
        $qb = $this->createQueryBuilder('c');

        return $qb
            ->select('COUNT(c.id)')
            ->join('c.groupe', 'g')
            ->join(
                'g.domaine',
                'domaine',
                Join::WITH,
                $qb->expr()->in(
                    'domaine',
                    array_map(function (Domaine $domain) {
                        return $domain->getId();
                    }, $domains)
                )
            )
            ->andWhere('c.dateCreation >= :date')
            ->setParameter('date', (new \DateTime())->sub(new \DateInterval('P30D')))

            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
