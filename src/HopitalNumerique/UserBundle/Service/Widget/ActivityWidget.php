<?php

namespace HopitalNumerique\UserBundle\Service\Widget;

use HopitalNumerique\AutodiagBundle\Repository\AutodiagEntryRepository;
use HopitalNumerique\ForumBundle\Repository\PostRepository;
use HopitalNumerique\NewAccountBundle\Model\Widget\Widget;
use HopitalNumerique\ObjetBundle\Repository\CommentaireRepository;
use HopitalNumerique\ObjetBundle\Repository\ConsultationRepository;
use HopitalNumerique\ObjetBundle\Repository\NoteRepository;
use HopitalNumerique\RechercheBundle\Repository\RequeteRepository;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\WidgetAbstract;
use Nodevo\MailBundle\Repository\RecommendationMailLogRepository;

/**
 * Class Activity
 */
class ActivityWidget extends WidgetAbstract
{
    /**
     * @var RequeteRepository $requestRepository
     */
    protected $requestRepository;

    /**
     * @var ConsultationRepository $consultationRepository
     */
    protected $consultationRepository;

    /**
     * @var PostRepository $postRepository
     */
    protected $postRepository;

    /**
     * @var NoteRepository $noteRepository
     */
    protected $noteRepository;

    /**
     * @var CommentaireRepository $commentsRepository
     */
    protected $commentsRepository;

    /**
     * @var AutodiagEntryRepository $autodiagEntryRepository
     */
    protected $autodiagEntryRepository;

    /**
     * @var RecommendationMailLogRepository $recommendationMailLogRepository
     */
    protected $recommendationMailLogRepository;

    /**
     * @param RequeteRepository $requestRepository
     * @param ConsultationRepository $consultationRepository
     * @param PostRepository $postRepository
     * @param NoteRepository $noteRepository
     * @param CommentaireRepository $commentsRepository
     * @param AutodiagEntryRepository $autodiagEntryRepository
     * @param RecommendationMailLogRepository $recommendationMailLogRepository
     */
    public function setRepositories(
        RequeteRepository $requestRepository,
        ConsultationRepository $consultationRepository,
        PostRepository $postRepository,
        NoteRepository $noteRepository,
        CommentaireRepository $commentsRepository,
        AutodiagEntryRepository $autodiagEntryRepository,
        RecommendationMailLogRepository $recommendationMailLogRepository
    ) {
        $this->requestRepository = $requestRepository;
        $this->consultationRepository = $consultationRepository;
        $this->postRepository = $postRepository;
        $this->noteRepository = $noteRepository;
        $this->commentsRepository = $commentsRepository;
        $this->autodiagEntryRepository = $autodiagEntryRepository;
        $this->recommendationMailLogRepository = $recommendationMailLogRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getWidget()
    {
        $user = $this->tokenStorage->getToken()->getUser();

        $html = $this->twig->render('HopitalNumeriqueUserBundle:widget:activity.html.twig', [
            'savedSearchesCount' => $this->requestRepository->countSavedRequestForUser($user),
            'objectsReadedCount' => $this->consultationRepository->countViewsForUser($user),
            'forumPostCount' => $this->postRepository->countPostForUser($user),
            'commentsCount' => $this->noteRepository->countForUser($user) + $this->commentsRepository->countForUser($user),
            'autodiagsCount' => $this->autodiagEntryRepository->countActiveForUser($user),
            'recommendationsLogCount' => $this->recommendationMailLogRepository->countForUser($user),
        ]);

        $title = $this->translator->trans('activity.title', [], 'widget');

        return new Widget('user-activity', $title, $html);
    }
}
