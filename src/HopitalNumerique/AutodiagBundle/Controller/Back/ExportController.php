<?php

namespace HopitalNumerique\AutodiagBundle\Controller\Back;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Service\Export\SurveyExport;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ExportController extends Controller
{
    public function surveyAction(Autodiag $autodiag)
    {
        $exporter = new SurveyExport($this->getDoctrine()->getManager());
        return $exporter->export($autodiag);
    }

    public function surveyLayoutAction()
    {
        $fileName =  $this->getParameter("kernel.root_dir") . '/../files/autodiag/questionnaire.xlsx';

        return $this->get('igorw_file_serve.response_factory')->create($fileName);
    }
}
