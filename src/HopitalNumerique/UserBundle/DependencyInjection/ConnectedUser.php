<?php
namespace HopitalNumerique\UserBundle\DependencyInjection;

use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Service récupérant l'utilisateur connecté.
 */
class ConnectedUser
{
    /**
     *
     * @var \HopitalNumerique\UserBundle\Entity\User|null User
     */
    private $user = null;


    /**
     * Constructeur.
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        if (null !== $tokenStorage->getToken() && $tokenStorage->getToken()->getUser() instanceof User) {
            $this->user = $tokenStorage->getToken()->getUser();
        }
    }


    /**
     * Retourne l'utilisateur connecté.
     *
     * @return \HopitalNumerique\UserBundle\Entity\User|null User
     */
    public function get()
    {
        return $this->user;
    }

    /**
     * Retourne s'il y a un utilisateur connecté.
     *
     * @return boolean Si connecté
     */
    public function is()
    {
        return (null !== $this->user);
    }
}
