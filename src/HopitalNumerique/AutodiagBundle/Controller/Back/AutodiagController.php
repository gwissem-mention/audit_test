<?php

namespace HopitalNumerique\AutodiagBundle\Controller\Back;

use HopitalNumerique\AutodiagBundle\Entity\AutodiagEntry;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\AutodiagBundle\Exception\Import\FileNotFoundException;
use HopitalNumerique\AutodiagBundle\Model\AutodiagUpdate;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Preset;
use HopitalNumerique\AutodiagBundle\Form\Type\Autodiag\AutodiagUpdateType;
use HopitalNumerique\AutodiagBundle\Form\Type\Autodiag\FileImportType;
use HopitalNumerique\AutodiagBundle\Grid\AutodiagGrid;
use HopitalNumerique\AutodiagBundle\Model\FileImport\Algorithm;
use HopitalNumerique\AutodiagBundle\Model\FileImport\Restitution;
use HopitalNumerique\AutodiagBundle\Model\FileImport\Survey;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AutodiagController extends Controller
{
    /**
     * Grid that list autodiag Models.
     *
     * @return Response
     */
    public function listAction()
    {
        $grid = new AutodiagGrid($this->container);

        return $grid->render('@HopitalNumeriqueAutodiag/Autodiag/list.html.twig');
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function createAction(Request $request)
    {
        return $this->editAction($request, new Autodiag());
    }

    /**
     * Edit autodiag model.
     *
     * @param Request  $request
     * @param Autodiag $autodiag
     *
     * @return RedirectResponse|Response
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

        $form = $this->createForm(AutodiagUpdateType::class, $domain, [
            'user' => $this->getUser(),
            'edit' => false,
        ]);

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

            $importHandler = $this->get('autodiag.import.handler');

            $importHandler->handleNotification(
                $autodiag,
                $form->get('notify_update')->getData(),
                $form->get('reason')->getData()
            );

            $this->addFlash('success', $this->get('translator')->trans('ad.back.saved'));

            return $this->redirectToRoute('hopitalnumerique_autodiag_edit', [
                'id' => $autodiag->getId(),
            ]);
        }

        $updates = $this->get('autodiag.history.reader')->getHistoryByAutodiag($autodiag);

        return $this->render('@HopitalNumeriqueAutodiag/Autodiag/Edit/general.html.twig', [
            'form' => $form->createView(),
            'model' => $autodiag,
            'updates' => $updates
        ]);
    }

    /**
     * Import survey data.
     *
     * @param Request  $request
     * @param Autodiag $autodiag
     *
     * @return RedirectResponse|Response
     *
     * @throws \Exception
     */
    public function surveyEditAction(Request $request, Autodiag $autodiag)
    {
        $importHandler = $this->get('autodiag.import.handler');
        $import = new Survey($autodiag);
        $form = $this->createForm(FileImportType::class, $import);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $notify = $form->get('notify_update')->getData();
                $reason = $form->get('reason')->getData();

                $import->setNotifyUpdate($notify);
                $import->setUpdateReason($reason);

                try {
                    $importHandler->handleSurveyImport(
                        $import,
                        $this->get('autodiag.import.chapter'),
                        $this->get('autodiag.import.question')
                    );
                    $this->get('autodiag.score_calculator')->defetAutodiagScore($autodiag);
                    $this->addFlash('success', $this->get('translator')->trans('ad.import.success'));
                } catch (FileNotFoundException $exception) {
                    $this->addFlash('danger', $this->get('translator')->trans('ad.import.error.file_not_found'));
                }

                return $this->redirectToRoute('hopitalnumerique_autodiag_edit_survey', [
                    'id' => $autodiag->getId(),
                ]);
            } else {
                foreach ($form->getErrors() as $error) {
                    $this->addFlash('danger', $error->getMessage());
                }
            }
        }

        return $this->render('HopitalNumeriqueAutodiagBundle:Autodiag/Edit:survey.html.twig', [
            'form' => $form->createView(),
            'model' => $autodiag,
            'history' => $this->get('autodiag.history.reader')->getHistory($autodiag, Autodiag\History::HISTORY_ENTRY_SURVEY),
            'chapterProgress' => $importHandler->getChapterProgress(),
            'questionProgress' => $importHandler->getQuestionProgress(),
        ]);
    }

    /**
     * @param Request  $request
     * @param Autodiag $autodiag
     *
     * @ParamConverter()
     *
     * @return RedirectResponse|Response
     */
    public function algorithmEditAction(Request $request, Autodiag $autodiag)
    {
        $importHandler = $this->get('autodiag.import.handler');
        $import = new Algorithm($autodiag);
        $form = $this->createForm(FileImportType::class, $import);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $notify = $form->get('notify_update')->getData();
                $reason = $form->get('reason')->getData();

                $import->setNotifyUpdate($notify);
                $import->setUpdateReason($reason);

                try {
                    $importHandler->handleAlgorithmImport($import, $this->get('autodiag.import.algorithm'));
                    $this->addFlash('success', $this->get('translator')->trans('ad.import.success'));
                } catch (FileNotFoundException $exception) {
                    $this->addFlash('danger', $this->get('translator')->trans('ad.import.error.file_not_found'));
                }

                return $this->redirectToRoute('hopitalnumerique_autodiag_edit_algorithm', [
                    'id' => $autodiag->getId(),
                ]);
            } else {
                foreach ($form->getErrors() as $error) {
                    $this->addFlash('danger', $error->getMessage());
                }
            }
        }

        return $this->render('HopitalNumeriqueAutodiagBundle:Autodiag/Edit:algorithm.html.twig', [
            'form' => $form->createView(),
            'model' => $autodiag,
            'history' => $this->get('autodiag.history.reader')->getHistory($autodiag, Autodiag\History::HISTORY_ENTRY_ALGORITHM),
            'progress' => $importHandler->getAlgorithmProgress(),
        ]);
    }

    /**
     * @param Request  $request
     * @param Autodiag $autodiag
     *
     * @ParamConverter("autodiag")
     *
     * @return RedirectResponse|Response
     */
    public function restitutionEditAction(Request $request, Autodiag $autodiag)
    {
        $importHandler = $this->get('autodiag.import.handler');
        $import = new Restitution($autodiag);
        $form = $this->createForm(FileImportType::class, $import);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $notify = $form->get('notify_update')->getData();
                $reason = $form->get('reason')->getData();

                $import->setNotifyUpdate($notify);
                $import->setUpdateReason($reason);

                try {
                    $importHandler->handleRestitutionImport($import, $this->get('autodiag.import.restitution'));
                    $this->addFlash('success', $this->get('translator')->trans('ad.import.success'));
                } catch (FileNotFoundException $exception) {
                    $this->addFlash('danger', $this->get('translator')->trans('ad.import.error.file_not_found'));
                }

                return $this->redirectToRoute('hopitalnumerique_autodiag_edit_restitution', [
                    'id' => $autodiag->getId(),
                ]);
            } else {
                foreach ($form->getErrors() as $error) {
                    $this->addFlash('danger', $error->getMessage());
                }
            }
        }

        return $this->render('@HopitalNumeriqueAutodiag/Autodiag/Edit/resitution.html.twig', [
            'form' => $form->createView(),
            'model' => $autodiag,
            'history' => $this->get('autodiag.history.reader')->getHistory($autodiag, Autodiag\History::HISTORY_ENTRY_RESTITUTION),
            'progress' => $importHandler->getRestitutionProgress(),
        ]);
    }

    /**
     * @param Autodiag $autodiag
     *
     * @return Response
     */
    public function autodiagEntriesAction(Autodiag $autodiag)
    {
        $grid = $this->get('autodiag.entries.grid');
        $grid->setAutodiag($autodiag);

        return $grid->render('@HopitalNumeriqueAutodiag/Autodiag/Edit/autodiag_entries.html.twig', [
            'model' => $autodiag,
        ]);
    }

    /**
     * @param AutodiagEntry $entry
     *
     * @return Response
     */
    public function autodiagEntryShowAction(AutodiagEntry $entry)
    {
        /** @var Synthesis $synthesis */
        $synthesis = $entry->getSynthesis();
        /** @var Autodiag $autodiag */
        $autodiag = $synthesis->getAutodiag();

        $items = [];

        /** @var Autodiag\Container\Chapter $chapter */
        foreach ($autodiag->getChapters() as $chapter) {
            $items[$chapter->getId()] = $this->get('autodiag.result.builder')->build($chapter, $synthesis);
        }

        $questionnaireReponses = null;

        if ($autodiag->getQuestionnaire() && $entry->getUser()) {
            $questionnaireReponses = $this
                ->get('hopitalnumerique_questionnaire.manager.reponse')
                ->reponsesByQuestionnaireByUser(
                    $autodiag->getQuestionnaire()->getId(),
                    $entry->getUser()->getId()
                )
            ;
        }

        return $this->render('@HopitalNumeriqueAutodiag/Autodiag/Edit/AutodiagEntry/show.html.twig', [
            'entry' => $entry,
            'model' => $autodiag,
            'items' => $items,
            'questionnaire' => $questionnaireReponses,
        ]);
    }

    /**
     * Action appelée dans le plugin "Outil" pour tinymce.
     *
     * @return Response
     */
    public function autodiagPluginsAction()
    {
        $autodiags = $this->get('autodiag.repository.autodiag')->findAll();

        return $this->render('HopitalNumeriqueAutodiagBundle:Autodiag:autodiag_plugin.html.twig', [
            'outils' => $autodiags,
            'texte' => $this->get('request')->request->get('texte'),
        ]);
    }

}
