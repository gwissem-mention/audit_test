<?php
namespace HopitalNumerique\AutodiagBundle\Service\Export;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container\Chapter;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Attribute;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Attribute\Weight;
use HopitalNumerique\AutodiagBundle\Model\FileImport\AttributeColumnsDefinition;
use HopitalNumerique\AutodiagBundle\Model\FileImport\ChapterColumnsDefinition;

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
        $this->addRow($sheet, ChapterColumnsDefinition::getColumns());
    }

    private function writeQuestionsHeaders(\PHPExcel_Worksheet $sheet)
    {
        $this->addRow($sheet, AttributeColumnsDefinition::getColumns());
    }

    private function writeChapterRow(\PHPExcel_Worksheet $sheet, Autodiag\Container\Chapter $chapter)
    {
        $chapterData = [];

        if (null === $chapter->getParent()) {
            $chapterData[] = $chapter->getCode();
            $chapterData[] = $chapter->getOrder();
            $chapterData[] = $chapter->getLabel();
            $chapterData[] = null;
            $chapterData[] = null;
        } else {
            $chapterData[] = $chapter->getParent()->getCode();
            $chapterData[] = $chapter->getOrder();
            $chapterData[] = $chapter->getParent()->getLabel();
            $chapterData[] = $chapter->getCode();
            $chapterData[] = $chapter->getLabel();
        }

        $chapterData[] = $chapter->getNumber();
        $chapterData[] = $chapter->getTitle();
        $chapterData[] = $chapter->getDescription();
        $chapterData[] = $chapter->getAdditionalDescription();

        $this->addRow($sheet, $chapterData);
    }

    private function writeAttributeRow(\PHPExcel_Worksheet $sheet, Attribute $attribute)
    {
        $weights = $this->manager->getRepository(Attribute::class)->getAttributeContainersWeight($attribute);

        $data = [
            AttributeColumnsDefinition::CODE => $attribute->getCode(),
            AttributeColumnsDefinition::ORDER => $attribute->getOrder(),
            AttributeColumnsDefinition::DESCRIPTION => $attribute->getDescription(),
            AttributeColumnsDefinition::NUMBER => $attribute->getNumber(),
            AttributeColumnsDefinition::LABEL => $attribute->getLabel(),
            AttributeColumnsDefinition::TYPE => $attribute->getType(),
            AttributeColumnsDefinition::COLORED => $attribute->isColored() ? ($attribute->isColorationInversed() ? '-1' : '1') : '0',
            AttributeColumnsDefinition::TOOLTIP => $attribute->getTooltip(),
        ];

        $categoryData = [];
        foreach ($weights as $weight) {
            /** @var Weight $weight */
            if ($weight->getContainer() instanceof Autodiag\Container\Chapter) {
                $data[AttributeColumnsDefinition::CHAPTER] = $weight->getContainer()->getCode();
                $data[AttributeColumnsDefinition::CHAPTER_WEIGHT] = $weight->getWeight();
            } elseif ($weight->getContainer() instanceof Autodiag\Container\Category) {
                $categoryData[] = [$weight->getContainer()->getCode(), $weight->getWeight()];
            }
        }

        $data[AttributeColumnsDefinition::CATEGORY_WEIGHT] = implode("\n", array_map(function ($element) {
            return implode("::", $element);
        }, $categoryData));

        $options = implode("\n", array_map(function (Attribute\Option $option) {
            return $option->getValue() . '::' . $option->getLabel();
        }, $attribute->getOptions()->toArray()));

        $this->addRow($sheet, [
            $data[AttributeColumnsDefinition::CODE],
            $data[AttributeColumnsDefinition::ORDER],
            array_key_exists(AttributeColumnsDefinition::CHAPTER, $data) ? $data[AttributeColumnsDefinition::CHAPTER] : '',
            $data[AttributeColumnsDefinition::DESCRIPTION],
            $data[AttributeColumnsDefinition::NUMBER],
            $data[AttributeColumnsDefinition::LABEL],
            $data[AttributeColumnsDefinition::TYPE],
            $options,
            $data[AttributeColumnsDefinition::COLORED],
            $data[AttributeColumnsDefinition::TOOLTIP],
            $data[AttributeColumnsDefinition::CATEGORY_WEIGHT],
            array_key_exists(AttributeColumnsDefinition::CHAPTER_WEIGHT, $data) ? $data[AttributeColumnsDefinition::CHAPTER_WEIGHT] : '',
        ]);
    }
}
