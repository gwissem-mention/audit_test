<?php

namespace HopitalNumerique\RechercheParcoursBundle\Service\Risk\Export;

use HopitalNumerique\ObjetBundle\Entity\RelatedRisk;
use HopitalNumerique\RechercheParcoursBundle\DTO\StepRiskDTO;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearchStep;
use Symfony\Component\Translation\TranslatorInterface;

class RiskCSVExport extends RiskExport
{
    /**
     * @inheritdoc
     */
    public function exportGuidedSearchStepRisks(GuidedSearchStep $guidedSearchStep, $risks)
    {
        $filepath = $this->getFilePath();
        $out = fopen($filepath, 'w');
        fputcsv($out, $this->getHeader());

        $this->reorderRisks($risks);

        foreach ($risks as $risk) {
            fputcsv($out, [
                $risk->natureLabel,
                $risk->label,
                $risk->probability ?: '',
                $risk->impact ?: '',
                !$risk->probability || !$risk->impact ? '' : $risk->probability * $risk->impact,
                $risk->initialSkillsRate  ?: '',
                $risk->currentSkillsRate ?: '',
                implode(', ', $this->getDisplayableResources($risk)),
                $risk->comment,
            ]);
        }

        fclose($out);

        return $filepath;
    }
}
