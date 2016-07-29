<?php
namespace HopitalNumerique\AutodiagBundle\Service\Export;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class SurveyExport
{

    protected $row = 1;

    public function __construct()
    {

    }

    public function export(Autodiag $autodiag)
    {
        $excel = new \PHPExcel();
        $sheet = $this->addSheet($excel, 'chapitres');
        $this->writeChapterHeaders($sheet);

        $chapters = $autodiag->getChapters();
        foreach ($chapters as $chapter) {
            $this->writeChapterRow($sheet, $chapter);
            foreach ($chapter->getChilds() as $child) {
                $this->writeChapterRow($sheet, $child);
            }
        }

        $this->row = 1;
        $sheet = $this->addSheet($excel, 'questions');
        $this->writeQuestionsHeaders($sheet);

        $attributes = $autodiag->getAttributes();
        foreach ($attributes as $attribute) {
            $this->writeAttributeRow($sheet, $attribute);
        }


        $file = stream_get_meta_data(tmpfile())['uri'];
        $this->getWriter($excel)->save($file);

        $response = new BinaryFileResponse($file);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'test.xlsx'
        );
        return $response;
    }

    private function writeChapterHeaders(\PHPExcel_Worksheet $sheet)
    {
        $this->addRow($sheet, [
            'code_chapitre',
            'libelle_chapitre',
            'code_chapitre_enfant',
            'libelle_chapitre_enfant',
            'titre_avant',
            'texte_avant',
            'texte_apres',
            'plan_action',
        ]);
    }

    private function writeQuestionsHeaders(\PHPExcel_Worksheet $sheet)
    {
        $this->addRow($sheet, [
            'code_question',
            'code_chapitre',
            'texte_avant',
            'libelle_question',
            'format_reponse',
            'items_reponse',
            'colorer_reponse',
            'infobulle_question',
            'ponderation_categorie',
            'ponderation_chapitre',
        ]);
    }

    private function writeChapterRow(\PHPExcel_Worksheet $sheet, Autodiag\Container\Chapter $chapter)
    {
        $chapterData = [];

        if (null === $chapter->getParent()) {
            $chapterData[] = $chapter->getCode();
            $chapterData[] = $chapter->getLabel();
            $chapterData[] = null;
            $chapterData[] = null;
        } else {
            $chapterData[] = $chapter->getParent()->getCode();
            $chapterData[] = $chapter->getParent()->getLabel();
            $chapterData[] = $chapter->getCode();
            $chapterData[] = $chapter->getLabel();
        }

        $chapterData[] = $chapter->getTitle();
        $chapterData[] = $chapter->getDescription();
        $chapterData[] = $chapter->getAdditionalDescription();

        $this->addRow($sheet, $chapterData);
    }
    
    private function writeAttributeRow(\PHPExcel_Worksheet $sheet, Autodiag\Attribute $attribute)
    {
        $data = [];
        $data[] = $attribute->getCode();
        
    }

    protected function addSheet(\PHPExcel $excel, $title)
    {

        foreach ($excel->getAllSheets() as $sheet) {
            if ($sheet->getTitle() == $title) {
                return $sheet;
            }
        }

        $sheet = $excel->createSheet();
        $sheet->setTitle($title);
        $sheet->setCodeName($title);
        return $sheet;
    }

    protected function addRow(\PHPExcel_Worksheet $sheet, $row)
    {
        $col = 'A';
        foreach ($row as $cell) {
            $sheet->setCellValue($col . $this->row, $cell);
            $col++;
        }
        $this->row++;
    }

    /**
     * Get Excel writer
     *
     * @param \PHPExcel $excel
     * @return \PHPExcel_Writer_Excel2007
     */
    protected function getWriter($excel)
    {
        return new \PHPExcel_Writer_Excel2007($excel);
    }
}
