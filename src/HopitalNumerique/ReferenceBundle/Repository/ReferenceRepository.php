<?php

namespace HopitalNumerique\ReferenceBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use HopitalNumerique\ReferenceBundle\Entity\Reference;

/**
 * ReferenceRepository
 */
class ReferenceRepository extends EntityRepository
{
    /**
     * Récupère tous les items de l'arborescence référence dans le bon ordre
     *
     * @return array
     */
    public function getArbo( $unlockedOnly = false, $fromDictionnaire = false, $fromRecherche = false, $domaineIds = array(), $actif = Reference::STATUT_ACTIF_ID )
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('ref.id, ref.libelle, ref.code, par.id as parent, ref.order')
            ->from('HopitalNumeriqueReferenceBundle:Reference', 'ref')
            ->leftJoin('ref.parents','par');

        if(count($domaineIds) !== 0)
        {
            $qb->leftJoin('ref.domaines', 'domaine')
                ->andWhere('domaine.id IN (:domainesId)')
                ->setParameter('domainesId', $domaineIds);
        }

        if (null !== $actif) {
            $qb
                ->andWHere('ref.etat = :etat')
                ->setParameter('etat', $actif)
            ;
        }
            
        if( $unlockedOnly )
        {
            $qb->andWhere('ref.lock = 0');
        }

        if( $fromDictionnaire )
        {
            $qb->andWhere('ref.reference = 1');
        }

        if( $fromRecherche )
        {
            $qb->andWhere('ref.inRecherche = 1');
        }

        $qb->orderBy('parent, ref.code, ref.order');

