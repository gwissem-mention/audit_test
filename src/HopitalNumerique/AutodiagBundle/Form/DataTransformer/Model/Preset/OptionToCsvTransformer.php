<?php
namespace HopitalNumerique\AutodiagBundle\Form\DataTransformer\Model\Preset;

use Doctrine\Common\Collections\ArrayCollection;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Preset\Attribute;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Preset\Attribute\Option;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * OptionToCsvTransformer transform CSVlike syntax to array, and reverse
 *
 * @package HopitalNumerique\AutodiagBundle\Form\DataTransformer\Model\Preset
 * @author Emmanuel Da Fonseca <edafonseca@nodevo.com>
 */
class OptionToCsvTransformer implements DataTransformerInterface
{
    const OPTION_VALUE_SEPARATOR = ";";

    public function transform($options)
    {
        if (null === $options) {
            return "";
        }

        if (!is_array($options)) {
            throw new TransformationFailedException('Invalid data.');
        }

        $csvLines = [];
        foreach ($options as $key => $option) {
            $csvLines[] = implode(self::OPTION_VALUE_SEPARATOR, [
                $key,
                $option,
            ]);
        }

        return implode(PHP_EOL, $csvLines);
    }

    public function reverseTransform($csv)
    {
        $collection = [];

        if (null !== $csv) {
            $lines = preg_split("/\\r\\n|\\r|\\n/", $csv);
            foreach ($lines as $line) {
                $values = explode(self::OPTION_VALUE_SEPARATOR, $line);
                if (count($values) != 2) {
                    throw new TransformationFailedException('Invalid data.');
                }

                if (strlen((float)$values[0]) != strlen($values[0])) {
                    throw new TransformationFailedException('Invalid data.');
                }
                $collection[$values[0]] = $values[1];
            }
        }

        return $collection;
    }
}
