<?php

namespace HopitalNumerique\AutodiagBundle\Model;

use Doctrine\ORM\EntityManager;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\History;
use HopitalNumerique\AutodiagBundle\Exception\Import\FileNotFoundException;
use HopitalNumerique\AutodiagBundle\Service\Attribute\AttributeBuilderProvider;
use HopitalNumerique\AutodiagBundle\Service\Import\AlgorithmWriter;
use HopitalNumerique\AutodiagBundle\Service\Import\ChapterWriter;
use HopitalNumerique\AutodiagBundle\Service\Import\QuestionWriter;
use HopitalNumerique\AutodiagBundle\Service\Import\RestitutionWriter;
use Nodevo\Component\Import\DataImporter;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use HopitalNumerique\AutodiagBundle\Event\AutodiagUpdatePublishedEvent;
use HopitalNumerique\AutodiagBundle\Events;

class AutodiagFileImportHandler
{
    /** @var EntityManager */
    protected $manager;

    /** @var SessionInterface */
    protected $session;

    /** @var AttributeBuilderProvider */
    protected $attributeBuilder;

    /** @var ContainerInterface */
    protected $container;

    /** @var ValidatorInterface */
    protected $validator;

    protected $translator;

    /**
     * @var EventDispatcherInterface $eventDispatcher
     */
    protected $eventDispatcher;

