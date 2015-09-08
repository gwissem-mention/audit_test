<?php

namespace HopitalNumerique\ImportExcelBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use HopitalNumerique\ImportExcelBundle\Manager\ImportExcelCategorieManager;

class ImportExcelManager extends BaseManager
{

    protected $_class = 'HopitalNumerique\AutodiagBundle\Entity\Categorie';
    protected $_importExcelCategorieManager;

    public function __construct($entityManager, ImportExcelCategorieManager $importExcelCategorieManager)
    {
        parent::__construct($entityManager);
        $this->_importExcelCategorieManager = $importExcelCategorieManager;
    }

    /**
     * Récupération des catégories dans le fichier excel d'import
     *
     * @param PHPExcel_Worksheet $sheetCategorie Feuille de catégorie d'excel
     *
     * @return array
     */
    public function getCategImported(\PHPExcel_Worksheet $sheetCategorie)
    {
        //Création du 
        $arrayCategorie = array();

        for ($i=2; $i <= $sheetCategorie->getHighestRow(); $i++) 
        { 
            //Si pour la ligne courante le libellé n'est pas rempli alors on s'arrête dans la lecture
            if( trim($sheetCategorie->getCellByColumnAndRow(1, $i)) == '' )
            {
                die('Erreur de format dans le fichier : categorie !');
                break;
            }

            $id      = $sheetCategorie->getCellByColumnAndRow(0, $i)->getValue();
            $libelle = $sheetCategorie->getCellByColumnAndRow(1, $i)->getValue();
            $note    = $sheetCategorie->getCellByColumnAndRow(2, $i)->getValue();

            $arrayCategorie[] = array(
                'id'      => $id,
                'libelle' => $libelle,
                'note'    => trim($note) === '' ? NULL : $note
            );
        }

        return $arrayCategorie;
    }

    /**
     * Récupération des chapitres dans le fichier excel d'import
     *
     * @param PHPExcel_Worksheet $sheetChapitre Feuille de chapitre d'excel
     *
     * @return array
     */
    public function getChapitreImported(\PHPExcel_Worksheet $sheetChapitre)
    {
        //Création du 
        $arrayChapitres = array();

        for ($i=2; $i <= $sheetChapitre->getHighestRow(); $i++) 
        { 
            //Si pour la ligne courante le libellé ou l'ID ne sont pas remplis alors on s'arrête dans la lecture
            if( trim($sheetChapitre->getCellByColumnAndRow(5, $i)) == '' || trim($sheetChapitre->getCellByColumnAndRow(1, $i)->getValue()) === "" )
            {
                die('Erreur de format dans le fichier : chapitre !');
                break;
            }

            $arrayChapitres[] = array(
                'id'              => trim($sheetChapitre->getCellByColumnAndRow(0, $i)->getValue()) === "" ? NULL : $sheetChapitre->getCellByColumnAndRow(0, $i)->getValue(),
                'code'            => trim($sheetChapitre->getCellByColumnAndRow(1, $i)->getValue()) === "" ? NULL : $sheetChapitre->getCellByColumnAndRow(1, $i)->getValue(),
                'idParent'        => trim($sheetChapitre->getCellByColumnAndRow(2, $i)->getValue()) === "" ? NULL : $sheetChapitre->getCellByColumnAndRow(2, $i)->getValue(),
                'codeParent'      => trim($sheetChapitre->getCellByColumnAndRow(3, $i)->getValue()) === "" ? NULL : $sheetChapitre->getCellByColumnAndRow(3, $i)->getValue(),
                'introduction'    => trim($sheetChapitre->getCellByColumnAndRow(4, $i)->getValue()) === "" ? NULL : $sheetChapitre->getCellByColumnAndRow(4, $i)->getValue(),
                'libelle'         => trim($sheetChapitre->getCellByColumnAndRow(5, $i)->getValue()) === "" ? NULL : $sheetChapitre->getCellByColumnAndRow(5, $i)->getValue(),
                'description'     => trim($sheetChapitre->getCellByColumnAndRow(6, $i)->getValue()) === "" ? NULL : $sheetChapitre->getCellByColumnAndRow(6, $i)->getValue(),
                'noteOptimale'    => trim($sheetChapitre->getCellByColumnAndRow(7, $i)->getValue()) === "" ? NULL : $sheetChapitre->getCellByColumnAndRow(7, $i)->getValue(),
                'noteMinimale'    => trim($sheetChapitre->getCellByColumnAndRow(8, $i)->getValue()) === "" ? NULL : $sheetChapitre->getCellByColumnAndRow(8, $i)->getValue(),
                'synthese'        => trim($sheetChapitre->getCellByColumnAndRow(9, $i)->getValue()) === "" ? NULL : $sheetChapitre->getCellByColumnAndRow(9, $i)->getValue(),
                'lien'            => trim($sheetChapitre->getCellByColumnAndRow(10, $i)->getValue()) === "" ? NULL : $sheetChapitre->getCellByColumnAndRow(10, $i)->getValue(),
                'descriptionLien' => trim($sheetChapitre->getCellByColumnAndRow(11, $i)->getValue()) === "" ? NULL : $sheetChapitre->getCellByColumnAndRow(11, $i)->getValue(),
                'affichageRestitutionBarre' => trim($sheetChapitre->getCellByColumnAndRow(12, $i)->getValue()) === "" ? null : $sheetChapitre->getCellByColumnAndRow(12, $i)->getValue() == '1',
                'affichageRestitutionRadar' => trim($sheetChapitre->getCellByColumnAndRow(13, $i)->getValue()) === "" ? null : $sheetChapitre->getCellByColumnAndRow(13, $i)->getValue() == '1'
            );
        }
        return $arrayChapitres;
    }

