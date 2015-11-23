<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Repository;

use Doctrine\ORM\PersistentCollection;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\UserBundle\Entity\User;
use Doctrine\ORM\Query\Expr;

/**
 * Repository de Groupe.
 */
class GroupeRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * Retourne les groupes n'ayant pas encore démarrés.
     * 
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine Domaine
     * @param boolean                                        $isActif (optionnel) Si les groupes doivent être actifs ou non actifs
     * @return array<\HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe> Groupes non démarrés
     */
    public function findNonDemarres(Domaine $domaine, $isActif = null)
    {
        $query = $this->createQueryBuilder('groupe');
        $aujourdhui = new \DateTime();
        $aujourdhui->setTime(0, 0, 0);

        $query
            ->andWhere('groupe.domaine = :domaine')
            ->setParameter('domaine', $domaine)
            ->andWhere('groupe.dateDemarrage > :aujourdhui')
            ->setParameter('aujourdhui', $aujourdhui)
        ;
        if (null !== $isActif)
        {
            $query
                ->andWhere('groupe.actif = :actif')
                ->setParameter('actif', $isActif)
            ;
        }

        return $query->getQuery()->getResult();
    }
    
    /**
     * Retourne les groupes en cours.
     * 
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine Domaine
     * @param \HopitalNumerique\UserBundle\Entity\User       $user    Utilisateur
     * @param boolean                                        $isActif (optionnel) Si les groupes doivent être actifs ou non actifs
     * @return array<\HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe> Groupes en cours
     */
    public function findEnCours(Domaine $domaine, User $user = null, $isActif = null)
    {
        $query = $this->createQueryBuilder('groupe');
        $aujourdhui = new \DateTime();
        $aujourdhui->setTime(0, 0, 0);

        $query
            ->andWhere('groupe.domaine = :domaine')
            ->setParameter('domaine', $domaine)
            ->andWhere('groupe.dateDemarrage <= :aujourdhui')
            ->andWhere('groupe.dateFin >= :aujourdhui')
            ->setParameter('aujourdhui', $aujourdhui)
        ;
        if (null !== $user)
        {
            $query
                ->innerJoin('groupe.users', 'user', Expr\Join::WITH, 'user = :user')
                ->setParameter('user', $user)
            ;
        }
        if (null !== $isActif)
        {
            $query
                ->andWhere('groupe.actif = :actif')
                ->setParameter('actif', $isActif)
            ;
        }

        return $query->getQuery()->getResult();
    }
    
    
    /**
     * Retourne les données pour le grid.
     *
     * @param array<\HopitalNumerique\DomaineBundle\Entity\Domaine> $domaines Domaines
     * @return array Données
     */
    public function getGridData(PersistentCollection $domaines)
    {
        $query = $this->createQueryBuilder('groupe');

        $query
            ->select
            (
                'groupe.id AS id',
                'groupe.titre AS titre',
                'groupe.dateInscriptionOuverture AS dateInscriptionOuverture',
                'groupe.dateDemarrage AS dateDemarrage',
                'groupe.dateFin AS dateFin',
                'groupe.vedette AS vedette',
                'groupe.actif AS actif',

                'domaine.nom AS domaineNom'
            )
            ->innerJoin('groupe.animateurs', 'animateur')
            ->innerJoin('groupe.domaine', 'domaine', Expr\Join::WITH, $query->expr()->in('domaine', ':domaines'))
            ->setParameter('domaines', $domaines->toArray())
            ->groupBy('id')
        ;

        return $query->getQuery()->getArrayResult();
    }
}
