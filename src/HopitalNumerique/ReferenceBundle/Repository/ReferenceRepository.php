<?php

namespace HopitalNumerique\ReferenceBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use HopitalNumerique\ReferenceBundle\Entity\Reference;

/**
 * ReferenceRepository.
 */
class ReferenceRepository extends EntityRepository
{
    public function getAllIndexedById()
    {
        return $this->createQueryBuilder('r', 'r.id')
            ->getQuery()->getResult()
        ;
    }

    /**
     * Récupère tous les items de l'arborescence référence dans le bon ordre.
     *
     * @param bool  $unlockedOnly
     * @param bool  $fromDictionnaire
     * @param bool  $fromRecherche
     * @param array $domaineIds
     * @param int   $actif
     *
     * @return array
     */
    public function getArbo(
        $unlockedOnly = false,
        $fromDictionnaire = false,
        $fromRecherche = false,
        $domaineIds = [],
        $actif = Reference::STATUT_ACTIF_ID
    ) {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('ref.id, ref.libelle, codes.label as code, par.id as parent, ref.order')
            ->from('HopitalNumeriqueReferenceBundle:Reference', 'ref')
            ->leftJoin('ref.codes', 'codes')
            ->leftJoin('ref.parents', 'par')
        ;

        if (count($domaineIds) !== 0) {
            $qb->leftJoin('ref.domaines', 'domaine')
                ->andWhere('domaine.id IN (:domainesId)')
                ->setParameter('domainesId', $domaineIds)
            ;
        }

        if (null !== $actif) {
            $qb
                ->andWHere('ref.etat = :etat')
                ->setParameter('etat', $actif)
            ;
        }

        if ($unlockedOnly) {
            $qb->andWhere('ref.lock = 0');
        }

        if ($fromDictionnaire) {
            $qb->andWhere('ref.reference = 1');
        }

        if ($fromRecherche) {
            $qb->andWhere('ref.inRecherche = 1');
        }

        $qb->orderBy('parent, codes.label, ref.order');

        return $qb->getQuery()->getResult();
    }

    /**
     * Récupère les données du grid sous forme de tableau correctement formaté.
     *
     * @param      $domainesIds
     * @param null $condition
     *
     * @return QueryBuilder
     */
    public function getDatasForGrid($domainesIds, $condition = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb
            ->select(
                'ref',
                'refEtat',
                'refParent',
                'domaine'
            )
            ->from('HopitalNumeriqueReferenceBundle:Reference', 'ref')
            ->leftJoin('ref.etat', 'refEtat')
            ->leftJoin('ref.parents', 'refParent')
            ->leftJoin('ref.domaines', 'domaine')
            ->where($qb->expr()->orX(
                $qb->expr()->in('domaine.id', ':domainesId'),
                $qb->expr()->isNull('domaine.id')
            ))
            ->setParameter('domainesId', $domainesIds)
            ->orderBy('ref.libelle')
        ;

        return $qb;
    }

    /**
     * Récupère les données pour l'export CSV.
     *
     * @return QueryBuilder
     */
    public function getDatasForExport($ids)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb
            ->select(
                'ref.id',
                'ref.libelle',
                'ref.sigle',
                'GROUP_CONCAT(DISTINCT refCodes.label SEPARATOR \', \') AS codes',
                'ref.order',
                'ref.reference',
                'ref.referenceLibelle',
                'ref.inRecherche',
                'ref.inGlossaire',
                'refEtat.libelle as etat',
                'GROUP_CONCAT(DISTINCT conceptParent.libelle SEPARATOR \',\') AS parentLibelles',
                'GROUP_CONCAT(DISTINCT domaine.nom SEPARATOR \',\') AS domaineNoms',
                'GROUP_CONCAT(DISTINCT synonymes.libelle SEPARATOR \',\') AS synonymesLibelle',
                'GROUP_CONCAT(DISTINCT champLexicalNoms.libelle SEPARATOR \',\') AS champLexicalNomsLibelle'
            )
            ->from('HopitalNumeriqueReferenceBundle:Reference', 'ref')
            ->leftJoin('ref.codes', 'refCodes')
            ->leftJoin('ref.etat', 'refEtat')
            ->leftJoin('ref.parents', 'conceptParent')
            ->leftJoin('ref.domaines', 'domaine')
            ->leftJoin('ref.synonymes', 'synonymes')
            ->leftJoin('ref.champLexicalNoms', 'champLexicalNoms')
            ->where('ref.id IN (:ids)')
            ->orderBy('refCodes.label, ref.order')
            ->groupBy('ref.id')
            ->setParameter('ids', $ids)
        ;

