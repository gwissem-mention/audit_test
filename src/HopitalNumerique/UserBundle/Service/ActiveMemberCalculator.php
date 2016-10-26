<?php
namespace HopitalNumerique\UserBundle\Service;

use CCDNForum\ForumBundle\Model\Component\Repository\PostRepository;
use HopitalNumerique\ObjetBundle\Repository\CommentaireRepository;
use HopitalNumerique\ObjetBundle\Repository\NoteRepository;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * Class ActiveMemberCalculator
 */
class ActiveMemberCalculator
{
    /** @var CommentaireRepository $commentaireRepository */
    protected $commentaireRepository;
    /** @var NoteRepository $noteRepository */
    protected $noteRepository;
    /** @var PostRepository $postRepository */
    protected $postRepository;

    public function __construct(
        CommentaireRepository $commentaireRepository,
        NoteRepository $noteRepository,
        PostRepository $postRepository
    ) {
        $this->commentaireRepository = $commentaireRepository;
        $this->noteRepository = $noteRepository;
        $this->postRepository = $postRepository;
    }

    public function getActiveMembers($users)
    {
        $activeMembers = [];
        $comments = $this->commentaireRepository->countGroupByUser();
        $posts = $this->postRepository->countGroupByUser();
        $notes = $this->noteRepository->countGroupByUser();

        /** @var User $user */
        foreach ($users as $user) {
            $nbComment = isset($comments[$user->getId()]) ? $comments[$user->getId()] : 0;
            $nbPost = isset($posts[$user->getId()]) ? $posts[$user->getId()] : 0;
            $nbNote = isset($notes[$user->getId()]) ? $notes[$user->getId()] : 0;
            $score = $nbComment + $nbNote + $nbPost;

            $activeMembers[] = [
                'idUser'    => $user->getId(),
                'nbComment' => $nbComment,
                'nbVisite'  => $user->getNbVisites(),
                'nbPost'    => $nbPost,
                'nbNote'    => $nbNote,
                'score'     => $score,
            ];
        }

        // Tri par score croissant
        usort($activeMembers, function ($a, $b) {
            return $a['score'] > $b['score'];
        });

        // Garde 1/5 des meilleurs scores
        $activeMembers = array_slice($activeMembers, -ceil(count($activeMembers) / 5));

        return $activeMembers;
    }
}
