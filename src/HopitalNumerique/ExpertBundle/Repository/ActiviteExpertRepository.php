<?php

namespace HopitalNumerique\ExpertBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * ActiviteExpertRepository.
 */
class ActiviteExpertRepository extends EntityRepository
{
    /**
     * Récupère les données du grid sous forme de tableau correctement formaté.
     *
     * @param null $condition
     *
     * @return QueryBuilder
     */
    public function getDatasForGrid($condition = null)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb
            ->select('actE.id,
                     actE.titre, 
                     actE.dateDebut, 
                     actE.dateFin, 
                     actE.nbVacationParExpert, 
                     actE.etatValidation,
                     etat.libelle as etatLibelle,
                     prestataire.libelle as prestataireLibelle,
                     unite.libelle as uniteLibelle,
                     activities.libelle as typeActiviteLibelle,
                     exp.lastname as expertNom, 
                     exp.firstname as expertPrenom, 
                     anap.lastname as anapNom, 
                     anap.firstname as anapPrenom'
            )
            ->from('HopitalNumeriqueExpertBundle:ActiviteExpert', 'actE')
            ->leftJoin('actE.expertConcernes', 'exp')
            ->leftJoin('actE.anapiens', 'anap')
            ->leftJoin('actE.etat', 'etat')
            ->leftJoin('actE.prestataire', 'prestataire')
            ->leftJoin('actE.uniteOeuvreConcerne', 'unite')
            ->leftJoin('actE.activities', 'activities')
            ->orderBy('actE.dateDebut', 'DESC')
            ->addOrderBy('actE.dateFin', 'ASC')
            ->addOrderBy('exp.lastname', 'ASC')
            ->addOrderBy('anap.lastname', 'ASC');

        return $qb;
    }

    /**
     * Recupération des activités concernant l'expert.
     *
     * @param int $idExpert Identifiant de l'expert
     *
     * @return QueryBuilder
     */
    public function getActivitesForExpert($idExpert)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('actE')
            ->from('HopitalNumeriqueExpertBundle:ActiviteExpert', 'actE')
            ->leftJoin('actE.expertConcernes', 'exp')
                ->where('exp.id = :idExpert')
                ->setParameter('idExpert', $idExpert)
            ->orderBy('actE.dateFin', 'DESC');

        return $qb;
    }

    /**
     * Recupération des activités concernant l'anapien.
     *
     * @param int $idAnapien Identifiant de l'anapien
     *
     * @return QueryBuilder
     */
    public function getActivitesForAnapien($idAnapien)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('actE')
            ->from('HopitalNumeriqueExpertBundle:ActiviteExpert', 'actE')
            ->leftJoin('actE.anapiens', 'anap')
                ->where('anap.id = :idAnapien')
                ->setParameter('idAnapien', $idAnapien)
            ->orderBy('actE.dateFin', 'DESC');

        return $qb;
    }
}
