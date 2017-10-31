<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Controller\Front;

use HopitalNumerique\CommunautePratiqueBundle\Domain\Command\Discussion\ReorderDiscussionCommand;
use HopitalNumerique\CommunautePratiqueBundle\Domain\Command\Discussion\ReorderDiscussionHandler;
use HopitalNumerique\CommunautePratiqueBundle\Form\Type\Discussion\DiscussionDomainType;
use HopitalNumerique\CoreBundle\Service\ObjectIdentity\UserSubscription;
use HopitalNumerique\FichierBundle\Entity\File;
use HopitalNumerique\FichierBundle\Service\FilePathFinder;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Message;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion;
use HopitalNumerique\CommunautePratiqueBundle\Service\SelectedDomainStorage;
use HopitalNumerique\CommunautePratiqueBundle\Security\Discussion\MessageVoter;
use HopitalNumerique\CommunautePratiqueBundle\Service\AvailableDomainsRetriever;
use HopitalNumerique\CommunautePratiqueBundle\Service\Export\Discussion\CSVExport;
use HopitalNumerique\CommunautePratiqueBundle\Security\Discussion\DiscussionVoter;
use HopitalNumerique\CommunautePratiqueBundle\Form\Type\Discussion\CreateDiscussionType;
use HopitalNumerique\CommunautePratiqueBundle\Form\Type\Discussion\DiscussionMessageType;
use HopitalNumerique\CommunautePratiqueBundle\Repository\Discussion\DiscussionRepository;
use HopitalNumerique\CommunautePratiqueBundle\Domain\Query\Discussion\DiscussionListQuery;
use HopitalNumerique\CommunautePratiqueBundle\Domain\Command\Discussion\ReadMessageHandler;
use HopitalNumerique\CommunautePratiqueBundle\Domain\Command\Discussion\ReadMessageCommand;
use HopitalNumerique\CommunautePratiqueBundle\Domain\Command\Discussion\DeleteMessageCommand;
use HopitalNumerique\CommunautePratiqueBundle\Domain\Command\Discussion\DeleteMessageHandler;
use HopitalNumerique\CommunautePratiqueBundle\Domain\Query\Discussion\DiscussionDisplayQuery;
use HopitalNumerique\CommunautePratiqueBundle\Domain\Command\Discussion\CreateDiscussionHandler;
use HopitalNumerique\CommunautePratiqueBundle\Domain\Command\Discussion\CreateDiscussionCommand;
use HopitalNumerique\CommunautePratiqueBundle\Domain\Command\Discussion\PostDiscussionMessageCommand;
use HopitalNumerique\CommunautePratiqueBundle\Domain\Command\Discussion\PostDiscussionMessageHandler;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class DiscussionController extends Controller
{
    /**
     * @param Groupe $group
     * @param Discussion|null $discussion
     * @param Objet|null $object
     *
     * @return Response
     */
    public function listAction(Groupe $group = null, Discussion $discussion = null, Objet $object = null)
    {
        $discussionRepository = $this->get(DiscussionRepository::class);

        $selectedDomain = $this->get(SelectedDomainStorage::class)->getSelectedDomain();
        $domains = $selectedDomain ? [$selectedDomain] : $this->get(AvailableDomainsRetriever::class)->getAvailableDomains();

        $discussions = $discussionRepository->queryForDiscussionList(DiscussionListQuery::createPublicDiscussionQuery($domains, $group, $this->getUser()));
        $this->getDoctrine()->getManager()->clear();
        $discussion = $discussion ?: ($group && $group->getPresentationDiscussion() ? $group->getPresentationDiscussion() : current($discussions));
        if ($discussion instanceof Discussion) {
            $discussion = $discussionRepository->queryForDiscussionDisplayQuery(
                DiscussionDisplayQuery::createPublicDiscussionQuery(
                    $discussion,
                    $domains,
                    $group,
                    $this->getUser()
                )
            );
            $discussionSubscribed = $discussion && $this->getUser() && $this->get(UserSubscription::class)->isSubscribed($discussion, $this->getUser());

            if ($discussion && $this->getUser()) {
                $this->get(ReadMessageHandler::class)->handle(new ReadMessageCommand($this->get('hopitalnumerique_user.repository.user')->find($this->getUser()->getId()), $discussion->getMessages()->last()->getId()));
            }
        } else {
            $discussion = null;
        }

        if ($this->isGranted(DiscussionVoter::CREATE)) {
            $newDiscussionCommand = new CreateDiscussionCommand(
                $this->getUser(),
                [$this->get('hopitalnumerique_domaine.dependency_injection.current_domaine')->get()],
                null,
                $object
            );
            $action = $this->generateUrl('hopitalnumerique_communautepratique_discussions_create_discussion', ['group' => $group ? $group->getId() : null]);
            if ($object) {
                $action = $this->generateUrl('hopitalnumerique_communautepratique_discussions_create_related_discussion', ['object' => $object->getId()]);
            }
            $newDiscussionForm = $this->createForm(CreateDiscussionType::class, $newDiscussionCommand, [
                'action' => $action,
            ])->createView()
            ;
        }

        if ($this->isGranted(DiscussionVoter::REPLY) && $discussion) {
            $answerDiscussionForm = $this->createForm(DiscussionMessageType::class, new PostDiscussionMessageCommand($discussion, $this->getUser()), [
                'action' => $this->generateUrl(
                    'hopitalnumerique_communautepratique_discussions_reply_discussion',
                    [
                        'discussion' => $discussion->getId(),
                        'group' => $group ? $group->getId() : null,
                    ]
                )
            ])->createView();
        }

        if ($discussion && $this->isGranted(DiscussionVoter::MANAGE_DOMAINS, $discussion)) {
            $discussionDomainsForm = $this->createForm(DiscussionDomainType::class, $discussion, [
                'action' => $this->generateUrl('hopitalnumerique_communautepratique_discussions_discussion_domains', ['discussion' => $discussion->getId()]),
            ])->createView();
        }

        $options= [
            'isDiscussionSubscribed' => isset($discussionSubscribed) ? $discussionSubscribed : false,
            'preopenNewDiscussionModal' => isset($newDiscussionForm) && $object,
            'group' => $group,
            'scope' => null === $group ? 'public' : 'group',
            'discussions' => $discussions,
            'currentDiscussion' => $discussion,
            'newDiscussionForm' => isset($newDiscussionForm) ? $newDiscussionForm : null,
            'answerDiscussionForm' => isset($answerDiscussionForm) ? $answerDiscussionForm : null,
            'discussionDomainsForm' => isset($discussionDomainsForm) ? $discussionDomainsForm : null,
        ];

        if ($group) {
            return $this->render('@HopitalNumeriqueCommunautePratique/front/discussion/discussions.html.twig', $options);
        }

        return $this->render('@HopitalNumeriqueCommunautePratique/front/discussion/public.html.twig', $options);
    }

    /**
     * @param Objet $object
     *
     * @return RedirectResponse
     */
    public function publicationDiscussionAction(Objet $object)
    {
        $selectedDomain = $this->get(SelectedDomainStorage::class)->getSelectedDomain();

        if ($discussion = $this->get(DiscussionRepository::class)->getPublicDiscussionForObject($object, $selectedDomain)) {
            return $this->redirectToRoute(
                'hopitalnumerique_communautepratique_discussions_public_desfult_discussion',
                [
                    'discussion' => $discussion->getId(),
                ]
            );
        }

        return $this->redirectToRoute(
            'hopitalnumerique_communautepratique_discussions_public_create_from_object',
            [
                'object' => $object->getId(),
            ]
        );
    }

    /**
     * @param Request $request
     * @param Groupe $group
     * @param Objet $object
     *
     * @return Response|RedirectResponse
     */
    public function createDiscussionAction(Request $request, Groupe $group = null, Objet $object = null)
    {
        $selectedDomain = $this->get(SelectedDomainStorage::class)->getSelectedDomain();
        $domains = $selectedDomain ? [$selectedDomain] : $this->get(AvailableDomainsRetriever::class)->getAvailableDomains();

        if ($this->getUser()) {
            if (!$this->isGranted('cdp_discussion_create')) {
                $cpArticle = $this->get('hopitalnumerique_domaine.dependency_injection.current_domaine')->get()->getCommunautePratiqueArticle();
                $request->getSession()->set('urlToRedirectAfterCDPRegistration', $request->getUri());

                return $this->redirectToRoute(
                    'hopital_numerique_publication_publication_article',
                    [
                        'id' => $cpArticle->getId(),
                        'categorie' => 'article',
                        'alias' => $cpArticle->getAlias(),
                    ]
                );
            }
        } else {
            $request->getSession()->set('urlToRedirect', $request->getUri());

            return $this->redirect($this->generateUrl('account_login'));
        }

        $this->denyAccessUnlessGranted('cdp_discussion_create');

        $command = new CreateDiscussionCommand($this->getUser(), $domains, $group, $object);

        $newDiscussionForm = $this->createForm(CreateDiscussionType::class, $command);

        $newDiscussionForm->handleRequest($request);

        if ($newDiscussionForm->isSubmitted() && $newDiscussionForm->isValid()) {
            $discussion = $this->get(CreateDiscussionHandler::class)->handle($command);

            $this->addFlash('success', $this->get('translator')->trans('discussion.new_discussion.success', [], 'cdp_discussion'));

            return $this->redirectResponse($group, $discussion);
        }

        return $this->render('@HopitalNumeriqueCommunautePratique/front/discussion/create_discussion.html.twig', [
            'newDiscussionForm' => $newDiscussionForm->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param Discussion $discussion
     * @param Groupe $group
     * @param Message $message
     *
     * @ParamConverter("message", class="HopitalNumeriqueCommunautePratiqueBundle:Discussion\Message", options={"id" = "message"})
     *
     * @return RedirectResponse|Response
     */
    public function replyAction(Request $request, Discussion $discussion, Groupe $group = null, Message $message = null)
    {
        if (null !== $message) {
            $this->denyAccessUnlessGranted(MessageVoter::EDIT, $message);
            $command = new PostDiscussionMessageCommand($discussion, $this->getUser(), $message);
        } else {
            $this->denyAccessUnlessGranted(DiscussionVoter::REPLY, $discussion);
            $command = new PostDiscussionMessageCommand($discussion, $this->getUser());
        }

        $form = $this->createForm(DiscussionMessageType::class, $command);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $messageUpdated = $this->get(PostDiscussionMessageHandler::class)->handle($command);

            if (null !== $message) {
                $this->addFlash('success', $this->get('translator')->trans('discussion.message.reply.edit.success', [], 'cdp_discussion'));
            } else {
                if ($messageUpdated->isPublished()) {
                    $this->addFlash('success', $this->get('translator')->trans('discussion.message.reply.add.success', [], 'cdp_discussion'));
                } else {
                    $this->addFlash('success', $this->get('translator')->trans('discussion.message.reply.add.moderate', [], 'cdp_discussion'));
                }
            }

            return $this->redirectResponse($group, $discussion);
        }

        if ($request->isXmlHttpRequest()) {
            return $this->render('@HopitalNumeriqueCommunautePratique/front/discussion/reply_form.html.twig', [
                'form' => $form->createView(),
                'message' => $message,
            ]);
        }

        return $this->render('@HopitalNumeriqueCommunautePratique/front/discussion/reply.html.twig', [
            'form' => $form->createView(),
            'message' => $message,
        ]);
    }

    /**
     * @param Discussion $discussion
     * @param Groupe $group
     *
     * @return Response
     */
    public function discussionAction(Discussion $discussion, Groupe $group = null)
    {
        $selectedDomain = $this->get(SelectedDomainStorage::class)->getSelectedDomain();
        $domains = $selectedDomain ? [$selectedDomain] : $this->get(AvailableDomainsRetriever::class)->getAvailableDomains();

        $discussion = $this->get(DiscussionRepository::class)->queryForDiscussionDisplayQuery(DiscussionDisplayQuery::createPublicDiscussionQuery($discussion, $domains, $group, $this->getUser()));

        if ($this->isGranted(DiscussionVoter::REPLY, $discussion)) {
            $answerDiscussionForm = $this->createForm(DiscussionMessageType::class, null, [
                'action' => $this->generateUrl('hopitalnumerique_communautepratique_discussions_reply_discussion', ['discussion' => $discussion->getId(), 'group' => $group ? $group->getId() : null])
            ])->createView();
        }

        if ($discussion && $this->isGranted(DiscussionVoter::MANAGE_DOMAINS, $discussion)) {
            $discussionDomainsForm = $this->createForm(DiscussionDomainType::class, $discussion, [
                'action' => $this->generateUrl('hopitalnumerique_communautepratique_discussions_discussion_domains', ['discussion' => $discussion->getId()]),
            ])->createView();
        }

        if ($this->getUser()) {
            $this->get(ReadMessageHandler::class)->handle(new ReadMessageCommand($this->getUser(), $discussion->getMessages()->last()->getId()));
        }

        return $this->render('@HopitalNumeriqueCommunautePratique/front/discussion/discussion.html.twig', [
            'group' => $group,
            'scope' => null === $group ? 'public' : 'group',
            'discussion' => $discussion,
            'isDiscussionSubscribed' => $this->getUser() && $this->get(UserSubscription::class)->isSubscribed($discussion, $this->getUser()),
            'discussionDomainsForm' => isset($discussionDomainsForm) ? $discussionDomainsForm : null,
            'answerDiscussionForm' => isset($answerDiscussionForm) ? $answerDiscussionForm : null,
        ]);
    }

    /**
     * @param Message $message
     *
     * @Security("is_granted('mark_as_helpful', message)")
     *
     * @return JsonResponse
     */
    public function toggleHelpfulMessageAction(Message $message)
    {
        $message->toggleHelpful();

        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse(null);
    }

    /**
     * @param Discussion $discussion
     * @param Groupe $group
     *
     * @return Response
     */
    public function toggleRecommendationAction(Discussion $discussion, Groupe $group = null)
    {
        $this->denyAccessUnlessGranted(DiscussionVoter::MARK_AS_RECOMMENDED, $discussion);

        $discussion->setRecommended(!$discussion->isRecommended());

        $this->getDoctrine()->getManager()->flush();

        $direction = $discussion->isRecommended() ? 'up' : 'down';

        $this->addFlash('success', $this->get('translator')->trans(
            sprintf('discussion.discussion.actions.up.success.%s', $direction),
            [],
            'cdp_discussion'
        ));

        return $this->redirectResponse($group, $discussion);
    }

    /**
     * @param Request $request
     * @param Discussion $discussion
     *
     * @return JsonResponse
     */
    public function updateDiscussionDomainsAction(Request $request, Discussion $discussion)
    {
        $form = $this->createForm(DiscussionDomainType::class, $discussion);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return new JsonResponse(null);
        }

        return new JsonResponse(null, 418);
    }

    /**
     * @param Message $message
     * @param Groupe $group
     *
     * @Security("is_granted('delete', message)")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteMessageAction(Message $message, Groupe $group = null)
    {
        $discussion = $message->getDiscussion();
        $this->get(DeleteMessageHandler::class)->handle(new DeleteMessageCommand($message));

        $this->addFlash('success', $this->get('translator')->trans('discussion.message.actions.delete.success', [], 'cdp_discussion'));

        return $this->redirectResponse($group, $discussion);
    }

    /**
     * @param Message $message
     * @param Groupe $group
     *
     * @Security("is_granted('validate', message)")
     *
     * @return RedirectResponse
     */
    public function validateMessageAction(Message $message, Groupe $group = null)
    {
        $message->setPublished(true);

        $this->getDoctrine()->getManager()->flush();

        $this->addFlash('success', $this->get('translator')->trans('discussion.message.reply.validate.success', [], 'cdp_discussion'));

        return $this->redirectResponse($group, $message->getDiscussion());
    }

    /**
     * @param Discussion $discussion
     * @param Groupe $group
     *
     * @Security("is_granted('download', discussion)")
     *
     * @return Response
     */
    public function downloadDiscussionAction(Discussion $discussion, Groupe $group = null)
    {
        $selectedDomain = $this->get(SelectedDomainStorage::class)->getSelectedDomain();
        $domains = $selectedDomain ? [$selectedDomain] : $this->get(AvailableDomainsRetriever::class)->getAvailableDomains();

        $discussion = $this->get(DiscussionRepository::class)->queryForDiscussionDisplayQuery(DiscussionDisplayQuery::createPublicDiscussionQuery($discussion, $domains, $group, $this->getUser()));

        $filePath = $this->get(CSVExport::class)->export($discussion);

        $response = new BinaryFileResponse($filePath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            sprintf('discussion_%d.csv', $discussion->getId())
        );

        return $response;
    }

    /**
     * @param Request $request
     * @param Discussion $discussion
     *
     * @Security("is_granted('copy_to_group', discussion)")
     *
     * @return RedirectResponse|Response
     */
    public function copyToGroupAction(Request $request, Discussion $discussion)
    {
        $groupRepository = $this->get('hopitalnumerique_communautepratique.repository.groupe');
        /** @var User $user */
        $user = $this->getUser();

        if ($user->hasRoleCDPAdmin()) {
            $groupsAvailable = $groupRepository->findAll();
        } else {
            $groupsAvailable = $user->getCommunautePratiqueAnimateurGroupes()->toArray();
            $groupsAvailable = array_filter($groupsAvailable, function (Groupe $group) use ($user) {

                if ($user->hasRoleCDPAdmin()) {
                    return true;
                }

                if ($group->getRequiredRoles()->count() === 0) {
                    return true;
                }

                foreach ($group->getRequiredRoles() as $role) {
                    if ($user->isGranted($role)) {
                        return true;
                    }
                }

                return false;
            });
        }

        $groupsAvailable = array_filter($groupsAvailable, function (Groupe $group) use ($discussion) {
            return !$discussion->getGroups()->contains($group) && $group->getDateDemarrage() < new \DateTime();
        });

        if ($groupId = $request->request->get('group', null)) {
            if ($group = $groupRepository->find($groupId)) {
                $discussion->addGroup($group);
                $this->getDoctrine()->getManager()->flush();

                $this->addFlash('success', $this->get('translator')->trans('discussion.discussion.actions.group_copy.success', [], 'cdp_discussion'));

                return $this->redirectResponse(null, $discussion);
            }
        }

        return $this->render('@HopitalNumeriqueCommunautePratique/front/discussion/copy.html.twig', [
            'groups' => $groupsAvailable,
            'discussion' => $discussion,
        ]);
    }

    /**
     * @param Discussion $discussion
     * @param Groupe|null $group
     *
     * @Security("is_granted('set_as_public', discussion)")
     *
     * @return RedirectResponse
     */
    public function setDiscussionPublicAction(Discussion $discussion, Groupe $group = null)
    {
        $discussion->setPublic(!$discussion->isPublic());
        $this->getDoctrine()->getManager()->flush();

        $this->addFlash('success', $this->get('translator')->trans('discussion.discussion.actions.public.success', [], 'cdp_discussion'));

        return $this->redirectResponse($group, $discussion);
    }

    /**
     * @param Discussion $discussion
     * @param Groupe|null $group
     *
     * @return RedirectResponse
     */
    public function subscribeAction(Request $request, Discussion $discussion, Groupe $group = null)
    {
        if (!$this->isGranted('subscribe', $discussion)) {
            $request->getSession()->set('urlToRedirect', $request->getUri());

            return $this->redirectToRoute('account_login');
        }

        $this->denyAccessUnlessGranted('subscribe', $discussion);

        $subscriptionService = $this->get(UserSubscription::class);
        if ($subscriptionService->isSubscribed($discussion, $this->getUser())) {
            $subscriptionService->unsubscribe($discussion, $this->getUser());

            $this->addFlash('success', $this->get('translator')->trans('discussion.discussion.actions.subscription.un.success', [], 'cdp_discussion'));
        } else {
            $subscriptionService->subscribe($discussion, $this->getUser());

            $this->addFlash('success', $this->get('translator')->trans('discussion.discussion.actions.subscription.sub.success', [], 'cdp_discussion'));
        }

        return $this->redirectResponse($group, $discussion);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function readAction(Request $request)
    {
        if (null !== ($messageId = $request->request->get('messageId'))) {
            try {
                $this->get(ReadMessageHandler::class)->handle(new ReadMessageCommand($this->getUser(), $messageId));
            } catch (\Exception $e) {
                return new JsonResponse(null, 418);
            }

            return new JsonResponse();
        }

        return new JsonResponse(null, 418);
    }

    /**
     * @param Request $request
     *
     * @Security("is_granted('reorder_discussion')")
     *
     * @return JsonResponse
     */
    public function reorderDiscussionAction(Request $request)
    {
        if (null !== ($order = $request->request->get('order'))) {
            $this->get(ReorderDiscussionHandler::class)->handle(new ReorderDiscussionCommand(json_decode($order, true)));

            return new JsonResponse();
        }

        return new JsonResponse(null, 418);
    }

    /**
     * @param Message $message
     * @param File $file
     *
     * @Security("is_granted('view_file', message)")
     *
     * @return BinaryFileResponse
     */
    public function visualizeFileAction(Message $message, File $file)
    {
        return new BinaryFileResponse($this->get(FilePathFinder::class)->getFilePath($file));
    }

    private function redirectResponse(Groupe $group = null, Discussion $discussion = null)
    {
        if (null !== $group && null !== $discussion && $discussion->getId()) {
            return $this->redirectToRoute('hopitalnumerique_communautepratique_groupe_view_default_discussion', [
                'groupe' => $group->getId(),
                'discussion' => $discussion->getId(),
            ]);
        } elseif (null !== $group) {
            return $this->redirectToRoute('hopitalnumerique_communautepratique_groupe_view', [
                'groupe' => $group->getId(),
            ]);
        } elseif (null === $group && $discussion && $discussion->getId()) {
            return $this->redirectToRoute('hopitalnumerique_communautepratique_discussions_public_desfult_discussion', [
                'discussion' => $discussion->getId(),
            ]);
        }

        return $this->redirectToRoute('hopitalnumerique_communautepratique_discussions_public');
    }
}
