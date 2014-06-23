<?php

namespace HopitalNumerique\RechercheBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * RequeteRepository
 */
class RequeteRepository extends EntityRepository
{
    /**
     * Retourne la liste des request dont les résultats sont nouveaux/mis à jour
     *
     * @param User $user L'utilisateur connecté
     *
     * @return QueryBuilder
     */
    public function getRequetesForDashboard( $user )
    {
        return $this->_em->createQueryBuilder()
                         ->select('req')
                         ->from('\HopitalNumerique\RechercheBundle\Entity\Requete', 'req')
                         ->andWhere('req.user = :user', '(req.isNew = 1 OR req.isUpdated = 1)')
                         ->setParameter('user', $user );
    }
}