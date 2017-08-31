<?php

namespace HopitalNumerique\ObjetBundle\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use HopitalNumerique\ObjetBundle\Entity\Consultation;
use HopitalNumerique\ObjetBundle\Entity\Contenu;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * ConsultationRepository.
 */
class ConsultationRepository extends EntityRepository
{
    /**
     * Retourne les dernières consultations de l'user $user.
     *
     * @param User $user L'user connecté
     *
     * @return array
     */
    public function getLastsConsultations($user, $domaineId)
    {
        $qb = $this->_em->createQueryBuilder();

        return $qb->select('clt')
                    ->from('\HopitalNumerique\ObjetBundle\Entity\Consultation', 'clt')
                    ->leftJoin('clt.objet', 'obj')
                        ->andWhere('clt.user = :user')
                    ->leftJoin('clt.domaine', 'domaine')
                        ->andWhere('domaine.id = :domaineId')
                        ->setParameters([
                            'user' => $user,
                            'domaineId' => $domaineId,
                        ])
                    ->orderBy('clt.dateLastConsulted', 'DESC');
    }

    /**
     * Returns latest view date of a publication/publication part for a user.
     *
     * @param User         $user
     * @param Objet|null   $object
     * @param Contenu|null $infradoc
     *
     * @return string|null Latest date
     */
    public function getUserLatestViewDate(User $user, Objet $object = null, Contenu $infradoc = null)
    {
        $query = $this->_em->createQueryBuilder()
            ->select('MAX(clt.dateLastConsulted)')
            ->from('\HopitalNumerique\ObjetBundle\Entity\Consultation', 'clt')
            ->andWhere('clt.user = :user')
            ->setParameter('user', $user);

        if ($object) {
            $query->andWhere('clt.objet = :objet')
                ->setParameter('objet', $object);
        }

        if ($infradoc) {
            $query->andWhere('clt.contenu = :contenu')
                ->setParameter('contenu', $infradoc);
        }

        return $query
            ->orderBy('clt.dateLastConsulted', 'DESC')
            ->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR);
    }

    public function getUsersConcerneByObjet($idObjet, $domaineIds)
    {
        $qb = $this->_em->createQueryBuilder();

        return $qb->select('clt')
                    ->from('HopitalNumeriqueObjetBundle:Consultation', 'clt')
                    ->leftJoin('clt.objet', 'obj')
                        ->andWhere('obj.id = :idObjet')
                    ->leftJoin('clt.domaine', 'domaine')
                        ->andWhere('domaine.id IN (:domaineIds)')
                        ->setParameters([
                            'idObjet' => $idObjet,
                            'domaineIds' => $domaineIds,
                        ])
                    ->leftJoin('clt.user', 'user')
                    ->groupBy('user')
                    ->orderBy('clt.dateLastConsulted', 'DESC');
    }

    /**
     * Get nombre consultations.
     *
     * @return int
     */
    public function getNbConsultations($domaineId = null)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('COUNT(clt)')
        ->from('\HopitalNumerique\ObjetBundle\Entity\Consultation', 'clt');

        if (!is_null($domaineId)) {
            $qb->leftJoin('clt.domaine', 'domaine')
                ->andWhere('domaine.id = :domaineId')
                ->setParameter('domaineId', $domaineId);
        }

        return $qb;
    }

    /**
     * @param User $user
     *
     * @return integer
     */
    public function countViewsForUser(User $user)
    {
        return $this->_em->createQueryBuilder()
            ->select('COUNT(c)')
            ->from(Consultation::class, 'c')
            ->andWhere('c.user = :userId')->setParameter('userId', $user->getId())
            ->andWhere('c.contenu IS NULL')

            ->getQuery()->getSingleScalarResult()
        ;
    }
}
