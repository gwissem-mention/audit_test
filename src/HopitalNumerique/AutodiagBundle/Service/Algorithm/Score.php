<?php

namespace HopitalNumerique\AutodiagBundle\Service\Algorithm;

use Doctrine\ORM\EntityManager;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container;
use HopitalNumerique\AutodiagBundle\Entity\AutodiagEntry;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\AutodiagBundle\Service\Attribute\AttributeBuilderProvider;
use HopitalNumerique\AutodiagBundle\Service\Attribute\PresetableAttributeBuilderInterface;

class Score
{
    /**
     * @var EntityManager
     */
    protected $manager;

    /**
     * @var AttributeBuilderProvider
     */
    protected $attributeProvider;

    protected $scores = [];

    public function __construct(EntityManager $manager, AttributeBuilderProvider $attributeProvider)
    {
        $this->manager = $manager;
        $this->attributeProvider = $attributeProvider;
    }

    /**
     * Calcul le score
     *
     * @param Container $container
     * @param array $values
     * @return float|mixed|null
     */
    public function getScore(Container $container, array $values)
    {
        $autodiag = $container->getAutodiag();
        $containerIds = $container->getNestedContainerIds();
        $notConcerned = true;

        $sum = 0;
        $min = 0;
        $max = 0;
        foreach ($values as $value) {
            if (!in_array($value['container_id'], $containerIds)) {
                continue;
            }

            $builder = $this->attributeProvider->getBuilder($value['type']);
            $score = $builder->computeScore($value['value']);

            if (null !== $score && $score != "-1") {
                $notConcerned = false;
                if ($builder instanceof PresetableAttributeBuilderInterface) {
                    $attributeMin = $builder->getPresetMinScore($autodiag);
                    $attributeMax = $builder->getPresetMaxScore($autodiag);
                } else {
                    $attributeMin = $value['lowest'];
                    $attributeMax = $value['highest'];
                }

                $min += ($attributeMin * $value['weight']);
                $max += ($attributeMax * $value['weight']);
                $sum += ($score * $value['weight']);
            }
        }

        if ($notConcerned) {
            return null;
        }

        // Fonction affine
        $a = ($max - $min) / 100;

        $result = 0;
        if ($a > 0) {
            $result =  ($sum - $min) / $a;
        }

        return $result;
    }

    protected function getContainerIds(Container $container)
    {
        $ids = [$container->getId()];
        foreach ($container->getChilds() as $child) {
            $ids = array_merge($ids, $this->getContainerIds($child));
        }
        return $ids;
    }
}
