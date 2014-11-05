<?php

namespace HopitalNumerique\AutodiagBundle\Manager;

use HopitalNumerique\AutodiagBundle\Entity\Outil;
use HopitalNumerique\AutodiagBundle\Entity\Resultat;
use HopitalNumerique\UserBundle\Entity\User;
use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entitÃ© Resultat.
 */
class ResultatManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\AutodiagBundle\Entity\Resultat';

    /**
     * Override : RÃ©cupÃ¨re les donnÃ©es pour le grid sous forme de tableau
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
            }else{
                $datas['user']          = '';
                $datas['etablissement'] = '';
            }

            $results[] = $datas;
        }

        return $results;
    }

    /**
     * Formatte les rÃ©sultats
     *
     * @param Resultat $resultat L'objet RÃ©sultat
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
            $chapitre->parent                      = !is_null($one->getParent()) ? $one->getParent()->getId() : null;

            //handle questions/reponses
            $chapitre = $this->buildQuestions( $one->getQuestions(), $chapitre, $questionsReponses, $questionsReponsesBack );

            //handle parents / enfants
            if( is_null($one->getParent()) )
                $parents[ $one->getId() ] = $chapitre;
            else
                $enfants[] = $chapitre;
        }

        //reformate les chapitres FRONT
        foreach($enfants as $enfant)
        {
            $parent = $parents[ $enfant->parent ];
            $parent->childs[] = $enfant;
            // RLE : On compte les rÃ©ponses des enfants Ã©galement
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
                        continue;
                $scoreTemp += ($question->max != 0 ) ? $question->tableValue * $question->ponderation * 100 / $question->max : 0;
                $compteur++;
            }
            //Parcourt les sous chapitres
            foreach ($parent->childs as $chapChild) 
            {
                foreach ($chapChild->questionsForCharts as $questionChild) 
                {
                    if($questionChild->tableValue == -1)
                        continue;
                    $scoreTemp += ($questionChild->max != 0 ) ? $questionChild->tableValue * $questionChild->ponderation * 100 / $questionChild->max : 0;
                    $compteur++;
                }
            }
            $parent->noteChapitre = ($compteur != 0 ) ? round($scoreTemp / $compteur, 0) : 0;
        }

        return $parents;
    }

    /**
     * RÃ©cupÃ¨re le dernier rÃ©sultat validÃ©
     *
     * @param Outil  $outil L'outil
     * @param User   $user  L'utilisateur connectÃ©
     *
     * @return Resultat
     */
    public function getLastResultatValided( Outil $outil, User $user )
    {
        return $this->getRepository()->getLastResultatValided($outil, $user)->getQuery()->getOneOrNullResult();
    }
    
    /**
     * Construit le tableau de graphiques pour la gÃ©nÃ©ration en FRONT
     * Axe : 1 = Chapitres // 2 = CatÃ©gories
     * 
     * @param Resultat $resultat  L'entitÃ©e rÃ©sultat
     * @param array    $chapitres Liste des chapitres
     *
     * @return array
     */
    public function buildCharts( Resultat $resultat, $chapitres )
    {
        $results = array();

        //rÃ©cupÃ¨re les donnÃ©es pour les graphiques
        $outil = $resultat->getOutil();

        //rÃ©cupÃ¨re le gros tableau de questions / rÃ©ponses
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

            //get Datas for Each axes : Chapitres / CatÃ©gories
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

            //get Datas for Each axes : Chapitres / CatÃ©gories
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
            $chart->title = 'Mes rÃ©sultats dÃ©taillÃ©s';
            $chart->datas = $this->buildDatasTable( $categories , $chapitresFormated, $questionsReponses );

            uasort($chart->datas->totauxChapitres, array($this,"triParOrderGraphTable"));

            $results['table'] = $chart;
        }

        return $results;
    }

    /**
     * Construit l'entitÃ©e SynthÃ¨se
     *
     * @param  User      $user   L'utilisateur connectÃ©
     * @param  Outil     $outil  L'outil/l'autodiag concernÃ©
     * @param  Reference $statut Le statut 'validÃ©' de la synthÃ¨se
     * @param  string    $nom    Nom de la synthÃ¨se
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
        if($a['order'] < $b['order'])
            return -1;
        if($a['order'] > $b['order'])
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
     * Construit le tableau de donnÃ©es pour le rendu graphique Table
     *
     * @param array $categories        Liste des entitÃ©s catÃ©gorie
     * @param array $chapitres         Liste des chapitres parents
     * @param array $questionsReponses Tableau de questions/rÃ©ponses
     *
     * @return StdClass
     */
    private function buildDatasTable( $categories, $chapitres, $questionsReponses )
    {
        $results = new \StdClass;

        //reorder chapitres
        $chapitresOrdered = array();
        foreach($chapitres as $one){
            $chapitresOrdered[ $one->order ] = $one;
        }
        ksort($chapitresOrdered);

        //build chapitres
        $results->chapitres = array();
        foreach($chapitresOrdered as $chapitre)
            $results->chapitres[$chapitre->id] = $chapitre->title;
        
        //build catÃ©gories
        $results->categories = array();
        $totalChapitres      = array();

        foreach($categories as $categorie){
            $categorieId = $categorie->getId();

            $results->categories[ $categorieId ]['title']     = $categorie->getTitle();
            $results->categories[ $categorieId ]['chapitres'] = array();

            foreach($chapitresOrdered as $chapitre)
                $results->categories[ $categorieId ]['chapitres'][$chapitre->id] = array( 'nbRep' => 0, 'nbQue' => 0, 'nbPoints' => 0, 'max' => 0, 'pond' => 0, 'nc' => true, 'affichageRestitutionBarre' => false, 'affichageRestitutionRadar' => false, 'affichageRestitutionTableau' => false );
            

            //get questions by catÃ©gorie
            $questions = $categorie->getQuestions();
            foreach($questions as $question)
            {
                //check If Question != texte
                if( $question->getType()->getId() != 417 ) {
                    //get parent chapitre ID
                    $chapitre = is_null($question->getChapitre()->getParent()) ? $question->getChapitre()->getId() : $question->getChapitre()->getParent()->getId();

                    //Add Chapitre if not exist
                    if ( !isset( $results->categories[ $categorieId ]['chapitres'][$chapitre] )  )
                        $results->categories[ $categorieId ]['chapitres'][$chapitre] = array( 'nbRep' => 0, 'nbQue' => 0, 'nbPoints' => 0, 'max' => 0, 'pond' => 0, 'nc' => true, 'affichageRestitutionTableau' => false  );
                    if ( !isset($totalChapitres[ $chapitre ]) )
                        $totalChapitres[ $chapitre ] = array( 'nbRep' => 0, 'nbQue' => 0, 'nbPoints' => 0, 'max' => 0, 'pond' => 0, 'nc' => true, 'affichageRestitutionTableau' => false );

                    //check If Question is concernÃ©e
                    if( isset($questionsReponses[ $question->getId() ]) ){
                        $one   = $questionsReponses[ $question->getId() ];

                        //update Chapitre
                        if( $one->tableValue != '' )
                            $results->categories[ $categorieId ]['chapitres'][$chapitre]['nbRep']++;

                        $results->categories[ $categorieId ]['chapitres'][$chapitre]['nbQue']++;
                        $results->categories[ $categorieId ]['chapitres'][$chapitre]['nbPoints']                    += ($one->tableValue * $one->ponderation);
                        $results->categories[ $categorieId ]['chapitres'][$chapitre]['max']                         += ($one->max * $one->ponderation);
                        $results->categories[ $categorieId ]['chapitres'][$chapitre]['pond']                        += $one->ponderation;
                        $results->categories[ $categorieId ]['chapitres'][$chapitre]['nc']                          = false;
                        $results->categories[ $categorieId ]['chapitres'][$chapitre]['affichageRestitutionTableau'] = is_null($question->getChapitre()->getParent()) ? $question->getChapitre()->getAffichageRestitutionTableau() : $question->getChapitre()->getParent()->getAffichageRestitutionTableau();

                        //update Total
                        if( $one->tableValue != '' )
                            $totalChapitres[ $chapitre ]['nbRep']++;
                        
                        $totalChapitres[ $chapitre ]['nbQue']++;
                        $totalChapitres[ $chapitre ]['nbPoints']                    += ($one->tableValue * $one->ponderation);
                        $totalChapitres[ $chapitre ]['max']                         += ($one->max * $one->ponderation);
                        $totalChapitres[ $chapitre ]['pond']                        += $one->ponderation;
                        $totalChapitres[ $chapitre ]['nc']                          = false;
                        $totalChapitres[ $chapitre ]['order']                       = is_null($question->getChapitre()->getParent()) ? $question->getChapitre()->getOrder() : $question->getChapitre()->getParent()->getOrder();
                        $totalChapitres[ $chapitre ]['affichageRestitutionTableau'] = is_null($question->getChapitre()->getParent()) ? $question->getChapitre()->getAffichageRestitutionTableau() : $question->getChapitre()->getParent()->getAffichageRestitutionTableau();
                    }
                }
            }
        }
        
        //build Total chapitre
        $results->totauxChapitres = $totalChapitres;

        return $results;
    }

    /**
     * Retourne le tableau de donnÃ©es lors de l'axe Categorie
     *
     * @param array $categories        Liste des catÃ©gories
     * @param array $questionsReponses Le tableau de questions/rÃ©ponses
     *
     * @return array
     */
    private function buildDatasAxeCategories( $categories, $questionsReponses )
    {
        $datas = array();
        foreach( $categories as $categorie )
        {
            $data        = new \StdClass;
            $data->title = $categorie->getTitle();
            $data->value = $this->calculMoyenneCategorie( $categorie, $questionsReponses );
            $data->taux  = $this->calculTauxCategorie( $categorie, $questionsReponses );
            $data->opti  = $categorie->getNote();
            $data->min   = null;
            $data->max   = null;

            $datas[] = $data;
        }

        return $datas;
    }

    /**
     * Retourne le tableau de donnÃ©es lors de l'axe Chapitre
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
            $data->title = $chapitre->title;
            $data->value = $this->calculMoyenneChapitre( $chapitre );
            $data->taux  = $this->calculTauxChapitre( $chapitre );
            $data->opti  = $chapitre->noteOpt;
            $data->min   = null;
            $data->max   = null;

            $datas[] = $data;
        }

        return $datas;
    }

    /**
     * Calcul de taux de remplissage de la catÃ©gorie
     *
     * @param Categorie $categorie         L'entitÃ©e CatÃ©gorie
     * @param array     $questionsReponses Tableaux de questions/rÃ©ponses
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
     * Calcul la moyenne pondÃ©rÃ©e
     *
     * @param Categorie $categorie         L'entitÃ©e categorie
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
     * Calcul la moyenne pondÃ©rÃ©e
     *
     * @param StdClass $chapitre Le chapitre
     *
     * @return double
     */
    public function calculMoyenneChapitre( $chapitre )
    {
        $sommeValues       = 0;
        $sommePonderations = 0;
        $chapitreConcerne  = false;

        $questions = $chapitre->questionsForCharts;
        foreach($questions as $question){
            $sommeValues       += ($question->value * $question->ponderation);
            $sommePonderations += $question->ponderation;

            $chapitreConcerne = true;
        }

        $childs = $chapitre->childs;
        foreach( $childs as $child ){
            $questions = $child->questionsForCharts;
            foreach($questions as $question){
                $sommeValues       += ($question->value * $question->ponderation);
                $sommePonderations += $question->ponderation;

                $chapitreConcerne = true;
            }   
        }        

        if( $chapitreConcerne === false )
            return 'NC';

        return $sommePonderations != 0 ? ($sommeValues / $sommePonderations) : 0;
    }

    /**
     * Construit la liste des questions pour le frontoffice
     *
     * @param array    $questions         Liste des questions
     * @param StdClass $chapitre          L'objet chapitre
     * @param array    $questionsReponses Liste des questionsrÃ©ponses
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
                //on ajoute seulement les questions valides pour les rÃ©sultats
                $one = $questionsReponses[ $question->getId() ];

                $one->question = $one->code != '' ? $one->code . '. ' . $one->question : $one->question;

                if( $one->initialValue !== '' ){
                    if( $one->noteMinimale !== '' && $one->initialValue <= $one->noteMinimale ){
                        $results[]     = $one;
                        $noteChapitre += $one->value;
                    }
                    
                    $nbQuestionsRemplies++;
                }

                //on ajoute TOUTES les questions aux chapitre pour les calculs liÃ©s aux graphiques (pondÃ©ration)
                $forCharts[] = $one;

                $nbQuestions++;
            }

            if( isset($questionsReponsesBack[ $question->getId() ]) )
                $forBack[] = $questionsReponsesBack[ $question->getId() ];
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
     * Construit un tableau PHP de questions / rÃ©ponses Ã  partir des rÃ©ponses d'un utilisateur
     *
     * @param array $reponses Les entitÃ©es rÃ©ponses
     *
     * @return array
     */
    public function buildQuestionsReponses( $reponses )
    {
        $results        = array();
        $resultsForBack = array();

        foreach($reponses as $reponse) {
            
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

            //Si != Texte, on calcul la rÃ©ponse Max
            if( $rep->type != 417 ){
                $tab = $this->calculMinAndMaxOption( $question );
                $rep->max = $tab['max'];
                $rep->min = $tab['min'];

                //on rapporte la valeur de note question sur 100
                $rep->value = $rep->max != 0 ? ($reponse->getValue() * 100) / $rep->max : 0;
            }else{
                $rep->value = $reponse->getValue();
                $rep->max   = 0;
                $rep->min   = 0;
            }

            $rep->initialValue = $reponse->getValue();

            //pour le front, on ajoute QUE les rÃ©ponses valides (! non concernÃ©s)
            if( $reponse->getValue() != -1 )
                $results[ $reponse->getQuestion()->getId() ] = $rep;

            //On ajoute TOUTE les questions pour le back (mÃªme les non concernÃ©s)
            $resultsForBack[ $reponse->getQuestion()->getId() ] = $rep;
        }

        return array( 'front' => $results, 'back' => $resultsForBack );
    }

    /**
     * Calcul la valeur maximum de la question
     *
     * @param Question $question L'entitÃ© Question
     *
     * @return integer
     */
    private function calculMinAndMaxOption( $question )
    {
        $max     = 0;
        $min     = null;
        $options = nl2br($question->getOptions());
        $options = explode('<br />', $options);

        foreach($options as $option){
            $tmp = explode(';', $option);
            $max = (isset($tmp[0]) && intval(trim($tmp[0])) > $max ) ? intval(trim($tmp[0])) : $max;
            $min = ( (isset($tmp[0]) && intval(trim($tmp[0])) < $min) || is_null($min) ) ? intval(trim($tmp[0])) : $min;
        }

        return array('max' => $max, 'min' => $min );
    }
}
