<?php

namespace HopitalNumerique\RechercheParcoursBundle\Service\Risk\Export;

use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearch;

/**
 * Class SynthesisCSVExport
 */
class SynthesisCSVExport extends SynthesisExport
{
    /**
     * {@inheritdoc}
     */
    public function exportGuidedSearch(GuidedSearch $guidedSearch, User $user = null)
    {
        $out = fopen('php://output', 'w');
        fputcsv($out, $this->getHeader());

        foreach ($this->parseGuidedSearch($guidedSearch, $user) as $risk) {
            fputcsv($out, $risk);
        }

        fclose($out);
    }
}
