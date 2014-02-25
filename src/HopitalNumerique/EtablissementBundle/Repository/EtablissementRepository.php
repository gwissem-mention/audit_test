<?php

namespace HopitalNumerique\EtablissementBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * EtablissementRepository
 */
class EtablissementRepository extends EntityRepository
{
    /**
     * Récupère les données du grid sous forme de tableau correctement formaté
     *
     * @return array
     */
    public function getDatasForGrid()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('eta.id, eta.nom, eta.finess, eta.ville, eta.codepostal, refType.libelle as typeOrganisme, refRegion.libelle as region, refDepartement.libelle as departement')
            ->from('HopitalNumeriqueEtablissementBundle:Etablissement', 'eta')
            ->leftJoin('eta.typeOrganisme','refType')
            ->leftJoin('eta.region','refRegion')
            ->leftJoin('eta.departement','refDepartement')
            ->orderBy('refRegion.libelle, refDepartement.libelle, eta.nom');
            
        return $qb->getQuery()->getResult();
    }
}