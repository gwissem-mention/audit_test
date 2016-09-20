<?php

namespace HopitalNumerique\AutodiagBundle\Repository\AutodiagEntry;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Attribute\Weight;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container;
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

    /**
     * @deprecated
     */
    public function getAllOriginalValuesByAutodiagAndContainer(Container $container)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "
            SELECT
                attribute.id AS attribute_id,
                attribute.type AS attribute_type,
                entry_value.value AS value,
                entry.id AS entry_id,
                attribute.type AS type,
                weight.weight as weight,
                weight.container_id as container_id,
                MAX(opt.option_value) as highest,
                MIN(opt.option_value) as lowest
            FROM ad_entry_value entry_value
            INNER JOIN ad_entry entry ON entry_value.autodiagentry_id = entry.id
            INNER JOIN ad_autodiag_attribute attribute ON entry_value.attribute_id = attribute.id
            INNER JOIN ad_autodiag_attribute_weight weight ON weight.attribute_id = attribute.id
            LEFT JOIN ad_autodiag_attribute_option opt ON opt.attribute_id = attribute.id AND opt.option_value <> '-1'
            WHERE entry.copy = 0
            AND weight.container_id IN (" . implode(',', $container->getNestedContainerIds()) . ")
            AND entry.copy = 0
            GROUP BY entry_value.id
        ";

        $stmt = $conn->query($sql);
        $data = [];
        while ($row = $stmt->fetch()) {
            if (!isset($data[$row['entry_id']])) {
                $data[$row['entry_id']] = [];
            }

            $data[$row['entry_id']][] = $row;
        }
        return $data;
    }

    /**
     * @deprecated
     */
    public function getSynthesisValuesByContainer($synthesisId, Container $container)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "
            SELECT
                attribute.id AS attribute_id,
                attribute.type AS attribute_type,
                entry_value.value AS value,
                entry.id AS entry_id,
                attribute.type AS type,
                weight.weight as weight,
                weight.container_id as container_id,
                MAX(opt.option_value) as highest,
                MIN(opt.option_value) as lowest
            FROM ad_entry_value entry_value
            INNER JOIN ad_entry entry ON entry_value.autodiagentry_id = entry.id
            INNER JOIN ad_synthesis_entry ase ON ase.entry_id = entry.id AND ase.synthesis_id = $synthesisId
            INNER JOIN ad_autodiag_attribute attribute ON entry_value.attribute_id = attribute.id
            INNER JOIN ad_autodiag_attribute_weight weight ON weight.attribute_id = attribute.id
            LEFT JOIN ad_autodiag_attribute_option opt ON opt.attribute_id = attribute.id AND opt.option_value <> '-1'
            WHERE weight.container_id IN (" . implode(',', $container->getNestedContainerIds()) . ")
            GROUP BY entry_value.id
        ";


        $stmt = $conn->query($sql);
        $data = [];
        while ($row = $stmt->fetch()) {
            if (!isset($data[$row['entry_id']])) {
                $data[$row['entry_id']] = [];
            }

            $data[$row['entry_id']][] = $row;
        }

        return $data;
    }

    /**
     * @deprecated
     */
    public function getSynthesisValues(Synthesis $synthesis)
    {
        $qb = $this->createQueryBuilder('v');
        $qb
            ->select(
                'attribute.id as attribute_id',
                'v.value'
            )
            ->join('v.attribute', 'attribute')
            ->join('v.entry', 'entry')
            ->join('entry.syntheses', 'syntheses')
            ->where('syntheses.id = :synthesis_id')
            ->groupBy('v.id')
            ->setParameter('synthesis_id', $synthesis->getId())
        ;

        return $qb->getQuery()->getArrayResult();
    }

    public function getSynthesisValuesForAlgorithm(Synthesis $synthesis)
    {
        $qb = $this->createQueryBuilder('v');
        $qb
            ->select(
                'GROUP_CONCAT(DISTINCT container.id) as container_id',
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
            ->leftJoin('attribute.options', 'options')
            ->join(Weight::class, 'container_weight', Join::WITH, 'container_weight.attribute = attribute.id')
            ->join('container_weight.container', 'container')
            ->where('syntheses.id = :synthesis_id')
            ->groupBy('v.id')
            ->addGroupBy('attribute.id')
            ->setParameter('synthesis_id', $synthesis->getId())
        ;

        return $qb->getQuery()->getArrayResult();
    }
}