    /**
     * Récupération des questions dans le fichier excel d'import
     *
     * @param PHPExcel_Worksheet $sheetQuestion Feuille des questions d'excel
     *
     * @return array
     */
    public function getQuestionsImported(\PHPExcel_Worksheet $sheetQuestion)
    {
        //Création du 
        $arrayQuestions = array();

        for ($i=2; $i <= $sheetQuestion->getHighestRow(); $i++) 
        {
            //Si pour la ligne courante le libellé ou le code ne sont pas remplis alors on s'arrête dans la lecture
            if( trim($sheetQuestion->getCellByColumnAndRow(5, $i)) == '' || trim($sheetQuestion->getCellByColumnAndRow(3, $i)->getValue()) === '')
            {
                die('Erreur de format dans le fichier : question !');
                break;
            }

            $arrayQuestions[] = array(
                'id'              => $sheetQuestion->getCellByColumnAndRow(0, $i)->getValue(),
                'idChapitre'     => $sheetQuestion->getCellByColumnAndRow(1, $i)->getValue(),
                'numChapitre'     => $sheetQuestion->getCellByColumnAndRow(2, $i)->getValue(),
                'numQuestion'     => trim($sheetQuestion->getCellByColumnAndRow(3, $i)->getValue()) === '' ? null : $sheetQuestion->getCellByColumnAndRow(3, $i)->getValue(),
                'intro'           => trim($sheetQuestion->getCellByColumnAndRow(4, $i)->getValue()) === '' ? null : $sheetQuestion->getCellByColumnAndRow(4, $i)->getValue(),
                'texte'           => $sheetQuestion->getCellByColumnAndRow(5, $i)->getValue(),
                'type'            => $sheetQuestion->getCellByColumnAndRow(6, $i)->getValue(),
                'options'         => trim($sheetQuestion->getCellByColumnAndRow(7, $i)->getValue()) === '' ? null : $sheetQuestion->getCellByColumnAndRow(7, $i)->getValue(),
                'noteMinimale'    => trim($sheetQuestion->getCellByColumnAndRow(8, $i)->getValue()) === '' ? null : $sheetQuestion->getCellByColumnAndRow(8, $i)->getValue(),
                'synthese'        => trim($sheetQuestion->getCellByColumnAndRow(9, $i)->getValue()) === '' ? null : $sheetQuestion->getCellByColumnAndRow(9, $i)->getValue(),
                'colored'         => $sheetQuestion->getCellByColumnAndRow(10, $i)->getValue(),
                'infobulle'       => trim($sheetQuestion->getCellByColumnAndRow(11, $i)->getValue()) === '' ? null : $sheetQuestion->getCellByColumnAndRow(11, $i)->getValue(),
                'categorie'       => $sheetQuestion->getCellByColumnAndRow(12, $i)->getValue(),
                'ponderation'     => trim($sheetQuestion->getCellByColumnAndRow(13, $i)->getValue()) === '' ? 1 : $sheetQuestion->getCellByColumnAndRow(13, $i)->getValue(),
                'order'           => trim($sheetQuestion->getCellByColumnAndRow(14, $i)->getValue()) === '' ? $i : $sheetQuestion->getCellByColumnAndRow(14, $i)->getValue(),
                'lien'            => trim($sheetQuestion->getCellByColumnAndRow(15, $i)->getValue()) === '' ? null : $sheetQuestion->getCellByColumnAndRow(15, $i)->getValue(),
                'descriptionLien' => trim($sheetQuestion->getCellByColumnAndRow(16, $i)->getValue()) === '' ? null : $sheetQuestion->getCellByColumnAndRow(16, $i)->getValue()
            );
        }

        return $arrayQuestions;
    }

