<?php

namespace HopitalNumerique\AutodiagBundle\Controller\Back;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Service\Export\SurveyExport;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ExportController extends Controller
{
    public function surveyAction(Autodiag $autodiag)
    {
        $exporter = new SurveyExport();
        return $exporter->export($autodiag);
    }
}
