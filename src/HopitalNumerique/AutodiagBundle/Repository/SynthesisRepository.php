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
                'id' => $id
            ])
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Retourne les synthèses de l'utilisateur et les synthèses qui lui ont été partagées
     *
     * @param User $user
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
     * pour l'autodiag passé en paramètre (et éventuellement le domaine)
     *
     * @param User $user
     * @param Domaine|null $domain
     * @param Autodiag $autodiag
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
     * Find comparable syntheses for user and by domaine
     *
     * @param User $user
     * @param Domaine|null $domaine
     * @return array
     */
    public function findComparable(User $user, Domaine $domaine = null)
    {
        $qb = $this->createComparableQueryBuilder($user, $domaine);

        return $qb->getQuery()->getResult();
    }

    /**
     * Find syntheses comparable to $reference
     *
     * @param Synthesis $reference
     * @param User $user
     * @param Domaine|null $domaine
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
     * Retourne les synthèses qui contiennent l'entry en paramètre
     *
     * @param AutodiagEntry $entry
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
                "synthesis",
                "user",
                "etablissement",
                "shares",
                "entries"
            )
            ->leftJoin('synthesis.user', 'user')
            ->leftJoin('user.etablissementRattachementSante', 'etablissement')
            ->leftJoin('synthesis.shares', 'shares')
            ->leftJoin('synthesis.entries', 'entries')
            ->where('synthesis.autodiag = :autodiag')
            ->setParameters([
                'autodiag' => $autodiag->getId()
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
     * Find syntheses with only one entry
     *
     * @param $ids
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
     * Find syntheses ids with only one entry
     *
     * @param $ids
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
                'CONCAT(user.prenom, \' \', user.nom) as fullname',
                'etab.nom as etablissement',
                'user.autreStructureRattachementSante as autre_etablissement',
                's.name',
                's.createdAt',
                's.updatedAt',
                's.validatedAt',
                's.completion'
            )
            ->join('s.user', 'user')
            ->leftJoin('user.etablissementRattachementSante', 'etab')
            ->where('s.id = :id')
            ->setParameter('id', $synthesisId);

        return $qb->getQuery()->getOneOrNullResult(Query::HYDRATE_ARRAY);
    }

    protected function createByUserQueryBuilder(User $user, Domaine $domain = null)
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
            ->setParameter('user', $user)
        ;

        if ($domain != null) {
            $qb
                ->join('autodiag.domaines', 'domaines', Join::WITH, 'domaines = :domain')
                ->setParameter('domain', $domain)
            ;
        }

        return $qb;
    }

    protected function createComparableQueryBuilder(User $user, Domaine $domaine = null)
    {
        $qb = $this->createByUserQueryBuilder($user, $domaine);
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
}
