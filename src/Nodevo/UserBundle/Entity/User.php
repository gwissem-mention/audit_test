<?php

namespace Nodevo\UserBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

//Asserts Stuff
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Nodevo\ToolsBundle\Validator\Constraints as Nodevo;

/**
 * User
 */
abstract class User extends BaseUser
{
    /**
     * @var string
     * @Assert\NotBlank(message="Le nom d'utilisateur ne peut pas être vide.")
     * @Assert\Length(
     *      min = "3",
     *      max = "50",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans le nom d'utilisateur.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans le nom d'utilisateur."
     * )
     * @Nodevo\Javascript(class="validate[required,minSize[3],maxSize[50]]")
     * @ORM\Column(name="usr_username", type="string", length=50, options = {"comment" = "Nom utilisateur pour la connexion"})
     */
    protected $username;

    /**
     * @var string
     *
     * @ORM\Column(name="usr_username_canonical", type="string", length= 50, options={"comment" = "Pseudonyme canonique"}, unique = true)
     */
    protected $usernameCanonical;

    /**
     * @var string
     * @Assert\NotBlank(message="L'adresse éléctronique ne peut pas être vide.")
     * @Assert\Length(
     *      min = "3",
     *      max = "50",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans le nom d'utilisateur.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans le nom d'utilisateur."
     * )
     * @Nodevo\Javascript(class="validate[required,custom[email]]")
     * @ORM\Column(name="usr_email", type="string", length= 50, options={"comment" = "Adresse électronique"})
     */
    protected $email;

    /**
     * @var string
     *
     * @ORM\Column(name="usr_email_canonical", type="string", length= 50, options={"comment" = "Adresse électronique canonique"}, unique = true)
     */
    protected $emailCanonical;

    /**
     * @var boolean
     *
     * @ORM\Column(name="usr_enabled", type="boolean", options={"comment" = "L utilisateur est-il activé ?"})
     */
    protected $enabled;

    /**
     * The salt to use for hashing
     *
     * @var string
     *
     * @ORM\Column(name="usr_salt", type="string", length= 100, options={"comment" = "Grain de sel de chiffrement du mot de passe"}, nullable = true)
     */
    protected $salt;

    /**
     * Encrypted password. Must be persisted.
     *
     * @var string
     *
     * @ORM\Column(name="usr_password", type="string", length= 100, options={"comment" = "Mot de passe"})
     */
    protected $password;

    /**
     * Plain password. Used for model validation. Must not be persisted.
     *
     * @var string
     */
    protected $plainPassword;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="usr_last_login", type="datetime", options={"comment" = "Date de la dernière connexion"}, nullable = true)
     */
    protected $lastLogin;

    /**
     * Random string sent to the user email address in order to verify it
     *
     * @var string
     *
     * @ORM\Column(name="usr_confirmation_token", type="string", length= 50, options={"comment" = "Jeton de confirmation du compte"}, nullable = true)
     */
    protected $confirmationToken;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="usr_password_requested_at", type="datetime", options={"comment" = "Date de demande du nouveau mot de passe"}, nullable = true)
     */
    protected $passwordRequestedAt;

    /**
     * @var Collection
     */
    protected $groups;

    /**
     * @var boolean
     *
     * @ORM\Column(name="usr_locked", type="boolean", options={"comment" = "Verrouillage de l utilisateur ?"}, nullable = true)
     */
    protected $locked;

    /**
     * @var boolean
     *
     * @ORM\Column(name="usr_expired", type="boolean", options={"comment" = "L utilisateur est-il activé ?"}, nullable = true)
     */
    protected $expired;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="usr_expires_at", type="datetime", options={"comment" = "Date d expiration de l utilisateur"}, nullable = true)
     */
    protected $expiresAt;

    /**
     * @var boolean
     *
     * @ORM\Column(name="usr_credentials_expired", type="boolean", options={"comment" = "Expiration du mot de passe ?"}, nullable = true)
     */
    protected $credentialsExpired;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="usr_credentials_expire_at", type="datetime", options={"comment" = "Date d expiration du mot de passe"}, nullable = true)
     */
    protected $credentialsExpireAt;
}