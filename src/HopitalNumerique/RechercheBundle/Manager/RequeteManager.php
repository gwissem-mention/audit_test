<?php

namespace HopitalNumerique\RechercheBundle\Manager;

use HopitalNumerique\UserBundle\Entity\User;
use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Requete.
 */
class RequeteManager extends BaseManager
{
    protected $class = 'HopitalNumerique\RechercheBundle\Entity\Requete';


    /**
     * Retourne la requête par défaut d'un utilisateur.
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $user User
     * @return \HopitalNumerique\RechercheBundle\Entity\Requete|null Requête
     */
    public function findDefaultByUser(User $user)
    {
        return $this->findOneBy([
            'user' => $user,
            'isDefault' => true
        ]);
    }
}
