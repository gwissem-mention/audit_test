<?php

namespace HopitalNumerique\ObjetBundle\Domain\Command;

use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\ObjetBundle\Entity\Risk;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use Symfony\Component\Validator\Constraints as Assert;

class EditRiskCommand
{
    /**
     * @var integer
     */
    public $riskId;

    /**
     * @var string
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    public $label;

    /**
     * @var Reference
     *
     * @Assert\NotBlank
     */
    public $nature;

    /**
     * @var Domaine[]
     *
     * @Assert\Count(min=1)
     */
    public $domains;

    /**
     * @var boolean
     */
    public $archived;

    /**
     * @var Risk
     */
    public $fusionTarget;

    /**
     * @var boolean
     */
    public $confirmFusion = false;

    /**
     * @var User|null
     */
    public $author;

    /**
     * @var boolean
     */
    public $publish = true;

    /**
     * @var boolean
     */
    public $private = false;

    /**
     * EditRiskCommand constructor.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->author = $user;
    }

    /**
     * @param User $user
     * @param Risk|null $risk
     *
     * @return EditRiskCommand
     */
    public static function createFromRisk(User $user, Risk $risk = null)
    {
        $command = new self($user);

        if (!is_null($risk)) {
            $command->riskId = $risk->getId();
            $command->label = $risk->getLabel();
            $command->archived = $risk->isArchived();
            $command->nature = $risk->getNature();
            $command->domains = $risk->getDomains();
            $command->private = $risk->isPrivate();
            $command->publish = !$risk->isPrivate();
        }

        return $command;
    }
}
