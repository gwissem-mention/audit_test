<?php

namespace HopitalNumerique\ObjetBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * RefContenuRepository
 */
class RefContenuRepository extends EntityRepository
{
    /**
     * Retourne la liste des références objet pour la recherche (prend en compte les dates de publication)
     *
     * @param array $references Liste des références
     *
     * @return QueryBuilder
     */
    public function getContenusForRecherche( $references )
    {
        $qb = $this->_em->createQueryBuilder();

        return $qb->select('refC')
                        ->from('\HopitalNumerique\ObjetBundle\Entity\RefContenu', 'refC')
                        ->leftJoin('refC.contenu','con')
                        ->leftJoin('con.objet','obj')
                        ->andWhere('refC.reference in (:references)','obj.etat = 3')
                        ->setParameter('references', $references )
                        ->orderBy('refC.primary', 'ASC');
    }
}