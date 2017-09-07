<?php

namespace HopitalNumerique\RechercheParcoursBundle\Domain\Command;

use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearchStep;
use HopitalNumerique\RechercheParcoursBundle\Service\GuidedSearchStepProgress;

/**
 * Class FindFirstUncompletedStepCommand
 */
class FindFirstUncompletedStepHandler
{
    /**
     * @var GuidedSearchStepProgress
     */
    protected $guidedSearchStepProgress;

    /**
     * FindFirstUncompletedStepHandler constructor.
     *
     * @param GuidedSearchStepProgress $guidedSearchStepProgress
     */
    public function __construct(GuidedSearchStepProgress $guidedSearchStepProgress)
    {
        $this->guidedSearchStepProgress = $guidedSearchStepProgress;
    }

    /**
     * Returns the first uncompleted step of the guided search for the current user.
     *
     * @param FindFirstUncompletedStepCommand $command
     *
     * @return GuidedSearchStep|null
     */
    public function handle(FindFirstUncompletedStepCommand $command)
    {
        $steps = $this->guidedSearchStepProgress->getUncompletedSteps(
            $command->guidedSearch,
            $command->user
        );

        $firstUncompletedStep = current($steps) ?: null;

        foreach ($command->rechercheParcoursDetails as $detail) {
            foreach ($steps as $step) {
                if ($detail->getId() === (int) explode(':', $step->getStepPath())[0]) {
                    if (!$detail->getShowChildren()) {
                        return $step;
                    }

                    foreach ($detail->getReference()->getEnfants() as $enfant) {
                        if ($enfant->getId() === (int) explode(':', $step->getStepPath())[1]) {
                            return $step;
                        }
                    }
                }
            }
        }

        return $firstUncompletedStep;
    }
}
