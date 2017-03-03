<?php

namespace HopitalNumerique\ExpertBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ActiviteExpertRepository.
 */
class ActiviteExpertRepository extends EntityRepository
{
    /**
     * Récupère les données du grid sous forme de tableau correctement formaté.
     *
     * @return Query Builder
     */
    public function getDatasForGrid($condition = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('actE.id,
                     actE.titre, 
                     actE.dateDebut, 
                     actE.dateFin, 
                     actE.nbVacationParExpert, 
                     actE.etatValidation,
                     etat.libelle as etatLibelle,
                     prestataire.libelle as prestataireLibelle,
                     unite.libelle as uniteLibelle,
                     typeActivite.libelle as typeActiviteLibelle,
                     exp.nom as expertNom, 
                     exp.prenom as expertPrenom, 
                     anap.nom as anapNom, 
                     anap.prenom as anapPrenom'
            )
            ->from('HopitalNumeriqueExpertBundle:ActiviteExpert', 'actE')
            ->leftJoin('actE.expertConcernes', 'exp')
            ->leftJoin('actE.anapiens', 'anap')
            ->leftJoin('actE.etat', 'etat')
            ->leftJoin('actE.prestataire', 'prestataire')
            ->leftJoin('actE.uniteOeuvreConcerne', 'unite')
            ->leftJoin('actE.typeActivite', 'typeActivite')
            ->orderBy('actE.dateDebut', 'DESC')
            ->addOrderBy('actE.dateFin', 'ASC')
            ->addOrderBy('exp.nom', 'ASC')
            ->addOrderBy('anap.nom', 'ASC');

        return $qb;
    }

    /**
     * Recupération des activités concernant l'expert.
     *
     * @param int $expertId Identifiant de l'expert
     *
     * @return [type]
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
     * @param int $expertId Identifiant de l'anapien
     *
     * @return [type]
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