        return $qb->getQuery()->getResult();
    }

    /**
     * Récupère les données du grid sous forme de tableau correctement formaté
     *
     * @return Query Builder
     */
    public function getDatasForGrid($domainesIds, $condition = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('ref.id, ref.libelle, ref.code, ref.reference, ref.inRecherche, ref.inGlossaire, refEtat.libelle as etat, refParent.id as idParent, ref.allDomaines, domaine.nom as domaineNom')
            ->from('HopitalNumeriqueReferenceBundle:Reference', 'ref')
            ->leftJoin('ref.etat','refEtat')
            ->leftJoin('ref.parents','refParent')
            ->leftJoin('ref.domaines', 'domaine')
                ->where($qb->expr()->orX(
                    $qb->expr()->in('domaine.id', ':domainesId'),
                    $qb->expr()->isNull('domaine.id')
                ))
                ->setParameter('domainesId', $domainesIds)
            ->groupBy('ref.id')
            ->orderBy('ref.libelle');
            
        return $qb;
    }

    /**
     * Récupère les données pour l'export CSV
     *
     * @return QueryBuilder
     */
    public function getDatasForExport( $ids )
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('ref.id, ref.libelle, ref.code, ref.reference, ref.inRecherche, ref.inGlossaire, refEtat.libelle as etat, GROUP_CONCAT(DISTINCT conceptParent.libelle SEPARATOR \',\') AS parentLibelles, GROUP_CONCAT(DISTINCT domaine.nom SEPARATOR \',\') AS domaineNoms')
            ->from('HopitalNumeriqueReferenceBundle:Reference', 'ref')
            ->leftJoin('ref.etat', 'refEtat')
            ->leftJoin('ref.parents', 'conceptParent')
            ->leftJoin('ref.domaines', 'domaine')
            ->where('ref.id IN (:ids)')
            ->orderBy('ref.code, ref.order')
            ->groupBy('ref.id')
            ->setParameter('ids', $ids)
        ;
            
        return $qb;
    }

    /**
     * Récupère les références ayant un domaine
     *
     * @return [type]
     */
    public function getReferencesWithDomaine()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('ref')
            ->from('HopitalNumeriqueReferenceBundle:Reference', 'ref')
            ->leftJoin('ref.domaines','domaine')
                ->where($qb->expr()->isNotNull('domaine.id'))
            ->orderBy('domaine.nom');
            
        return $qb;
    }

    public function getReferencesUserConnectedForForm( $userId )
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('ref')
            ->from('HopitalNumeriqueReferenceBundle:Reference', 'ref')
            ->leftJoin('ref.domaines','domaine')
            ->leftJoin('domaine.users','user')
            ->where($qb->expr()->orX(
                $qb->expr()->eq('user.id', ':userId'),
                $qb->expr()->isNull('domaine.id')
            ))
            ->setParameter('userId', $userId)
            ->andWhere('ref.reference = 1')
            ->andWhere('ref.inRecherche = 1')
            ->orderBy('ref.order', 'ASC');
            
        return $qb;
    }

    public function getRefsByDomaineByParent($idParent, $idDomaine)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('ref')
            ->from('HopitalNumeriqueReferenceBundle:Reference', 'ref')
            ->innerJoin('ref.domaines', 'domaine', Expr\Join::WITH, 'domaine.id = :idDomaine')
            ->innerJoin('ref.parents', 'par', Expr\Join::WITH, 'par.id = :idParent')
            ->setParameters(array(
                'idDomaine' => $idDomaine,
                'idParent'  => $idParent,
            ))
            ->orderBy('ref.order');
            
        return $qb;
    }

    /**
     * Récupère les différents ref_code des références
     *
     * @return QueryBuilder
     */
    public function getAllRefCode(array $domainesQuestionnaireId)
    {
        //TODO : check si la fonction est utilisée autre part que pour les questions + passées les domaines du questionnaire pour filtrer
        $qb = $this->_em->createQueryBuilder();
        $qb->select('ref')
            ->from('HopitalNumeriqueReferenceBundle:Reference', 'ref')
            ->leftJoin('ref.domaines', 'domaine')
                ->where($qb->expr()->orX(
                    $qb->expr()->in('domaine.id', ':domainesId'),
                    $qb->expr()->isNull('domaine.id')
                ))
                ->setParameter('domainesId', $domainesQuestionnaireId)
            ->groupBy('ref.code')
            ->orderBy('ref.code');
            
        return $qb;
    }

    /**
     * Retourne les références enfants.
     *
     * @return array<\HopitalNumerique\ReferenceBundle\Entity\Reference> Références
     */
    public function findByParent(Reference $parent)
    {
        $qb = $this->createQueryBuilder('reference');

        $qb
            ->innerJoin('reference.parents', 'parent', Expr\Join::WITH, $qb->expr()->eq('parent.id', ':parent'))
            ->setParameter('parent', $parent)
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * Récupère les références en fonction de leur code
     *
     * @return array
     */
    public function findByCode($code, $actif = null)
    {
        return $this->findByCodeParent($code, null, $actif);
    }

    /**
     * Récupère références en fonction du code et de l'id du parent
     *
     * @return array
     */
    public function findByCodeParent($code, $parent = null, $actif = null)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb
            ->select('ref')
            ->from('HopitalNumeriqueReferenceBundle:Reference', 'ref')
            ->andWhere('ref.code = :code')
            ->setParameter('code', $code)
            ->orderBy('ref.order','ASC')
        ;

        if (null !== $actif) {
            $qb
                ->andWHere('ref.etat = :etat')
                ->setParameter('etat', ($actif ? Reference::STATUT_ACTIF_ID : Reference::STATUT_INACTIF_ID))
            ;
        }

        if (null !== $parent) {
            $qb
                ->innerJoin('ref.parents', 'parent', Expr\Join::WITH, 'parent.id = :parent')
                ->setParameter('parent', $parent)
            ;

        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Retourne les références selon des domaines.
     *
     * @param array<\HopitalNumerique\DomaineBundle\Entity\Domaine> $domaines    Domaines
     * @param boolean|null                                          $actif       Actif
     * @param boolean|null                                          $lock        Lock
     * @param boolean|null                                          $parentable  Parentable
     * @param boolean                                               $reference   Reference
     * @param boolean                                               $inRecherche InRecherche ?
     * @return array<\HopitalNumerique\ReferenceBundle\Entity\Reference> Références
     */
    public function findByDomaines($domaines, $actif, $lock, $parentable, $reference, $inRecherche)
    {
        if (0 === count($domaines)) {
            return [];
        }

        $qb = $this->createQueryBuilder('reference');

        $qb
            ->leftJoin('reference.domaines', 'domaine')
            ->where($qb->expr()->orX(
                $qb->expr()->in('domaine.id', ':domaines'),
                $qb->expr()->eq('reference.allDomaines', ':allDomaines')
            ))
            ->orderBy('reference.order', 'ASC')
            ->setParameters([
                'domaines' => $domaines,
                'allDomaines' => true
            ])
        ;
        if (null !== $actif) {
            $qb
                ->andWhere($qb->expr()->eq('reference.etat', ':actif'))
                ->setParameter('actif', $actif ? Reference::STATUT_ACTIF_ID : Reference::STATUT_INACTIF_ID)
            ;
        }
        if (null !== $lock) {
            $qb
                ->andWhere($qb->expr()->eq('reference.lock', ':lock'))
                ->setParameter('lock', $lock)
            ;
        }
        if (null !== $parentable) {
            $qb
                ->andWhere($qb->expr()->eq('reference.parentable', ':parentable'))
                ->setParameter('parentable', $parentable)
            ;
        }
        if (null !== $reference) {
            $qb
                ->andWhere($qb->expr()->eq('reference.reference', ':reference'))
                ->setParameter('reference', $reference)
            ;
        }
        if (null !== $inRecherche) {
            $qb
                ->andWhere($qb->expr()->eq('reference.inRecherche', ':inRecherche'))
                ->setParameter('inRecherche', $inRecherche)
            ;
        }

        return $qb->getQuery()->getResult();
    }
}
