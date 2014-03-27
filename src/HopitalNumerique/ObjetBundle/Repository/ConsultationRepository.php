<?php

namespace HopitalNumerique\ObjetBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ConsultationRepository
 */
class ConsultationRepository extends EntityRepository
{
    /**
     * Retourne les dernières consultations de l'user $user
     *
     * @param User $user L'user connecté
     *
     * @return array
     */
    public function getLastsConsultations( $user )
    {
        $qb = $this->_em->createQueryBuilder();

        return $qb->select('clt')
                    ->from('\HopitalNumerique\ObjetBundle\Entity\Consultation', 'clt')
                    ->leftJoin('clt.objet','obj')
                    ->andWhere('clt.user = :user')
                    ->setParameter('user', $user )
                    ->orderBy('clt.dateLastConsulted', 'ASC');
    }
}