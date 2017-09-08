<?php

namespace HopitalNumerique\RechercheParcoursBundle\Domain\Command\GuidedSearch;

use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearch;

/**
 * Class SendAnalyzesCommand
 */
class SendAnalyzesCommand
{
    /**
     * @var GuidedSearch
     */
    public $guidedSearch;

    /**
     * @var User
     */
    public $user;

    public $sender;

    public $recipient;

    public $subject;

    public $body;

    /**
     * SendAnalyzesCommand constructor.
     *
     * @param GuidedSearch $guidedSearch
     * @param User         $user
     * @param              $sender
     * @param              $recipient
     * @param              $subject
     * @param              $body
     */
    public function __construct(GuidedSearch $guidedSearch, User $user, $sender, $recipient, $subject, $body)
    {
        $this->guidedSearch = $guidedSearch;
        $this->user = $user;
        $this->sender = $sender;
        $this->recipient = $recipient;
        $this->subject = $subject;
        $this->body = $body;
    }
}
