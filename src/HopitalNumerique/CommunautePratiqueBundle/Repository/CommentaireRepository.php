<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Repository;

use HopitalNumerique\UserBundle\Entity\Document;
use Doctrine\ORM\Query\Expr;

/**
 * Repository de Commentaire.
 */
class CommentaireRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * Retourne true si le document n'est appelé dans aucun commentaire
     * @return \Doctrine\ORM\QueryBuilder QueryBuilder
     */
    public function safeDelete($document) {
        $query = $this->createQueryBuilder('commentaire')
            ->where('commentaire.message LIKE :doc')
            ->setParameter(':doc', '%/' . $document . '/%');
        return $query;
    }
}