<?php

namespace HopitalNumerique\AutodiagBundle\Controller\Back;

use HopitalNumerique\AutodiagBundle\Model\AutodiagAdminFileImport;
use HopitalNumerique\AutodiagBundle\Model\AutodiagAdminUpdate;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Form\Type\Autodiag\AutodiagAdminUpdateType;
use HopitalNumerique\AutodiagBundle\Form\Type\Autodiag\FileImportType;
use HopitalNumerique\AutodiagBundle\Grid\ModelGrid;
use HopitalNumerique\AutodiagBundle\Service\Import\ChapterWriter;
use HopitalNumerique\AutodiagBundle\Service\Import\QuestionWriter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AutodiagController extends Controller
{
    /**
     * Grid that list autodiag Models
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        $grid = new ModelGrid($this->container);

        return $grid->render('HopitalNumeriqueAutodiagBundle:Model:list.html.twig');
    }

    public function createAction(Request $request)
    {
        return $this->editAction($request, new Autodiag);
    }

    /**
     * Edit autodiag model
     *
     * @param Request $request
     * @param Autodiag $autodiag
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, Autodiag $autodiag)
    {
        $domain = new AutodiagAdminUpdate();
        $domain->setAutodiag($autodiag);
        $criticite = $this->get('autodiag.attribute_builder_provider')->getBuilder('criticite')->getPreset($autodiag) ?: new Autodiag\Preset($autodiag, 'criticite');
        $maitrise = $this->get('autodiag.attribute_builder_provider')->getBuilder('maitrise')->getPreset($autodiag) ?: new Autodiag\Preset($autodiag, 'maitrise');
        $domain->addPreset($criticite);
        $domain->addPreset($maitrise);

        $form = $this->createForm(new AutodiagAdminUpdateType($this->get('autodiag.attribute_builder_provider')), $domain);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($domain->getAutodiag());
            $manager->flush();

            foreach ($domain->getPresets() as $preset) {
                $this->get('autodiag.attribute_builder_provider')
                    ->getBuilder($preset->getType())
                    ->setPreset($domain->getAutodiag(), $preset->getPreset());
            }

            $this->addFlash('success', 'Autodiag enregistré');
            return $this->redirectToRoute('hopitalnumerique_autodiag_edit', [
                'id' => $autodiag->getId()
            ]);
        }

        return $this->render('@HopitalNumeriqueAutodiag/Autodiag/Edit/general.html.twig', [
            'form' => $form->createView(),
            'model' => $autodiag,
        ]);
    }

    public function surveyEditAction(Request $request, Autodiag $autodiag)
    {
        $import = new AutodiagAdminFileImport();
        $form = $this->createForm(FileImportType::class, $import);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // Import chapter
            $chapterImporter = $this->get('autodiag.import.chapter');
            $chapterImporter->setWriter(
                new ChapterWriter($this->getDoctrine()->getManager(), $autodiag)
            );
            $chapterProgress = $chapterImporter->import($import->getFile());

            // Import questions
            $questionImporter = $this->get('autodiag.import.question');
            $questionImporter->setWriter(
                new QuestionWriter(
                    $this->getDoctrine()->getManager(),
                    $autodiag,
                    $this->get('autodiag.attribute_builder_provider')
                )
            );
            $questionProgress = $questionImporter->import($import->getFile());

            dump($chapterProgress);
            dump($questionProgress);
            die;
        }

        return $this->render('HopitalNumeriqueAutodiagBundle:Autodiag/Edit:survey.html.twig', [
            'form' => $form->createView(),
            'model' => $autodiag,
        ]);
    }
}
