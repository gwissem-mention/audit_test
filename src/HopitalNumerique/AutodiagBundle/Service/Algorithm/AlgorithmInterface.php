<?php

namespace HopitalNumerique\AutodiagBundle\Service\Algorithm;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container;

interface AlgorithmInterface
{
    public function getScore(Container $container, array $values);
}
