<?php
namespace HopitalNumerique\AutodiagBundle\Service\Export;

use HopitalNumerique\AutodiagBundle\Entity\Synthesis;

class AutodiagEntriesExport
{
    /**
     * @param Synthesis[] $syntheses
     */
    public function exportList($syntheses)
    {
        foreach ($syntheses as $synthesis) {

        }
    }

    protected function getHeaderRow()
    {
        $header = [
            'Nom',
            'Utilisateur',
            'Établissement',
            'Remplissage',
            'Création',
            'Dernier enregistrement',
            'Validation',
            'Partagé avec',
            'Synthèse',
        ];
    }
}
