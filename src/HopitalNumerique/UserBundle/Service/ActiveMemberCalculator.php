<?php

namespace HopitalNumerique\UserBundle\Service;

use CCDNForum\ForumBundle\Model\Component\Repository\PostRepository;
use HopitalNumerique\ObjetBundle\Repository\CommentaireRepository;
use HopitalNumerique\ObjetBundle\Repository\NoteRepository;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\UserBundle\Repository\UserRepository;
use Nodevo\MailBundle\Repository\RecommendationMailLogRepository;

/**
 * Class ActiveMemberCalculator.
 */
class ActiveMemberCalculator
{
    /** @var CommentaireRepository $commentaireRepository */
    protected $commentaireRepository;
    /** @var NoteRepository $noteRepository */
    protected $noteRepository;
    /** @var PostRepository $postRepository */
    protected $postRepository;

    /**
     * @var RecommendationMailLogRepository $recommendationMailLogRepository
     */
    protected $recommendationMailLogRepository;

    /**
     * @var UserRepository $userRepository
     */
    protected $userRepository;

    /**
     * @var array $membersActivity
     */
    private $membersActivity;

    /**
     * ActiveMemberCalculator constructor.
     *
     * @param CommentaireRepository $commentaireRepository
     * @param NoteRepository        $noteRepository
     * @param PostRepository        $postRepository
     * @param RecommendationMailLogRepository $recommendationMailLogRepository
     * @param UserRepository $userRepository
     */
    public function __construct(
        CommentaireRepository $commentaireRepository,
        NoteRepository $noteRepository,
        PostRepository $postRepository,
        RecommendationMailLogRepository $recommendationMailLogRepository,
        UserRepository $userRepository
    ) {
        $this->commentaireRepository = $commentaireRepository;
        $this->noteRepository = $noteRepository;
        $this->postRepository = $postRepository;
        $this->recommendationMailLogRepository = $recommendationMailLogRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @param $users
     *
     * @deprecated Use getMemberActivity multiple times instead
     * @return array
     */
    public function getActiveMembers($users)
    {
        $activeMembers = $this->getAllActiveMembers();

        $activeMembersFiltered = [];
        foreach ($users as $user) {
            if (true === $activeMembers[$user->getId()]['activeUser']) {
                $activeMembersFiltered[$user->getId()] = $activeMembers[$user->getId()];
            }
        }

        return $activeMembersFiltered;
    }

    /**
     * @return array
     */
    public function getAllActiveMembers()
    {
        if (!is_null($this->membersActivity)) {
            return $this->membersActivity;
        }

        $usersId = $this->userRepository->getUsersId();
        $visitsCount = $this->userRepository->getVisitsCountGroupedByUser();
        $comments = $this->commentaireRepository->countGroupByUser();
        $posts = $this->postRepository->countGroupByUser();
        $notes = $this->noteRepository->countGroupByUser();
        $recommendations = $this->recommendationMailLogRepository->countGroupByUser();

        $activeMembers = array_map(function ($user) use ($visitsCount, $comments, $posts, $notes, $recommendations) {
            $nbComment = isset($comments[$user['id']]) ? $comments[$user['id']] : 0;
            $nbPost = isset($posts[$user['id']]) ? $posts[$user['id']] : 0;
            $nbNote = isset($notes[$user['id']]) ? $notes[$user['id']] : 0;
            $recommendationsCount = isset($recommendations[$user['id']]) ? $recommendations[$user['id']] : 0;
            $score = $nbComment + $nbNote + $nbPost + $recommendationsCount;

            return [
                'idUser' => $user['id'],
                'nbComment' => $nbComment,
                'visitCount' => $visitsCount[$user['id']]['visitCount'],
                'nbPost' => $nbPost,
                'nbNote' => $nbNote,
                'recommendationsCount' => $recommendationsCount,
                'score' => $score,
                'activeUser' => false,
            ];
        }, $usersId);

        // Tri par score croissant
        usort($activeMembers, function ($a, $b) {
            return $a['score'] > $b['score'];
        });

        // Top 20% setted as active user
        $activeMembersCount = count($activeMembers);
        for ($i = $activeMembersCount - ($activeMembersCount / 5); $i < $activeMembersCount; $i++) {
            $activeMembers[$i]['activeUser'] = true;
        }

        // Change la clé du tableau pour qu'elle corresponde à l'id de l'utilisateur
        $activeMembersWithNewKey = $activeMembers;
        foreach ($activeMembers as $activeMember) {
            $activeMembersWithNewKey[$activeMember['idUser']] = $activeMember;
        }

        $this->membersActivity = $activeMembersWithNewKey;

        return $activeMembersWithNewKey;
    }

    /**
     * @param User $user
     *
     * @return array
     */
    public function getMemberActivity(User $user) {
        return $this->getAllActiveMembers()[$user->getId()];
    }
}
