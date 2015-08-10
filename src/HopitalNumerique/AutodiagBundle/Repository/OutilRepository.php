<?php

namespace HopitalNumerique\AutodiagBundle\Repository;

use Doctrine\ORM\EntityRepository;
use HopitalNumerique\AutodiagBundle\Entity\Outil;

/**
 * OutilRepository
 */
class OutilRepository extends EntityRepository
{
    /**
     * Récupère les données du grid sous forme de tableau correctement formaté
     *
     * @return Query Builder
     */
    public function getDatasForGrid($domainesIds, $condition = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('out')
            ->from('HopitalNumeriqueAutodiagBundle:Outil', 'out')
            ->leftJoin('out.domaines', 'domaine')
                ->where($qb->expr()->orX(
                    $qb->expr()->in('domaine.id', ':domainesId'),
                    $qb->expr()->isNull('domaine.id')
                ))
                ->setParameter('domainesId', $domainesIds)
            ->orderBy('out.title', 'ASC');
            
        return $qb;
    }

    public function getOutilsByDate(\DateTime $dateDebut = null, \DateTime $dateFin = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('out')
            ->from('HopitalNumeriqueAutodiagBundle:Outil', 'out');

            if(!is_null($dateDebut))
            {
                $qb->where('out.dateCreation <= :dateDebut')
                    ->setParameter('dateDebut', $dateDebut);
            }
            if(!is_null($dateDebut))
            {
                $qb->andWhere('out.dateCreation >= :dateFin')
                    ->setParameter('dateFin', $dateFin);
            }
            
        return $qb;
    }

    /**
     * Retourne un tableau à 2 dimensions avec les duos de tous les Resultat/Chapitre.
     * 
     * @param \HopitalNumerique\AutodiagBundle\Entity\Outil $outil
     */
    public function getChapitreParentIdsAndResultatIds(Outil $outil)
    {
        $requete = $this->_em->createQueryBuilder();
        
        $requete
            ->select('outilResultat.id AS resultatId, outilChapitre.id AS chapitreId')
            ->from('HopitalNumeriqueAutodiagBundle:Chapitre', 'outilChapitre')
            ->where('outilChapitre.parent IS NULL')
            ->leftJoin('outilChapitre.enfants', 'outilChapitreEnfant')
            ->innerJoin('outilChapitre.outil', 'outil', 'WITH', 'outil = :outil')
            ->setParameter('outil', $outil)
            ->innerJoin('outil.resultats', 'outilResultat', 'WITH', 'outilResultat.user IS NOT NULL AND outilResultat.dateValidation IS NOT NULL')
            ->innerJoin('HopitalNumeriqueAutodiagBundle:Question', 'outilQuestion', 'WITH', '(outilChapitre = outilQuestion.chapitre OR outilChapitreEnfant = outilQuestion.chapitre)')
            ->leftJoin('outilQuestion.reponses', 'outilReponse', 'WITH', 'outilReponse.resultat = outilResultat')
            ->groupBy('outilResultat.id, outilChapitre.id')
        ;

        return ($requete->getQuery()->getResult());
    }

    /**
     * Retourne un tableau à 2 dimensions avec les duos de tous les Resultat/Categorie.
     *
     * @param \HopitalNumerique\AutodiagBundle\Entity\Outil $outil
     */
    public function getCategoriesIdsAndResultatIds(Outil $outil)
    {
        $requete = $this->_em->createQueryBuilder();
    
        $requete
            ->select('outilResultat.id AS resultatId, outilCategorie.id AS categorieId')
            ->from('HopitalNumeriqueAutodiagBundle:Categorie', 'outilCategorie')
            ->innerJoin('outilCategorie.outil', 'outil', 'WITH', 'outil = :outil')
            ->setParameter('outil', $outil)
            ->innerJoin('outil.resultats', 'outilResultat', 'WITH', 'outilResultat.user IS NOT NULL AND outilResultat.dateValidation IS NOT NULL')
            ->innerJoin('outilCategorie.questions', 'outilQuestion')
            ->leftJoin('outilQuestion.reponses', 'outilReponse', 'WITH', 'outilReponse.resultat = outilResultat')
            ->groupBy('outilResultat.id, outilCategorie.id')
        ;
    
        return ($requete->getQuery()->getResult());
    }
    
