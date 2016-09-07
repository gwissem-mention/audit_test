<?php

namespace HopitalNumerique\AutodiagBundle\Service\Synthesis;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\AutodiagEntry;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\Config\Definition\Exception\Exception;

class SynthesisGenerator
{
    const SYNTHESIS_NOT_ALLOWED = 1;
    const NEED_AT_LEAST_2 = 2;

    /**
     * @param Autodiag $autodiag
     * @param $syntheses
     * @param User $user
     *
     * @return Synthesis
     */
    public function generateSynthesis(Autodiag $autodiag, $syntheses, User $user)
    {
        if (!$autodiag->isSynthesisAuthorized()) {
            throw new Exception('synthesis_not_allowed', SynthesisGenerator::SYNTHESIS_NOT_ALLOWED);
        }

        if (count($syntheses) < 2) {
            throw new Exception('need_at_least_2', SynthesisGenerator::NEED_AT_LEAST_2);
        }

        $newSynthesis = Synthesis::create($autodiag);

        /** @var \HopitalNumerique\AutodiagBundle\Entity\Synthesis $synthesis */
        foreach ($syntheses as $synthesis) {
            $syntheseEntries = $synthesis->getEntries();

            /** @var AutodiagEntry $entry */
            foreach ($syntheseEntries as $entry) {
                if ($entry->isCopy()) {
                    $entry->addSynthesis($newSynthesis);
                    $newSynthesis->addEntry($entry);
                } else {
                    $copy = clone($entry);
                    $copy->setCopy(true);
                    $copy->addSynthesis($newSynthesis);
                    $newSynthesis->addEntry($copy);
                }
            }
        }

        $newSynthesis->setUser($user);
        $newSynthesis->validate();

        return $newSynthesis;
    }
}

