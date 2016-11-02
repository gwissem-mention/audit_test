<?php

namespace HopitalNumerique\AutodiagBundle\Repository\AutodiagEntry;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Attribute\Weight;
use HopitalNumerique\AutodiagBundle\Entity\AutodiagEntry;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;

class ValueRepository extends EntityRepository
{
    /**
     * @deprecated
     */
    public function getValuesAndWeight(array $entries)
    {
        $qb = $this->createQueryBuilder('v');
        $qb
            ->select(
                'container.id as container_id',
                'v.value',
                'weight.weight',
                'attribute.type',
                'MIN(options.value) as lowest',
                'MAX(options.value) as highest'
            )
            ->join('v.entry', 'entry')
            ->join('v.attribute', 'attribute')
            ->leftJoin('attribute.options', 'options', Join::WITH, 'options.value != -1')
            ->join(Weight::class, 'weight', Join::WITH, 'attribute.id = weight.attribute')
            ->join('weight.container', 'container')
            ->where('entry.id IN (:entries_id)')
            ->groupBy('v.id')
            ->addGroupBy('container.id')
            ->setParameters([
                'entries_id' => array_map(function (AutodiagEntry $entry) {
                    return $entry->getId();
                }, $entries),
            ]);

        return $qb->getQuery()->getResult();
    }

    public function getSynthesisValuesForAlgorithm(Synthesis $synthesis)
    {
        $qb = $this->createQueryBuilder('v');
        $qb
            ->select(
                'container.id as container_id',
                'attribute.id as attribute_id',
                'attribute.type as type',
                'container_weight.weight as weight',
                'MAX(options.value) as highest',
                'MIN(options.value) as lowest',
                'v.value'
            )
            ->join('v.entry', 'entry')
            ->join('entry.syntheses', 'syntheses')
            ->join('v.attribute', 'attribute')
            ->leftJoin('attribute.options', 'options', Join::WITH, 'options.value != \'-1\'')
            ->join(Weight::class, 'container_weight', Join::WITH, 'container_weight.attribute = attribute.id')
            ->join('container_weight.container', 'container')
            ->where('syntheses.id = :synthesis_id')
            ->groupBy('attribute.id')
            ->addGroupBy('container.id')
            ->addGroupBy('v.id')
            ->setParameter('synthesis_id', $synthesis->getId())
        ;

        return $qb->getQuery()->getArrayResult();
    }

    public function getAnswersCount(Synthesis $synthesis)
    {
        $qb = $this->createQueryBuilder('v');
        $qb
            ->select('count(distinct v.attribute) as total', 'container.id as container_id')
            ->join('v.entry', 'entry')
            ->join('entry.syntheses', 'syntheses')
            ->join('v.attribute', 'attribute')
            ->join(Weight::class, 'weight', Join::WITH, 'weight.attribute = attribute.id')
            ->join('weight.container', 'container')
            ->where('syntheses.id = :synthesis_id')
            ->andWhere('v.valid = TRUE')
            ->groupBy('container.id')
            ->setParameter('synthesis_id', $synthesis->getId())
        ;

        $result = $qb->getQuery()->getArrayResult();
        $data = [];
        foreach ($result as $count) {
            $data[$count['container_id']] = (int) $count['total'];
        }

        return $data;
    }

    public function getGlobalCompletion(Synthesis $synthesis)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select('count(syntheses.id) * 100 / count(distinct attribute.id)')
            ->from('HopitalNumeriqueAutodiagBundle:Autodiag\Attribute', 'attribute')
            ->leftJoin(AutodiagEntry\Value::class, 'val', Join::WITH, 'val.attribute = attribute.id AND val.valid = TRUE')
            ->leftJoin('val.entry', 'entry')
            ->leftJoin('entry.syntheses', 'syntheses', Join::WITH, 'syntheses.id = :synthesis_id')
            ->where('attribute.autodiag = :autodiag_id')
            ->setParameters([
                'autodiag_id' => $synthesis->getAutodiag()->getId(),
                'synthesis_id' => $synthesis->getId(),
            ])
        ;

