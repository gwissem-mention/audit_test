<?php

namespace HopitalNumerique\RechercheParcoursBundle\Domain\Command\GuidedSearch;

use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearch;

/**
 * Class RemoveGuidedSearchCommand
 */
class RemoveGuidedSearchCommand
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
     * RemoveGuidedSearchCommand constructor.
     *
     * @param GuidedSearch $guidedSearch
     * @param User         $user
     */
    public function __construct(GuidedSearch $guidedSearch, User $user)
    {
        $this->guidedSearch = $guidedSearch;
        $this->user = $user;
    }
}
