<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service\Export\Comment;


use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\StreamedResponse;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Commentaire;

/**
 * Class ExportCsv
 */
class Csv
{
    /**
     * Generate response for exporting a CSV from comments
     *
     * @param Commentaire[] $comments
     * @param $title
     *
     * @return StreamedResponse
     */
    public function generateResponse($comments, $title)
    {
        $response = new StreamedResponse(function () use ($comments) {
            $handle = fopen('php://output', 'r+');
            /** @var Commentaire $comment */
            fputcsv(
                $handle,
                [
                    'nom',
                    'dateCreation',
                    'dateModification',
                    'message',
                ],
                ';'
            );
            foreach ($comments as $comment) {
                $line = [
                    $comment->getUser()->getNomPrenom(),
                    $comment->getDateCreation()->format('Y-m-d'),
                    $comment->getDateDerniereModification()->format('Y-m-d'),
                    strip_tags($comment->getMessage()),
                ];
                fputcsv(
                    $handle,
                    array_map("utf8_decode", $line),
                    ';'
                );
            }
        });
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('charset', 'ISO-8859-1');
        $response->headers->set(
            'Content-Disposition',
            'attachment; filename="'.'commentaires_' . str_replace(" ", "_", strtolower($title)) . '.csv' . '"'
        );

        return $response;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function canExportCsv(User $user, Groupe $group)
    {
        if ($user->isGroupAnimator($group)
            || $user->hasRoleAdminHn()
            || $user->hasRoleAdmin()
            || $user->hasRoleAdminDomaine()) {
            return true;
        }
        return false;
    }
}
