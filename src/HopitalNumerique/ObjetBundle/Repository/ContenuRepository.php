<?php

namespace HopitalNumerique\ObjetBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ContenuRepository
 */
class ContenuRepository extends EntityRepository
{
    /**
     * Retourne tous les elements de contenu pour l'objet $id
     *
     * @param integer $id ID de l'objet
     *
     * @return array
     */
    public function getArboForObjet( $id )
    {
        return $this->_em->createQueryBuilder()
                         //->select('con.id, con.titre, refParent.id as parent, con.order')
                         ->select('con')
                         ->from('\HopitalNumerique\ObjetBundle\Entity\Contenu', 'con')
                         ->leftJoin('con.objet','obj')
                         //->leftJoin('con.parent','refParent')
                         ->where('obj.id = :id')
                         ->orderBy('con.parent, con.order')
                         ->setParameter('id', $id);
    }
    
    /**
     * Retourne tous les elements de contenu pour l'objet $id
     *
     * @param array $id ID de l'objet
     *
     * @return array
     */
    public function getArboForObjets( $ids )
    {
        return $this->_em->createQueryBuilder()
            ->select('con')
            ->from('\HopitalNumerique\ObjetBundle\Entity\Contenu', 'con')
            ->leftJoin('con.objet','obj')
            ->where('obj.id IN (:ids)')
            ->orderBy('con.parent, con.order')
            ->setParameter('ids', $ids);
    }

    /**
     * Retourne le nombre des contenus ayant le même alias
     *
     * @param Contenu $contenu Objet contenu
     *
     * @return integer
     */
    public function countAlias($contenu)
    {
        $objet = $contenu->getObjet();

        return $this->_em->createQueryBuilder()
                         ->select('count(con)')
                         ->from('\HopitalNumerique\ObjetBundle\Entity\Contenu', 'con')
                         ->leftJoin('con.objet','obj')
                         ->andWhere('obj.id = :objet','con.id != :id')
                         ->andWhere('con.alias = :alias')
                         ->setParameter('objet', $objet->getId() )
                         ->setParameter('id', $contenu->getId() )
                         ->setParameter('alias', $contenu->getAlias() );
    }

    /**
     * Compte le nombre de contenu parents lié à l'objet
     *
     * @param Objet $objet Objet
     *
     * @return integer
     */
    public function countContenu( $objet )
    {
        return $this->_em->createQueryBuilder()
                         ->select('count(con)')
                         ->from('\HopitalNumerique\ObjetBundle\Entity\Contenu', 'con')
                         ->leftJoin('con.objet','obj')
                         ->andWhere('obj.id = :objet', 'con.parent IS NULL')
                         ->setParameter('objet', $objet->getId() );
    }
}