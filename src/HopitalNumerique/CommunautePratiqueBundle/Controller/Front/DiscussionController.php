<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Controller\Front;

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
     * @param Discussion|null $discussion
     *
     * @return Response
     */
    public function publicAction(Discussion $discussion = null)
    {
        $discussionRepository = $this->get(DiscussionRepository::class);

        $selectedDomain = $this->get(SelectedDomainStorage::class)->getSelectedDomain();
        $domains = $selectedDomain ? [$selectedDomain] : $this->get(AvailableDomainsRetriever::class)->getAvailableDomains();

        $discussions = $discussionRepository->queryForDiscussionList(DiscussionListQuery::createPublicDiscussionQuery($domains, [], $this->getUser()));
        $this->getDoctrine()->getManager()->clear();
        $discussion = $discussionRepository->queryForDiscussionDisplayQuery(DiscussionDisplayQuery::createPublicDiscussionQuery($discussion ?: current($discussions), $domains, $this->getUser()));

        if ($this->isGranted(DiscussionVoter::CREATE)) {
            $newDiscussionCommand = new CreateDiscussionCommand($this->getUser(), [$this->get('hopitalnumerique_domaine.dependency_injection.current_domaine')->get()]);
            $newDiscussionForm = $this->createForm(CreateDiscussionType::class, $newDiscussionCommand, [
                'action' => $this->generateUrl('hopitalnumerique_communautepratique_discussions_create_discussion')
            ])->createView()
            ;
        }

        if ($this->isGranted(DiscussionVoter::REPLY)) {
            $answerDiscussionForm = $this->createForm(DiscussionMessageType::class, null, [
                'action' => $this->generateUrl('hopitalnumerique_communautepratique_discussions_reply_discussion', ['discussion' => $discussion->getId()])
            ])->createView();
        }

        return $this->render('@HopitalNumeriqueCommunautePratique/front/discussion/public.html.twig', [
            'discussions' => $discussions,
            'currentDiscussion' => $discussion,
            'newDiscussionForm' => isset($newDiscussionForm) ? $newDiscussionForm : null,
            'answerDiscussionForm' => isset($answerDiscussionForm) ? $answerDiscussionForm : null,
        ]);
    }

    /**
     * @param Request $request
     *
     * @Security("is_granted('cdp_discussion_create')")
     *
     * @return Response|RedirectResponse
     */
    public function createDiscussionAction(Request $request)
    {
        $command = new CreateDiscussionCommand($this->getUser(), [$this->get('hopitalnumerique_domaine.dependency_injection.current_domaine')->get()]);

        $newDiscussionForm = $this->createForm(CreateDiscussionType::class, $command);

        $newDiscussionForm->handleRequest($request);

        if ($newDiscussionForm->isSubmitted() && $newDiscussionForm->isValid()) {
            $discussion = $this->get(CreateDiscussionHandler::class)->handle($command);

            $this->addFlash('success', $this->get('translator')->trans('discussion.new_discussion.success', [], 'cdp_discussion'));

            return $this->redirectToRoute('hopitalnumerique_communautepratique_discussions_public_desfult_discussion', [
                'discussion' => $discussion->getId(),
            ]);
        }

        return $this->render('@HopitalNumeriqueCommunautePratique/front/discussion/create_discussion.html.twig', [
            'newDiscussionForm' => $newDiscussionForm->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param Discussion $discussion
     * @param Message $message
     *
     * @ParamConverter("message", class="HopitalNumeriqueCommunautePratiqueBundle:Discussion\Message", options={"id" = "message"})
     *
     * @return RedirectResponse|Response
     */
    public function replyAction(Request $request, Discussion $discussion, Message $message = null)
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
            $this->get(PostDiscussionMessageHandler::class)->handle($command);

            return $this->redirectToRoute('hopitalnumerique_communautepratique_discussions_public_desfult_discussion', [
                'discussion' => $discussion->getId(),
            ]);
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
     *
     * @return Response
     */
    public function discussionAction(Discussion $discussion)
    {
        $selectedDomain = $this->get(SelectedDomainStorage::class)->getSelectedDomain();
        $domains = $selectedDomain ? [$selectedDomain] : $this->get(AvailableDomainsRetriever::class)->getAvailableDomains();

        $discussion = $this->get(DiscussionRepository::class)->queryForDiscussionDisplayQuery(DiscussionDisplayQuery::createPublicDiscussionQuery($discussion, $domains, $this->getUser()));

        if ($this->isGranted(DiscussionVoter::REPLY, $discussion)) {
            $answerDiscussionForm = $this->createForm(DiscussionMessageType::class, null, [
                'action' => $this->generateUrl('hopitalnumerique_communautepratique_discussions_reply_discussion', ['discussion' => $discussion->getId()])
            ])->createView();
        }

        return $this->render('@HopitalNumeriqueCommunautePratique/front/discussion/discussion.html.twig', [
            'discussion' => $discussion,
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
     *
     * @return Response
     */
    public function toggleRecommendationAction(Discussion $discussion)
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

        return $this->redirectToRoute('hopitalnumerique_communautepratique_discussions_public_desfult_discussion', [
            'discussion' => $discussion->getId(),
        ]);
    }

    /**
     * @param Message $message
     *
     * @Security("is_granted('delete', message)")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteMessageAction(Message $message)
    {
        $discussionId = $message->getDiscussion()->getId();
        $this->get(DeleteMessageHandler::class)->handle(new DeleteMessageCommand($message));

        $this->addFlash('success', $this->get('translator')->trans('discussion.message.actions.delete.success', [], 'cdp_discussion'));

        return $this->redirectToRoute('hopitalnumerique_communautepratique_discussions_public_desfult_discussion', [
            'discussion' => $discussionId,
        ]);
    }


    /**
     * @param Discussion $discussion
     *
     * @Security("is_granted('download', discussion)")
     *
     * @return Response
     */
    public function downloadDiscussionAction(Discussion $discussion)
    {
        $selectedDomain = $this->get(SelectedDomainStorage::class)->getSelectedDomain();
        $domains = $selectedDomain ? [$selectedDomain] : $this->get(AvailableDomainsRetriever::class)->getAvailableDomains();

        $discussion = $this->get(DiscussionRepository::class)->queryForDiscussionDisplayQuery(DiscussionDisplayQuery::createPublicDiscussionQuery($discussion, $domains, $this->getUser()));

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
            $groupsAvailable = array_filter($groupRepository->findAll(), function (Groupe $group) use ($discussion) {
                return !$discussion->getGroups()->contains($group);
            });
        } else {
            $groupsAvailable = $user->getCommunautePratiqueAnimateurGroupes()->filter(function (Groupe $group) use ($discussion) {
                return !$discussion->getGroups()->contains($group);
            });
        }

        if ($groupId = $request->request->get('group', null)) {
            if ($group = $groupRepository->find($groupId)) {
                $discussion->addGroup($group);
                $this->getDoctrine()->getManager()->flush();

                $this->addFlash('success', $this->get('translator')->trans('discussion.discussion.actions.group_copy.success', [], 'cdp_discussion'));

                return $this->redirectToRoute('hopitalnumerique_communautepratique_discussions_public_desfult_discussion', [
                    'discussion' => $discussion->getId(),
                ]);
            }
        }

        return $this->render('@HopitalNumeriqueCommunautePratique/front/discussion/copy.html.twig', [
            'groups' => $groupsAvailable,
            'discussion' => $discussion,
        ]);
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
}