        return $qb;
    }

    /**
     * Récupère les références ayant un domaine.
     *
     * @return [type]
     */
    public function getReferencesWithDomaine()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('ref')
            ->from('HopitalNumeriqueReferenceBundle:Reference', 'ref')
            ->leftJoin('ref.domaines', 'domaine')
            ->where($qb->expr()->isNotNull('domaine.id'))
            ->orderBy('domaine.nom')
        ;

        return $qb;
    }

    public function getReferencesUserConnectedForForm($userId)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('ref')
            ->from('HopitalNumeriqueReferenceBundle:Reference', 'ref')
            ->leftJoin('ref.domaines', 'domaine')
            ->leftJoin('domaine.users', 'user')
            ->where($qb->expr()->orX(
                $qb->expr()->eq('user.id', ':userId'),
                $qb->expr()->isNull('domaine.id')
            ))
            ->setParameter('userId', $userId)
            ->andWhere('ref.reference = 1')
            ->andWhere('ref.inRecherche = 1')
            ->orderBy('ref.order', 'ASC')
        ;

        return $qb;
    }

    public function getRefsByDomaineByParent($idParent, $idDomaine)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('ref')
            ->from('HopitalNumeriqueReferenceBundle:Reference', 'ref')
            ->innerJoin('ref.domaines', 'domaine', Expr\Join::WITH, 'domaine.id = :idDomaine')
            ->innerJoin('ref.parents', 'par', Expr\Join::WITH, 'par.id = :idParent')
            ->setParameters([
                'idDomaine' => $idDomaine,
                'idParent' => $idParent,
            ])
            ->orderBy('ref.order')
        ;

        return $qb;
    }

    /**
     * Récupère les différents ref_code des références.
     *
     * @return QueryBuilder
     */
    public function getAllRefCode(array $domainesQuestionnaireId)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('ref')
            ->from('HopitalNumeriqueReferenceBundle:Reference', 'ref')
            ->leftJoin('ref.domaines', 'domaine')
            ->leftJoin('ref.codes', 'codes')
            ->where($qb->expr()->orX(
                $qb->expr()->in('domaine.id', ':domainesId'),
                $qb->expr()->isNull('domaine.id')
            ))
            ->setParameter('domainesId', $domainesQuestionnaireId)
            ->groupBy('codes.label')
            ->orderBy('codes.label')
        ;

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
     * Récupère les références en fonction de leur code.
     *
     * @return array
     */
    public function findByCode($code, $actif = null)
    {
        return $this->findByCodeParent($code, null, $actif);
    }

    /**
     * Récupère références en fonction du code et de l'id du parent.
     *
     * @return array
     */
    public function findByCodeParent($code, $parent = null, $actif = null)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb
            ->select('ref')
            ->from('HopitalNumeriqueReferenceBundle:Reference', 'ref')
            ->leftJoin('ref.codes', 'codes')
            ->andWhere('codes.label = :code')
            ->setParameter('code', $code)
            ->orderBy('ref.order', 'ASC')
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
     * @param      $domaines
     * @param      $actif
     * @param      $lock
     * @param      $parentable
     * @param      $reference
     * @param      $inRecherche
     * @param      $inGlossaire
     * @param bool $resultsInArray
     *
     * @return Reference[]
     */
    public function findByDomaines(
        $domaines,
        $actif,
        $lock,
        $parentable,
        $reference,
        $inRecherche,
        $inGlossaire,
        $resultsInArray = false
    ) {
        if (0 === count($domaines)) {
            return [];
        }

        $qb = $this->createQueryBuilder('reference');

        $qb
            ->select('
                reference,
                referenceParent,
                allDomaines,
                domainesDisplay
            ')
            ->leftJoin('reference.parents', 'referenceParent')
            ->leftJoin('reference.domaines', 'allDomaines')
            ->leftJoin('reference.domaines', 'domaine')
            ->leftJoin('reference.domainesDisplay', 'domainesDisplay')
            ->where($qb->expr()->orX(
                $qb->expr()->in('domaine.id', ':domaines'),
                $qb->expr()->eq('reference.allDomaines', ':allDomaines')
            ))
            ->setParameters([
                'domaines' => $domaines,
                'allDomaines' => true,
            ])
            ->orderBy('reference.order', 'ASC')
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
        if (null !== $inGlossaire) {
            $qb
                ->andWhere($qb->expr()->eq('reference.inGlossaire', ':inGlossaire'))
                ->setParameter('inGlossaire', $inGlossaire)
            ;
        }

        if ($resultsInArray == true) {
            $result = $qb->getQuery()->getArrayResult();
            array_walk($result, function (&$reference) {
                $reference['domainesId'] = array_map(function ($domaine) {
                    return $domaine['id'];
                }, $reference['domaines']);

                $reference['domainesDisplayId'] = array_map(function ($domaine) {
                    return $domaine['id'];
                }, $reference['domainesDisplay']);

                $reference['domainesDisplayId'] = array_intersect(
                    $reference['domainesId'],
                    $reference['domainesDisplayId']
                );
            });

            return $result;
        } else {
            return $qb->getQuery()->getResult();
        }
    }

    /**
     * Récupère les domaines en fonction de la référence.
     *
     * @param $idReference
     *
     * @return array
     */
    public function getDomainesByReference($idReference)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('reference, dom')
            ->from('HopitalNumeriqueReferenceBundle:Reference', 'reference')
            ->join('reference.domaines', 'dom')
            ->where('reference.id = :idReference')
            ->setParameter('idReference', $idReference)
        ;

        return $qb->getQuery()->getResult();
    }
}
