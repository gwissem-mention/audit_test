<?php
namespace HopitalNumerique\AutodiagBundle\Service\Export;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container\Chapter;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Attribute;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Attribute\Weight;

class SurveyExport extends AbstractExport
{
    public function export(Autodiag $autodiag)
    {
        $excel = new \PHPExcel();
        $sheet = $this->addSheet($excel, 'chapitres');
        $this->writeChapterHeaders($sheet);

        $excel->removeSheetByIndex(0);

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

        return $this->getFileResponse($excel, $autodiag->getTitle(), 'questionnaire');
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

    private function writeAttributeRow(\PHPExcel_Worksheet $sheet, Attribute $attribute)
    {
        $weights = $this->manager->getRepository(Attribute::class)->getAttributeContainersWeight($attribute);

        $data = [
            'code_question' => $attribute->getCode(),
            'text_avant' => $attribute->getLabel(),
            'libelle_question' => $attribute->getLabel(),
            'format_reponse' => $attribute->getType(),
            'colorer_reponse' => $attribute->isColored() ? '1' : '-1',
            'infobulle_question' => $attribute->getTooltip(),
        ];

        $categoryData = [];
        foreach ($weights as $weight) {
            /** @var Weight $weight */
            if ($weight->getContainer() instanceof Autodiag\Container\Chapter) {
                $data['code_chapitre'] = $weight->getContainer()->getCode();
                $data['ponderation_chapitre'] = $weight->getWeight();
            } elseif ($weight->getContainer() instanceof Autodiag\Container\Category) {
                $categoryData[] = [$weight->getContainer()->getCode(), $weight->getWeight()];
            }
        }

        $data['poderation_categorie'] = implode("\n", array_map(function ($element) {
            return implode("::", $element);
        }, $categoryData));

        $options = implode("\n", array_map(function (Attribute\Option $option) {
            return $option->getValue() . '::' . $option->getLabel();
        }, $attribute->getOptions()->toArray()));

        $this->addRow($sheet, [
            $data['code_question'],
            array_key_exists('code_chapitre', $data) ? $data['code_chapitre'] : '',
            $data['text_avant'],
            $data['libelle_question'],
            $data['format_reponse'],
            $options,
            $data['colorer_reponse'],
            $data['infobulle_question'],
            $data['poderation_categorie'],
            array_key_exists('ponderation_chapitre', $data) ? $data['ponderation_chapitre'] : '',
        ]);
    }
}
