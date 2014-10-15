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
            if( trim($sheetCategorie->getCellByColumnAndRow(0, $i)) == '' )
                break;

            $libelle = $sheetCategorie->getCellByColumnAndRow(0, $i)->getValue();
            $note    = $sheetCategorie->getCellByColumnAndRow(1, $i)->getValue();

            $arrayCategorie[] = array(
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
            //Si pour la ligne courante le libellé ou le conde ne sont pas remplis alors on s'arrête dans la lecture
            if( trim($sheetChapitre->getCellByColumnAndRow(3, $i)) == '' || trim($sheetChapitre->getCellByColumnAndRow(0, $i)->getValue()) === "" )
                break;

            $arrayChapitres[] = array(
                'code'            => trim($sheetChapitre->getCellByColumnAndRow(0, $i)->getValue()) === "" ? NULL : $sheetChapitre->getCellByColumnAndRow(0, $i)->getValue(),
                'codeParent'      => trim($sheetChapitre->getCellByColumnAndRow(1, $i)->getValue()) === "" ? NULL : $sheetChapitre->getCellByColumnAndRow(1, $i)->getValue(),
                'libelle'         => trim($sheetChapitre->getCellByColumnAndRow(3, $i)->getValue()) === "" ? NULL : $sheetChapitre->getCellByColumnAndRow(3, $i)->getValue(),
                'noteOptimale'    => trim($sheetChapitre->getCellByColumnAndRow(5, $i)->getValue()) === "" ? NULL : $sheetChapitre->getCellByColumnAndRow(5, $i)->getValue(),
                'noteMinimale'    => trim($sheetChapitre->getCellByColumnAndRow(6, $i)->getValue()) === "" ? NULL : $sheetChapitre->getCellByColumnAndRow(6, $i)->getValue(),
                'introduction'    => trim($sheetChapitre->getCellByColumnAndRow(2, $i)->getValue()) === "" ? NULL : $sheetChapitre->getCellByColumnAndRow(2, $i)->getValue(),
                'synthese'        => trim($sheetChapitre->getCellByColumnAndRow(7, $i)->getValue()) === "" ? NULL : $sheetChapitre->getCellByColumnAndRow(7, $i)->getValue(),
                'description'     => trim($sheetChapitre->getCellByColumnAndRow(4, $i)->getValue()) === "" ? NULL : $sheetChapitre->getCellByColumnAndRow(4, $i)->getValue(),
                'lien'            => trim($sheetChapitre->getCellByColumnAndRow(8, $i)->getValue()) === "" ? NULL : $sheetChapitre->getCellByColumnAndRow(8, $i)->getValue(),
                'descriptionLien' => trim($sheetChapitre->getCellByColumnAndRow(9, $i)->getValue()) === "" ? NULL : $sheetChapitre->getCellByColumnAndRow(9, $i)->getValue()
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
            if( trim($sheetQuestion->getCellByColumnAndRow(3, $i)) == '' || trim($sheetQuestion->getCellByColumnAndRow(1, $i)->getValue()) === '')
                break;

            $arrayQuestions[] = array(
                'numChapitre'     => $sheetQuestion->getCellByColumnAndRow(0, $i)->getValue(),
                'numQuestion'     => trim($sheetQuestion->getCellByColumnAndRow(1, $i)->getValue()) === '' ? NULL : $sheetQuestion->getCellByColumnAndRow(1, $i)->getValue(),
                'intro'           => trim($sheetQuestion->getCellByColumnAndRow(2, $i)->getValue()) === '' ? NULL : $sheetQuestion->getCellByColumnAndRow(2, $i)->getValue(),
                'texte'           => $sheetQuestion->getCellByColumnAndRow(3, $i)->getValue(),
                'type'            => $sheetQuestion->getCellByColumnAndRow(4, $i)->getValue(),
                'options'         => trim($sheetQuestion->getCellByColumnAndRow(5, $i)->getValue()) === '' ? NULL : $sheetQuestion->getCellByColumnAndRow(5, $i)->getValue(),
                'noteMinimale'    => trim($sheetQuestion->getCellByColumnAndRow(6, $i)->getValue()) === '' ? NULL : $sheetQuestion->getCellByColumnAndRow(6, $i)->getValue(),
                'synthese'        => trim($sheetQuestion->getCellByColumnAndRow(7, $i)->getValue()) === '' ? NULL : $sheetQuestion->getCellByColumnAndRow(7, $i)->getValue(),
                'colored'         => $sheetQuestion->getCellByColumnAndRow(8, $i)->getValue(),
                'infobulle'       => trim($sheetQuestion->getCellByColumnAndRow(9, $i)->getValue()) === '' ? NULL : $sheetQuestion->getCellByColumnAndRow(9, $i)->getValue(),
                'categorie'       => $sheetQuestion->getCellByColumnAndRow(10, $i)->getValue(),
                'ponderation'     => trim($sheetQuestion->getCellByColumnAndRow(11, $i)->getValue()) === '' ? 1 : $sheetQuestion->getCellByColumnAndRow(11, $i)->getValue(),
                'order'           => trim($sheetQuestion->getCellByColumnAndRow(12, $i)->getValue()) === '' ? $i : $sheetQuestion->getCellByColumnAndRow(12, $i)->getValue(),
                'lien'            => trim($sheetQuestion->getCellByColumnAndRow(13, $i)->getValue()) === '' ? $i : $sheetQuestion->getCellByColumnAndRow(13, $i)->getValue(),
                'descriptionLien' => trim($sheetQuestion->getCellByColumnAndRow(14, $i)->getValue()) === '' ? $i : $sheetQuestion->getCellByColumnAndRow(14, $i)->getValue()
            );
        }

        return $arrayQuestions;
    }
}