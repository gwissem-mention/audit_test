<?php

namespace HopitalNumerique\AutodiagBundle\Service\Synthesis;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use Symfony\Component\Config\Definition\Exception\Exception;

class SynthesisGenerator
{
    const SYNTHESIS_NOT_ALLOWED = 1;
    const NEED_AT_LEAST_2 = 2;

    public function generateSynthesis(Autodiag $autodiag, $syntheses)
    {
        if (!$autodiag->isSynthesisAuthorized()) {
            throw new Exception('synthesis_not_allowed', SynthesisGenerator::SYNTHESIS_NOT_ALLOWED);
        }

        if (count($syntheses) < 2) {
            throw new Exception('need_at_least_2', SynthesisGenerator::NEED_AT_LEAST_2);
        }

        $synthesis = Synthesis::create($autodiag);
        /** @var \HopitalNumerique\AutodiagBundle\Entity\Synthesis $synthese */
        foreach ($syntheses as $synthese) {
            $syntheseEntries = $synthese->getEntries();

            foreach ($syntheseEntries as $entry) {
                $copy = clone($entry);
                $copy->setCopy(true);
                $synthesis->addEntry($copy);
            }
        }

        return $synthesis;
    }
}