    /**
     * Récupération des chapitres dans le fichier excel d'import
     *
     * @param PHPExcel_Worksheet $sheetResultat Feuille de chapitre d'excel
     *
     * @return array
     */
    public function getResultatImported(\PHPExcel_Worksheet $sheetResultat)
    {
        //Création du tableau des résultats
        $arrayResultats = array();

        for ($i=2; $i <= $sheetResultat->getHighestRow(); $i++) 
        { 
            //Si pour la ligne courante le libellé ou le conde ne sont pas remplis alors on s'arrête dans la lecture
            if( trim($sheetResultat->getCellByColumnAndRow(9, $i)) == '' || trim($sheetResultat->getCellByColumnAndRow(0, $i)->getValue()) === "" )
            {
                die('Erreur de format dans le fichier : resultat !');
                break;
            }
            $arrayResultats[] = array(
                'id'                     => trim($sheetResultat->getCellByColumnAndRow(0, $i)->getValue()) === "" ? NULL : $sheetResultat->getCellByColumnAndRow(0, $i)->getValue(),
                'nom'                    => trim($sheetResultat->getCellByColumnAndRow(1, $i)->getValue()) === "" ? NULL : $sheetResultat->getCellByColumnAndRow(1, $i)->getValue(),
                'dateDerniereSauvegarde' => trim($sheetResultat->getCellByColumnAndRow(2, $i)->getValue()) === "" ? NULL : new \DateTime($sheetResultat->getCellByColumnAndRow(2, $i)->getValue()),
                'dateValidation'         => trim($sheetResultat->getCellByColumnAndRow(3, $i)->getValue()) === "" ? NULL : new \DateTime($sheetResultat->getCellByColumnAndRow(3, $i)->getValue()),
                'dateCreation'           => trim($sheetResultat->getCellByColumnAndRow(4, $i)->getValue()) === "" ? NULL : new \DateTime($sheetResultat->getCellByColumnAndRow(4, $i)->getValue()),
                'tauxRemplissage'        => trim($sheetResultat->getCellByColumnAndRow(5, $i)->getValue()) === "" ? NULL : $sheetResultat->getCellByColumnAndRow(5, $i)->getValue(),
                'pdf'                    => trim($sheetResultat->getCellByColumnAndRow(6, $i)->getValue()) === "" ? NULL : $sheetResultat->getCellByColumnAndRow(6, $i)->getValue(),
                'remarque'               => trim($sheetResultat->getCellByColumnAndRow(7, $i)->getValue()) === "" ? NULL : $sheetResultat->getCellByColumnAndRow(7, $i)->getValue(),
                'statut'                 => trim($sheetResultat->getCellByColumnAndRow(8, $i)->getValue()) === "" ? NULL : $sheetResultat->getCellByColumnAndRow(8, $i)->getValue(),
                'outil'                  => trim($sheetResultat->getCellByColumnAndRow(9, $i)->getValue()) === "" ? NULL : $sheetResultat->getCellByColumnAndRow(9, $i)->getValue(),
                'user'                   => trim($sheetResultat->getCellByColumnAndRow(10, $i)->getValue()) === "" ? NULL : $sheetResultat->getCellByColumnAndRow(10, $i)->getValue(),
                'synthese'               => trim($sheetResultat->getCellByColumnAndRow(11, $i)->getValue()) === "" ? NULL : ($sheetResultat->getCellByColumnAndRow(11, $i)->getValue() == "Oui")
            );
        }
        return $arrayResultats;
    }

