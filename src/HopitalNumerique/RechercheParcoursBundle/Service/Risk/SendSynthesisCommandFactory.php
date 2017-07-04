<?php

namespace HopitalNumerique\RechercheParcoursBundle\Service\Risk;

use Nodevo\MailBundle\Manager\MailManager;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearch;
use HopitalNumerique\RechercheParcoursBundle\Domain\Command\Risk\SendSynthesisCommand;

class SendSynthesisCommandFactory
{
    /**
     * @var MailManager $mailManager
     */
    protected $mailManager;

    /**
     * SendSynthesisCommandFactory constructor.
     *
     * @param MailManager $mailManager
     */
    public function __construct(MailManager $mailManager)
    {
        $this->mailManager = $mailManager;
    }

    /**
     * @param GuidedSearch $guidedSearch
     * @param User|null $user
     *
     * @return SendSynthesisCommand
     */
    public function buildCommand(GuidedSearch $guidedSearch, User $user = null)
    {
        $command = new SendSynthesisCommand($guidedSearch, $user);

        if (!is_null($user)) {
            $command->sender = $user->getEmail();
        }

        if (!is_null($mailTemplate = $this->mailManager->getGuidedSearchSynthesisMail())) {
            $command->subject = $mailTemplate->getObjet();
            $command->content = $mailTemplate->getBody();
        }

        return $command;
    }
}
