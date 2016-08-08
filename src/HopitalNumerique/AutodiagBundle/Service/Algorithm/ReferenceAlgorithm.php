<?php

namespace HopitalNumerique\AutodiagBundle\Service\Algorithm;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Reference;

class ReferenceAlgorithm
{
    public static function compute(Reference $reference, $values)
    {
        switch ($reference->getValue()) {
            case 'moyenne':
                return static::average($values);
            case 'mediane':
                return static::mediane($values);
            default:
                if (strpos($reference->getValue(), 'decile') === 0) {
                    return static::decile(substr($reference->getValue(), -1), $values);
                }
        }
        return null;
    }

    public static function average($values)
    {
        return array_sum($values) / count($values);
    }

    public static function mediane($values)
    {
        sort($values);
        $count = count($values);
        $middleval = floor(($count-1)/2);
        if ($count % 2) {
            $median = $values[$middleval];
        } else {
            $low = $values[$middleval];
            $high = $values[$middleval + 1];
            $median = (($low + $high) / 2);
        }
        return $median;
    }

    public static function decile($decile, $values)
    {
        sort($values);
        $serieLength = count($values);
        $decileCount = $serieLength / 10;

        if ($decileCount % 1 === 0) {
            // Valeur entières
            return $values[$decile * $decileCount];
        } else {
            // Valeur décimale
            return $values[ceil($decile * $decileCount)];
        }
    }
}
