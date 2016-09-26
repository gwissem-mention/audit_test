<?php

namespace HopitalNumerique\AutodiagBundle\Repository;

use Doctrine\ORM\EntityRepository;
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
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select('synthesis', 'entries')
            ->from('HopitalNumeriqueAutodiagBundle:Synthesis', 'synthesis')
            ->leftJoin('synthesis.shares', 'shares', Join::WITH, 'shares = :user')
            ->leftJoin('synthesis.entries', 'entries')
//            ->leftJoin('entries.values', 'values')
            ->orWhere('synthesis.user = :user')
            ->orWhere('shares.id IS NOT NULL')
            ->setParameter('user', $user)
        ;

        if ($domain != null) {
            $qb
                ->join('synthesis.autodiag', 'autodiag')
                ->join('autodiag.domaines', 'domaines', Join::WITH, 'domaines = :domain')
                ->setParameter('domain', $domain)
            ;
        }

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
}
