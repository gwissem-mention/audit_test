<?php

namespace HopitalNumerique\ImportExcelBundle\Manager;

use HopitalNumerique\AutodiagBundle\Manager\QuestionManager as QuestManagerAutodiag;
use HopitalNumerique\ImportExcelBundle\Manager\ImportExcelCategorieManager;
use HopitalNumerique\ImportExcelBundle\Manager\ImportExcelChapitreManager;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;

/**
 * Manager de l'entité Question.
 */
class ImportExcelQuestionManager extends QuestManagerAutodiag
{
    protected $_class = 'HopitalNumerique\AutodiagBundle\Entity\Question';
    protected $_importExcelCategorieManager;
    protected $_importExcelChapitreManager;
    protected $_referenceManager;

    public function __construct($entityManager, ImportExcelCategorieManager $importExcelCategorieManager, ImportExcelChapitreManager $importExcelChapitreManager, ReferenceManager $referenceManager )
    {
        parent::__construct($entityManager);
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
    public function saveQuestionImported( $arrayQuestions, $outil )
    {
        foreach ($arrayQuestions as $questionDonnees) 
        {
            //Création d'une nouvelle catégorie
            $question = $this->createEmpty();

            //Récupération du chapitre
            if(trim($questionDonnees['numChapitre']) !== "")
            {
                $chapitre = $this->_importExcelChapitreManager->findOneBy(array('code' => $questionDonnees['numChapitre'], 'outil' => $outil));
                $question->setChapitre($chapitre);
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
            $question->setColored($questionDonnees['colored'] == '1');
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

            $this->save( $question );
        }
    }
}