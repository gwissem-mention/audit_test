<?php
namespace HopitalNumerique\AutodiagBundle\Manager;

use HopitalNumerique\AutodiagBundle\Entity\Outil;
use HopitalNumerique\AutodiagBundle\Entity\Resultat;
use HopitalNumerique\UserBundle\Entity\User;
use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use Symfony\Component\HttpFoundation\Response;

/**
 * Manager de l'entité Resultat.
 */
class ResultatManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\AutodiagBundle\Entity\Resultat';
    
    /**
     * @var \HopitalNumerique\AutodiagBundle\Manager\OutilManager OutilManager
     */
    private $outilManager;
    
    /** (non-PHPdoc)
     * @see \Nodevo\ToolsBundle\Manager\Manager::__construct()
     */
    public function __construct($em, OutilManager $outilManager)
    {
        parent::__construct($em);
        
        $this->outilManager = $outilManager;
    }

    /**
     * Override : Récupère les données pour le grid sous forme de tableau
     *
     * @return array
     */
    public function getDatasForGrid( \StdClass $condition = null )
    {
        $resultats = $this->findBy( array( $condition->field => $condition->value) );
        $results   = array();

        foreach($resultats as $resultat)
        {
            $datas               = array();
            $datas['id']         = $resultat->getId();
            $datas['taux']       = $resultat->getTauxRemplissage() . '%';
            $datas['lastSave']   = $resultat->getDateLastSave();
            $datas['validation'] = $resultat->getDateValidation();

            if( $user = $resultat->getUser() )
            {
                $datas['user']          = $user->getPrenomNom();
                $datas['etablissement'] = $user->getEtablissementRattachementSante() ? $user->getEtablissementRattachementSante()->getNom() : $user->getAutreStructureRattachementSante();
            } else {
                $datas['user']          = '';
                $datas['etablissement'] = '';
            }

            $results[] = $datas;
        }

        return $results;
    }

    /**
     * Formate les résultats
     *
     * @param Resultat $resultat L'objet Résultat
     *
     * @return array
     */
    public function formateResultat( Resultat $resultat )
    {
        //build reponses array
        $tab                   = $this->buildQuestionsReponses( $resultat->getReponses() );
        $questionsReponses     = $tab['front'];
        $questionsReponsesBack = $tab['back'];

        //build chapitres and add previous responses
        $parents = array();
        $enfants = array();

        //preorder chapters
        $chapitres = $this->makeChaptersOrdered( $resultat->getOutil()->getChapitres() );
        $syntheseResultatsFormates = array();
        foreach ($resultat->getResultats() as $syntheseResultat)
            $syntheseResultatsFormates[] = $this->formateResultat($syntheseResultat);
        

        foreach($chapitres as $one)
        {
            $chapitre = new \StdClass;
            
            //build chapitre values
            $chapitre->id                          = $one->getId();
            $chapitre->synthese                    = $one->getSynthese();
            $chapitre->title                       = $one->getCode() != '' ? $one->getCode() . '. ' . $one->getTitle() : $one->getTitle();
            $chapitre->code                        = $one->getCode();
            $chapitre->childs                      = array();
            $chapitre->noteMin                     = $one->getNoteMinimale();
            $chapitre->noteOpt                     = $one->getNoteOptimale();
            $chapitre->intro                       = $one->getIntro();
            $chapitre->desc                        = $one->getDesc();
            $chapitre->lien                        = $one->getLien();
            $chapitre->descriptionLien             = $one->getDescriptionLien();
            $chapitre->order                       = $one->getOrder();
            $chapitre->affichageRestitutionBarre   = $one->getAffichageRestitutionBarre();
            $chapitre->affichageRestitutionRadar   = $one->getAffichageRestitutionRadar();
            $chapitre->affichageRestitutionTableau = $one->getAffichageRestitutionTableau();
            $chapitre->centPourcentReponseObligatoire = $one->getOutil()->isCentPourcentReponseObligatoire();
            $chapitre->parent                      = !is_null($one->getParent()) ? $one->getParent()->getId() : null;
            
            $chapitre->syntheseChapitres = array();
            foreach ($syntheseResultatsFormates as $syntheseResultatFormate)
            {
                foreach ($syntheseResultatFormate as $syntheseChapitreFormate)
                {
                    if ($syntheseChapitreFormate->id == $chapitre->id)
                    {
                        $chapitre->syntheseChapitres[] = $syntheseChapitreFormate;
                        break;
                    }
                }
            }

            //handle questions/reponses
            $chapitre = $this->buildQuestions( $one->getQuestions(), $chapitre, $questionsReponses, $questionsReponsesBack );
            
            //handle parents / enfants
            if( is_null($one->getParent()) )
            {
                $parents[ $one->getId() ] = $chapitre;
            }
            else
            {
                $enfants[] = $chapitre;
            }
        }

        //reformate les chapitres FRONT
        foreach($enfants as $enfant)
        {
            $parent = $parents[ $enfant->parent ];
            $parent->childs[] = $enfant;
            // RLE : On compte les réponses des enfants également
            $parent->nbQuestionsRemplies += $enfant->nbQuestionsRemplies;
        }

        //Calcul de la notre des chapitres parents
        foreach ($parents as $parent) 
        {
            $scoreTemp = 0;
            $compteur  = 0;
            foreach ($parent->questionsForCharts as $question) 
            {
                if($question->tableValue == -1)
                {
                    continue;
                }
                $scoreTemp += ($question->max != 0 ) ? $question->tableValue * $question->ponderation * 100 / $question->max : 0;
                $compteur++;
            }
            //Parcourt les sous chapitres
            foreach ($parent->childs as $chapChild) 
            {
                foreach ($chapChild->questionsForCharts as $questionChild) 
                {
                    if($questionChild->tableValue == -1)
                    {
                        continue;
                    }
                    $scoreTemp += ($questionChild->max != 0 ) ? $questionChild->tableValue * $questionChild->ponderation * 100 / $questionChild->max : 0;
                    $compteur++;
                }
            }
            $parent->noteChapitre = ($compteur != 0 ) ? round($scoreTemp / $compteur, 0) : 0;
        }
        
        return $parents;
    }

    /**
     * Récupère le dernier résultat validé
     *
     * @param Outil  $outil L'outil
     * @param User   $user  L'utilisateur connecté
     *
     * @return Resultat
     */
    public function getLastResultatValided( Outil $outil, User $user )
    {
        return $this->getRepository()->getLastResultatValided($outil, $user)->getQuery()->getOneOrNullResult();
    }

    /**
     * Récupération des autodiags de l'utilisateur trié par domaine pour l'interface de "mon compte"
     *
     * @param User $user [description]
     *
     * @return [type]
     */
    public function getAutodiagsMonCompte(User $user)
    {
        $resultatsByDomaine = array();

        //Récupération des résultats à trier
        $resultats = $this->findBy( array( 'user' => $user ), array('dateLastSave' => 'DESC') );
        
        //Tri les résultats par domaine
        foreach ($resultats as $resultat) 
        {
            foreach ($resultat->getOutil()->getDomaines() as $domaineOutil) 
            {
                if(!array_key_exists($domaineOutil->getId(), $resultatsByDomaine))
                {
                    $resultatsByDomaine[$domaineOutil->getId()] = array();
                }

                $resultatsByDomaine[$domaineOutil->getId()][] = $resultat;
            }
        }

        return $resultatsByDomaine;
    }
    
    /**
     * Construit le tableau de graphiques pour la génération en FRONT
     * Axe : 1 = Chapitres // 2 = Catégories
     * 
     * @param Resultat $resultat  L'entitée résultat
     * @param array    $chapitres Liste des chapitres
     *
     * @return array
     */
    public function buildCharts( Resultat $resultat, $chapitres )
    {
        $results = array();

        //récupère les données pour les graphiques
        $outil = $resultat->getOutil();

        //récupère le gros tableau de questions / réponses
        $questionsReponses = $this->buildQuestionsReponses( $resultat->getReponses() );
        $questionsReponses = $questionsReponses['front'];

        $categoriesTemp = $outil->getCategories();

        //cas first chart
        if ( $outil->isColumnChart() )
        {
            $chapitresFormated = array();
            foreach ($chapitres as $chapitre)
            {
                if($chapitre->affichageRestitutionBarre)
                {
                    $chapitresFormated[] = $chapitre;
                }
            }

            //get Datas for Each axes : Chapitres / Catégories
            $datasAxeChapitre   = $this->buildDatasAxeChapitre( $chapitresFormated );

            $categories     = array();
            foreach ($categoriesTemp as $categorie) 
            {
                if($categorie->getAffichageRestitutionBarre())
                {
                    $categories[] = $categorie;
                }
            }
            $datasAxeCategories = $this->buildDatasAxeCategories( $categories , $questionsReponses );

            $chart         = new \StdClass;
            $chart->title  = $outil->getColumnChartLabel();
            $chart->panels = ($outil->getColumnChartAxe() == 1) ? $datasAxeChapitre : $datasAxeCategories;
            
            $results['barre'] = $chart;
        }

        //cas Spider Web
        if ( $outil->isRadarChart() )
        {
            $chapitresFormated = array();
            foreach ($chapitres as $chapitre)
            {
                if($chapitre->affichageRestitutionRadar)
                {
                    $chapitresFormated[] = $chapitre;
                }
            }

            //get Datas for Each axes : Chapitres / Catégories
            $datasAxeChapitre   = $this->buildDatasAxeChapitre( $chapitresFormated );
            
            $categories     = array();
            foreach ($categoriesTemp as $categorie) 
            {
                if($categorie->getAffichageRestitutionRadar())
                {
                    $categories[] = $categorie;
                }
            }
            $datasAxeCategories = $this->buildDatasAxeCategories( $categories , $questionsReponses );

            $chart        = new \StdClass;
            $chart->title = $outil->getRadarChartLabel();
            $chart->datas = ($outil->getRadarChartAxe() == 1) ? $datasAxeChapitre : $datasAxeCategories;
            
            
            
            if ($outil->isRadarChartAfficheBenchmark())
            {
                if ($outil->getRadarChartAxe() == 1) // Chapitre
                {
                    $chapitresRemplisCaracteristiques = $this->outilManager->getCaracteristiquesChapitresRemplis($outil);

                    foreach ($chart->datas as $radarData)
                    {
                        $chapitreId = $radarData->id;

                        if ($outil->isRadarChartBenchmarkAfficheMoyenne())
                            $radarData->moyennePourcentage = (isset($chapitresRemplisCaracteristiques[$chapitreId]) ? $chapitresRemplisCaracteristiques[$chapitreId]['moyennePourcentage'] : '0');
                        if ($outil->isRadarChartBenchmarkAfficheDecile2())
                            $radarData->decile2Pourcentage = (isset($chapitresRemplisCaracteristiques[$chapitreId]) ? $chapitresRemplisCaracteristiques[$chapitreId]['decile2Pourcentage'] : '0');
                        if ($outil->isRadarChartBenchmarkAfficheDecile8())
                            $radarData->decile8Pourcentage = (isset($chapitresRemplisCaracteristiques[$chapitreId]) ? $chapitresRemplisCaracteristiques[$chapitreId]['decile8Pourcentage'] : '0');
                    }
                }
                else
                {
                    $categoriesRempliesCaracteristiques = $this->outilManager->getCaracteristiquesCategoriesRemplies($outil);

                    foreach ($chart->datas as $radarData)
                    {
                        $chapitreId = $radarData->id;

                        if ($outil->isRadarChartBenchmarkAfficheMoyenne())
                            $radarData->moyennePourcentage = (isset($categoriesRempliesCaracteristiques[$chapitreId]) ? $categoriesRempliesCaracteristiques[$chapitreId]['moyennePourcentage'] : '0');
                        if ($outil->isRadarChartBenchmarkAfficheDecile2())
                            $radarData->decile2Pourcentage = (isset($categoriesRempliesCaracteristiques[$chapitreId]) ? $categoriesRempliesCaracteristiques[$chapitreId]['decile2Pourcentage'] : '0');
                        if ($outil->isRadarChartBenchmarkAfficheDecile8())
                            $radarData->decile8Pourcentage = (isset($categoriesRempliesCaracteristiques[$chapitreId]) ? $categoriesRempliesCaracteristiques[$chapitreId]['decile8Pourcentage'] : '0');
                    }
                }
                
            }

            $results['radar'] = $chart;
        }

        //cas Table
        if ( $outil->isTableChart() )
        {
            $chapitresFormated = array();
            foreach ($chapitres as $chapitre)
            {
                if($chapitre->affichageRestitutionTableau)
                {
                    $chapitresFormated[] = $chapitre;
                }
            }
            
            $categories     = array();
            foreach ($categoriesTemp as $categorie) 
            {
                if($categorie->getAffichageRestitutionTableau())
                {
                    $categories[] = $categorie;
                }
            }

            $chart        = new \StdClass;
            $chart->title = 'Mes résultats détaillés';
            $chart->datas = $this->buildDatasTable( $categories , $chapitresFormated, $questionsReponses );

            uasort($chart->datas->totauxChapitres, array($this, 'triParOrderGraphTable'));

            $results['table'] = $chart;
        }
        
        return $results;
    }

    /**
     * Construit l'entitée Synthèse
     *
     * @param  User      $user   L'utilisateur connecté
     * @param  Outil     $outil  L'outil/l'autodiag concerné
     * @param  Reference $statut Le statut 'validé' de la synthèse
     * @param  string    $nom    Nom de la synthèse
     *
     * @return Resultat
     */
    public function buildSynthese( User $user, Outil $outil, $statut, $nom )
    {
        //create Synthese Object
        $today    = new \DateTime();
        $synthese = $this->createEmpty();

        $synthese->setOutil( $outil );
        $synthese->setName( $nom );
        $synthese->setDateLastSave( $today );
        $synthese->setDateValidation( $today );
        $synthese->setUser( $user );
        $synthese->setSynthese( true );
        $synthese->setStatut( $statut );

        $this->save( $synthese );

        return $synthese;
    }









    /**
     * Trie par note une stdClass
     *
     * @param [type] $a [description]
     * @param [type] $b [description]
     *
     * @return [type]
     */
    public function triParNote($a, $b)
    {
        if($a->noteChapitre < $b->noteChapitre)
            return -1;
        if($a->noteChapitre > $b->noteChapitre)
            return 1;
        if($a->order > $b->order)
            return 1;
        else
            return -1;
    }
    /**
     * Trie par note une stdClass
     *
     * @param [type] $a [description]
     * @param [type] $b [description]
     *
     * @return [type]
     */
    public function triParNoteQuestion($a, $b)
    {
        if($a->value < $b->value)
            return -1;
        if($a->value > $b->value)
            return 1;
        if($a->order > $b->order)
            return 1;
        else
            return -1;
    }
    /**
     * Trie pour le graph tableau
     *
     * @param [type] $a [description]
     * @param [type] $b [description]
     *
     * @return [type]
     */
    public function triParOrderGraphTable($a, $b)
    {
        if (!array_key_exists('order', $a) || !array_key_exists('order', $b))
            return 0;
        if ($a['order'] < $b['order'])
            return -1;
        if ($a['order'] > $b['order'])
            return 1;
    }

    /**
     * Ordonne les chapitres parents (by order) puis ses enfants (by order)
     *
     * @param array $chapitres Les chapitres
     *
     * @return array
     */
    private function makeChaptersOrdered( $chapitres )
    {
        $parentsOrdered = array();
        $enfantsOrdered = array();
        foreach($chapitres as $one){
            if( is_null($one->getParent()) )
                $parentsOrdered[ $one->getOrder() ] = $one;
            else{
                if( !isset($enfantsOrdered[ $one->getParent()->getId() ]) )
                    $enfantsOrdered[ $one->getParent()->getId() ] = array();

                $enfantsOrdered[ $one->getParent()->getId() ][ $one->getOrder() ] = $one;
            }
        }
        ksort($parentsOrdered);
        $chapitresOrdered = array();
        foreach($parentsOrdered as $one){
            $chapitresOrdered[] = $one;

            if( isset($enfantsOrdered[ $one->getId() ]) ){
                ksort($enfantsOrdered[ $one->getId() ]);
                foreach($enfantsOrdered[ $one->getId() ] as $child)
                    $chapitresOrdered[] = $child;
            }
        }

        return $chapitresOrdered;
    }

    /**
     * Construit le tableau de données pour le rendu graphique Table
     *
     * @param array $categories        Liste des entités catégorie
     * @param array $chapitres         Liste des chapitres parents
     * @param array $questionsReponses Tableau de questions/réponses
     *
     * @return StdClass
     */
    private function buildDatasTable( $categories, $chapitres, $questionsReponses )
    {
        $results = new \StdClass;
        $totalChapitres      = array();

        //reorder chapitres
        $chapitresOrdered = array();
        foreach($chapitres as $one)
        {
            $chapitresOrdered[ $one->order ] = $one;
            ksort($chapitresOrdered[ $one->order ]->childs);
        }
        ksort($chapitresOrdered);

        //build chapitres
        $results->chapitres = array();
        foreach($chapitresOrdered as $chapitre)
        {
            $results->chapitres[$chapitre->id] = $chapitre->title;
            
            $enfants = array();
            foreach ($chapitre->childs as $chapitreEnfant)
            {
                $enfants[$chapitreEnfant->order] = $chapitreEnfant;
            }
            ksort($enfants);
            
            foreach ($enfants as $chapitreEnfant)
            {
                $results->chapitres[$chapitreEnfant->id] = $chapitreEnfant->title;
            }
        }
        
        //build catégories
        $results->categories = array();

        foreach($categories as $categorie)
        {
            $categorieId = $categorie->getId();

            $results->categories[ $categorieId ]['title']     = $categorie->getTitle();
            $results->categories[ $categorieId ]['chapitres'] = array();

            foreach ($chapitresOrdered as $chapitre)
            {
                $results->categories[ $categorieId ]['chapitres'][$chapitre->id] = array( 'nbRep' => 0, 'nbQue' => 0, 'nbPoints' => 0, 'max' => 0, 'pond' => 0, 'nbPointsPourc' => 0, 'maxPourc' => 0, 'nc' => true, 'affichageRestitutionBarre' => false, 'affichageRestitutionRadar' => false, 'affichageRestitutionTableau' => false );
                if (!isset($totalChapitres[intval($chapitre->id)]) )
                {
                    $totalChapitres[intval($chapitre->id)] = array
                    (
                        'nbRep' => 0,
                        'nbQue' => 0,
                        'nbPoints' => 0,
                        'max' => 0,
                        'pond' => 0,
                        'nbPointsPourc' => 0,
                        'maxPourc' => 0,
                        'nc' => true,
                        'order' => $chapitre->order,
                        'affichageRestitutionTableau' => $chapitre->affichageRestitutionTableau,
                        'isParent' => true
                    );
                }
                    
                foreach ($chapitre->childs as $chapitreEnfant)
                {
                    $results->categories[ $categorieId ]['chapitres'][$chapitreEnfant->id] = array( 'nbRep' => 0, 'nbQue' => 0, 'nbPoints' => 0, 'max' => 0, 'pond' => 0, 'nbPointsPourc' => 0, 'maxPourc' => 0, 'nc' => true, 'affichageRestitutionBarre' => false, 'affichageRestitutionRadar' => false, 'affichageRestitutionTableau' => false );
                    if (!isset($totalChapitres[intval($chapitreEnfant->id)]))
                    {
                        $totalChapitres[intval($chapitreEnfant->id)] = array
                        (
                            'nbRep' => 0,
                            'nbQue' => 0,
                            'nbPoints' => 0,
                            'max' => 0,
                            'pond' => 0,
                            'nbPointsPourc' => 0,
                            'maxPourc' => 0,
                            'nc' => true,
                            'order' => $chapitreEnfant->order,
                            'affichageRestitutionTableau' => $chapitreEnfant->affichageRestitutionTableau,
                            'isParent' => false
                        );
                    }
                }
            }

            //get questions by catégorie
            $questions = $categorie->getQuestions();
            foreach($questions as $question)
            {
                //check If Question != texte
                if( $question->getType()->getId() != 417 )
                {
                    //get parent chapitre ID
                    $chapitreParentId = null;
                    $chapitreId = $question->getChapitre()->getId();
                    if (null !== $question->getChapitre()->getParent())
                    {
                        $chapitreParentId = $question->getChapitre()->getParent()->getId();
                    }

                    //Add Chapitre if not exist
                    if ( !isset( $results->categories[ $categorieId ]['chapitres'][$chapitreId] ) )
                    {
                        $results->categories[ $categorieId ]['chapitres'][$chapitreId] = array( 'nbRep' => 0, 'nbQue' => 0, 'nbPoints' => 0, 'max' => 0, 'pond' => 0, 'nbPointsPourc' => 0, 'maxPourc' => 0, 'nc' => true, 'affichageRestitutionTableau' => false );
                    }
                    
                    //check If Question is concernée
                    if( isset($questionsReponses[ $question->getId() ]) )
                    {
                        $one   = $questionsReponses[ $question->getId() ];

                        //update Chapitre
                        //Détails utilisé individuellement dans les colonnes
                        if( $one->tableValue != '' )
                        {
                            $results->categories[ $categorieId ]['chapitres'][$chapitreId]['nbRep']++;
                            if (null !== $chapitreParentId)
                            {
                                $results->categories[ $categorieId ]['chapitres'][$chapitreParentId]['nbRep']++;
                            }
                        }

	                    if($one->max == 0) {
		                    $one->max = 1;
	                    }

	                    if($one->ponderation == 0) {
		                    $one->ponderation = 1;
	                    }

                        $results->categories[ $categorieId ]['chapitres'][$chapitreId]['nbQue']++;
                        $results->categories[ $categorieId ]['chapitres'][$chapitreId]['nbPoints']                    += ($one->tableValue * $one->ponderation);
                        $results->categories[ $categorieId ]['chapitres'][$chapitreId]['max']                         += ($one->max * $one->ponderation);
                        $results->categories[ $categorieId ]['chapitres'][$chapitreId]['pond']                        += $one->ponderation;
                        $results->categories[ $categorieId ]['chapitres'][$chapitreId]['nbPointsPourc']               += (($one->tableValue * $one->ponderation) / ($one->max * $one->ponderation)) * 100 * $one->ponderation;
                        $results->categories[ $categorieId ]['chapitres'][$chapitreId]['maxPourc']                    += $one->ponderation * 100;
                        $results->categories[ $categorieId ]['chapitres'][$chapitreId]['nc']                          = false;
                        $results->categories[ $categorieId ]['chapitres'][$chapitreId]['affichageRestitutionTableau'] = is_null($question->getChapitre()->getParent()) ? $question->getChapitre()->getAffichageRestitutionTableau() : $question->getChapitre()->getParent()->getAffichageRestitutionTableau();
                        
                        if (null !== $chapitreParentId)
                        {
                            $results->categories[ $categorieId ]['chapitres'][$chapitreParentId]['nbQue']++;
                            $results->categories[ $categorieId ]['chapitres'][$chapitreParentId]['nbPoints']                    += ($one->tableValue * $one->ponderation);
                            $results->categories[ $categorieId ]['chapitres'][$chapitreParentId]['max']                         += ($one->max * $one->ponderation);
                            $results->categories[ $categorieId ]['chapitres'][$chapitreParentId]['pond']                        += $one->ponderation;
                            $results->categories[ $categorieId ]['chapitres'][$chapitreParentId]['nbPointsPourc']               += (($one->tableValue * $one->ponderation) / ($one->max * $one->ponderation)) * 100 * $one->ponderation;
                            $results->categories[ $categorieId ]['chapitres'][$chapitreParentId]['maxPourc']                    += $one->ponderation * 100;
                            $results->categories[ $categorieId ]['chapitres'][$chapitreParentId]['nc']                          = false;
                            $results->categories[ $categorieId ]['chapitres'][$chapitreParentId]['affichageRestitutionTableau'] = is_null($question->getChapitre()->getParent()) ? $question->getChapitre()->getAffichageRestitutionTableau() : $question->getChapitre()->getParent()->getAffichageRestitutionTableau();
                        }

                        //update Total
                        //Total utilisé dans la colonne total (il n'est pas recalculé par rapport au détail mais via ce tableau)
                        if( $one->tableValue != '' )
                        {
                            $totalChapitres[ $chapitreId ]['nbRep']++;
                            if (null !== $chapitreParentId)
                            {
                                $totalChapitres[$chapitreParentId]['nbRep']++;
                            }
                        }
                        
                        $totalChapitres[ $chapitreId ]['nbQue']++;
                        $totalChapitres[ $chapitreId ]['nbPoints']                    += ($one->tableValue * $one->ponderation);
                        $totalChapitres[ $chapitreId ]['max']                         += ($one->max * $one->ponderation);
                        $totalChapitres[ $chapitreId ]['pond']                        += $one->ponderation;
                        //Ajout du nombre de point en pourcentage pour éviter les problèmes d'arrondies
                        $totalChapitres[ $chapitreId ]['nbPointsPourc']               += (($one->tableValue * $one->ponderation) / ($one->max * $one->ponderation)) * 100 * $one->ponderation;
                        $totalChapitres[ $chapitreId ]['maxPourc']                    += $one->ponderation * 100;
                        $totalChapitres[ $chapitreId ]['nc']                          = false;
                        $totalChapitres[ $chapitreId ]['order']                       = $question->getChapitre()->getOrder();
                        $totalChapitres[ $chapitreId ]['affichageRestitutionTableau'] = is_null($question->getChapitre()->getParent()) ? $question->getChapitre()->getAffichageRestitutionTableau() : $question->getChapitre()->getParent()->getAffichageRestitutionTableau();

                        if (null !== $chapitreParentId)
                        {
                            $totalChapitres[ $chapitreParentId ]['nbQue']++;
                            $totalChapitres[ $chapitreParentId ]['nbPoints']                    += ($one->tableValue * $one->ponderation);
                            $totalChapitres[ $chapitreParentId ]['max']                         += ($one->max * $one->ponderation);
                            $totalChapitres[ $chapitreParentId ]['pond']                        += $one->ponderation;
                            $totalChapitres[ $chapitreParentId ]['nbPointsPourc']               += (($one->tableValue * $one->ponderation) / ($one->max * $one->ponderation)) * 100 * $one->ponderation;
                            $totalChapitres[ $chapitreParentId ]['maxPourc']                    += $one->ponderation * 100;
                            $totalChapitres[ $chapitreParentId ]['nc']                          = false;
                            $totalChapitres[ $chapitreParentId ]['order']                       = is_null($question->getChapitre()->getParent()) ? $question->getChapitre()->getOrder() : $question->getChapitre()->getParent()->getOrder();
                            $totalChapitres[ $chapitreParentId ]['affichageRestitutionTableau'] = is_null($question->getChapitre()->getParent()) ? $question->getChapitre()->getAffichageRestitutionTableau() : $question->getChapitre()->getParent()->getAffichageRestitutionTableau();
                        }
                    }
                }
            }
        }

        //build Total chapitre
        $results->totauxChapitres = $totalChapitres;
        
        return $results;
    }

    /**
     * Retourne le tableau de données lors de l'axe Categorie
     *
     * @param array $categories        Liste des catégories
     * @param array $questionsReponses Le tableau de questions/réponses
     *
     * @return array
     */
    private function buildDatasAxeCategories( $categories, $questionsReponses )
    {
        $datas = array();
        foreach( $categories as $categorie )
        {
            $data        = new \StdClass;
            $data->id = $categorie->getId();
            $data->title = $categorie->getTitle();
            $data->value = $this->calculMoyenneCategorie( $categorie, $questionsReponses );
            $data->taux  = $this->calculTauxCategorie( $categorie, $questionsReponses );
            $data->opti  = $categorie->getNote();
            $data->min   = null;
            $data->max   = null;
            $data->centPourcentReponseObligatoire = $categorie->getOutil()->isCentPourcentReponseObligatoire();

            $datas[] = $data;
        }

        return $datas;
    }

    /**
     * Retourne le tableau de données lors de l'axe Chapitre
     *
     * @param array $chapitres Les chapitres
     *
     * @return array
     */
    public function buildDatasAxeChapitre( $chapitres )
    {
        $datas = array();

        //reorder chapitres
        $chapitresOrdered = array();
        foreach($chapitres as $one){
            $chapitresOrdered[ $one->order ] = $one;
        }
        ksort($chapitresOrdered);

        foreach($chapitresOrdered as $chapitre)
        {
            $data        = new \StdClass;
            $data->id = $chapitre->id;
            $data->title = $chapitre->title;
            $data->value = $this->calculMoyenneChapitre( $chapitre );
            $data->taux  = $this->calculTauxChapitre( $chapitre );
            $data->opti  = $chapitre->noteOpt;
            $data->min   = null;
            $data->max   = null;
            $data->centPourcentReponseObligatoire = (isset($chapitre->centPourcentReponseObligatoire) ? $chapitre->centPourcentReponseObligatoire : false);

            $datas[] = $data;
        }

        return $datas;
    }

    /**
     * Calcul de taux de remplissage de la catégorie
     *
     * @param Categorie $categorie         L'entitée Catégorie
     * @param array     $questionsReponses Tableaux de questions/réponses
     *
     * @return double
     */
    private function calculTauxCategorie( $categorie, $questionsReponses )
    {
        $questions           = $categorie->getQuestions();
        $nbQuestions         = 0;
        $nbQuestionsRemplies = 0;

        foreach($questions as $question)
        {
            //get reponse
            if( isset($questionsReponses[$question->getId()]) ){
                $reponse = $questionsReponses[$question->getId()];
                
                if( $reponse->initialValue !== '' )
                    $nbQuestionsRemplies++;

                $nbQuestions++;
            }
        }

        return $nbQuestions != 0 ? number_format(( ($nbQuestionsRemplies * 100) / $nbQuestions), 0) : 0;
    }

    /**
     * Calcul le Taux de remplissage du chapitre
     *
     * @param StdClass $chapitre Le chapitre
     *
     * @return double
     */
    private function calculTauxChapitre( $chapitre )
    {
        $nbQuestions         = $chapitre->nbQuestions;
        $nbQuestionsRemplies = $chapitre->nbQuestionsRemplies;

        $childs = $chapitre->childs;
        foreach( $childs as $child ){
            $nbQuestions         += $child->nbQuestions;
            //Cette ligne active fait bugger le taux de remplissage...
            //$nbQuestionsRemplies += $child->nbQuestionsRemplies;
        }

        return $nbQuestions != 0 ? number_format(( ($nbQuestionsRemplies * 100) / $nbQuestions), 0) : 0;
    }

    /**
     * Calcul la moyenne pondérée
     *
     * @param Categorie $categorie         L'entitée categorie
     * @param array     $questionsReponses Tableau de questions/reponses
     *
     * @return double
     */
    private function calculMoyenneCategorie( $categorie, $questionsReponses )
    {
        $questions = $categorie->getQuestions();

        $sommeValues       = 0;
        $sommePonderations = 0;

        foreach($questions as $question)
        {
            //get reponse
            if( isset($questionsReponses[$question->getId()]) ){
                $reponse = $questionsReponses[$question->getId()];
                $sommeValues       += ($reponse->value * $reponse->ponderation);
                $sommePonderations += $reponse->ponderation;
            }
        }

        return $sommePonderations != 0 ? ($sommeValues / $sommePonderations) : 0;
    }

    /**
     * Calcul la moyenne pondérée
     *
     * @param StdClass $chapitre Le chapitre
     *
     * @return double
     */
    public function calculMoyenneChapitre( $chapitre )
    {
        //<-- Cas d'une synthèse
        /*if (isset($chapitre->syntheseChapitres) && count($chapitre->syntheseChapitres) > 0)
        {
            $chapitreMoyennes = array();
            foreach ($chapitre->syntheseChapitres as $syntheseChapitre)
            {
                $chapitreMoyenne = $this->getSommesCalculMoyenneChapitre($syntheseChapitre);
                if (null !== $chapitreMoyenne && $chapitreMoyenne['sommeMaxPourc'] != 0)

                    $chapitreMoyennes[] = $chapitreMoyenne;
            }

            $sommeValues = 0;
            $sommeMaxPourc = 0;
            foreach ($chapitreMoyennes as $chapitreMoyenne)
            {
                $sommeValues += $chapitreMoyenne['sommeValues'];
                $sommeMaxPourc += $chapitreMoyenne['sommeMaxPourc'];
            }

            if ($sommeMaxPourc == 0)
                return 0;
            return ($sommeValues / $sommeMaxPourc);
        }*/
        //-->
        
        /*
        La requête qu'il fallait faire :
        SELECT question.que_id, SUM(reponse.rep_value), SUM(question.que_ponderation)
        FROM `hn_outil_reponse` AS reponse
        INNER JOIN hn_outil_question AS question ON reponse.que_id = question.que_id
        INNER JOIN hn_outil_chapitre AS chapitre ON question.cha_id = chapitre.cha_id
        	AND chapitre.cha_id IN (1255, 1270, 1271, 1272)
            --AND chapitre.out_id = 4
        WHERE reponse.res_id IN (426, 427, 429, 430, 431, 432,433, 436, 441, 442)
        	AND reponse.rep_value != '' AND rep_value != '-1'
        --GROUP BY question.que_id
        */

        $sommesCalculMoyenneChapitre = $this->getSommesCalculMoyenneChapitre($chapitre);
        
        if (null === $sommesCalculMoyenneChapitre)
            return 'NC';
        
        return $sommesCalculMoyenneChapitre['sommeMaxPourc'] != 0 ? ($sommesCalculMoyenneChapitre['sommeValues'] / $sommesCalculMoyenneChapitre['sommeMaxPourc']) : 0;
    }
    /**
     * 
     * @param \stdClass $chapitre
     * @return array|NULL
     */
    private function getSommesCalculMoyenneChapitre($chapitre)
    {
        $sommeValues       = 0;
        $sommeMaxPourc     = 0;
        $chapitreConcerne  = false;
        
        $questions = $chapitre->questionsForCharts;
        foreach($questions as $question)
        {
            $sommeValues       += ($question->value * $question->ponderation);
            $sommeMaxPourc     += $question->ponderation;

            $chapitreConcerne = true;
        }

        $childs = $chapitre->childs;
        foreach( $childs as $child )
        {
            $questions = $child->questionsForCharts;
            foreach($questions as $question)
            {
                $sommeValues       += ($question->value * $question->ponderation);

                $sommeMaxPourc     += $question->ponderation;
        
                $chapitreConcerne = true;
            }

        }

        if( $chapitreConcerne === false )
            return null;
        
        return array('sommeValues' => $sommeValues, 'sommeMaxPourc' => $sommeMaxPourc);
    }

    /**
     * Construit la liste des questions pour le frontoffice
     *
     * @param array    $questions         Liste des questions
     * @param StdClass $chapitre          L'objet chapitre
     * @param array    $questionsReponses Liste des questionsréponses
     *
     * @return array
     */
    public function buildQuestions( $questions, $chapitre, $questionsReponses, $questionsReponsesBack )
    {
        $results             = array();
        $forBack             = array();
        $forCharts           = array();
        $noteChapitre        = 0;
        $nbQuestions         = 0;
        $nbQuestionsRemplies = 0;
        foreach ($questions as $question)
        {
            if( isset($questionsReponses[ $question->getId() ]) )
            {
                //on ajoute seulement les questions valides pour les résultats
                $one = $questionsReponses[ $question->getId() ];

                $one->question = $one->code != '' ? $one->code . '. ' . $one->question : $one->question;

                if( $one->initialValue !== '' )
                {
                    if( $one->noteMinimale !== '' && ( ( $one->colored != -1 && $one->initialValue <= $one->noteMinimale )
                        || ( $one->colored == -1 && $one->initialValue >= $one->noteMinimale ) )
                    ) {
                        $results[]     = $one;
                        $noteChapitre += $one->value;
                    }
                    
                    $nbQuestionsRemplies++;
                }

                //on ajoute TOUTES les questions aux chapitre pour les calculs liés aux graphiques (pondération)
                $forCharts[] = $one;

                $nbQuestions++;
            }

            if( isset($questionsReponsesBack[ $question->getId() ]) )
            {
                $forBack[] = $questionsReponsesBack[ $question->getId() ];
            }
        }

        $chapitre->questions           = $results;
        $chapitre->questionsForCharts  = $forCharts;
        $chapitre->questionsBack       = $forBack;
        $chapitre->noteChapitre        = $noteChapitre;
        $chapitre->nbQuestions         = $nbQuestions;
        $chapitre->nbQuestionsRemplies = $nbQuestionsRemplies;

        return $chapitre;
    }

    /**
     * Construit un tableau PHP de questions / réponses à partir des réponses d'un utilisateur
     *
     * @param array $reponses Les entitées réponses
     *
     * @return array
     */
    public function buildQuestionsReponses( $reponses )
    {
        $results        = array();
        $resultsForBack = array();

        foreach($reponses as $reponse)
        {
            
            $rep = new \StdClass;

            //reponses values
            $question           = $reponse->getQuestion();
            $rep->tableValue    = $reponse->getValue();
            $rep->remarque      = $reponse->getRemarque();

            //questions values
            $rep->id              = $question->getId();
            $rep->question        = $question->getTexte();
            $rep->code            = $question->getCode();
            $rep->intro           = $question->getIntro();
            $rep->ordreResultat   = $question->getOrdreResultat();
            $rep->noteMinimale    = $question->getNoteMinimale();
            $rep->synthese        = $question->getSynthese();
            $rep->ponderation     = $question->getPonderation();
            $rep->order           = $question->getOrder();
            $rep->lien            = $question->getLien();
            $rep->descriptionLien = $question->getDescriptionLien();
            $rep->type            = $question->getType()->getId();
            $rep->colored         = $question->getColored();
            $rep->options         = explode( '<br />', nl2br( $question->getOptions() ) );

            //Si != Texte, on calcul la réponse Max
            if ( $rep->type != 417 )
            {
                $tab = $this->calculMinAndMaxOption( $question );
                $rep->max = $tab['max'];
                $rep->min = $tab['min'];

                //on rapporte la valeur de note question sur 100
                $rep->value = $rep->max != 0 ? ($reponse->getValue() * 100) / $rep->max : 0;
            }
            else
            {
                $rep->value = $reponse->getValue();
                $rep->max   = 0;
                $rep->min   = 0;
            }

            $rep->initialValue = $reponse->getValue();

            //pour le front, on ajoute QUE les réponses valides (! non concernés)
            if( $reponse->getValue() != -1 )
            {
                $results[ $reponse->getQuestion()->getId() ] = $rep;
            }

            //On ajoute TOUTE les questions pour le back (même les non concernés)
            $resultsForBack[ $reponse->getQuestion()->getId() ] = $rep;
        }

        return array( 'front' => $results, 'back' => $resultsForBack );
    }

    public function exportCsvCustom( $resultat, $user, $colonnes, $datas, $filename, $kernelCharset )
    {
        // Array to csv (copy from APY\DataGridBundle\Grid\Export\DSVExport.php)
        $outstream = fopen("php://temp", 'r+');

        fputcsv($outstream, array('Autodiagnostic "' . $resultat->getOutil()->getTitle() . '" - "' . $resultat->getName() . '"'));
        $userName = ($user == 'anon.') ? '' :  (" par " . $user->getAppellation());
        fputcsv($outstream, array("Plan d'actions exporté le " . date('d/m/Y') . " à " . date('H:i') . $userName));

        //Ajout de la colonne d'en-têtes
        $colonnesLines = array_values($colonnes);
        fputcsv($outstream, $colonnesLines, ';', '"');

        //creation du FlatArray pour la conversion en CSV
        $keys      = array_keys($colonnes);
        $flatArray = array();
        foreach($datas as $data) {
            $ligne = array();
            foreach($keys as $key) {
                //cas Tableau
                if( is_array($data) ){
                    $val     = $data[$key];
                    $ligne[] = is_null($val) ? '' : $val;
                //Cas Objet
                }else{
                    //colonne External 2 test
                    if( strpos($key, '.') !== false) {
                        //cas des foreign colonnes : on explode sur le ':' et on vérifie la présence d'une valeur
                        $fcts = explode('.', $key);
                        $fct1 = 'get'. ucfirst($fcts[0]);
                        $tmp  = call_user_func(array($data, $fct1 ));
                        //si il existe une valeur pour le 1er get, on tente de récupérer le second
                        if( $tmp ) {
                            $fct2    = 'get'. ucfirst($fcts[1]);
                            $val     =  call_user_func(array($tmp, $fct2 ));
                            $ligne[] = is_null($val) ? '' : $val;
                        }else
                            $ligne[] = '';
                    //simple colonne
                    }else{
                        $fct     = 'get'.ucfirst($key);
                        $val     = call_user_func(array($data,$fct));
                        $ligne[] = is_null($val) ? '' : $val;
                    }
                }
            }

            $flatArray[] = $ligne;
        }

        //génération du CSV
        foreach ($flatArray as $line)
            fputcsv($outstream, $line, ';', '"');

        //on replace le buffer au début pour refaire la lecture
        rewind($outstream);

        //génération du contenu
        $content = '';
        while (($buffer = fgets($outstream)) !== false)
            $content .= $buffer;

        fclose($outstream);

        // Charset and Length
        $charset = 'ISO-8859-1';
        if ($charset != $kernelCharset && function_exists('mb_strlen')) {
            $content  = mb_convert_encoding($content, $charset, $kernelCharset);
            $filesize = mb_strlen($content, '8bit');
        } else {
            $filesize = strlen($content);
            $charset  = $kernelCharset;
        }

        //build header
        $headers = array(
            'Content-Description'       => 'File Transfer',
            'Content-Type'              => 'text/comma-separated-values',
            'Content-Disposition'       => sprintf('attachment; filename="%s"', $filename),
            'Content-Transfer-Encoding' => 'binary',
            'Cache-Control'             => 'must-revalidate',
            'Pragma'                    => 'public',
            'Content-Length'            => $filesize
        );

        //return a Symfony Response
        $response = new Response($content, 200, $headers);
        $response->setCharset( $charset );
        $response->expire();

        return $response;
    }





    /**
     * Calcul la valeur maximum de la question
     *
     * @param Question $question L'entité Question
     *
     * @return integer
     */
    private function calculMinAndMaxOption( $question )
    {
        $max     = 0;
        $min     = null;
        $options = nl2br($question->getOptions());
        $options = explode('<br />', $options);

        foreach($options as $option)
        {
            $tmp = explode(';', $option);
            if (-1 != $tmp[0])
            {
                $max = (isset($tmp[0]) && intval(trim($tmp[0])) > $max ) ? intval(trim($tmp[0])) : $max;
                $min = ( (isset($tmp[0]) && intval(trim($tmp[0])) < $min) || is_null($min) ) ? intval(trim($tmp[0])) : $min;
            }
        }

        return array('max' => $max, 'min' => $min );
    }
}
