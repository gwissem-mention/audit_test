<?php

namespace HopitalNumerique\RechercheBundle\Manager;

use HopitalNumerique\UserBundle\Entity\User;
use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Requete.
 */
class RequeteManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\RechercheBundle\Entity\Requete';

    /**
     * Retourne la liste des request dont les résultats sont nouveaux/mis à jour
     *
     * @param User $user L'utilisateur connecté
     *
     * @return array
     */
    public function getRequetesForDashboard( $user )
    {
        return $this->getRepository()->getRequetesForDashboard( $user )->getQuery()->getResult();
    }

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