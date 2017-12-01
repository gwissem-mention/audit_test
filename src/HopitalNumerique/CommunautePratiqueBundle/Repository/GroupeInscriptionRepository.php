<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Repository;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
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

    /**
     * Returns query builder of group members.
     *
     * @param integer $groupId
     *
     * @return QueryBuilder
     */
    public function getUsersInGroupQueryBuilder($groupId)
    {
        return $this->createQueryBuilder('groupe_inscription')
            ->select('user.id')
            ->join('groupe_inscription.user', 'user')
            ->where('groupe_inscription.groupe = :groupId')
            ->andWhere('groupe_inscription.actif = :activeState')
            ->setParameters([
                'activeState' => 1,
                'groupId' => (int)$groupId,
            ]);
    }

    /**
     * Returns query builder of practice community members.
     *
     * @param array|null $domainIds Filter users that are in at least one of the given domains
     *
     * @return QueryBuilder
     */
    public function createCommunityMembersQueryBuilder($domainIds = null)
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('user.id')
            ->from('HopitalNumeriqueUserBundle:User', 'user')
            ->where('user.inscritCommunautePratique = :isRegistered')
            ->setParameter('isRegistered', 1)
        ;

        if (is_array($domainIds)) {
            $qb
                ->join('user.domaines', 'domain')
                ->andWhere($qb->expr()->in('domain.id', $domainIds))
            ;
        }

        return $qb;
    }
}
