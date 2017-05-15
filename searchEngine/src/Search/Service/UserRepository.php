<?php

namespace Search\Service;

use Search\Model\User;

class UserRepository
{
    /**
     * @var \PDO
     */
    protected $connexion;

    /**
     * UserRepository constructor.
     * @param \PDO $connexion
     */
    public function __construct(\PDO $connexion)
    {
        $this->connexion = $connexion;
    }

    /**
     * Get user by token
     *
     * @param $token
     *
     * @return User
     */
    public function getUserByToken($token)
    {
        $result = $this->connexion->query(
            'SELECT usr.roles FROM core_user_token token INNER JOIN core_user usr ON usr.usr_id = token.user_id WHERE token = "' . $token . '" LIMIT 1;'
        );
        $userData = $result->fetch();

        $user = new User();

        if (false !== $userData && isset($userData['roles'])) {
            $user->setRoles(unserialize($userData['roles']));
        }

        return $user;
    }
}
