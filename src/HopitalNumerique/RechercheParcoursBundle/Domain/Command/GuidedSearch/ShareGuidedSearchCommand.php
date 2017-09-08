<?php

namespace HopitalNumerique\RechercheParcoursBundle\Domain\Command\GuidedSearch;

use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearch;
use Symfony\Component\Validator\Constraints as Assert;

class ShareGuidedSearchCommand
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
     * @var string $label
     * @Assert\NotBlank
     */
    public $email;

    /**
     * @var boolean
     */
    public $initialData = true;

    /**
     * ShareGuidedStepCommand constructor.
     *
     * @param GuidedSearch $guidedSearch
     * @param User $user
     */
    public function __construct(GuidedSearch $guidedSearch, User $user)
    {
        $this->guidedSearch = $guidedSearch;
        $this->user = $user;
    }
}
