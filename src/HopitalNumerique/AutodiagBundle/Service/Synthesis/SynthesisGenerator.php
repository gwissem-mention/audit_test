<?php

namespace HopitalNumerique\AutodiagBundle\Service\Synthesis;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\AutodiagEntry;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\AutodiagBundle\Event\SynthesisGeneratedEvent;
use HopitalNumerique\AutodiagBundle\Events;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SynthesisGenerator
{
    const SYNTHESIS_NOT_ALLOWED = 1;
    const NEED_AT_LEAST_2 = 2;
    const SYNTHESIS_NOT_VALIDATED = 3;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param Autodiag $autodiag
     * @param $syntheses
     * @param User $user
     *
     * @return Synthesis
     *
     * @throws \Exception
     */
    public function generateSynthesis(Autodiag $autodiag, $syntheses, User $user)
    {
        if (!$autodiag->isSynthesisAuthorized()) {
            throw new \Exception('synthesis_not_allowed', self::SYNTHESIS_NOT_ALLOWED);
        }

        if (count($syntheses) < 2) {
            throw new \Exception('need_at_least_2', self::NEED_AT_LEAST_2);
        }

        $newSynthesis = Synthesis::create($autodiag);

        $newEntries = [];
        /** @var \HopitalNumerique\AutodiagBundle\Entity\Synthesis $synthesis */
        foreach ($syntheses as $synthesis) {
            if (!$synthesis->isValidated()) {
                throw new \Exception('Synthesis is not valide', self::SYNTHESIS_NOT_VALIDATED);
            }

            $syntheseEntries = $synthesis->getEntries();
            /** @var AutodiagEntry $entry */
            foreach ($syntheseEntries as $entry) {
                if ($entry->isCopy()) {
                    $newEntries[] = $entry;
                } else {
                    $copy = clone $entry;
                    $copy->setCopy(true);
                    $newEntries[] = $copy;
                }
            }
        }

        foreach ($newEntries as $entry) {
            $entry->addSynthesis($newSynthesis);
            $newSynthesis->addEntry($entry);
        }

        $newSynthesis->setUser($user);
        $newSynthesis->validate();

        $event = new SynthesisGeneratedEvent($newSynthesis, $syntheses);
        $this->eventDispatcher->dispatch(Events::SYNTHESIS_GENERATED, $event);

        return $newSynthesis;
    }
}
