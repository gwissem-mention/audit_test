<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service\Export\Comment;


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
            foreach ($comments as $comment) {
                fputcsv(
                    $handle,
                    [
                        $comment->getUser()->getNomPrenom(),
                        $comment->getDateCreation()->format('Y-m-d'),
                        $comment->getDateDerniereModification()->format('Y-m-d'),
                        strip_tags($comment->getMessage()),
                    ],
                    ';'
                );
            }
        });
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set(
            'Content-Disposition',
            'attachment; filename="'.'commentaires_' . str_replace(" ", "_", strtolower($title)) . '.csv' . '"'
        );

        return $response;
    }
}
