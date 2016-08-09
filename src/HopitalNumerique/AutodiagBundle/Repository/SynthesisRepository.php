<?php

namespace HopitalNumerique\AutodiagBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\AutodiagEntry\Value;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;

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
            ->addSelect('autodiag', 'entries', 'values')
            ->leftJoin('synthesis.autodiag', 'autodiag')
            ->leftJoin('synthesis.entries', 'entries')
            ->leftJoin('entries.values', 'values')
            ->where('synthesis.id = :id')
            ->setParameters([
                'id' => $id
            ])
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @deprecated Should not be used
     */
    public function getCompletion(Synthesis $synthesis)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select('attribute.id', 'count(value.id) as responses')
            ->from('HopitalNumeriqueAutodiagBundle:Autodiag', 'autodiag')
            ->join('autodiag.attributes', 'attribute')
            ->leftJoin(Value::class, 'value', Join::WITH, 'value.attribute = attribute.id')
            ->leftJoin('value.entry', 'entry')
            ->leftJoin('entry.synthesis', 'synthesis', Join::WITH, 'synthesis.id = :synthesis_id')
            ->groupBy('attribute.id')
            ->setParameters([
                'synthesis_id' => $synthesis->getId()
            ]);

        $queryResult = $qb->getQuery()->getResult();

        $result = [];
        array_walk($queryResult, function ($element) use (&$result) {
            $result[$element['id']] = (int)$element['responses'] > 0;
        });
        return $result;
    }

    public function findByUserId($idUser)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select('synthesis')
            ->from('HopitalNumeriqueAutodiagBundle:Synthesis', 'synthesis')
            ->where('synthesis.user = :user_id')
            ->setParameters([
               'user_id' => $idUser
            ]);

        return $qb->getQuery()->getResult();
    }
}
