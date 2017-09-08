<?php

namespace HopitalNumerique\RechercheParcoursBundle\Domain\Command\Risk;

use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearch;
use HopitalNumerique\UserBundle\Entity\User;

class SendSynthesisCommand
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
     * @var string $recipient
     */
    public $recipient;

    /**
     * @var string $sender
     */
    public $sender;

    /**
     * @var string $subject
     */
    public $subject;

    /**
     * @var string $content
     */
    public $content;

    /**
     * SendSynthesisCommand constructor.
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