    /**
     * Handler constructor.
     *
     * @param EntityManager            $em
     * @param SessionInterface         $session
     * @param AttributeBuilderProvider $attributeBuilder
     * @param TokenStorageInterface    $tokenStorage
     * @param ValidatorInterface       $validator
     * @param Translator               $translator
     * @param EventDispatcherInterface $eventDispatcher
     *
     * @internal param ContainerInterface $container
     */
    public function __construct(
        EntityManager $em,
        SessionInterface $session,
        AttributeBuilderProvider $attributeBuilder,
        TokenStorageInterface $tokenStorage,
        ValidatorInterface $validator,
        Translator $translator,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->manager = $em;
        $this->session = $session;
        $this->attributeBuilder = $attributeBuilder;
        $this->tokenStorage = $tokenStorage;
        $this->validator = $validator;
        $this->translator = $translator;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param AutodiagFileImport $model
     * @param DataImporter       $chapterImporter
     * @param DataImporter       $questionImporter
     *
     * @throws FileNotFoundException
     */
    public function handleSurveyImport(
        AutodiagFileImport $model,
        DataImporter $chapterImporter,
        DataImporter $questionImporter
    ) {
        if (null === $model->getFile()) {
            throw new FileNotFoundException();
        }

        $autodiag = $model->getAutodiag();

        // Import chapter
        $chapterImporter->setWriter(
            new ChapterWriter($this->manager, $autodiag, $this->validator)
        );
        $chapterProgress = $chapterImporter->import($model->getFile());
        $this->session->set('survey_import_progress_chapter', $chapterProgress);

        // Import questions
        $questionImporter->setWriter(
            new QuestionWriter(
                $this->manager,
                $autodiag,
                $this->attributeBuilder,
                $this->validator,
                $this->translator
            )
        );
        $questionProgress = $questionImporter->import($model->getFile());
        $this->session->set('survey_import_progress_question', $questionProgress);

        if (!$chapterProgress->hasErrors() && !$questionProgress->hasErrors()) {
            // Save history
            $user = $this->tokenStorage->getToken()->getUser();
            $history = History::createSurveyImport(
                $autodiag,
                $user,
                $model->getNotifyUpdate(),
                $model->getUpdateReason()
            );
            $this->manager->persist($history);
        }

        $this->updateImportPublicUpdatedDate($model);

        $this->manager->flush();
    }

    /**
     * @return mixed
     */
    public function getChapterProgress()
    {
        $surveyProgress = $this->session->get('survey_import_progress_chapter');
        if (null !== $surveyProgress) {
            $this->session->remove('survey_import_progress_chapter');
        }

        return $surveyProgress;
    }

    /**
     * @return mixed
     */
    public function getQuestionProgress()
    {
        $questionProgress = $this->session->get('survey_import_progress_question');
        if (null !== $questionProgress) {
            $this->session->remove('survey_import_progress_question');
        }

        return $questionProgress;
    }

    /**
     * @return mixed
     */
    public function getAlgorithmProgress()
    {
        $progress = $this->session->get('algorithm_import_progress');
        if (null !== $progress) {
            $this->session->remove('algorithm_import_progress');
        }

        return $progress;
    }

    /**
     * @return mixed
     */
    public function getRestitutionProgress()
    {
        $progress = $this->session->get('restitution_import_progress');
        if (null !== $progress) {
            $this->session->remove('restitution_import_progress');
        }

        return $progress;
    }

    /**
     * @param Autodiag $autodiag
     * @param bool     $notify
     * @param string   $reason
     */
    public function handleNotification(Autodiag $autodiag, $notify, $reason)
    {
        // Save history
        $user = $this->tokenStorage->getToken()->getUser();
        $history = History::createGeneralNotification($autodiag, $user, $notify, $reason);
        $this->manager->persist($history);

        $this->updatePublicUpdatedDate($autodiag, new \DateTime(), $reason);

        $this->manager->flush();
    }

    /**
     * @param AutodiagFileImport $model
     * @param DataImporter $algorithmImporter
     *
     * @throws \Exception
     */
    public function handleAlgorithmImport(AutodiagFileImport $model, DataImporter $algorithmImporter)
    {
        if (null === $model->getFile()) {
            throw new FileNotFoundException();
        }

        $autodiag = $model->getAutodiag();

        // Import chapter
        $algorithmImporter->setWriter(
            new AlgorithmWriter($this->manager, $autodiag)
        );
        $algorithmProgress = $algorithmImporter->import($model->getFile());
        $this->session->set('algorithm_import_progress', $algorithmProgress);

        // Save history
        $user = $this->tokenStorage->getToken()->getUser();
        $history = History::createAlgorithmImport(
            $autodiag,
            $user,
            $model->getNotifyUpdate(),
            $model->getUpdateReason()
        );
        $this->manager->persist($history);

        $this->updateImportPublicUpdatedDate($model);

        $this->manager->flush();
    }

    /**
     * @param AutodiagFileImport $model
     * @param DataImporter $restitutionImporter
     *
     * @throws FileNotFoundException
     */
    public function handleRestitutionImport(AutodiagFileImport $model, DataImporter $restitutionImporter)
    {
        if (null === $model->getFile()) {
            throw new FileNotFoundException();
        }

        $autodiag = $model->getAutodiag();

        $restitutionImporter->setWriter(
            new RestitutionWriter($this->manager, $autodiag, $this->validator)
        );
        $importProgress = $restitutionImporter->import($model->getFile());
        $this->session->set('restitution_import_progress', $importProgress);

        // Save history
        $user = $this->tokenStorage->getToken()->getUser();
        $history = History::createRestitutionImport(
            $autodiag,
            $user,
            $model->getNotifyUpdate(),
            $model->getUpdateReason()
        );
        $this->manager->persist($history);

        $this->updateImportPublicUpdatedDate($model);

        $this->manager->flush();
    }

    /**
     * @param AutodiagFileImport $model
     */
    protected function updateImportPublicUpdatedDate(AutodiagFileImport $model)
    {
        // Update public updated date if notify checked
        if ($model->getNotifyUpdate()) {
            $this->updatePublicUpdatedDate($model->getAutodiag(), new \DateTime(), $model->getUpdateReason());
        }
    }

    /**
     * Set public updated date and throw related event.
     *
     * @param Autodiag  $autodiag
     * @param \DateTime $date
     * @param string    $reason
     */
    protected function updatePublicUpdatedDate(Autodiag $autodiag, \DateTime $date, $reason)
    {
        $autodiag->setPublicUpdatedDate($date);

        $event = new AutodiagUpdatePublishedEvent($autodiag, $reason);
        $this->eventDispatcher->dispatch(Events::AUTODIAG_UPDATE_PUBLISHED, $event);
    }
}
