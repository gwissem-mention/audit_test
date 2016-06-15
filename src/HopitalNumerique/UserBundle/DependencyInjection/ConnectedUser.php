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
     * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface TokenStorage
     */
    private $tokenStorage;

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
        $this->tokenStorage = $tokenStorage;
    }


    /**
     * Retourne l'utilisateur connecté.
     *
     * @return \HopitalNumerique\UserBundle\Entity\User|null User
     */
    public function get()
    {
        if (null === $this->user) {
            if (null !== $this->tokenStorage->getToken() && $this->tokenStorage->getToken()->getUser() instanceof User) {
                $this->user = $this->tokenStorage->getToken()->getUser();
            }
        }

        return $this->user;
    }

    /**
     * Retourne s'il y a un utilisateur connecté.
     *
     * @return boolean Si connecté
     */
    public function is()
    {
        return (null !== $this->get());
    }
}