    /**
     * Récupération des chapitres dans le fichier excel d'import
     *
     * @param PHPExcel_Worksheet $sheetReponse Feuille de chapitre d'excel
     *
     * @return array
     */
    public function getReponsesImported(\PHPExcel_Worksheet $sheetReponse)
    {
        //Création du tableau des résultats
        $arrayReponses = array();

        for ($i=2; $i <= $sheetReponse->getHighestRow(); $i++) 
        { 
            //Si pour la ligne courante le libellé ou le conde ne sont pas remplis alors on s'arrête dans la lecture
            if( trim($sheetReponse->getCellByColumnAndRow(4, $i)) == '' || trim($sheetReponse->getCellByColumnAndRow(0, $i)->getValue()) === "" )
            {
                die('Erreur de format dans le fichier : reponse !');
                break;
            }

            $arrayReponses[] = array(
                'id'       => trim($sheetReponse->getCellByColumnAndRow(0, $i)->getValue()) === "" ? NULL : $sheetReponse->getCellByColumnAndRow(0, $i)->getValue(),
                'valeur'   => trim($sheetReponse->getCellByColumnAndRow(1, $i)->getValue()) === "" ? "" : $sheetReponse->getCellByColumnAndRow(1, $i)->getValue(),
                'remarque' => trim($sheetReponse->getCellByColumnAndRow(2, $i)->getValue()) === "" ? "" : $sheetReponse->getCellByColumnAndRow(2, $i)->getValue(),
                'resultat' => trim($sheetReponse->getCellByColumnAndRow(3, $i)->getValue()) === "" ? NULL : $sheetReponse->getCellByColumnAndRow(3, $i)->getValue(),
                'question' => trim($sheetReponse->getCellByColumnAndRow(4, $i)->getValue()) === "" ? NULL : $sheetReponse->getCellByColumnAndRow(4, $i)->getValue()
            );
        }
        return $arrayReponses;
    }

    /**
     * Récupération des syntheses dans le fichier excel d'import
     *
     * @param PHPExcel_Worksheet $sheetReponse Feuille de chapitre d'excel
     *
     * @return array
     */
    public function getSyntheseImported(\PHPExcel_Worksheet $sheetSynthese)
    {
        //Création du tableau des résultats
        $arraySyntheses = array();

        for ($i=2; $i <= $sheetSynthese->getHighestRow(); $i++) 
        { 
            //Si pour la ligne courante le libellé ou le conde ne sont pas remplis alors on s'arrête dans la lecture
            if( trim($sheetSynthese->getCellByColumnAndRow(0, $i)) == '' || trim($sheetSynthese->getCellByColumnAndRow(1, $i)->getValue()) == '' )
            {
                die('Erreur de format dans le fichier : reponse !');
                break;
            }

            $arraySyntheses[intval($sheetSynthese->getCellByColumnAndRow(0, $i)->getValue())][] = trim($sheetSynthese->getCellByColumnAndRow(1, $i)->getValue()) === "" ? "" : $sheetSynthese->getCellByColumnAndRow(1, $i)->getValue();
        }
        return $arraySyntheses;
    }
}