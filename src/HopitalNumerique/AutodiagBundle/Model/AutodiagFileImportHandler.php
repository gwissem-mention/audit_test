<?php
namespace HopitalNumerique\AutodiagBundle\Model;

use Doctrine\ORM\EntityManager;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\History;
use HopitalNumerique\AutodiagBundle\Service\Attribute\AttributeBuilderProvider;
use HopitalNumerique\AutodiagBundle\Service\Import\ChapterWriter;
use HopitalNumerique\AutodiagBundle\Service\Import\QuestionWriter;
use Nodevo\Component\Import\DataImporter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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

    /**
     * Handler constructor
     *
     * @param EntityManager $em
     * @param SessionInterface $session
     * @param AttributeBuilderProvider $attributeBuilder
     * @param TokenStorageInterface $tokenStorage
     * @internal param ContainerInterface $container
     */
    public function __construct(
        EntityManager $em,
        SessionInterface $session,
        AttributeBuilderProvider $attributeBuilder,
        TokenStorageInterface $tokenStorage
    ) {
        $this->manager = $em;
        $this->session = $session;
        $this->attributeBuilder = $attributeBuilder;
        $this->tokenStorage = $tokenStorage;
    }

    public function handleSurveyImport(AutodiagFileImport $model, DataImporter $chapterImporter, DataImporter $questionImporter)
    {
        $autodiag = $model->getAutodiag();
        if (null !== $model->getFile()) {
            // Import chapter
            $chapterImporter->setWriter(
                new ChapterWriter($this->manager, $autodiag)
            );
            $chapterProgress = $chapterImporter->import($model->getFile());
            $this->session->set('survey_import_progress_chapter', $chapterProgress);

            // Import questions
            $questionImporter->setWriter(
                new QuestionWriter($this->manager, $autodiag, $this->attributeBuilder)
            );
            $questionProgress = $questionImporter->import($model->getFile());
            $this->session->set('survey_import_progress_question', $questionProgress);

            // Save history
            $user = $this->tokenStorage->getToken()->getUser();
            $history = History::createSurveyImport($autodiag, $user);
            $this->manager->persist($history);
        }

        // Update public updated date if notify checked
        if ($model->getNotifyUpdate()) {
            $autodiag->setPublicUpdatedDate(new \DateTime());
        }

        $this->manager->flush();
    }

    public function getChapterProgress()
    {
        $surveyProgress = $this->session->get('survey_import_progress_chapter');
        if (null !== $surveyProgress) {
            $this->session->remove('survey_import_progress_chapter');
        }
        return $surveyProgress;
    }

    public function getQuestionProgress()
    {
        $questionProgress = $this->session->get('survey_import_progress_question');
        if (null !== $questionProgress) {
            $this->session->remove('survey_import_progress_question');
        }
        return $questionProgress;
    }

    public function handleAlgorithmImport(AutodiagFileImport $model)
    {
        $autodiag = $model->getAutodiag();
        
        // Update public updated date if notify checked
        if ($model->getNotifyUpdate()) {
            $autodiag->setPublicUpdatedDate(new \DateTime());
        }

        $this->manager->flush();
    }
}
