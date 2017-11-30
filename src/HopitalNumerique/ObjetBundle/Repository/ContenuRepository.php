<?php

namespace HopitalNumerique\ObjetBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ObjetBundle\Entity\Contenu;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * ContenuRepository.
 */
class ContenuRepository extends EntityRepository
{
    /**
     * @param integer $contentId
     *
     * @return Contenu
     */
    public function findByIdWithJoin($contentId)
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.objet', 'o')->addSelect('o')
            ->leftJoin('c.domaines', 'cd')->addSelect('cd')
            ->leftJoin('o.domaines', 'od')->addSelect('od')

            ->andWhere('c.id = :contentId')->setParameter('contentId', $contentId)

            ->setMaxResults(1)
            ->getQuery()->getSingleResult()
        ;
    }
    /**
     * @param array $contentIds
     *
     * @return Contenu[]
     */
    public function findByIdsWithJoin($contentIds)
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.objet', 'o')->addSelect('o')
            ->leftJoin('c.domaines', 'cd')->addSelect('cd')
            ->leftJoin('o.domaines', 'od')->addSelect('od')

            ->andWhere('c.id IN (:contentIds)')->setParameter('contentIds', $contentIds)

            ->getQuery()->getResult()
        ;
    }

    /**
     * Retourne tous les elements de contenu pour l'objet $id.
     *
     * @param int|array $id ID de(s) l'objet(s)
     *
     * @return array
     */
    public function getArboForObjet($id, $domaineIds = [])
    {
        $qb = $this->_em->createQueryBuilder()
                        ->select('con')
                        ->from('\HopitalNumerique\ObjetBundle\Entity\Contenu', 'con')
                        ->leftJoin('con.objet', 'obj');

        if (is_array($id)) {
            $qb->where('obj.id IN (:id)');
        } else {
            $qb->where('obj.id = :id');
        }

        if (count($domaineIds) !== 0) {
            $qb->leftJoin('obj.domaines', 'domaine')
                ->andWhere('domaine.id IN (:domainesId)')
                ->setParameter('domainesId', $domaineIds);
        }

        $qb->orderBy('con.parent, con.order')
           ->setParameter('id', $id);

        return $qb;
    }

    /**
     * Retourne le nombre des contenus ayant le même alias.
     *
     * @param Contenu $contenu Objet contenu
     *
     * @return int
     */
    public function countAlias($contenu)
    {
        $objet = $contenu->getObjet();

        return $this->_em->createQueryBuilder()
                         ->select('count(con)')
                         ->from('\HopitalNumerique\ObjetBundle\Entity\Contenu', 'con')
                         ->leftJoin('con.objet', 'obj')
                         ->andWhere('obj.id = :objet', 'con.id != :id')
                         ->andWhere('con.alias = :alias')
                         ->setParameter('objet', $objet->getId())
                         ->setParameter('id', $contenu->getId())
                         ->setParameter('alias', $contenu->getAlias());
    }

    /**
     * Compte le nombre de contenu parents lié à l'objet.
     *
     * @param Objet $objet Objet
     *
     * @return int
     */
    public function countContenu($objet)
    {
        return $this->_em->createQueryBuilder()
                         ->select('count(con)')
                         ->from('\HopitalNumerique\ObjetBundle\Entity\Contenu', 'con')
                         ->leftJoin('con.objet', 'obj')
                         ->andWhere('obj.id = :objet', 'con.parent IS NULL')
                         ->setParameter('objet', $objet->getId());
    }

    /**
     * @param User           $user
     * @param Domaine[]|null $domains
     *
     * @return array
     */
    public function getViewedContent(User $user, $domains = null)
    {
        $qb = $this->createQueryBuilder('content')
            ->addSelect('object', 'consultations')
            ->join('content.objet', 'object')
            ->join('content.consultations', 'consultations', Expr\Join::WITH, 'consultations.user = :userId')
            ->setParameter('userId', $user->getId())
        ;

        if (null !== $domains) {
            $qb
                ->join('consultations.domaine', 'domain', Expr\Join::WITH, 'domain.id IN (:domains)')
                ->setParameter('domains', $domains)
            ;
        }

        return $qb
            ->orderBy('consultations.consultationDate', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}
