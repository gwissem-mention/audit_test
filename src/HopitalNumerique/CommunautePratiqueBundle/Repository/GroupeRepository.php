<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Repository;

use Doctrine\ORM\PersistentCollection;

/**
 * Repository de Groupe.
 */
class GroupeRepository extends \Doctrine\ORM\EntityRepository
{
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

                'domaine.nom AS domaineNom'
            )
            ->innerJoin('groupe.animateurs', 'animateur')
            ->innerJoin('groupe.domaine', 'domaine', \Doctrine\ORM\Query\Expr\Join::WITH, $query->expr()->in('domaine', ':domaines'))
            ->setParameter('domaines', $domaines->toArray())
            ->groupBy('id')
        ;

        return $query->getQuery()->getArrayResult();
    }
}
