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
     * @param integer|array $id ID de(s) l'objet(s)
     *
     * @return array
     */
    public function getArboForObjet( $id, $domaineIds = array() )
    {
        $qb = $this->_em->createQueryBuilder()
                        ->select('con')
                        ->from('\HopitalNumerique\ObjetBundle\Entity\Contenu', 'con')
                        ->leftJoin('con.objet','obj');

        if( is_array($id) )
        {
            $qb->where('obj.id IN (:id)');
        }
        else
        {
            $qb->where('obj.id = :id');
        }

        if(count($domaineIds) !== 0)
        {
            $qb->leftJoin('obj.domaines', 'domaine')
                ->andWhere('domaine.id IN (:domainesId)')
                ->setParameter('domainesId', $domaineIds);
        }

        $qb->orderBy('con.parent, con.order')
           ->setParameter('id', $id);

        return $qb;
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