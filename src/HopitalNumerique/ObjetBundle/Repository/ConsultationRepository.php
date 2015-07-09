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
    public function getLastsConsultations( $user, $domaineId )
    {
        $qb = $this->_em->createQueryBuilder();

        return $qb->select('clt')
                    ->from('\HopitalNumerique\ObjetBundle\Entity\Consultation', 'clt')
                    ->leftJoin('clt.objet','obj')
                        ->andWhere('clt.user = :user')
                    ->leftJoin('clt.domaine', 'domaine')
                        ->andWhere('domaine.id = :domaineId')
                        ->setParameters(array(
                            'user'      => $user,
                            'domaineId' => $domaineId
                        ))
                    ->orderBy('clt.dateLastConsulted', 'DESC');
    }

    public function getUsersConcerneByObjet($idObjet, $domaineIds)
    {
        $qb = $this->_em->createQueryBuilder();

        return $qb->select('clt')
                    ->from('HopitalNumeriqueObjetBundle:Consultation', 'clt')
                    ->leftJoin('clt.objet','obj')
                        ->andWhere('obj.id = :idObjet')
                    ->leftJoin('clt.domaine', 'domaine')
                        ->andWhere('domaine.id IN (:domaineIds)')
                        ->setParameters(array(
                            'idObjet'    => $idObjet,
                            'domaineIds' => $domaineIds
                        ))
                    ->leftJoin('clt.user', 'user')
                    ->groupBy('user')
                    ->orderBy('clt.dateLastConsulted', 'DESC');   
    }

    /**
     * Get nombre consultations
     *
     * @return int
     */
    public function getNbConsultations($domaineId = null) {
      $qb = $this->_em->createQueryBuilder();

      $qb->select('COUNT(clt)')
        ->from('\HopitalNumerique\ObjetBundle\Entity\Consultation', 'clt');

        if(!is_null($domaineId))
        {

            $qb->leftJoin('clt.domaine', 'domaine')
                ->andWhere('domaine.id = :domaineId')
                ->setParameter('domaineId', $domaineId);
        }

      return $qb;
    }
}