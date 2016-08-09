<?php

namespace HopitalNumerique\AutodiagBundle\Controller\Back;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Service\Export\AlgorithmExport;
use HopitalNumerique\AutodiagBundle\Service\Export\RestitutionExport;
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

    public function algorithmAction(Autodiag $autodiag)
    {
        $exporter = new AlgorithmExport($this->getDoctrine()->getManager());
        return $exporter->export($autodiag);
    }

    public function algorithmLayoutAction()
    {
        $fileName =  $this->getParameter("kernel.root_dir") . '/../files/autodiag/algorithme.xlsx';

        return $this->get('igorw_file_serve.response_factory')->create($fileName);
    }

    public function restitutionAction(Autodiag $autodiag)
    {
        $exporter = new RestitutionExport($this->getDoctrine()->getManager());
        return $exporter->export($autodiag);
    }

    public function restitutionLayoutAction()
    {
        $fileName =  $this->getParameter("kernel.root_dir") . '/../files/autodiag/resultat.xlsx';

        return $this->get('igorw_file_serve.response_factory')->create($fileName);
    }
}
