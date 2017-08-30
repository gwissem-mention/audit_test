<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Repository;

use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;

/**
 * Repository de Groupe.
 */
class GroupeInscriptionRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * Retourne les groupes n'ayant pas encore démarrés.
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine Domaine
     * @param \HopitalNumerique\UserBundle\Entity\User       $user    Utilisateur
     * @param bool                                           $isActif (optionnel) Si les groupes doivent être actifs ou non actifs
     *
     * @return array<\HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe> Groupes non démarrés
     */
    public function getInscription(Groupe $groupe, User $user)
    {
        $query = $this->createQueryBuilder('inscription');
        $query
            ->andWhere('inscription.groupe = :groupe')
            ->setParameter('groupe', $groupe)
            ->andWhere('inscription.user = :user')
            ->setParameter('user', $user)
        ;

        return $query->getQuery()->getResult();
    }

    /**
     * Returns practice community groups of given user.
     *
     * @param User $user
     *
     * @return Groupe[]
     */
    public function getUserGroups(User $user)
    {
        $query = $this->createQueryBuilder('inscription');
        $query
            ->andWhere('inscription.user = :user')
            ->setParameter('user', $user)
        ;

        return $query->getQuery()->getResult();
    }
}
