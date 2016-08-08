<?php

namespace HopitalNumerique\AutodiagBundle\Service\Algorithm\Reference;

class Average
{
    public static function compute($values)
    {
        return array_sum($values) / count($values);
    }
}
