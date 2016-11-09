<?php
namespace HopitalNumerique\AutodiagBundle\Service\Export;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Service\Import\AlgorithmWriter;

class AlgorithmExport extends AbstractExport
{
    private $referencesCount = 10;

    public function export(Autodiag $autodiag)
    {
        $excel = new \PHPExcel();
        $excel->removeSheetByIndex(0);
        $sheet = $this->addSheet($excel, 'algorithme');

        $this->writeHeader($sheet);

        $row = [
            $autodiag->getAlgorithm(),
            $autodiag->getRestitution()->getScoreColor(),
            $autodiag->getRestitution()->getScoreLabel(),
        ];

        $references = $autodiag->getReferences();
        $referencesKeyed = [];
        foreach ($references as $reference) {
            /** @var Autodiag\Reference $reference */
            $referencesKeyed[$reference->getNumber()] = $reference;
        }

        for ($i = 1; $i <= $this->referencesCount; $i++) {
            if (isset($referencesKeyed[$i])) {
                $row = array_merge($row, [
                    $referencesKeyed[$i]->getLabel(),
                    $referencesKeyed[$i]->getValue(),
                    $referencesKeyed[$i]->getColor(),
                ]);
            }
        }
        $this->addRow($sheet, $row);

        return $this->getFileResponse($excel, $autodiag->getTitle(), 'algorithme');
    }

    protected function writeHeader(\PHPExcel_Worksheet $sheet)
    {
        $data = [
            AlgorithmWriter::COLUMN_ALGORITHM,
            AlgorithmWriter::COLUMN_SCORE_COLOR,
            AlgorithmWriter::COLUMN_SCORE_LABEL,
        ];
        for ($i = 1; $i <= $this->referencesCount; $i++) {
            $data = array_merge($data, [
                'libelle_valeur_reference_' . $i,
                'calcul_valeur_reference_' . $i,
                'couleur_reference_' . $i,
            ]);
        }

        $this->addRow($sheet, $data);
    }
}
