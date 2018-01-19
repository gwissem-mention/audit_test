<?php

namespace HopitalNumerique\CommunautePratiqueBundle\EventListener\Group;

use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\CommunautePratiqueBundle\Events;
use HopitalNumerique\CoreBundle\Service\ObjectIdentity\UserSubscription;
use Symfony\Component\Translation\TranslatorInterface;
use HopitalNumerique\QuestionnaireBundle\Entity\Reponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use HopitalNumerique\DomaineBundle\DependencyInjection\CurrentDomaine;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Message;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion;
use HopitalNumerique\CommunautePratiqueBundle\Event\Group\GroupRegistrationEvent;

class GroupRegistrationSubscriber implements EventSubscriberInterface
{
    /**
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * @var TranslatorInterface $translator
     */
    protected $translator;

    /**
     * @var CurrentDomaine $currentDomain
     */
    protected $currentDomain;

    /**
     * @var \Twig_Environment $twig
     */
    protected $twig;

    /**
     * @var UserSubscription
     */
    protected $userSubscription;

    /**
     * GroupRegistrationSubscriber constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param TranslatorInterface $translator
     * @param CurrentDomaine $currentDomain
     * @param \Twig_Environment $twig
     * @param UserSubscription $userSubscription
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator,
        CurrentDomaine $currentDomain,
        \Twig_Environment $twig,
        UserSubscription $userSubscription
    ) {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->currentDomain = $currentDomain;
        $this->twig = $twig;
        $this->userSubscription = $userSubscription;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::GROUP_REGISTRATION => [
                ['generatePresentationMessage', 0],
                ['autoSubscribe', 0],
            ],
        ];
    }

    /**
     * @param GroupRegistrationEvent $event
     */
    public function autoSubscribe(GroupRegistrationEvent $event)
    {
        foreach ($event->getGroup()->getDiscussions() as $discussion) {
            $this->userSubscription->subscribe($discussion, $event->getUser());
        }
    }

    /**
     * @param GroupRegistrationEvent $event
     */
    public function generatePresentationMessage(GroupRegistrationEvent $event)
    {
        $group = $event->getGroup();
        $user = $event->getUser();
        $answers = $event->getAnswers();
        $domains = $event->getDomains() ? $event->getDomains() : [$this->currentDomain->get()];

        if (null === ($discussion = $group->getPresentationDiscussion())) {
            $discussion = new Discussion(
                $this->translator->trans('discussion.message.presentation.title',
                    [],
                    'cdp_discussion'
                ),
                null,
                $domains
            );
            $discussion
                ->addGroup($group)
                ->setPublic(false)
            ;
            $group->setPresentationDiscussion($discussion);

            $this->entityManager->persist($discussion);
        }

        $questions = [];
        usort($answers, function (Reponse $a, Reponse $b) {
            if ($a->getQuestion()->getOrdre() === $b->getQuestion()->getOrdre()) {
                return 0;
            }

            return $a->getQuestion()->getOrdre() < $b->getQuestion()->getOrdre() ? -1 : 1;
        });
        foreach ($answers as $answer) {
            $questions[$answer->getQuestion()->getLibelle()] = $this->answerToString($answer);
        }

        $message = new Message(
            $discussion,
            $this->twig->render(
                '@HopitalNumeriqueCommunautePratique/front/discussion/presentation_message.html.twig',
                [
                    'answers' => $questions,
                    'user' => $user,
                ]
            ),
            $user
        );
        $this->entityManager->persist($message);

        $this->entityManager->flush($discussion);
        $this->entityManager->flush($message);
        $this->entityManager->flush($group);
    }

    /**
     * @param Reponse $answer
     *
     * @return string
     */
    protected function answerToString(Reponse $answer)
    {
        if ($answer->getQuestion()->getTypeQuestion()->getId() === 4) {
            return (bool) $answer->getReponse() ? 'Oui' : 'Non';
        } elseif ($answer->getReferenceMulitple()->count()) {
            $values = [];

            foreach ($answer->getReferenceMulitple() as $reference) {
                $values[] = $reference->getLibelle();
            }

            return implode(', ', $values);
        } elseif ($answer->getReference()) {
            return $answer->getReference()->getLibelle();
        } elseif ($answer->getEtablissement()) {
            return $answer->getEtablissement()->getNom();
        } elseif ($answer->getEtablissementMulitple()->count()) {
            $values = [];

            foreach ($answer->getEtablissementMulitple() as $reference) {
                $values[] = $reference->getNom();
            }

            return implode(', ', $values);
        }

        return $answer->getReponse();
    }
}
