<?php

namespace HopitalNumerique\AutodiagBundle\Service\Algorithm;

class Score
{
    public function getScore($entry, $container)
    {
        return $this->getSumScore() / $this->getSumMax();
    }
}
