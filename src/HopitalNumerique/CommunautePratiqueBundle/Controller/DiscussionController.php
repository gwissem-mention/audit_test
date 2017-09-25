<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Message;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion;
use HopitalNumerique\CommunautePratiqueBundle\Service\SelectedDomainStorage;
use HopitalNumerique\CommunautePratiqueBundle\Service\AvailableDomainsRetriever;
use HopitalNumerique\CommunautePratiqueBundle\Repository\Discussion\DiscussionRepository;
use HopitalNumerique\CommunautePratiqueBundle\Domain\Query\Discussion\DiscussionListQuery;
use HopitalNumerique\CommunautePratiqueBundle\Domain\Command\Discussion\DeleteMessageCommand;
use HopitalNumerique\CommunautePratiqueBundle\Domain\Command\Discussion\DeleteMessageHandler;
use HopitalNumerique\CommunautePratiqueBundle\Domain\Query\Discussion\DiscussionDisplayQuery;

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

        $discussions = $discussionRepository->getPublicDiscussionsForDomains(DiscussionListQuery::getPublicDiscussion($domains, [], $this->getUser()));
        $this->getDoctrine()->getManager()->clear();
        $discussion = $discussionRepository->getFirstPublicDiscussion(DiscussionDisplayQuery::getPublicDiscussion($discussion ?: current($discussions), $this->getUser()));

        return $this->render('@HopitalNumeriqueCommunautePratique/discussion/public.html.twig', [
            'discussions' => $discussions,
            'currentDiscussion' => $discussion,
        ]);
    }

    /**
     * @param Discussion $discussion
     *
     * @return Response
     */
    public function discussionAction(Discussion $discussion)
    {
        $discussion = $this->get(DiscussionRepository::class)->getFirstPublicDiscussion(DiscussionDisplayQuery::getPublicDiscussion($discussion, $this->getUser()));

        return $this->render('@HopitalNumeriqueCommunautePratique/discussion/discussion.html.twig', [
            'discussion' => $discussion,
        ]);
    }

    /**
     * @param Message $message
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
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
