<?php

namespace HopitalNumerique\ImportExcelBundle\Manager;

use HopitalNumerique\AutodiagBundle\Manager\ReponseManager as ReponseManagerAutodiag;
use Nodevo\ToolsBundle\Tools\Chaine;
use Doctrine\ORM\EntityManager;
use HopitalNumerique\AutodiagBundle\Manager\ResultatManager;
use HopitalNumerique\AutodiagBundle\Manager\QuestionManager;

/**
 * Manager de l'entité Categorie.
 */
class ImportExcelReponsesManager extends ReponseManagerAutodiag
{
    protected $_class = 'HopitalNumerique\AutodiagBundle\Entity\Reponse';
    protected $_referenceManager;
    protected $_userManager;

    public function __construct($entityManager, ResultatManager $resultatManager, QuestionManager $questionManager )
    {
        parent::__construct($entityManager);
        $this->_resultatManager = $resultatManager;
        $this->_questionManager      = $questionManager;
    }

    public function getReponsesAsArrayByResultat($resultatIds)
    {
        $reponsesByResultat = array();

        $reponses = $this->getRepository()->getReponsesByResultats($resultatIds)->getQuery()->getResult();

        foreach ($reponses as $reponse) 
        {
            if(!array_key_exists($reponse['resId'], $reponsesByResultat))
            {
                $reponsesByResultat[$reponse['resId']] = array();
            }

            $reponsesByResultat[$reponse['resId']][$reponse['id']] = $reponse;
        }

        return $reponsesByResultat;
    }

    /**
     * Récupération des données des résultats du fichier excel d'import
     *
     * @param Array $arrayReponse     Tableau des données de résultats à persist
     * @param Array $outil            Autodiag
     * @param Array $arrayIdResultats Tableau des ids de résultat correspondant aux anciens et nouveaux
     * @param Array $arrayIdQuestions Tableau des ids de questions correspondant aux anciens et nouveaux
     *
     * @return void
     */
    public function saveReponseImported( $arrayReponse, $outil, $arrayIdResultats, $arrayIdQuestions )
    {

        $reponses = array();

        foreach ($arrayReponse as $key => $reponseDonnees) 
        {
            //Création d'une nouvelle catégorie
            $reponse = $this->createEmpty();

            $reponse->setValue($reponseDonnees['valeur']);
            $reponse->setRemarque($reponseDonnees['remarque']);
            if(!is_null($reponseDonnees['resultat']))
            {
                $reponse->setResultat($this->_resultatManager->findOneBy(array('id' => $arrayIdResultats[intval($reponseDonnees['resultat'])] )));
            }
            if(!is_null($reponseDonnees['question']))
            {
                $reponse->setQuestion($this->_questionManager->findOneBy(array('id' => $arrayIdQuestions[intval($reponseDonnees['question'])] )));
            }

            $reponses[] = $reponse;
        }

        $this->save( $reponses );
    }
}