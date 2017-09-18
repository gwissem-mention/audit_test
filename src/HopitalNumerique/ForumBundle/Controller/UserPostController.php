<?php

namespace HopitalNumerique\ForumBundle\Controller;

use HopitalNumerique\ForumBundle\Entity\Post;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use HopitalNumerique\ForumBundle\Domain\Command\SendEmailToSubscriberCommand;
use CCDNForum\ForumBundle\Controller\UserPostController as UserPostControllerBase;

class UserPostController extends UserPostControllerBase
{
    use ForumControllerAuthorizationCheckerTrait;

    /**
     * @param string $forumName
     * @param int    $postId
     *
     * @return RenderResponse|RedirectResponse
     */
    public function editProcessAction($forumName, $postId)
    {
        $post = $this->getPostModel()->findOnePostByIdWithTopicAndBoard($postId, true);
        $this->container->get('hopitalnumerique_forum.service.piece_jointe')->verifyPieceJointe($post);

        return parent::editProcessAction($forumName, $postId);
    }

    /**
     * Télécharge la PJ du post.
     *
     * @param Post $post
     *
     * @return RedirectResponse
     */
    public function activierPostEnAttenteAction(Post $post)
    {
        $post->setEnAttente(false);

        $this->container->get('hopitalnumerique_forum.manager.post')->save($post);

        if (null === $post->getEditedDate()) {
            $sendEmailToSubscriberCommand = new SendEmailToSubscriberCommand($this->getUser(), $post);

            $this->container->get('hopitalnumerique_forum.send_email_to_subscriber_handler')->handle(
                $sendEmailToSubscriberCommand
            );
        }

        $this->container->get('session')->getFlashBag()->add(
            'success',
            'Le post de ' . $post->getCreatedBy()->getAppellation() . ' a été activé.'
        );

        return $this->redirectResponse(
            $this->path(
                'ccdn_forum_user_topic_show',
                [
                    'forumName' => $post->getTopic()->getBoard()->getCategory()->getForum()->getName(),
                    'topicId'   => $post->getTopic()->getId(),
                ]
            )
        );
    }

    /**
     * Télécharge la PJ du post.
     *
     * @param Post $post
     *
     * @return RedirectResponse
     */
    public function downloadPieceJointeAction(Post $post)
    {
        $nomPieceJointe = substr($post->getPieceJointe(), 0, strrpos($post->getPieceJointe(), '_')) . substr($post->getPieceJointe(), strrpos($post->getPieceJointe(), '.'));

        $options = [
            'serve_filename' => $nomPieceJointe,
            'absolute_path' => true,
            'inline' => false,
        ];

        if (file_exists('../' . $post->getPieceJointeUrl())) {
            return $this->container->get('igorw_file_serve.response_factory')->create('../' . $post->getPieceJointeUrl(), 'application/force-download', $options);
        }

        $this->container->get('session')->getFlashBag()->add('danger', 'Fichier introuvable.');

        return $this->redirectResponse($this->path('ccdn_forum_user_topic_show', ['topicId' => $post->getTopic()->getId()]));
    }

    /**
     * @param Request $request
     * @param Post    $post
     *
     * @return JsonResponse
     */
    public function moveAction(Request $request, Post $post)
    {
        $ancienTopic = $post->getTopic()->getId();
        $topicId = $request->request->get('topic');

        // On vérifie que le post à déplacer ne soit pas le premier du fil
        if ($post->getTopic()->getFirstPost()->getId() != $post->getId()) {
            // On vérifie que l'utilisateur a le droit de déplacer le post
            if ($this->isGranted('ROLE_ADMINISTRATEUR_1')
                || $this->isGranted('ROLE_ADMINISTRATEUR_DU_DOMAINE_HN_107'
                    || $this->isGranted('ROLE_ADMINISTRATEUR_DE_DOMAINE_106'))
            ) {
                $entityManager = $this->container->get('doctrine.orm.entity_manager');

                $newTopic = $this->container->get('ccdn_forum_forum.repository.topic')->findOneTopicByIdWithPosts($topicId);
                $post->setTopic($newTopic);
                $post->setCreatedDate(new \DateTime());
                $post->setEditedDate(new \DateTime());
                $entityManager->persist($post);
                $entityManager->flush();

                $this->container->get('session')->getFlashBag()->add('success', 'Le post a bien été déplacé.');

                return new JsonResponse([
                    'success' => true,
                    'url' => $this->getRouter()->generate('ccdn_forum_user_topic_show', [
                        'topicId' => $topicId,
                    ]),
                ], 200);
            } else {
                $this->container->get('session')->getFlashBag()->add('danger', 'Vous ne pouvez pas déplacer le premier post d\'un fil de discussion.');
            }
        } else {
            $this->container->get('session')->getFlashBag()->add('danger', 'Vous ne pouvez pas déplacer le premier post d\'un fil de discussion.');
        }

        return new JsonResponse([
            'success' => false,
            'url' => $this->getRouter()->generate('ccdn_forum_user_topic_show', [
                'topicId' => $ancienTopic,
            ]),
        ], 200);
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }
}
