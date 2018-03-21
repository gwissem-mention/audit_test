<?php

namespace HopitalNumerique\ObjetBundle\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use HopitalNumerique\ObjetBundle\Entity\Consultation;
use HopitalNumerique\ObjetBundle\Entity\Contenu;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\UserBundle\Entity\User;
use Doctrine\ORM\Query\Expr;

/**
 * ConsultationRepository.
 */
class ConsultationRepository extends EntityRepository
{
    const CONSULTATION_DELAY = 'PT15M';

    /**
     * Retourne les dernières consultations de l'user $user.
     *
     * @param User $user L'user connecté
     *
     * @return QueryBuilder
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
                    ->orderBy('clt.consultationDate', 'DESC');
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
            ->select('MAX(clt.consultationDate)')
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
            ->orderBy('clt.consultationDate', 'DESC')
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
                    ->orderBy('clt.consultationDate', 'DESC');
    }

    /**
     * Get nombre consultations.
     *
     * @return QueryBuilder
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

    /**
     * @param $parameters
     *
     * @return Consultation|null
     */
    public function findCurrentConsultation($parameters)
    {
        /** @var Consultation $consultation */
        $consultation = $this->findOneBy($parameters, ['consultationDate' => 'DESC']);

        if (
            null !== $consultation &&
            $consultation->getConsultationDate() >= (new \DateTimeImmutable())->sub(new \DateInterval(self::CONSULTATION_DELAY))
        ) {
            return $consultation;
        }

        return null;
    }

    /**
     * @param $objetId
     * @param \DateTimeImmutable $interval
     *
     * @return mixed
     */
    public function countConsultationsByObjectAndInterval($objetId, \DateTimeImmutable $interval)
    {
        return $this->createQueryBuilder('consultation')
            ->select('COUNT(consultation.id) as viewsCount')
            ->join('consultation.objet', 'objet', Expr\Join::WITH, 'objet.id = :objet')
            ->setParameter('objet', $objetId)
            ->andWhere('consultation.consultationDate >= :intervalDate')
            ->setParameter('intervalDate', $interval)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
