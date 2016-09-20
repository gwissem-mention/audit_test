<?php

namespace HopitalNumerique\AutodiagBundle\Repository;

use Doctrine\ORM\EntityRepository;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag;

class AutodiagEntryRepository extends EntityRepository
{
    /**
     * Get original entries (not copies) by autodiag
     *
     * @param Autodiag $autodiag
     * @return array
     */
    public function findOriginalByAutodiag(Autodiag $autodiag)
    {
        $qb = $this->createQueryBuilder('entry');
        $qb
            ->addSelect('values')
            ->join('entry.syntheses', 'syntheses')
            ->leftJoin('entry.values', 'values')
            ->where('syntheses.autodiag = :autodiag_id')
            ->andWhere('entry.copy = FALSE')
            ->setParameters([
                'autodiag_id' => $autodiag->getId()
            ])
        ;
return $qb->getQuery()->getArrayResult();
        dump($qb->getQuery()->getArrayResult());die;

        return $qb->getQuery()->getResult();
    }

    public function getEntriesForReferenceGenerator($autodiagId)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "
            SELECT entry.id AS entry_id
            FROM ad_entry entry
            INNER JOIN ad_synthesis_entry ase ON ase.entry_id = entry.id
            INNER JOIN ad_synthesis synthesis ON synthesis.id = ase.synthesis_id
            WHERE synthesis.autodiag_id = $autodiagId
            AND entry.copy = 0
            GROUP BY entry.id
        ";

        $stmt = $conn->query($sql);
        $data = [];
        while ($row = $stmt->fetch()) {
            yield $row['entry_id'];
        }
    }
}
