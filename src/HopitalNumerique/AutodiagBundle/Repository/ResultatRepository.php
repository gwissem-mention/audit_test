<?php

namespace HopitalNumerique\AutodiagBundle\Repository;

use Doctrine\ORM\EntityRepository;
use HopitalNumerique\AutodiagBundle\Entity\Outil;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * ResultatRepository
 */
class ResultatRepository extends EntityRepository
{
    /**
     * Récupère le dernier résultat validé
     *
     * @param Outil  $outil L'outil
     * @param User   $user  L'utilisateur connecté
     *
     * @return Resultat
     */
    public function getLastResultatValided( Outil $outil, User $user )
    {
        return $this->_em->createQueryBuilder()
                         ->select('res')
                         ->from('\HopitalNumerique\AutodiagBundle\Entity\Resultat', 'res')
                         ->andWhere('res.outil = :outil', 'res.user = :user')
                         ->andWhere('res.synthese = 0')
                         ->setParameter('user', $user )
                         ->setParameter('outil', $outil )
                         ->setMaxResults(1)
                         ->orderBy('res.dateValidation','DESC');
    }
}