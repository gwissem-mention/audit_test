<?php

namespace HopitalNumerique\ModuleBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * ModuleRepository
 * 
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class ModuleRepository extends EntityRepository
{
    /**
     * Récupère les données du grid sous forme de tableau correctement formaté
     *
     * @return array
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function getDatasForGrid($domainesIds, $condition = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('mod.id, mod.titre, refEtat.libelle as statut, productions.titre as prod_titre, domaine.nom as domaineNom')
            ->from('HopitalNumeriqueModuleBundle:Module', 'mod')
            ->leftJoin('mod.statut','refEtat')
            ->leftJoin('mod.productions','productions')
            ->leftJoin('mod.domaines', 'domaine')
                ->where($qb->expr()->orX(
                    $qb->expr()->in('domaine.id', ':domainesId'),
                    $qb->expr()->isNull('domaine.id')
                ))
            ->setParameter('domainesId', $domainesIds)
            ->orderBy('mod.titre')
            ->groupBy('mod.id', 'domaine.id');
        
        return $qb;
    }

    /**
     * Récupère les modules et leurs sessions actives et leurs inscriptions pas encore passées
     *
     * @return array(Module)
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function getAllInscriptionsBySessionsActivesNonPasseesByModules()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('mod')
            ->from('HopitalNumeriqueModuleBundle:Module', 'mod')
            ->leftJoin('mod.statut', 'refEtat')
            ->where('refEtat.id = 3')
            ->leftJoin('mod.sessions', 'session', Join::WITH, 'session.etat = 403')
            ->andWhere('session.dateSession >= :today')
            ->setParameter('today', new \DateTime() )
            ->leftJoin('session.inscriptions', 'inscription', Join::WITH, 'inscription.etatInscription = 407')
            ->orderBy('mod.titre');
        
        return $qb;
    }
}