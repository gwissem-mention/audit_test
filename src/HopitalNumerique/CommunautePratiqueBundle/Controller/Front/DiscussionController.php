<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Controller\Front;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Message;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion;
use HopitalNumerique\CommunautePratiqueBundle\Service\SelectedDomainStorage;
use HopitalNumerique\CommunautePratiqueBundle\Service\AvailableDomainsRetriever;
use HopitalNumerique\CommunautePratiqueBundle\Form\Type\Discussion\CreateDiscussionType;
use HopitalNumerique\CommunautePratiqueBundle\Repository\Discussion\DiscussionRepository;
use HopitalNumerique\CommunautePratiqueBundle\Domain\Query\Discussion\DiscussionListQuery;
use HopitalNumerique\CommunautePratiqueBundle\Domain\Command\Discussion\DeleteMessageCommand;
use HopitalNumerique\CommunautePratiqueBundle\Domain\Command\Discussion\DeleteMessageHandler;
use HopitalNumerique\CommunautePratiqueBundle\Domain\Query\Discussion\DiscussionDisplayQuery;
use HopitalNumerique\CommunautePratiqueBundle\Domain\Command\Discussion\CreateDiscussionHandler;
use HopitalNumerique\CommunautePratiqueBundle\Domain\Command\Discussion\CreateDiscussionCommand;

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
        $discussion = $discussionRepository->queryForDiscussionDisplayQuery(DiscussionDisplayQuery::createPublicDiscussionQuery($discussion ?: current($discussions), $this->getUser()));

        if ($this->getUser()) {
            $newDiscussionCommand = new CreateDiscussionCommand($this->getUser(), [$this->get('hopitalnumerique_domaine.dependency_injection.current_domaine')->get()]);
            $newDiscussionForm = $this->createForm(CreateDiscussionType::class, $newDiscussionCommand, [
                'action' => $this->generateUrl('hopitalnumerique_communautepratique_discussions_create_discussion')
            ])->createView();
        }

        return $this->render('@HopitalNumeriqueCommunautePratique/front/discussion/public.html.twig', [
            'discussions' => $discussions,
            'currentDiscussion' => $discussion,
            'newDiscussionForm' => isset($newDiscussionForm) ? $newDiscussionForm : null
        ]);
    }

    /**
     * @param Request $request
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

            return $this->redirectToRoute('hopitalnumerique_communautepratique_discussions_public_desfult_discussion', [
                'discussion' => $discussion->getId(),
            ]);
        }

        return $this->render('@HopitalNumeriqueCommunautePratique/front/discussion/create_discussion.html.twig', [
            'newDiscussionForm' => $newDiscussionForm->createView(),
        ]);
    }

    /**
     * @param Discussion $discussion
     *
     * @return Response
     */
    public function discussionAction(Discussion $discussion)
    {
        $discussion = $this->get(DiscussionRepository::class)->queryForDiscussionDisplayQuery(DiscussionDisplayQuery::createPublicDiscussionQuery($discussion, $this->getUser()));

        return $this->render('@HopitalNumeriqueCommunautePratique/front/discussion/discussion.html.twig', [
            'discussion' => $discussion,
        ]);
    }

    /**
     * @param Message $message
     *
     * @return RedirectResponse
     */
    public function toggleHelpfulMessageAction(Message $message)
    {
        $message->toggleHelpful();

        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('hopitalnumerique_communautepratique_discussions_public_desfult_discussion', [
            'discussion' => $message->getDiscussion()->getId(),
        ]);
    }

    /**
     * @param Message $message
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteMessageAction(Message $message)
    {
        $discussionId = $message->getDiscussion()->getId();
        $this->get(DeleteMessageHandler::class)->handle(new DeleteMessageCommand($message));

        return $this->redirectToRoute('hopitalnumerique_communautepratique_discussions_public_desfult_discussion', [
            'discussion' => $discussionId,
        ]);
    }
}
