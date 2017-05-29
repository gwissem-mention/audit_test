<?php

namespace HopitalNumerique\AutodiagBundle\Service\Export;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
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
            $chapterData[] = $chapter->getNumber();
            $chapterData[] = $chapter->getLabel();
            $chapterData[] = null;
            $chapterData[] = null;
            $chapterData[] = null;
        } else {
            $chapterData[] = $chapter->getParent()->getCode();
            $chapterData[] = $chapter->getOrder();
            $chapterData[] = $chapter->getParent()->getNumber();
            $chapterData[] = $chapter->getParent()->getLabel();
            $chapterData[] = $chapter->getCode();
            $chapterData[] = $chapter->getNumber();
            $chapterData[] = $chapter->getLabel();
        }

        $chapterData[] = $chapter->getTitle();
        $chapterData[] = $chapter->getDescription();
        $chapterData[] = $chapter->getAdditionalDescription();

        $actionPlans = array_map(function (Autodiag\ActionPlan $actionPlan) use ($chapter) {
            if ($chapter === $actionPlan->getContainer() && count($actionPlan->getLinks()) > 0) {
                $actionPlanString =
                    $actionPlan->getValue()
                    . '::'
                    . $actionPlan->isVisible()
                    . '::'
                    . $actionPlan->getDescription()
                ;

                /** @var Autodiag\ActionPlan\Link $link */
                foreach ($actionPlan->getLinks() as $link) {
                    $actionPlanString .= '::' . $link->getDescription() . '::' . $link->getUrl();
                }

                return $actionPlanString;
            }

            return null;
        }, $chapter->getAutodiag()->getActionPlans()->toArray());

        $actionPlans = implode("\n", array_filter($actionPlans, function ($var) {
            return $var != null;
        }));

        $chapterData[] = $actionPlans;

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
            AttributeColumnsDefinition::UNIT => $attribute->getUnit(),
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
            return implode('::', $element);
        }, $categoryData));

        $actionPlans = [];
        foreach ($attribute->getAutodiag()->getActionPlans() as $actionPlan) {
            if ($actionPlan->getAttribute() === $attribute) {
                $actionPlans[] = $actionPlan;
            }
        }

        $options = implode("\n", array_map(function (Attribute\Option $option) use ($actionPlans) {
            $i = 0;

            while ($i < count($actionPlans)) {
                /** @var Autodiag\ActionPlan $actionPlan */
                $actionPlan = $actionPlans[$i];
                if ($actionPlan->getValue() === $option->getValue() && count($actionPlan->getLinks()) > 0) {
                    $actionPlanString =
                        $option->getValue()
                        . '::'
                        . $option->getLabel()
                        . '::'
                        . $actionPlan->isVisible()
                        . '::'
                        . $actionPlan->getDescription()
                    ;

                    /** @var Autodiag\ActionPlan\Link $link */
                    foreach ($actionPlan->getLinks() as $link) {
                        $actionPlanString .= '::' . $link->getDescription() . '::' . $link->getUrl();
                    }

                    return $actionPlanString;
                }

                ++$i;
            }

            return $option->getValue() . '::' . $option->getLabel();
        }, $attribute->getOptions()->toArray()));

        $this->addRow($sheet, [
            $data[AttributeColumnsDefinition::CODE],
            $data[AttributeColumnsDefinition::ORDER],
            array_key_exists(AttributeColumnsDefinition::CHAPTER, $data) ? $data[AttributeColumnsDefinition::CHAPTER] : '',
            $data[AttributeColumnsDefinition::DESCRIPTION],
            $data[AttributeColumnsDefinition::NUMBER],
            $data[AttributeColumnsDefinition::LABEL],
            $data[AttributeColumnsDefinition::UNIT],
            $data[AttributeColumnsDefinition::TYPE],
            $options,
            $data[AttributeColumnsDefinition::COLORED],
            $data[AttributeColumnsDefinition::TOOLTIP],
            $data[AttributeColumnsDefinition::CATEGORY_WEIGHT],
            array_key_exists(AttributeColumnsDefinition::CHAPTER_WEIGHT, $data) ? $data[AttributeColumnsDefinition::CHAPTER_WEIGHT] : '',
        ]);
    }
}
