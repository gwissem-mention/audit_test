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

    public function getScore(Synthesis $synthesis, Container $container)
    {
        $key = $this->getCacheKey($synthesis->getAutodiag(), $container, $synthesis);
        if (array_key_exists($key, $this->scores)) {
            return $this->scores[$key];
        }


        $autodiag = $synthesis->getAutodiag();
        $repository = $this->manager->getRepository(AutodiagEntry\Value::class);
        $values = $repository->getValuesAndWeight($synthesis, $container);

        $sum = 0;
        $min = 0;
        $max = 0;
        foreach ($values as $value) {
            $builder = $this->attributeProvider->getBuilder($value['type']);
            $score = $builder->computeScore($value['value']);
            if (null !== $score && $score > -1) {
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

        // Fonction affine
        $a = ($max - $min) / 100;
        if ($a > 0) {
            return ($sum - $min) / $a;
        }
        return null;

        if ($max > 0) {
            return round($sum / $max * 100);
        }
        return null;
    }

    protected function getAttributeBounds(PresetableAttributeBuilderInterface $builder, $min, $max)
    {

    }

    protected function getCacheKey(Autodiag $autodiag, Container $container, Synthesis $synthesis)
    {
        return 'SCORE__'
        . $autodiag->getId()
        . $container->getId()
        . $synthesis->getId();
    }
}
