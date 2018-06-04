<?php

namespace HopitalNumerique\ModuleBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use HopitalNumerique\ModuleBundle\Entity\Module;
use HopitalNumerique\ModuleBundle\Entity\SessionStatus;
use HopitalNumerique\ReferenceBundle\Entity\Reference;

/**
 * ModuleRepository.
 *
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class ModuleRepository extends EntityRepository
{
    /**
     * Récupère les données du grid sous forme de tableau correctement formaté.
     *
     * @return array
     *
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function getDatasForGrid($domainesIds, $condition = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('mod.id, mod.titre, refEtat.libelle as statut, productions.titre as prod_titre, domaine.nom as domaineNom', 'form.lastname as formateurNom, form.firstname as formateurPrenom')
            ->from('HopitalNumeriqueModuleBundle:Module', 'mod')
            ->leftjoin('mod.formateur', 'form')
            ->leftJoin('mod.statut', 'refEtat')
            ->leftJoin('mod.productions', 'productions')
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
     * Récupère les modules et leurs sessions actives et leurs inscriptions pas encore passées.
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllInscriptionsBySessionsActivesNonPasseesByModules()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('mod')
            ->from('HopitalNumeriqueModuleBundle:Module', 'mod')
            ->join('mod.statut', 'refEtat', Join::WITH, 'refEtat.id = :activeReferenceId')
            ->join('mod.sessions', 'session', Join::WITH, 'session.etat = :activeSessionId')
            ->join('session.inscriptions', 'inscription', Join::WITH, 'inscription.etatInscription = :acceptedStatusId')
            ->where('session.dateSession >= :today')
            ->setParameters([
                'activeSessionId' => SessionStatus::STATUT_SESSION_FORMATION_ACTIVE_ID,
                'acceptedStatusId' => SessionStatus::STATUT_FORMATION_ACCEPTED_ID,
                'activeReferenceId' => Reference::STATUT_ACTIF_ID,
                'today' => new \DateTime(),
            ])
            ->orderBy('mod.titre');

        return $qb;
    }

    /**
     * Récupère les modules du domaine passé en param.
     *
     * @return array(Module)
     *
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function getModuleActifForDomaine($domaineId)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('mod')
            ->from('HopitalNumeriqueModuleBundle:Module', 'mod')
            ->leftJoin('mod.statut', 'refEtat')
            ->where('refEtat.id = 3')
            ->leftJoin('mod.domaines', 'domaine')
            ->andWhere('domaine.id = :domaineId')
            ->setParameter('domaineId', $domaineId)
            ->orderBy('mod.titre');

        return $qb;
    }

    /**
     * Returns modules from ids.
     *
     * @param array $modulesId List of module ids
     *
     * @return Module[]
     */
    public function getModules(array $modulesId)
    {
        return $this->_em->createQueryBuilder('module')
            ->select('module')
            ->from('HopitalNumeriqueModuleBundle:Module', 'module')
            ->leftJoin('module.statut', 'refEtat')
            ->where('refEtat.id = :activeState')
            ->andWhere('module.id IN (:moduleIds)')
            ->setParameters([
                'activeState' => Reference::STATUT_ACTIF_ID,
                'moduleIds' => $modulesId,
            ])
            ->getQuery()->getResult();
    }
}
