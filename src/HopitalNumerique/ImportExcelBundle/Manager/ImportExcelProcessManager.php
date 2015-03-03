<?php
namespace HopitalNumerique\ImportExcelBundle\Manager;

use HopitalNumerique\AutodiagBundle\Manager\ProcessManager;
use HopitalNumerique\AutodiagBundle\Entity\Outil;
use HopitalNumerique\AutodiagBundle\Entity\ProcessChapitre;

/**
 * Manager de l'entité OutilProcess.
 */
class ImportExcelProcessManager extends ProcessManager
{
    /**
     * Importe les process de l'autodiag.
     *
     * @param \PHPExcel $phpExcel Fichier Excel
     * @param \HopitalNumerique\AutodiagBundle\Entity\Outil Outil
     * @param array $concordanceChapitreIds
     * @return void
     */
    public function importSheetsProcess(\PHPExcel $phpExcel, Outil $outil, array $concordanceChapitreIds)
    {
        $sheetProcess = $phpExcel->getSheetByName('process');
        $sheetProcessChapitre = $phpExcel->getSheetByName('process_chapitre');
    
        if (null !== $sheetProcess && null !== $sheetProcessChapitre)
        {
            /**
             * @var array<integer, \HopitalNumerique\AutodiagBundle\Entity\Process> Les Process créés classés selon leur ancien ID
             */
            $concordancesProcess = array();
            
            for ($i = 2; $i <= $sheetProcess->getHighestRow(); $i++)
            {
                $process = $this->createEmpty();
                $process->setOutil($outil);
                $process->setLibelle(trim($sheetProcess->getCellByColumnAndRow(1, $i)->getValue()));
                $process->setOrder(intval(trim($sheetProcess->getCellByColumnAndRow(2, $i)->getValue())));

                $ancienProcessId = intval(trim($sheetProcess->getCellByColumnAndRow(0, $i)->getValue()));
                $concordancesProcess[$ancienProcessId] = $process;
            }
            
            for ($i = 2; $i <= $sheetProcessChapitre->getHighestRow(); $i++)
            {
                $ancienProcessId = intval(trim($sheetProcessChapitre->getCellByColumnAndRow(0, $i)->getValue()));
                $ancienChapitreId = trim($sheetProcessChapitre->getCellByColumnAndRow(1, $i)->getValue());
                
                if (!isset($concordanceChapitreIds[$ancienChapitreId]))
                    throw new \Exception('L\'ID de chapitre "'.$ancienChapitreId.'" n\'existe pas dans la feuille process_chapitre.');
                if (!isset($concordancesProcess[$ancienProcessId]))
                    throw new \Exception('L\'ID de process "'.$ancienProcessId.'" n\'existe pas dans la feuille process_chapitre.');
                
                $chapitre = $this->chapitreManager->findOneById($concordanceChapitreIds[$ancienChapitreId]);
                if (null === $chapitre)
                    throw new \Exception('L\'ID de chapitre "'.$ancienChapitreId.'" n\'existe pas dans la feuille process_chapitre.');
                
                $processChapitre = new ProcessChapitre();
                $processChapitre->setProcess($concordancesProcess[$ancienProcessId]);
                $processChapitre->setChapitre($chapitre);
                $processChapitre->setOrder(intval(trim($sheetProcessChapitre->getCellByColumnAndRow(2, $i)->getValue())));
                $concordancesProcess[$ancienProcessId]->addProcessChapitre($processChapitre);
            }

            $this->save($concordancesProcess);
        }
    }
}
