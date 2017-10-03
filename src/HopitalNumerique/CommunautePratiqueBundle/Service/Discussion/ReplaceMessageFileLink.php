<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service\Discussion;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Message;
use HopitalNumerique\FichierBundle\Entity\File;
use Symfony\Component\Routing\RouterInterface;

class ReplaceMessageFileLink
{
    /**
     * @var RouterInterface $router
     */
    protected $router;

    /**
     * ReplaceMessageFileLink constructor.
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * Replace temp preview uri to discussion message file preview uri
     *
     * @param Message $message
     */
    public function replaceFilesLink(Message $message)
    {
        $baseUrls = [];
        $messageFileUrls = [];

        foreach ($message->getFiles() as $file) {
            $baseUrls[] = $this->router->generate('hopitalnumerique_fichier_view', ['file' => $file->getId()]);
            $messageFileUrls[] = $this->router->generate(
                'hopitalnumerique_communautepratique_discussions_discussion_message_file',
                [
                    'message' => $message->getId(),
                    'file' => $file->getId(),
                ]
            );
        }

        $message->setContent(str_replace($baseUrls, $messageFileUrls, $message->getContent()));
    }
}
