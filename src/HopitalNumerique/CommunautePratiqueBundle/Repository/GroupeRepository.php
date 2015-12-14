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
     * @param \HopitalNumerique\UserBundle\Entity\User       $user    Utilisateur
     * @param boolean                                        $isActif (optionnel) Si les groupes doivent être actifs ou non actifs
     * @return array<\HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe> Groupes non démarrés
     */
    public function findNonDemarres(Domaine $domaine, User $user = null, $isActif = null)
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
        if (null !== $user) {
            $query
                ->innerJoin('groupe.users', 'user', Expr\Join::WITH, 'user = :user')
                ->setParameter('user', $user)
            ;
        }
        if (null !== $isActif) {
            $query
                ->andWhere('groupe.actif = :actif')
                ->setParameter('actif', $isActif)
            ;
        }

        $query
            ->orderBy('groupe.dateDemarrage')
            ->orderBy('groupe.dateFin')
        ;

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
        if (null !== $user) {
            $query
                ->innerJoin('groupe.users', 'user', Expr\Join::WITH, 'user = :user')
                ->setParameter('user', $user)
            ;
        }
        if (null !== $isActif) {
            $query
                ->andWhere('groupe.actif = :actif')
                ->setParameter('actif', $isActif)
            ;
        }

        $query
            ->orderBy('groupe.dateDemarrage')
            ->orderBy('groupe.dateFin')
        ;

        return $query->getQuery()->getResult();
    }

    /**
     * Retourne les groupes terminés.
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine Domaine
     * @param \HopitalNumerique\UserBundle\Entity\User       $user    Utilisateur
     * @param boolean                                        $isActif (optionnel) Si les groupes doivent être actifs ou non actifs
     * @return array<\HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe> Groupes en cours
     */
    public function findTermines(Domaine $domaine, User $user, $isActif = null)
    {
        $query = $this->createQueryBuilder('groupe');
        $aujourdhui = new \DateTime();
        $aujourdhui->setTime(0, 0, 0);

        $query
            ->andWhere('groupe.domaine = :domaine')
            ->setParameter('domaine', $domaine)
            ->andWhere('groupe.dateFin < :aujourdhui')
            ->setParameter('aujourdhui', $aujourdhui)
        ;
        if (null !== $user) {
            $query
                ->innerJoin('groupe.users', 'user', Expr\Join::WITH, 'user = :user')
                ->setParameter('user', $user)
            ;
        }
        if (null !== $isActif) {
            $query
                ->andWhere('groupe.actif = :actif')
                ->setParameter('actif', $isActif)
            ;
        }

        $query
            ->orderBy('groupe.dateDemarrage')
            ->orderBy('groupe.dateFin')
        ;

        return $query->getQuery()->getResult();
    }

    /**
     * Retourne les groupes non fermés.
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine               Domaine
     * @param \HopitalNumerique\UserBundle\Entity\User       $user                  Utilisateur
     * @param boolean                                        $enVedette             (optionnel) En vedette
     * @param boolean                                        $isActif               (optionnel) Si les groupes doivent être actifs ou non actifs
     * @param boolean                                        $dateInscriptionPassee (optionnel) Si la date d'inscription doit être passée ou pas
     * @return array<\HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe> Groupes non fermés
     */
    public function findNonFermes(Domaine $domaine, User $user = null, $enVedette = null, $isActif = null, $dateInscriptionPassee = null)
    {
        $query = $this->createQueryBuilder('groupe');
        $aujourdhui = new \DateTime();
        $aujourdhui->setTime(0, 0, 0);

        $query
            ->leftJoin('groupe.fiches', 'fiche')
            ->addSelect('fiche')
            ->leftJoin('groupe.users', 'groupeUser')
            ->addSelect('groupeUser')
            ->leftJoin('groupe.documents', 'document')
            ->addSelect('document')
            ->leftJoin('fiche.commentaires', 'ficheCommentaire')
            ->addSelect('ficheCommentaire')
            ->leftJoin('groupe.commentaires', 'groupeCommentaire')
            ->addSelect('groupeCommentaire')
            ->andWhere('groupe.domaine = :domaine')
            ->setParameter('domaine', $domaine)
            ->andWhere('groupe.dateFin > :aujourdhui')
            ->setParameter('aujourdhui', $aujourdhui)
            ->addOrderBy('groupe.dateDemarrage')
            ->addOrderBy('groupe.dateFin')
        ;
        if (null !== $user) {
            $query
                ->innerJoin('groupe.users', 'user', Expr\Join::WITH, 'user = :user')
                ->setParameter('user', $user)
                ->addOrderBy('user.nom')
                ->addOrderBy('user.prenom')
            ;
        }
        if (null !== $enVedette) {
            $query
                ->andWhere('groupe.vedette = :vedette')
                ->setParameter('vedette', $enVedette)
            ;
        }
        if (null !== $isActif) {
            $query
                ->andWhere('groupe.actif = :actif')
                ->setParameter('actif', $isActif)
            ;
        }
        if (null !== $dateInscriptionPassee) {
            $query->andWhere('groupe.dateInscriptionOuverture '.($dateInscriptionPassee ? '<=' : '>').' :aujourdhui');
        }

        return $query->getQuery()->getResult();
    }


    /**
     * Retourne la QueryBuilder des groupes ayant des publications.
     *
     * @param boolean $isActif (optionnel) Si les groupes doivent être actifs ou non actifs
     * @return \Doctrine\ORM\QueryBuilder QueryBuilder
     */
    public function findWithPublicationsQueryBuilder($isActif = null)
    {
        $query = $this->createQueryBuilder('groupe');

        $query
            ->innerJoin('groupe.publications', 'publication')
            ->addSelect('publication')
            ->orderBy('groupe.dateDemarrage')
        ;
        if (null !== $isActif) {
            $query
                ->andWhere('groupe.actif = :actif')
                ->setParameter('actif', $isActif)
            ;
        }

        return $query;
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
            ->leftJoin('groupe.animateurs', 'animateur')
            ->innerJoin('groupe.domaine', 'domaine', Expr\Join::WITH, $query->expr()->in('domaine', ':domaines'))
            ->setParameter('domaines', $domaines->toArray())
            ->groupBy('id')
        ;

        return $query->getQuery()->getArrayResult();
    }
}
