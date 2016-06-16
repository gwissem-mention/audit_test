<?php

namespace HopitalNumerique\AutodiagBundle\Controller\Back;

use HopitalNumerique\AutodiagBundle\Model\AutodiagFileImport;
use HopitalNumerique\AutodiagBundle\Model\AutodiagUpdate;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Preset;
use HopitalNumerique\AutodiagBundle\Form\Type\Autodiag\AutodiagUpdateType;
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
        $attributeBuilderProvider = $this->get('autodiag.attribute_builder_provider');

        $domain = new AutodiagUpdate(
            $autodiag,
            [
                $attributeBuilderProvider->getBuilder('criticite')->getPreset($autodiag) ?: new Preset($autodiag, 'criticite'),
                $attributeBuilderProvider->getBuilder('maitrise')->getPreset($autodiag) ?: new Preset($autodiag, 'maitrise'),
            ]
        );

        $form = $this->createForm(AutodiagUpdateType::class, $domain);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($domain->getAutodiag());
            $manager->flush();

            foreach ($domain->getPresets() as $preset) {
                $attributeBuilderProvider
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
        $session = $this->get('session');
        $import = new AutodiagFileImport();
        $form = $this->createForm(FileImportType::class, $import);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Import chapter
            $chapterImporter = $this->get('autodiag.import.chapter');
            $chapterImporter->setWriter(
                new ChapterWriter(
                    $this->get('doctrine.orm.entity_manager'),
                    $autodiag
                )
            );
            $chapterProgress = $chapterImporter->import($import->getFile());
            $session->set('survey_import_progress_chapter', $chapterProgress);

            // Import questions
            $questionImporter = $this->get('autodiag.import.question');
            $questionImporter->setWriter(
                new QuestionWriter(
                    $this->get('doctrine.orm.entity_manager'),
                    $autodiag,
                    $this->get('autodiag.attribute_builder_provider')
                )
            );
            $questionProgress = $questionImporter->import($import->getFile());
            $session->set('survey_import_progress_question', $questionProgress);

            $this->addFlash('success', 'ad.autodiag.import.success');

            return $this->redirectToRoute('hopitalnumerique_autodiag_survey', [
                'id' => $autodiag->getId()
            ]);
        }

        $chapterProgress = $session->get('survey_import_progress_chapter');
        if (null !== $chapterProgress) {
            $session->remove('survey_import_progress_chapter');
        }

        $questionProgress = $session->get('survey_import_progress_question');
        if (null !== $questionProgress) {
            $session->remove('survey_import_progress_question');
        }

        return $this->render('HopitalNumeriqueAutodiagBundle:Autodiag/Edit:survey.html.twig', [
            'form' => $form->createView(),
            'model' => $autodiag,
            'chapterProgress' => $chapterProgress,
            'questionProgress' => $questionProgress,
        ]);
    }
}
