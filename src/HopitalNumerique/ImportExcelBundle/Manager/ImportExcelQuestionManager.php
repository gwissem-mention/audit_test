<?php

namespace HopitalNumerique\ImportExcelBundle\Manager;

use HopitalNumerique\AutodiagBundle\Manager\QuestionManager as QuestManagerAutodiag;
use HopitalNumerique\ImportExcelBundle\Manager\ImportExcelCategorieManager;
use HopitalNumerique\ImportExcelBundle\Manager\ImportExcelChapitreManager;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;
use HopitalNumerique\UserBundle\Manager\UserManager;

/**
 * Manager de l'entité Question.
 */
class ImportExcelQuestionManager extends QuestManagerAutodiag
{
    protected $_class = 'HopitalNumerique\AutodiagBundle\Entity\Question';
    protected $_importExcelCategorieManager;
    protected $_importExcelChapitreManager;
    protected $_referenceManager;

    public function __construct($entityManager, ImportExcelCategorieManager $importExcelCategorieManager, ImportExcelChapitreManager $importExcelChapitreManager, UserManager $userManager, ReferenceManager $referenceManager )
    {
        parent::__construct($entityManager, $userManager, $referenceManager);
        $this->_importExcelCategorieManager = $importExcelCategorieManager;
        $this->_importExcelChapitreManager  = $importExcelChapitreManager;
        $this->_referenceManager            = $referenceManager;
    }

    /**
     * Récupération des données des catégories du fichier excel d'import
     *
     * @param Array $arrayCategories Tableau des données de catégories à persist
     *
     * @return void
     */
    public function saveQuestionImported( $arrayQuestions, $outil, $arrayIdChapitres )
    {
        $arrayIdsQuestions         = array();
        $arrayIdsQuestionsImported = array();
        $arrayIdsQuestionsSaved    = array();

        //Récupération des id de questions présent dans le fichier d'import
        foreach ($arrayQuestions as $question) 
        {
            if(!is_null($question['id']))
            {
                $arrayIdsQuestionsImported[] = $question['id'];
            }
        }

        foreach ($arrayQuestions as $questionDonnees) 
        {
            $question = array_key_exists('id', $questionDonnees) && !is_null($questionDonnees['id']) ? $this->findOneById($questionDonnees['id']) : null;

            //Dans le cas où le findOneById n'a rien trouvé
            if(is_null($question))
            {
                //Création d'une nouvelle catégorie
                $question = $this->createEmpty();
                if(array_key_exists('id', $questionDonnees) && !is_null($questionDonnees['id']))
                {
                    $question->setId($questionDonnees['id']);
                }
            }
            elseif($question->getChapitre()->getOutil()->getId() !== $outil->getId())
            {
                die('Erreur dans les questions : Vous modifié une question d\'un autre autodiagnostic !');
            }

            //Récupération du chapitre
            if(trim($questionDonnees['numChapitre']) !== "")
            {
                $chapitre = $this->_importExcelChapitreManager->findOneBy(array('id' => $arrayIdChapitres[intval($questionDonnees['idChapitre'])], 'outil' => $outil));

                if(!is_null($chapitre))
                {
                    $question->setChapitre($chapitre);
                }
                else
                {
                    //Dans le cas où cette question ne correspond à aucun chapitre on stop les questions
                    die('La question ' . $questionDonnees['numQuestion'] . ' ne correspond à aucun chapitre, veuillez le corriger.');
                    break;
                }
            }
            $question->setCode($questionDonnees['numQuestion']);
            $question->setIntro($questionDonnees['intro']);
            $question->setTexte($questionDonnees['texte']);

            //Recupèration du type de la question
            if(trim($questionDonnees['type']) !== "")
            {
                $type = $this->_referenceManager->findOneBy(array('libelle' => $questionDonnees['type']));
                $question->setType($type);
            }

            //Parse les options de réponses : Force un point si jamais il y a une virgule dans la value
            $options = explode("\n", $questionDonnees['options']);
            foreach ($options as &$option) 
            {
                $values = explode(";", $option);
                $values[0] = str_replace(",", ".", $values[0]);
                $option = implode(";", $values);
            }
            $question->setOptions(implode("\n", $options));
            
            $question->setNoteMinimale($questionDonnees['noteMinimale']);
            $question->setSeuil($questionDonnees['noteMinimale']);
            $question->setSynthese($questionDonnees['synthese']);
            $colored = $questionDonnees['colored'] == '1' ? 1 : ( $questionDonnees['colored'] == '0' ? 0 : -1 );
            $question->setColored($colored);
            $question->setInfoBulle($questionDonnees['infobulle']);

            //Récupération de la catégorie
            if(trim($questionDonnees['categorie']) !== "")
            {
                $categorie = $this->_importExcelCategorieManager->findOneBy(array('title' => $questionDonnees['categorie'], 'outil' => $outil));
                $question->setCategorie($categorie);
            }

            $question->setPonderation($questionDonnees['ponderation']);
            $question->setOrder($questionDonnees['order']);
            $question->setLien($questionDonnees['lien']);
            $question->setDescriptionLien($questionDonnees['descriptionLien']);

            $this->saveForceId( $question );

            $arrayIdsQuestions[$questionDonnees['id']] = $question->getId();
            
            $arrayIdsQuestionsSaved[] = $question->getId();
        }


        //Récupération des questions associées au chapitre en base
        $questions = $this->findBy(array('chapitre' => $arrayIdChapitres));
        $questionsToDelete = array();
        foreach ($questions as $question) 
        {
            //Si la question n'est pas dans les lignes importées et ne vient pas d'etre importé on la delete
            if(!in_array($question->getId(), $arrayIdsQuestionsImported)
                && !in_array($question->getId(), $arrayIdsQuestionsSaved))
            {
                $questionsToDelete[] = $question;
            }
        }
        
        $this->delete($questionsToDelete);

        return $arrayIdsQuestions;
    }
}