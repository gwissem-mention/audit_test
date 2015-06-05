<?php

namespace HopitalNumerique\ExpertBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * ActiviteExpertRepository
 */
class ActiviteExpertRepository extends EntityRepository
{
    /**
     * Récupère les données du grid sous forme de tableau correctement formaté
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
}
