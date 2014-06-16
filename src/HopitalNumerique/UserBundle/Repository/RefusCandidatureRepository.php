<?php

namespace HopitalNumerique\UserBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * RefusCandidatureRepository
 */
class RefusCandidatureRepository extends EntityRepository
{
    /**
     * Récupère les données du grid sous forme de tableau correctement formaté
     *
     * @return array
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function getDatasForGrid()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('refCand.id, refCand.dateRefus, refCand.motifRefus, user.id as userId, user.nom as userNom, user.prenom as userPrenom, refRegion.libelle as userRegion')
            ->from('HopitalNumeriqueUserBundle:RefusCandidature', 'refCand')
            ->leftJoin('refCand.user','user')
            ->leftJoin('user.region','refRegion')
            ->orderBy('refCand.dateRefus');
        
        return $qb;
    }
}
