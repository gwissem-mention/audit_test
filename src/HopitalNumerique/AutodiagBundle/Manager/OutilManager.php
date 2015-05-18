<?php

namespace HopitalNumerique\AutodiagBundle\Manager;

use Doctrine\ORM\EntityManager;
use HopitalNumerique\UserBundle\Manager\UserManager;
use HopitalNumerique\AutodiagBundle\Entity\Outil;
use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use Nodevo\ToolsBundle\Tools\Chaine;

/**
 * Manager de l'entité Outil.
 */
class OutilManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\AutodiagBundle\Entity\Outil';
    protected $_userManager;

    /**
     * Constructeur du manager gérant les références
     *
     * @param \Doctrine\ORM\EntityManager $entityManager EntityManager
     * @return void
     */
    public function __construct(EntityManager $entityManager, UserManager $userManager)
    {
        parent::__construct($entityManager);

        $this->_userManager = $userManager;
    }

    /**
     * Sauvegarde l'outil :gère les mises en forme et exceptions
     *
     * @param Outil $outil L'outil à sauvegarder
     *
     * @return empty
     */
    public function saveOutil( Outil $outil )
    {
        //manage alias
        $tool = new Chaine( ( $outil->getAlias() == '' ? $outil->getTitle() : $outil->getAlias() ) );
        $outil->setAlias( $tool->minifie() );

        //Hnadle boolean fields
        if( !$outil->isColumnChart() ){
            $outil->setColumnChartLabel( null );
            $outil->setColumnChartAxe( null );
        }

        if( !$outil->isRadarChart() ){
            $outil->setRadarChartLabel( null );
            $outil->setRadarChartAxe( null );
        }

        $this->save( $outil );
    }

    /**
     * Override : Récupère les données pour le grid sous forme de tableau
     *
     * @return array
     */
    public function getDatasForGrid( \StdClass $condition = null )
    {
        $domainesIds = $this->_userManager->getUserConnected()->getDomainesId();
        $outils = $this->getRepository()->getDatasForGrid( $domainesIds, $condition )->getQuery()->getResult();

        $results = array();

        foreach($outils as $outil) {
            $object                 = array();
            $object['id']           = $outil->getId();
            $object['title']        = $outil->getTitle();
            $object['dateCreation'] = $outil->getDateCreation();
            $object['statut']       = $outil->getStatut()->getLibelle();
            $object['nbChap']       = 0;
            $object['nbQuest']      = 0;
            $object['nbForm']       = 0;
            $object['nbFormValid']  = 0;
            $object['domainesNom']  = '';

            foreach ($outil->getDomaines() as $domaine) 
            {
                $object['domainesNom'] = $object['domainesNom'] === '' ? $domaine->getNom() : $object['domainesNom'] . '|' . $domaine->getNom();
            }

            //do some maths
            $chapitres = $outil->getChapitres();
            $object['nbChap'] = count($chapitres);
            foreach($chapitres as $chapitre)
            {
                $object['nbQuest'] += count($chapitre->getQuestions());
            }

            $resultats = $outil->getResultats();
            foreach($resultats as $resultat) 
            {
                if( is_null($resultat->getDateValidation()) )
                {
                    $object['nbForm']++;
                }
                else
                {
                    $object['nbFormValid']++;
                }
            }

            //set result to big array
            $results[] = $object;
        }

        return $results;
    }

    /**
     * Modifie l'état de l'outil
     *
     * @param array     $outils Liste des outils
     * @param Reference $ref    Référence désirée
     *
     * @return empty
     */
    public function toogleState($outils, $ref)
    {
        foreach($outils as $outil)
            $outil->setStatut( $ref );

        //save
        $this->_em->flush();
    }

    /**
     * 
     * @param Outil $outil
     */
    public function getCaracteristiquesChapitresRemplis(Outil $outil)
    {
        $resultatIdsRemplisByChapitreParentIds = $this->getResultatsRemplisIdsGroupedByChapitreParentRempliId($outil);
        $caracteristiquesChapitresRemplis = array();

        foreach ($resultatIdsRemplisByChapitreParentIds as $chapitreParentId => $resultatIds)
        {
            $caracteristiquesChapitresRemplis[$chapitreParentId] = $this->getCaracteristiquesChapitre($chapitreParentId, $resultatIds);
        }

        return $caracteristiquesChapitresRemplis;
    }
    
    /**
     *
     * @param Outil $outil
     */
    public function getCaracteristiquesCategoriesRemplies(Outil $outil)
    {
        $resultatIdsRemplisByCategorieIds = $this->getResultatsRemplisIdsGroupedByCategorieRemplieId($outil);
        $caracteristiquesCategoriesRemplies = array();
    
        foreach ($resultatIdsRemplisByCategorieIds as $categorieId => $resultatIds)
        {
            $caracteristiquesCategoriesRemplies[$categorieId] = $this->getCaracteristiquesCategorie($categorieId, $resultatIds);
        }
    
        return $caracteristiquesCategoriesRemplies;
    }
    
    /**
     * Retourne les caractéristiques d'un chapitre
     *
     * @param integer $chapitreParentId ID du chapitre
     * @param array<integer> $resultatIds Ids des résultarts
     */
    private function getCaracteristiquesChapitre($chapitreParentId, array $resultatIds)
    {
        $moyennesChapitreByResultat = $this->getRepository()->getMoyennesChapitreForEachResultat($chapitreParentId, $resultatIds);
        
        $chapitreCaracteristiques = array
        (
            'moyenne' => 0,
            'decile2' => 0,
            'decile8' => 0,
            'moyennePourcentage' => 0,
            'decile2Pourcentage' => 0,
            'decile8Pourcentage' => 0
        );
        
        foreach ($moyennesChapitreByResultat as $moyenneChapitreByResultat)
        {
            $chapitreCaracteristiques['moyenne'] += $moyenneChapitreByResultat['moyenne'];
        }
        $chapitreCaracteristiques['moyenne'] = $chapitreCaracteristiques['moyenne'] / count($moyennesChapitreByResultat);
        $chapitreCaracteristiques['decile2'] = $moyennesChapitreByResultat[ceil(count($moyennesChapitreByResultat) * 0.2) - 1]['moyenne'];
        $chapitreCaracteristiques['decile8'] = $moyennesChapitreByResultat[ceil(count($moyennesChapitreByResultat) * 0.8) - 1]['moyenne'];
        
        $chapitreCaracteristiques['moyennePourcentage'] = intval($chapitreCaracteristiques['moyenne'] * 100);
        $chapitreCaracteristiques['decile2Pourcentage'] = intval($chapitreCaracteristiques['decile2'] * 100);
        $chapitreCaracteristiques['decile8Pourcentage'] = intval($chapitreCaracteristiques['decile8'] * 100);

        return $chapitreCaracteristiques;
    }
    
    /**
     * Retourne les caractéristiques d'une catégorie
     *
     * @param integer $categorieId ID de la catégorie
     * @param array<integer> $resultatIds Ids des résultarts
     */
    private function getCaracteristiquesCategorie($categorieId, array $resultatIds)
    {
        $moyennesCategorieByResultat = $this->getRepository()->getMoyennesCategorieForEachResultat($categorieId, $resultatIds);
        
        $categorieCaracteristiques = array
        (
            'moyenne' => 0,
            'decile2' => 0,
            'decile8' => 0,
            'moyennePourcentage' => 0,
            'decile2Pourcentage' => 0,
            'decile8Pourcentage' => 0
        );
        
        foreach ($moyennesCategorieByResultat as $moyenneCategorieByResultat)
        {
            $categorieCaracteristiques['moyenne'] += $moyenneCategorieByResultat['moyenne'];
        }
        $categorieCaracteristiques['moyenne'] = $categorieCaracteristiques['moyenne'] / count($moyennesCategorieByResultat);
        $categorieCaracteristiques['decile2'] = $moyennesCategorieByResultat[ceil(count($moyennesCategorieByResultat) * 0.2) - 1]['moyenne'];
        $categorieCaracteristiques['decile8'] = $moyennesCategorieByResultat[ceil(count($moyennesCategorieByResultat) * 0.8) - 1]['moyenne'];
        
        $categorieCaracteristiques['moyennePourcentage'] = intval($categorieCaracteristiques['moyenne'] * 100);
        $categorieCaracteristiques['decile2Pourcentage'] = intval($categorieCaracteristiques['decile2'] * 100);
        $categorieCaracteristiques['decile8Pourcentage'] = intval($categorieCaracteristiques['decile8'] * 100);

        return $categorieCaracteristiques;
    }
    
    /**
     * Retourne la liste des IDs de OutilResultat regroupés par IDs de OutilChapitre dont le chapitre pour chaque résultat a été rempli à 100%.
     */
    private function getResultatsRemplisIdsGroupedByChapitreParentRempliId(Outil $outil)
    {
        $chapitreIdsAndResultatIds = $this->getRepository()->getChapitreParentIdsAndResultatIds($outil);
        $chapitreIdsAndResultatIdsNonRemplis = $this->getRepository()->getChapitresParentsNonRemplisIdsAndResultatsNonRemplisIds($outil);

        $resultatIdsRemplisByChapitreIds = array();
        
        // On initialise $resultatIdsRemplisByChapitreIds avec tous les chapitres (remplis à 100% ou pas)
        foreach ($chapitreIdsAndResultatIds as $chapitreIdAndResultatId)
        {
            $resultatId = $chapitreIdAndResultatId['resultatId'];
            $chapitreId = $chapitreIdAndResultatId['chapitreId'];
            
            if (!isset($resultatIdsRemplisByChapitreIds[$chapitreId]))
                $resultatIdsRemplisByChapitreIds[$chapitreId] = array();
            
            $resultatIdsRemplisByChapitreIds[$chapitreId][] = $resultatId;
        }
        
        // On enlève dans $resultatIdsRemplisByChapitreIds les chapitres/résultats non remplis à 100
        foreach ($chapitreIdsAndResultatIdsNonRemplis as $chapitreIdAndResultatIdNonRempli)
        {
            $resultatId = $chapitreIdAndResultatIdNonRempli['resultatId'];
            $chapitreId = $chapitreIdAndResultatIdNonRempli['chapitreId'];
            
            foreach ($resultatIdsRemplisByChapitreIds[$chapitreId] as $i => $resultatIdRempli)
            {
                if ($resultatId == $resultatIdRempli)
                {
                    unset($resultatIdsRemplisByChapitreIds[$chapitreId][$i]);
                    if (count($resultatIdsRemplisByChapitreIds[$chapitreId]) == 0)
                        unset($resultatIdsRemplisByChapitreIds[$chapitreId]);
                    break;
                }
            }
        }
        
        return $resultatIdsRemplisByChapitreIds;
    }
    
    /**
     * Retourne la liste des IDs de OutilResultat regroupés par IDs de OutilChapitre dont le chapitre pour chaque résultat a été rempli à 100%.
     */
    private function getResultatsRemplisIdsGroupedByCategorieRemplieId(Outil $outil)
    {
        $categorieIdsAndResultatIds = $this->getRepository()->getCategoriesIdsAndResultatIds($outil);
        $categorieIdsAndResultatIdsNonRemplis = $this->getRepository()->getCategoriesNonRempliesIdsAndResultatsNonRemplisIds($outil);
    
        $resultatIdsRemplisByCategorieId = array();
    
        // On initialise $resultatIdsRemplisByChapitreIds avec tous les chapitres (remplis à 100% ou pas)
        foreach ($categorieIdsAndResultatIds as $categorieIdAndResultatId)
        {
            $resultatId = $categorieIdAndResultatId['resultatId'];
            $categorieId = $categorieIdAndResultatId['categorieId'];
    
            if (!isset($resultatIdsRemplisByCategorieId[$categorieId]))
                $resultatIdsRemplisByCategorieId[$categorieId] = array();
    
            $resultatIdsRemplisByCategorieId[$categorieId][] = $resultatId;
        }
    
        // On enlève dans $resultatIdsRemplisByChapitreIds les chapitres/résultats non remplis à 100
        foreach ($categorieIdsAndResultatIdsNonRemplis as $chapitreIdAndResultatIdNonRempli)
        {
            $resultatId = $chapitreIdAndResultatIdNonRempli['resultatId'];
            $categorieId = $chapitreIdAndResultatIdNonRempli['categorieId'];
    
            foreach ($resultatIdsRemplisByCategorieId[$categorieId] as $i => $resultatIdRempli)
            {
                if ($resultatId == $resultatIdRempli)
                {
                    unset($resultatIdsRemplisByCategorieId[$categorieId][$i]);
                    if (count($resultatIdsRemplisByCategorieId[$categorieId]) == 0)
                        unset($resultatIdsRemplisByCategorieId[$categorieId]);
                    break;
                }
            }
        }
    
        return $resultatIdsRemplisByCategorieId;
    }
}
