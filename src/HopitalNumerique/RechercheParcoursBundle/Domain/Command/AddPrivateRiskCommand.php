<?php

namespace HopitalNumerique\RechercheParcoursBundle\Domain\Command;

use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearch;
use Symfony\Component\Validator\Constraints as ASsert;

class AddPrivateRiskCommand
{
    /**
     * @var GuidedSearch $guidedSearch
     */
    public $guidedSearch;

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
     * @param GuidedSearch $guidedSearch
     * @param User|null $user
     */
    public function __construct(GuidedSearch $guidedSearch, User $user = null)
    {
        $this->guidedSearch = $guidedSearch;
        $this->user = $user;
    }
}
