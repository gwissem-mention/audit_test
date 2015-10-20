<?php

namespace HopitalNumerique\AutodiagBundle\Repository;

use Doctrine\ORM\EntityRepository;
use HopitalNumerique\AutodiagBundle\Entity\Outil;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * ResultatRepository
 */
class ResultatRepository extends EntityRepository
{
    /**
     * Récupère le dernier résultat validé
     *
     * @param Outil  $outil L'outil
     * @param User   $user  L'utilisateur connecté
     *
     * @return Resultat
     */
    public function getLastResultatValided( Outil $outil, User $user )
    {
        return $this->_em->createQueryBuilder()
                         ->select('res')
                         ->from('\HopitalNumerique\AutodiagBundle\Entity\Resultat', 'res')
                         ->andWhere('res.outil = :outil', 'res.user = :user')
                         ->andWhere('res.synthese = 0')
                         ->setParameter('user', $user )
                         ->setParameter('outil', $outil )
                         ->setMaxResults(1)
                         ->orderBy('res.dateValidation','DESC');
    }

    public function getResultsAsArray( $idOutil )
    {
        return $this->_em->createQueryBuilder()
                         ->select('res.id,
                                   res.name,
                                   res.dateLastSave,
                                   res.dateCreation,
                                   res.dateValidation,
                                   res.tauxRemplissage,
                                   res.pdf,
                                   res.remarque,
                                   st.id as stId,
                                   out.id as outId,
                                   usr.id as usrId,
                                   res.synthese'
                            )
                         ->from('\HopitalNumerique\AutodiagBundle\Entity\Resultat', 'res')
                         ->leftJoin('res.outil', 'out')
                             ->andWhere('out.id = :idOutil')
                             ->setParameter('idOutil', $idOutil )
                         ->leftJoin('res.statut', 'st')
                         ->leftJoin('res.user', 'usr')
                         ->orderBy('res.id', 'ASC');
    }



    public function getResultsSynthesesAsArray( $idOutil )
    {
        return $this->_em->createQueryBuilder()
                         ->select('res.id,
                                   synth.id as synthId'
                            )
                         ->from('\HopitalNumerique\AutodiagBundle\Entity\Resultat', 'res')
                         ->leftJoin('res.resultats', 'synth')
                         ->leftJoin('res.outil', 'out')
                             ->andWhere('out.id = :idOutil')
                             ->setParameter('idOutil', $idOutil )
                         ->orderBy('res.id', 'ASC');
    }
}