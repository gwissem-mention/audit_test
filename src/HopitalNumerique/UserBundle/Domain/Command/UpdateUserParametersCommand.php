<?php

namespace HopitalNumerique\UserBundle\Domain\Command;

use HopitalNumerique\NotificationBundle\Entity\Settings;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;

/**
 * Class UpdateUserParametersCommand
 */
class UpdateUserParametersCommand
{
    /**
     * @var string
     * @Assert\NotBlank(groups={"changePassword"})
     * @SecurityAssert\UserPassword(groups={"changePassword"})
     */
    public $currentPassword;

    /**
     * @var string
     * @Assert\Regex(pattern="((?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,})", message="Le mot de passe doit comporter au moins 6 caractères et être composé d'au moins une lettre minuscule, d'une lettre majuscule et d'un chiffre.")
     */
    public $newPassword;

}
