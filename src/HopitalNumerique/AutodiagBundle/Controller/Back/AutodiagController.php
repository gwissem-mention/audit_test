<?php

namespace HopitalNumerique\AutodiagBundle\Controller\Back;

use HopitalNumerique\AutodiagBundle\Entity\Restitution;
use HopitalNumerique\AutodiagBundle\Model\AutodiagFileImport;
use HopitalNumerique\AutodiagBundle\Model\AutodiagUpdate;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Preset;
use HopitalNumerique\AutodiagBundle\Form\Type\Autodiag\AutodiagUpdateType;
use HopitalNumerique\AutodiagBundle\Form\Type\Autodiag\FileImportType;
use HopitalNumerique\AutodiagBundle\Grid\ModelGrid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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

        return $grid->render('@HopitalNumeriqueAutodiag/Autodiag/list.html.twig');
    }

    public function createAction(Request $request)
    {
        return $this->editAction($request, new Autodiag());
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

    /**
     * Import survey data
     *
     * @param Request $request
     * @param Autodiag $autodiag
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function surveyEditAction(Request $request, Autodiag $autodiag)
    {
        $importHandler = $this->get('autodiag.import.handler');
        $import = new AutodiagFileImport($autodiag);
        $form = $this->createForm(FileImportType::class, $import);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $importHandler->handleSurveyImport(
                $import,
                $this->get('autodiag.import.chapter'),
                $this->get('autodiag.import.question')
            );

            $this->addFlash('success', 'ad.autodiag.import.success');

            return $this->redirectToRoute('hopitalnumerique_autodiag_edit_survey', [
                'id' => $autodiag->getId()
            ]);
        }

        return $this->render('HopitalNumeriqueAutodiagBundle:Autodiag/Edit:survey.html.twig', [
            'form' => $form->createView(),
            'model' => $autodiag,
            'history' => $this->get('autodiag.history.reader')->getHistory(Autodiag\History::HISTORY_ENTRY_SURVEY),
            'chapterProgress' => $importHandler->getChapterProgress(),
            'questionProgress' => $importHandler->getQuestionProgress(),
        ]);
    }

    /**
     * @param Request $request
     * @param Autodiag $autodiag
     *
     * @ParamConverter()
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function algorithmEditAction(Request $request, Autodiag $autodiag)
    {
        $importHandler = $this->get('autodiag.import.handler');
        $import = new AutodiagFileImport($autodiag);
        $form = $this->createForm(FileImportType::class, $import);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $importHandler->handleAlgorithmImport($import, $this->get('autodiag.import.algorithm'));
            $this->addFlash('success', 'ad.autodiag.import.success');

            return $this->redirectToRoute('hopitalnumerique_autodiag_edit_algorithm', [
                'id' => $autodiag->getId()
            ]);
        }
        return $this->render('HopitalNumeriqueAutodiagBundle:Autodiag/Edit:algorithm.html.twig', [
            'form' => $form->createView(),
            'model' => $autodiag,
            'history' => $this->get('autodiag.history.reader')->getHistory(Autodiag\History::HISTORY_ENTRY_ALGORITHM),
            'progress' => $importHandler->getAlgorithmProgress(),
        ]);
    }

    /**
     * @param Request $request
     * @param Autodiag $autodiag
     *
     * @ParamConverter("autodiag")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function restitutionEditAction(Request $request, Autodiag $autodiag)
    {
        $importHandler = $this->get('autodiag.import.handler');
        $import = new AutodiagFileImport($autodiag);
        $form = $this->createForm(FileImportType::class, $import);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $importHandler->handleRestitutionImport($import, $this->get('autodiag.import.algorithm'));

            $this->addFlash('success', 'ad.autodiag.import.success');

            return $this->redirectToRoute('hopitalnumerique_autodiag_edit_restitution', [
                'id' => $autodiag->getId()
            ]);
        }
        return $this->render('@HopitalNumeriqueAutodiag/Autodiag/Edit/resitution.html.twig', [
            'form' => $form->createView(),
            'model' => $autodiag,
            'history' => $this->get('autodiag.history.reader')->getHistory(Autodiag\History::HISTORY_ENTRY_RESTITUTION),
            'progress' => $importHandler->getRestitutionProgress(),
        ]);
    }
}
