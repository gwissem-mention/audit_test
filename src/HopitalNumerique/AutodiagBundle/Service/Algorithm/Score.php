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
     * @TODO: supprimer le getValuesAndWeight car génère trop de requêtes. Utiliser les valeurs de Entry[] et stocker...
     *          ...en locale le weight par container/attribute ainsi que le min/max. Peut être faire une grosse requête
     *          une seule fois pour récupérer toutes ces valeurs
     *
     * @param Autodiag $autodiag
     * @param Container $container
     * @param array $entries
     * @return float|mixed|null
     */
    public function getScore(Autodiag $autodiag, Container $container, array $entries)
    {
        $key = $this->getCacheKey($autodiag, $container, $entries);

        if (array_key_exists($key, $this->scores)) {
            return $this->scores[$key];
        }

        $repository = $this->manager->getRepository(AutodiagEntry\Value::class);
        $values = $repository->getValuesAndWeight($entries);
        $containerIds = $this->getContainerIds($container);

        $sum = 0;
        $min = 0;
        $max = 0;
        foreach ($values as $value) {
            if (!in_array($value['container_id'], $containerIds)) {
                continue;
            }

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
    }

    protected function getContainerIds(Container $container)
    {
        $ids = [$container->getId()];
        foreach ($container->getChilds() as $child) {
            $ids = array_merge($ids, $this->getContainerIds($child));
        }
        return $ids;
    }

    protected function getCacheKey(Autodiag $autodiag, Container $container, array $entries)
    {
        return 'SCORE__'
            . $autodiag->getId()
            . $container->getId()
            . implode('-', array_map(function (AutodiagEntry $entry) {
                return $entry->getId();
            }, $entries))
        ;
    }
}
