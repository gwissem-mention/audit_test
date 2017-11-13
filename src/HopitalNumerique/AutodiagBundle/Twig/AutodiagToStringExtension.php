<?php

namespace HopitalNumerique\AutodiagBundle\Twig;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag;

class AutodiagToStringExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('autodiagToString', [$this, 'getAutodiagString']),
        ];
    }

    /**
     * Set autodiag as a string to detect all glossary words.
     *
     * @param Autodiag $autodiag
     *
     * @return string
     */
    public function getAutodiagString(Autodiag $autodiag)
    {
        $stringParts = [
            $autodiag->getInstructions(),
        ];

        /** @var Autodiag\Container\Chapter $chapter */
        foreach ($autodiag->getChapters() as $chapter) {
            $stringParts[] = $chapter->getTitle();
            $stringParts[] = $chapter->getDescription();
            $stringParts[] = $chapter->getAdditionalDescription();

            foreach ($chapter->getAttributes() as $attribute) {
                $stringParts[] = $attribute->getAdditionalDescription();
                $stringParts[] = $attribute->getDescription();
                $stringParts[] = $attribute->getLabel();
                $stringParts[] = $attribute->getExtendedLabel();
                $stringParts[] = $attribute->getTooltip();
            }
        }

        /** @var Autodiag\Attribute $attribute */
        foreach ($autodiag->getAttributes() as $attribute) {
            $stringParts[] = $attribute->getAdditionalDescription();
            $stringParts[] = $attribute->getDescription();
            $stringParts[] = $attribute->getLabel();
            $stringParts[] = $attribute->getExtendedLabel();
            $stringParts[] = $attribute->getTooltip();
        }

        return implode(' ', $stringParts);
    }

    public function getName()
    {
        return 'hopitalnumerique_autodiag_to_string';
    }
}
