<?php

namespace HopitalNumerique\AutodiagBundle\Manager;

use HopitalNumerique\AutodiagBundle\Entity\Outil;
use HopitalNumerique\AutodiagBundle\Entity\Resultat;
use HopitalNumerique\UserBundle\Entity\User;
use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Resultat.
 */
class ResultatManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\AutodiagBundle\Entity\Resultat';

    /**
     * Override : Récupère les données pour le grid sous forme de tableau
     *
     * @return array
     */
    public function getDatasForGrid( $condition = null )
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
     * Formatte les résultats
     *
     * @param Resultat $resultat L'objet Résultat
     *
     * @return array
     */
    public function formateResultat( Resultat $resultat, $front = false )
    {
        //build reponses array
        $questionsReponses = $this->buildQuestionsReponses( $resultat->getReponses() );

        //build chapitres and add previous responses
        $parents     = array();
        $enfants     = array();

        //preorder chapters
        $chapitres = $this->makeChaptersOrdered( $resultat->getOutil()->getChapitres() );

        foreach($chapitres as $one) {
            $chapitre = new \StdClass;
            
            //build chapitre values
            $chapitre->id       = $one->getId();
            $chapitre->synthese = $one->getSynthese();
            $chapitre->title    = $one->getCode() != '' ? $one->getCode() . '. ' . $one->getTitle() : $one->getTitle();
            $chapitre->childs   = array();
            $chapitre->noteMin  = $one->getNoteMinimale();
            $chapitre->noteOpt  = $one->getNoteOptimale();
            $chapitre->order    = $one->getOrder();
            $chapitre->parent   = !is_null($one->getParent()) ? $one->getParent()->getId() : null;

            //handle questions/reponses
            $chapitre = $front ? $this->buildQuestionsForFront( $one->getQuestions(), $chapitre, $questionsReponses ) : $this->buildQuestionsForBack( $one->getQuestions(), $chapitre, $questionsReponses );

            //handle parents / enfants
            if( is_null($one->getParent()) )
                $parents[ $one->getId() ] = $chapitre;
            else
                $enfants[] = $chapitre;
        }

        //reformate les chapitres
        foreach($enfants as $enfant){
            $parent = $parents[ $enfant->parent ];
            $parent->childs[] = $enfant;
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

        //get Datas for Each axes : Chapitres / Catégories
        $datasAxeChapitre   = $this->buildDatasAxeChapitre( $chapitres );
        $datasAxeCategories = $this->buildDatasAxeCategories( $outil->getCategories() , $questionsReponses );

        //cas first chart
        if ( $outil->isColumnChart() )
        {
            $chart         = new \StdClass;
            $chart->title  = $outil->getColumnChartLabel();
            $chart->panels = ($outil->getColumnChartAxe() == 1) ? $datasAxeChapitre : $datasAxeCategories;

            $results['barre'] = $chart;
        }

        //cas Spider Web
        if ( $outil->isRadarChart() )
        {
            $chart        = new \StdClass;
            $chart->title = $outil->getRadarChartLabel();
            $chart->datas = ($outil->getRadarChartAxe() == 1) ? $datasAxeChapitre : $datasAxeCategories;

            $results['radar'] = $chart;
        }

        //cas Table
        if ( $outil->isTableChart() )
        {
            $chart        = new \StdClass;
            $chart->title = 'Mes résultats détaillés';
            $chart->datas = $this->buildDatasTable( $outil->getCategories(), $chapitres, $questionsReponses );

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
        
        //build catégories
        $results->categories = array();
        $totalChapitres      = array();

        foreach($categories as $categorie){
            $categorieId = $categorie->getId();

            $results->categories[ $categorieId ]['title']     = $categorie->getTitle();
            $results->categories[ $categorieId ]['chapitres'] = array();

            //get questions by catégorie
            $questions = $categorie->getQuestions();
            foreach($questions as $question)
            {
                //check If Question != texte
                if( $question->getType()->getId() != 417 ) {
                    //get parent chapitre ID
                    $chapitre = is_null($question->getChapitre()->getParent()) ? $question->getChapitre()->getId() : $question->getChapitre()->getParent()->getId();

                    //Add Chapitre if not exist
                    if ( !isset( $results->categories[ $categorieId ]['chapitres'][$chapitre] )  )
                        $results->categories[ $categorieId ]['chapitres'][$chapitre] = array( 'nbRep' => 0, 'nbQue' => 0, 'nbPoints' => 0, 'max' => 0, 'pond' => 0, 'nc' => true );
                    if ( !isset($totalChapitres[ $chapitre ]) )
                        $totalChapitres[ $chapitre ] = array( 'nbRep' => 0, 'nbQue' => 0, 'nbPoints' => 0, 'max' => 0, 'pond' => 0, 'nc' => true );

                    //check If Question is concernée
                    if( isset($questionsReponses[ $question->getId() ]) ){
                        $one   = $questionsReponses[ $question->getId() ];

                        //update Chapitre
                        if( $one->tableValue != '' )
                            $results->categories[ $categorieId ]['chapitres'][$chapitre]['nbRep']++;

                        $results->categories[ $categorieId ]['chapitres'][$chapitre]['nbQue']++;
                        $results->categories[ $categorieId ]['chapitres'][$chapitre]['nbPoints'] += ($one->tableValue * $one->ponderation);
                        $results->categories[ $categorieId ]['chapitres'][$chapitre]['max']      += ($one->max * $one->ponderation);
                        $results->categories[ $categorieId ]['chapitres'][$chapitre]['pond']     += $one->ponderation;
                        $results->categories[ $categorieId ]['chapitres'][$chapitre]['nc']        = false;

                        //update Total
                        if( $one->tableValue != '' )
                            $totalChapitres[ $chapitre ]['nbRep']++;
                        
                        $totalChapitres[ $chapitre ]['nbQue']++;
                        $totalChapitres[ $chapitre ]['nbPoints'] += ($one->tableValue * $one->ponderation);
                        $totalChapitres[ $chapitre ]['max']      += ($one->max * $one->ponderation);
                        $totalChapitres[ $chapitre ]['pond']     += $one->ponderation;
                        $totalChapitres[ $chapitre ]['nc']        = false;
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
            $data->title = $categorie->getTitle();
            $data->value = $this->calculMoyenneCategorie( $categorie, $questionsReponses );
            $data->taux  = $this->calculTauxCategorie( $categorie, $questionsReponses );
            $data->opti  = $categorie->getNote();

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
    private function buildDatasAxeChapitre( $chapitres )
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
            $nbQuestionsRemplies += $child->nbQuestionsRemplies;
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
    private function calculMoyenneChapitre( $chapitre )
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
     * Construit la liste des questions pour le backoffice
     *
     * @param array    $questions         Liste des questions
     * @param StdClass $chapitre          L'objet chapitre
     * @param array    $questionsReponses Liste des questionsréponses
     *
     * @return array
     */
    private function buildQuestionsForBack( $questions, $chapitre, $questionsReponses )
    {
        $results = array();
        foreach ($questions as $question) {
            if( isset($questionsReponses[ $question->getId() ]) )
                $results[] = $questionsReponses[ $question->getId() ];
        }

        $chapitre->questions = $results;

        return $chapitre;
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
    private function buildQuestionsForFront( $questions, $chapitre, $questionsReponses )
    {
        $results             = array();
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

                if( $one->initialValue !== '' ){
                    if( $one->noteMinimale !== '' and $one->initialValue <= $one->noteMinimale ){
                        $results[]     = $one;
                        $noteChapitre += $one->value;
                    }
                    
                    $nbQuestionsRemplies++;
                }

                //on ajoute TOUTES les questions aux chapitre pour les calculs liés aux graphiques (pondération)
                $forCharts[] = $one;

                $nbQuestions++;
            }
        }

        $chapitre->questions           = $results;
        $chapitre->questionsForCharts  = $forCharts;
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
    private function buildQuestionsReponses( $reponses )
    {
        $results = array();
        foreach($reponses as $reponse) {
            if( $reponse->getValue() != -1 ){
                $rep = new \StdClass;

                //reponses values
                $question           = $reponse->getQuestion();
                $rep->tableValue    = $reponse->getValue();
                $rep->remarque      = $reponse->getRemarque();

                //questions values
                $rep->id            = $question->getId();
                $rep->question      = $question->getTexte();
                $rep->code          = $question->getCode();
                $rep->ordreResultat = $question->getOrdreResultat();
                $rep->noteMinimale  = $question->getNoteMinimale();
                $rep->synthese      = $question->getSynthese();
                $rep->ponderation   = $question->getPonderation();
                $rep->order         = $question->getOrder();
                $rep->type          = $question->getType()->getId();

                //Si != Texte, on calcul la réponse Max
                if( $rep->type != 417 ){
                    $rep->max = $this->calculMaxOption( $question );

                    //on rapporte la valeur de note question sur 100
                    $rep->value = $rep->max != 0 ? ($reponse->getValue() * 100) / $rep->max : 0;
                }else{
                    $rep->value = $reponse->getValue();
                    $rep->max   = 0;
                }

                $rep->initialValue = $reponse->getValue();

                $results[ $reponse->getQuestion()->getId() ] = $rep;
            }
        }

        return $results;
    }

    /**
     * Calcul la valeur maximum de la question
     *
     * @param Question $question L'entité Question
     *
     * @return integer
     */
    private function calculMaxOption( $question )
    {
        $max = 0;
        $options  = nl2br($question->getOptions());
        $options  = explode('<br />', $options);

        foreach($options as $option){
            $tmp = explode(';', $option);
            $max = (isset($tmp[0]) && intval(trim($tmp[0])) > $max ) ? intval(trim($tmp[0])) : $max;
        }

        return $max;
    }
}