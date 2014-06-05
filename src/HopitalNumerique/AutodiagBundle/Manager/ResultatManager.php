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
     * Formatte les résultats pour la partie backoffice
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
        $chapitres      = $resultat->getOutil()->getChapitres();
        $parents        = array();
        $enfants        = array();

        foreach($chapitres as $one) {
            $chapitre = new \StdClass;
            
            //build chapitre values
            $chapitre->id       = $one->getId();
            $chapitre->synthese = $one->getSynthese();
            $chapitre->title    = $one->getTitle();
            $chapitre->childs   = array();
            $chapitre->noteMin  = $one->getNoteMinimale();
            $chapitre->parent   = !is_null($one->getParent()) ? $one->getParent()->getId() : null;

            //handle questions/reponses
            $chapitre = $front ? $this->buildQuestionsForFront( $one->getQuestions(), $chapitre, $questionsReponses ) : $this->buildQuestionsForBack( $one->getQuestions(), $chapitre, $questionsReponses );

            //handle parents / enfants
            if( is_null($one->getParent()) ){
                $parents[ $one->getId() ] = $chapitre;
            }else
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

        //cas first chart
        if ( $outil->isColumnChart() )
        {
            $chart         = new \StdClass;
            $chart->title  = $outil->getColumnChartLabel();
            $chart->panels = array();

            //get by Chapitre
            if( $outil->getColumnChartAxe() == 1 )
            {
                foreach($chapitres as $chapitre)
                {
                    $panel        = new \StdClass;
                    $panel->title = $chapitre->title;
                    $panel->value = $this->calculMoyenneChapitre( $chapitre );

                    $chart->panels[] = $panel;
                }
            //get By catégories
            }
            else
            {
                $questionsReponses = $this->buildQuestionsReponses( $resultat->getReponses() );
                $categories        = $outil->getCategories();

                foreach( $categories as $categorie )
                {
                    $panel        = new \StdClass;
                    $panel->title = $categorie->getTitle();
                    $panel->value = $this->calculMoyenneCategorie( $categorie, $questionsReponses );

                    $chart->panels[] = $panel;
                }
            }

            $results['barre'] = $chart;
        }

        //cas Spider Web
        if ( $outil->isRadarChart() )
        {
            $chart         = new \StdClass;
            $chart->title  = $outil->getRadarChartLabel();
            $chart->panels = array();


            //getRadarChartAxe

            $results['radar'] = $chart;
        }


        return $results;
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
                if( $reponse->value != '' ) {
                    $sommeValues       += ($reponse->value * $reponse->ponderation);
                    $sommePonderations += $reponse->ponderation;
                }
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

        $questions = $chapitre->questionsForCharts;
        foreach($questions as $question){
            $sommeValues       += ($question->value * $question->ponderation);
            $sommePonderations += $question->ponderation;
        }

        $childs = $chapitre->childs;
        foreach( $childs as $child ){
            $questions = $child->questionsForCharts;
            foreach($questions as $question){
                $sommeValues       += ($question->value * $question->ponderation);
                $sommePonderations += $question->ponderation;
            }   
        }

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
        $results      = array();
        $forCharts    = array();
        $noteChapitre = 0;

        foreach ($questions as $question)
        {
            if( isset($questionsReponses[ $question->getId() ]) )
            {
                //on ajoute seulement les questions valides pour les résultats
                $one = $questionsReponses[ $question->getId() ];
                if( ( ($one->type == 415 && $one->value != 0 ) || ($one->type == 416 && $one->value != 0) || ($one->type == 417 && $one->value != '') ) && $one->value < $one->noteMinimale){
                    $results[]     = $one;
                    $noteChapitre += $one->value;
                }

                //on ajoute TOUTES les questions aux chapitre pour les calculs liés aux graphiques (pondération)
                $forCharts[] = $one;
            }
        }

        $chapitre->questions          = $results;
        $chapitre->questionsForCharts = $forCharts;
        $chapitre->noteChapitre       = $noteChapitre;

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
            $rep = new \StdClass;

            //reponses values
            $question           = $reponse->getQuestion();
            $rep->value         = $reponse->getValue();
            $rep->remarque      = $reponse->getRemarque();

            //questions values
            $rep->id            = $question->getId();
            $rep->question      = $question->getTexte();
            $rep->ordreResultat = $question->getOrdreResultat();
            $rep->noteMinimale  = $question->getNoteMinimale();
            $rep->synthese      = $question->getSynthese();
            $rep->ponderation   = $question->getPonderation();
            $rep->order         = $question->getOrder();
            $rep->type          = $question->getType()->getId();

            $results[ $reponse->getQuestion()->getId() ] = $rep;
        }

        return $results;
    }
}