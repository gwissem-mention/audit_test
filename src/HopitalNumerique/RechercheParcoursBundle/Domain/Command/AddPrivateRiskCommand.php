<?php

namespace HopitalNumerique\RechercheParcoursBundle\Domain\Command;

use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use Symfony\Component\Validator\Constraints as ASsert;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearchStep;

class AddPrivateRiskCommand
{
    /**
     * @var GuidedSearchStep $guidedSearchStep
     */
    public $guidedSearchStep;

    /**
     * @var User|null $user
     */
    public $user;

    /**
     * @var Reference $nature
     * @Assert\NotBlank
     */
    public $nature;

    /**
     * @var string $label
     * @Assert\NotBlank
     */
    public $label;

    /**
     * AddPrivateRiskCommand constructor.
     *
     * @param GuidedSearchStep $guidedSearchStep
     * @param User|null $user
     */
    public function __construct(GuidedSearchStep $guidedSearchStep, User $user = null)
    {
        $this->guidedSearchStep = $guidedSearchStep;
        $this->user = $user;
    }
}
