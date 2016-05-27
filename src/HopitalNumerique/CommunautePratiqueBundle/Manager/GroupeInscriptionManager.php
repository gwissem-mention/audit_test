<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Manager;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Inscription;

/**
 * Manager de Inscription.
 */
class GroupeInscriptionManager extends \Nodevo\ToolsBundle\Manager\Manager
{
    protected $_class = 'HopitalNumerique\CommunautePratiqueBundle\Entity\Inscription';
    /**
     * Retourne les groupes n'ayant pas encore démarrés.
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine Domaine
     * @param \HopitalNumerique\UserBundle\Entity\User       $user    Utilisateur
     * @return array<\HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe> Groupes non démarrés
     */
    public function getInscription(Groupe $groupe, User $user)
    {
        return $this->getRepository()->getInscription($groupe, $user);
    }
}
