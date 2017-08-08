<?php

namespace HopitalNumerique\RechercheParcoursBundle\Domain\Command;

use Doctrine\ORM\PersistentCollection;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearch;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursDetails;

/**
 * Class FindFirstUncompletedStepCommand
 */
class FindFirstUncompletedStepCommand
{
    /**
     * @var RechercheParcoursDetails[]
     */
    public $rechercheParcoursDetails;

    /**
     * @var GuidedSearch
     */
    public $guidedSearch;

    /**
     * @var User
     */
    public $user;

    /**
     * FindFirstUncompletedStepCommand constructor.
     *
     * @param PersistentCollection|RechercheParcoursDetails[] $rechercheParcoursDetails
     * @param GuidedSearch                                    $guidedSearch
     * @param User                                            $user
     */
    public function __construct(
        PersistentCollection $rechercheParcoursDetails,
        GuidedSearch $guidedSearch,
        User $user
    ) {
        $this->rechercheParcoursDetails = $rechercheParcoursDetails;
        $this->guidedSearch = $guidedSearch;
        $this->user = $user;
    }
}