        return floor($qb->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR));
    }

    public function getFullValuesByEntry($autodiagId, $entryId)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->from('HopitalNumeriqueAutodiagBundle:Autodiag\Attribute', 'attribute')
            ->select(
                'val.id as value_id, val.value as value_value, val.comment as value_comment',
                'entry.id as entry_id',
                'attribute.id as attribute_id, attribute.code, attribute.label as attribute_label,
                    attribute.type, attribute.colored, attribute.colorationInversed',
                'options.value as option_value, options.label as option_label',
                'presets.preset',
                'group_concat(container.id) as container_id'
            )
            ->leftJoin(
                AutodiagEntry\Value::class,
                'val',
                Join::WITH,
                'val.attribute = attribute.id AND val.entry = :entry_id'
            )
            ->leftJoin('val.entry', 'entry')
            ->join(Weight::class, 'weight', Join::WITH, 'weight.attribute = attribute.id')
            ->join('weight.container', 'container')
            ->leftJoin('attribute.options', 'options', Join::WITH, 'options.value = val.value')
            ->join('attribute.autodiag', 'autodiag')
            ->leftJoin('autodiag.presets', 'presets')
            ->groupBy('attribute.id')
            ->orderBy('attribute.order', 'ASC')
            ->where('autodiag.id = :autodiag_id')
            ->setParameters([
                'entry_id' => $entryId,
                'autodiag_id' => $autodiagId
            ])
        ;

        $results = $qb->getQuery()->getArrayResult();

        array_walk($results, function (&$result) {
            $result['container_id'] = explode(',', $result['container_id']);
        });

        return $results;
    }

    /**
     * Get entry values intersection from $synthesis, between $synthesis and $reference
     *
     * @param Synthesis $synthesis
     * @param Synthesis $reference
     * @return array
     */
    public function findAttributeIdsIntersection(Synthesis $synthesis, Synthesis $reference)
    {
        $qb = $this->createQueryBuilder('v');
        $qb
            ->select('attribute.id')
            ->join('v.attribute', 'attribute')
            ->join('v.entry', 'entry')
            ->join('entry.syntheses', 'synthesis')

            ->join(Synthesis::class, 'reference', Join::WITH, 'reference.id = :reference_id')
            ->join('reference.entries', 'reference_entry')
            ->join('reference_entry.values', 'values', Join::WITH, 'values.attribute = v.attribute')

            ->where('synthesis.id = :synthesis_id')
            ->setParameters([
                'synthesis_id' => $synthesis->getId(),
                'reference_id' => $reference->getId(),
            ])
        ;

        $data = $qb->getQuery()->getResult();
        $result = [];

        array_walk($data, function ($element) use (&$result) {
             $result[$element['id']] = true;
        });

        return $result;
    }

    public function getFullValues($autodiagId, $entries)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->from('HopitalNumeriqueAutodiagBundle:Autodiag\Attribute', 'attribute')
            ->select(
                'val.id as value_id, val.value as value_value, val.comment as value_comment',
                'entry.id as entry_id',
                'attribute.id as attribute_id, attribute.code, attribute.label as attribute_label,
                    attribute.type, attribute.colored, attribute.colorationInversed',
                'options.value as option_value, options.label as option_label',
                'presets.preset',
                'group_concat(container.id) as container_id'
            )
            ->leftJoin(
                AutodiagEntry\Value::class,
                'val',
                Join::WITH,
                'val.attribute = attribute.id AND val.entry IN (:entries_id)'
            )
            ->leftJoin('val.entry', 'entry')
            ->join(Weight::class, 'weight', Join::WITH, 'weight.attribute = attribute.id')
            ->join('weight.container', 'container')
            ->leftJoin('attribute.options', 'options', Join::WITH, 'options.value = val.value')
            ->join('attribute.autodiag', 'autodiag')
            ->leftJoin('autodiag.presets', 'presets')
            ->groupBy('attribute.id')
            ->addGroupBy('entry.id')
            ->where('autodiag.id = :autodiag_id')
            ->setParameters([
                'entries_id' => implode(', ', $entries),
                'autodiag_id' => $autodiagId,
            ])
        ;

        $results = $qb->getQuery()->getArrayResult();

        array_walk($results, function (&$result) {
            $result['container_id'] = explode(',', $result['container_id']);
        });

        return $results;
    }
}
