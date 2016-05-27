<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Repository;

use Doctrine\ORM\PersistentCollection;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use Doctrine\ORM\Query\Expr;

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
     * @param boolean                                        $isActif (optionnel) Si les groupes doivent être actifs ou non actifs
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
}