    /**
     * Retourne un tableau à 2 dimensions avec les duos Resultat/Chapitre parent remplis à moins de 100%.
     *
     * @param \HopitalNumerique\AutodiagBundle\Entity\Outil $outil
     */
    public function getChapitresParentsNonRemplisIdsAndResultatsNonRemplisIds(Outil $outil)
    {
        $requete = $this->_em->createQueryBuilder();
    
        $requete
            ->select('outilResultat.id AS resultatId, outilChapitre.id AS chapitreId')
            ->from('HopitalNumeriqueAutodiagBundle:Chapitre', 'outilChapitre')
            ->where('outilChapitre.parent IS NULL')
            ->andWhere('outilReponse IS NOT NULL')
            ->leftJoin('outilChapitre.enfants', 'outilChapitreEnfant')
            ->innerJoin('outilChapitre.outil', 'outil', 'WITH', 'outil = :outil')
            ->setParameter('outil', $outil)
            // Ne concerne que les questionnaires des personnes connectées
            ->innerJoin('outil.resultats', 'outilResultat', 'WITH', 'outilResultat.user IS NOT NULL AND outilResultat.dateValidation IS NOT NULL')
            ->innerJoin('HopitalNumeriqueAutodiagBundle:Question', 'outilQuestion', 'WITH', '(outilChapitre = outilQuestion.chapitre OR outilChapitreEnfant = outilQuestion.chapitre)')
            // Si réponse vide, on considère que le questionnaire n'est pas rempli à 100%
            ->leftJoin('outilQuestion.reponses', 'outilReponse', 'WITH', 'outilReponse.resultat = outilResultat AND outilReponse.value = :vide')
            ->setParameter('vide', '')
            ->groupBy('outilResultat.id, outilChapitre.id')
        ;

        return ($requete->getQuery()->getResult());
    }
    
    /**
     * Retourne un tableau à 2 dimensions avec les duos Resultat/Categorie parent remplis à moins de 100%.
     *
     * @param \HopitalNumerique\AutodiagBundle\Entity\Outil $outil
     */
    public function getCategoriesNonRempliesIdsAndResultatsNonRemplisIds(Outil $outil)
    {
        $requete = $this->_em->createQueryBuilder();
    
        $requete
            ->select('outilResultat.id AS resultatId, outilCategorie.id AS categorieId')
            ->from('HopitalNumeriqueAutodiagBundle:Categorie', 'outilCategorie')
            ->andWhere('outilReponse IS NOT NULL')
            ->innerJoin('outilCategorie.outil', 'outil', 'WITH', 'outil = :outil')
            ->setParameter('outil', $outil)
            // Ne concerne que les questionnaires des personnes connectées
            ->innerJoin('outil.resultats', 'outilResultat', 'WITH', 'outilResultat.user IS NOT NULL AND outilResultat.dateValidation IS NOT NULL')
            ->innerJoin('outilCategorie.questions', 'outilQuestion')
            // Si réponse vide, on considère que le questionnaire n'est pas rempli à 100%
            ->leftJoin('outilQuestion.reponses', 'outilReponse', 'WITH', 'outilReponse.resultat = outilResultat AND outilReponse.value = :vide')
            ->setParameter('vide', '')
            ->groupBy('outilResultat.id, outilCategorie.id')
        ;

        return ($requete->getQuery()->getResult());
    }
    
    /**
     * Retourne les caractéristiques d'un chapitre
     * 
     * @param integer $chapitreParentId ID du chapitre
     * @param array<integer> $resultatIds Ids des résultarts
     */
    public function getMoyennesChapitreForEachResultat($chapitreParentId, array $resultatIds)
    {
        $requete = $this->_em->createQueryBuilder();

        $requete
            ->select('AVG(outilReponse.value * outilQuestion.ponderation) * COUNT(outilReponse) / SUM(outilQuestion.ponderation) AS moyenne')
            ->from('HopitalNumeriqueAutodiagBundle:Reponse', 'outilReponse')
            ->innerJoin('outilReponse.question', 'outilQuestion')
            ->innerJoin('outilQuestion.chapitre', 'outilChapitre', 'WITH', 'outilChapitre.id = :chapitreId OR outilChapitre.parent = :chapitreId')
            ->setParameter('chapitreId', $chapitreParentId)
            ->where($requete->expr()->in('outilReponse.resultat', $resultatIds))
            ->andWhere('outilReponse.value != :nonConcerne')
            ->setParameter('nonConcerne', -1)
            ->groupBy('outilReponse.resultat')
            ->orderBy('moyenne', 'ASC')
        ;
        
        return ($requete->getQuery()->getResult());
    }
    
    /**
     * Retourne les caractéristiques d'une catégorie
     * 
     * @param integer $categorieId ID du chapitre
     * @param array<integer> $resultatIds Ids des résultarts
     */
    public function getMoyennesCategorieForEachResultat($categorieId, array $resultatIds)
    {
        $requete = $this->_em->createQueryBuilder();

        $requete
            ->select('AVG(outilReponse.value * outilQuestion.ponderation) * COUNT(outilReponse) / SUM(outilQuestion.ponderation) AS moyenne')
            ->from('HopitalNumeriqueAutodiagBundle:Reponse', 'outilReponse')
            ->innerJoin('outilReponse.question', 'outilQuestion')
            ->innerJoin('outilQuestion.categorie', 'outilCategorie', 'WITH', 'outilCategorie.id = :categorieId')
            ->setParameter('categorieId', $categorieId)
            ->where($requete->expr()->in('outilReponse.resultat', $resultatIds))
            ->andWhere('outilReponse.value != :nonConcerne')
            ->setParameter('nonConcerne', -1)
            ->groupBy('outilReponse.resultat')
            ->orderBy('moyenne', 'ASC')
        ;
        
        return ($requete->getQuery()->getResult());
    }
}
