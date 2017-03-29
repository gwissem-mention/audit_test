<?php

namespace HopitalNumerique\EtablissementBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * EtablissementRepository.
 */
class EtablissementRepository extends EntityRepository
{
    /**
     * Récupère les données pour l'export CSV.
     *
     * @return QueryBuilder
     */
    public function getDatasForExport($ids)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('etab')
            ->from('HopitalNumeriqueEtablissementBundle:Etablissement', 'etab')
            ->where('etab.id IN (:ids)')
            ->orderBy('etab.nom')
            ->setParameter('ids', $ids);

        return $qb;
    }

    /**
     * @param $string
     *
     * @return array
     */
    public function findByNameFinessCity($string)
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('etablissement.id as id, CONCAT(CONCAT(CONCAT(CONCAT(etablissement.nom, \' - \'), etablissement.finess), \' - \'), etablissement.ville) as text')
            ->from('HopitalNumeriqueEtablissementBundle:Etablissement', 'etablissement')
            ->where('etablissement.nom LIKE :nom')
            ->orWhere('etablissement.finess LIKE :finess')
            ->orWhere('etablissement.ville LIKE :ville')
            ->setParameters([
                'nom' => "%" . $string . "%",
                'finess' => "%" . $string . "%",
                'ville' => "%" . $string . "%",
            ])
        ;

        return $qb->getQuery()->getArrayResult();
    }
}
