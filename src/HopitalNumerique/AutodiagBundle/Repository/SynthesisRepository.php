<?php

namespace HopitalNumerique\AutodiagBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\AutodiagEntry;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\UserBundle\Entity\User;

class SynthesisRepository extends EntityRepository
{
    /**
     * @param $id
     *
     * @return Synthesis|null
     */
    public function getFullyLoadedSynthesis($id)
    {
        $qb = $this->createQueryBuilder('synthesis');
        $qb
            ->addSelect('autodiag', 'user', 'entries_user', 'entries')
            ->leftJoin('synthesis.user', 'user')
            ->leftJoin('synthesis.autodiag', 'autodiag')
            ->leftJoin('synthesis.entries', 'entries')
            ->leftJoin('entries.user', 'entries_user')
            ->where('synthesis.id = :id')
            ->setParameters([
                'id' => $id,
            ])
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Retourne les synthèses de l'utilisateur et les synthèses qui lui ont été partagées.
     *
     * @param User $user
     *
     * @return array
     */
    public function findByUser(User $user, Domaine $domain = null)
    {
        $qb = $this->createByUserQueryBuilder($user, $domain);
        $qb
            ->orderBy('synthesis.updatedAt', 'desc')
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * Retourne les synthèses de l'utilisateur et les synthèses qui lui ont été partagées
     * pour l'autodiag passé en paramètre (et éventuellement le domaine).
     *
     * @param User         $user
     * @param Domaine|null $domain
     * @param Autodiag     $autodiag
     *
     * @return array
     */
    public function findByUserAndAutodiag(User $user, Autodiag $autodiag, Domaine $domain = null)
    {
        $qb = $this->createByUserQueryBuilder($user, $domain);
        $qb
            ->andWhere('synthesis.autodiag = :autodiag')
            ->setParameter('autodiag', $autodiag)
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * Find comparable syntheses for user and by domaine.
     * That means that there must be at least 2 syntheses for one autodiag
     *
     * @param User         $user
     * @param Domaine|null $domaine
     *
     * @return array
     */
    public function findComparable(User $user, Domaine $domaine = null, Autodiag $autodiag = null)
    {
        $qb = $this->createComparableQueryBuilder($user, $domaine, $autodiag);

        $comparable = $qb->getQuery()->getResult();

        $autodiagGroup = [];
        /** @var Synthesis $synthesis */
        foreach ($comparable as $synthesis) {
            $id = $synthesis->getAutodiag()->getId();
            array_key_exists($id, $autodiagGroup) ? $autodiagGroup[$id]++ : $autodiagGroup[$id] = 1;
        }

        return array_filter($comparable, function (Synthesis $synthesis) use ($autodiagGroup) {
            return $autodiagGroup[$synthesis->getAutodiag()->getId()] > 1;
        });
    }

    /**
     * Find syntheses comparable to $reference.
     *
     * @param Synthesis    $reference
     * @param User         $user
     * @param Domaine|null $domaine
     *
     * @return array
     */
    public function findComparableWith(Synthesis $reference, User $user, Domaine $domaine = null)
    {
        $qb = $this->createComparableQueryBuilder($user, $domaine);
        $qb
            ->andWhere('autodiag = :autodiag_id')
            ->setParameter('autodiag_id', $reference->getAutodiag()->getId())
            ->andWhere('synthesis.id != :reference_id')
            ->setParameter('reference_id', $reference->getId())
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * Check if there is more than one comparable Synthesis for autodiag and user.
     *
     * @param User     $user
     * @param Autodiag $autodiag
     *
     * @return bool
     */
    public function hasComparableForAutodiag(User $user, Autodiag $autodiag)
    {
        $qb = $this->createComparableQueryBuilder($user, null, $autodiag);

        return count($qb->getQuery()->getArrayResult()) > 1;
    }

    /**
     * Retourne les synthèses qui contiennent l'entry en paramètre.
     *
     * @param AutodiagEntry $entry
     *
     * @return array
     */
    public function findSynthesesByEntry(AutodiagEntry $entry)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select('synthesis')
            ->from('HopitalNumeriqueAutodiagBundle:Synthesis', 'synthesis')
            ->join('synthesis.entries', 'entries', Join::WITH, 'entries.id = :entry')
            ->setParameters([
                'entry' => $entry->getId(),
            ])
        ;

        return $qb->getQuery()->getResult();
    }

    public function getDatasForGrid(Autodiag $autodiag)
    {
        $qb = $this->createQueryBuilder('synthesis');
        $qb
            ->select(
                'synthesis',
                'user',
                'etablissement',
                'shares',
                'entries'
            )
            ->leftJoin('synthesis.user', 'user')
            ->leftJoin('user.organization', 'etablissement')
            ->leftJoin('synthesis.shares', 'shares')
            ->leftJoin('synthesis.entries', 'entries')
            ->where('synthesis.autodiag = :autodiag')
            ->andWhere('synthesis.createdFrom IS NULL')
            ->setParameters([
                'autodiag' => $autodiag->getId(),
            ])
        ;

        return $qb;
    }

    public function markAsComputingByAutodiag(Autodiag $autodiag)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->update('HopitalNumeriqueAutodiagBundle:Synthesis', 'synthesis')
            ->set('synthesis.computeBeginning', time())
            ->where('synthesis.autodiag = :autodiag_id')
            ->setParameter('autodiag_id', $autodiag->getId())
        ;
        $qb->getQuery()->execute();
    }

    public function getScorePolling($synthesis)
    {
        $qb = $this->createQueryBuilder('synthesis');
        $qb
            ->select('synthesis.id')
            ->where(
                $qb->expr()->isNotNull('synthesis.computeBeginning')
            )
            ->andWhere(
                $qb->expr()->in('synthesis.id', $synthesis)
            );

        $result = $qb->getQuery()->getArrayResult();
        foreach ($result as &$one) {
            $one = $one['id'];
        }

        return $result;
    }

    /**
     * Find syntheses with only one entry.
     *
     * @param $ids
     *
     * @return array
     */
    public function findSimpleByIds($ids)
    {
        $qb = $this->createQueryBuilder('s');
        $qb
            ->join('s.entries', 'entries')
            ->where('s.id in (:ids)')
            ->groupBy('s.id')
            ->having('count(entries.id) = 1')
            ->setParameter('ids', $ids);

        return $qb->getQuery()->getResult();
    }

    /**
     * Find syntheses ids with only one entry.
     *
     * @param $ids
     *
     * @return array
     */
    public function findSimpleIdsByIds($ids)
    {
        $qb = $this->createQueryBuilder('s', 's.id');
        $qb
            ->select('s.id')
            ->join('s.entries', 'entries')
            ->where('s.id in (:ids)')
            ->groupBy('s.id')
            ->having('count(entries.id) = 1')
            ->setParameter('ids', $ids);

        return array_keys($qb->getQuery()->getArrayResult());
    }

    public function getSynthesisDetailsForExport($synthesisId)
    {
        $qb = $this->createQueryBuilder('s');
        $qb
            ->select(
                'CONCAT(user.firstname, \' \', user.lastname) as fullname',
                'etab.nom as etablissement',
                'user.organizationLabel as autre_etablissement',
                's.name',
                's.createdAt',
                's.updatedAt',
                's.validatedAt',
                's.completion'
            )
            ->join('s.user', 'user')
            ->leftJoin('user.organization', 'etab')
            ->where('s.id = :id')
            ->setParameter('id', $synthesisId);

        return $qb->getQuery()->getOneOrNullResult(Query::HYDRATE_ARRAY);
    }

    protected function createByUserQueryBuilder(User $user, Domaine $domain = null, Autodiag $autodiag = null)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select('synthesis', 'entries')
            ->from('HopitalNumeriqueAutodiagBundle:Synthesis', 'synthesis')
            ->join('synthesis.autodiag', 'autodiag')
            ->leftJoin('synthesis.shares', 'shares', Join::WITH, 'shares = :user')
            ->leftJoin('synthesis.entries', 'entries')
            ->orWhere('synthesis.user = :user')
            ->orWhere('shares.id IS NOT NULL')
            ->andWhere('synthesis.createdFrom IS NULL')
            ->setParameter('user', $user)
        ;

        if (null !== $domain) {
            $qb
                ->join('autodiag.domaines', 'domaines', Join::WITH, 'domaines = :domain')
                ->setParameter('domain', $domain)
            ;
        }

        if (null !== $autodiag) {
            $qb
                ->andWhere('autodiag.id = :autodiag_id')
                ->setParameter('autodiag_id', $autodiag->getId())
            ;
        }

        return $qb;
    }

    /**
     * A synthesis is comparable if :
     *  - it is validated
     *  - it is on autodiag wich allow comparison
     *  - has only one entry
     *
     * @param User $user
     * @param Domaine|null $domaine
     * @param Autodiag|null $autodiag
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function createComparableQueryBuilder(User $user, Domaine $domaine = null, Autodiag $autodiag = null)
    {
        $qb = $this->createByUserQueryBuilder($user, $domaine, $autodiag);
        $qb
            ->andWhere(
                $qb->expr()->isNotNull('synthesis.validatedAt')
            )
            ->andWhere(
                $qb->expr()->eq('autodiag.comparisonAuthorized', true)
            )
            ->groupBy('synthesis.id')
            ->having('count(entries) = 1')
            ->orderBy('synthesis.updatedAt', 'desc')
        ;

        return $qb;
    }

    /**
     * @param User           $user
     * @param Domaine[]|null $domains
     *
     * @return array
     */
    public function findByUserOrderedByAutodiagNameAndSynthesisUpdate(User $user, $domains = null)
    {
        $qb = $this->createQueryBuilder('synthesis')
            ->addSelect('autodiag', 'shares', 'presets', 'entries', 'user')
            ->leftJoin('synthesis.autodiag', 'autodiag')
            ->leftJoin('synthesis.shares', 'shares')
            ->join('synthesis.user', 'user', Join::WITH, 'user.id = :userId OR shares.id = :userId')
            ->leftJoin('autodiag.presets', 'presets')
            ->join('synthesis.entries', 'entries')
            ->setParameter('userId', $user->getId())
        ;
        
        if (null !== $domains) {
            $qb->join('autodiag.domaines', 'domains', Join::WITH, 'domains.id IN (:domains)')
                ->setParameter('domains', $domains)
                ->addSelect('domains')
            ;
        }

        return $qb->orderBy('autodiag.title')
           ->addOrderBy('synthesis.updatedAt', 'DESC')
           ->getQuery()
           ->getResult()
        ;
    }
}
