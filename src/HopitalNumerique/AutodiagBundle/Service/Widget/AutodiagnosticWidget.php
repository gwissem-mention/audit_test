<?php

namespace HopitalNumerique\AutodiagBundle\Service\Widget;

use HopitalNumerique\NewAccountBundle\Model\Widget\WidgetExtension;
use Symfony\Component\Routing\RouterInterface;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use Symfony\Component\Translation\TranslatorInterface;
use HopitalNumerique\NewAccountBundle\Model\Widget\Widget;
use HopitalNumerique\DomaineBundle\Service\BaseUrlProvider;
use HopitalNumerique\UserBundle\Service\ShareMessageGenerator;
use HopitalNumerique\AutodiagBundle\Repository\SynthesisRepository;
use HopitalNumerique\DomaineBundle\DependencyInjection\CurrentDomaine;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\WidgetAbstract;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\DomainAwareTrait;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\DomainAwareInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class AutodiagnosticWidget
 */
class AutodiagnosticWidget extends WidgetAbstract implements DomainAwareInterface
{
    use DomainAwareTrait;

    /**
     * @var SynthesisRepository
     */
    protected $synthesisRepository;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var BaseUrlProvider
     */
    protected $baseUrlProvider;

    /**
     * @var CurrentDomaine $currentDomainService
     */
    protected $currentDomainService;

    /**
     * @var ShareMessageGenerator
     */
    protected $shareMessageGenerator;

    /**
     * AutodiagnosticWidget constructor.
     *
     * @param \Twig_Environment     $twig
     * @param TokenStorageInterface $tokenStorage
     * @param TranslatorInterface   $translator
     * @param SynthesisRepository   $synthesisRepository
     * @param RouterInterface       $router
     * @param BaseUrlProvider       $baseUrlProvider
     * @param CurrentDomaine        $currentDomainService
     * @param ShareMessageGenerator $shareMessageGenerator
     */
    public function __construct(
        \Twig_Environment $twig,
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator,
        SynthesisRepository $synthesisRepository,
        RouterInterface $router,
        BaseUrlProvider $baseUrlProvider,
        CurrentDomaine $currentDomainService,
        ShareMessageGenerator $shareMessageGenerator
    ) {
        parent::__construct($twig, $tokenStorage, $translator);

        $this->synthesisRepository = $synthesisRepository;
        $this->router = $router;
        $this->baseUrlProvider = $baseUrlProvider;
        $this->currentDomainService = $currentDomainService;
        $this->shareMessageGenerator = $shareMessageGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function getWidget()
    {
        $user = $this->tokenStorage->getToken()->getUser();

        $syntheses = $this->synthesisRepository->findByUserOrderedByAutodiagNameAndSynthesisUpdate(
            $user,
            $this->domains
        );

        $data = [];

        /** @var Synthesis $synthesis */
        foreach ($syntheses as $synthesis) {
            $autodiag = $synthesis->getAutodiag();
            $baseUrl = $this->baseUrlProvider->getBaseUrl($autodiag->getDomaines()->toArray(), $this->domains);

            $isOwner = $synthesis->getUser()->getId() === $user->getId();

            $shareMessage = $this->shareMessageGenerator->getShareMessage(
                $this->synthesisRepository->getSynthesisShares($synthesis),
                $synthesis->getUser(),
                $user
            );

            if (!isset($data[$autodiag->getId()])) {
                $data[$autodiag->getId()] = [
                    'autodiag' => [
                        'id' => $autodiag->getId(),
                        'title' => $autodiag->getTitle(),
                        'lastUpdate' => $autodiag->getPublicUpdatedDate(),
                        'actions' => [
                            'add' => $baseUrl . $this->router->generate(
                                'hopitalnumerique_autodiag_entry_add',
                                ['autodiag' => $autodiag->getId()]
                            )
                        ]
                    ],
                    'syntheses' => [],
                ];
            }

            $entryId = $synthesis->getEntries()->count() === 1 ? $synthesis->getEntries()->first()->getId() : null;
            $showUrl = null;

            if (null !== $entryId && !$synthesis->isValidated()) {
                $showUrl = $baseUrl . $this->router->generate(
                    'hopitalnumerique_autodiag_entry_edit',
                    ['entry' => $entryId]
                );
            } elseif ($synthesis->isValidated()) {
                $showUrl = $baseUrl . $this->router->generate(
                    'hopitalnumerique_autodiag_restitution_index',
                    ['synthesis' => $synthesis->getId()]
                );
            }

            $sendUrl = $this->router->generate(
                'hopitalnumerique_autodiag_restitution_send_result',
                ['synthesis' => $synthesis->getId()]
            );

            $validationUrl = $baseUrl . $this->router->generate(
                'hopitalnumerique_autodiag_validation_index',
                ['synthesis' => $synthesis->getId()]
            );

            $shareUrl = $baseUrl . $this->router->generate(
                'hopitalnumerique_autodiag_share_index',
                ['synthesis' => $synthesis->getId()]
            );

            $deleteUrl = $this->router->generate(
                'hopitalnumerique_autodiag_account_delete_synthesis',
                ['synthesis' => $synthesis->getId()]
            );

            $data[$autodiag->getId()]['syntheses'][$synthesis->getId()] = [
                'id' => $synthesis->getId(),
                'name' => $synthesis->getName(),
                'lastUpdate' => $synthesis->getUpdatedAt(),
                'completion' => $synthesis->getCompletion(),
                'isValid' => $synthesis->isValidated(),
                'validationEnabled' => $synthesis->getCompletion() === 100 || $synthesis->getAutodiag()->isPartialResultsAuthorized(),
                'entryId' => $entryId,
                'showUrl' => $showUrl,
                'sameDomain' => $baseUrl === $this->currentDomainService->get()->getUrl(),
                'sendUrl' => $sendUrl,
                'validationUrl' => $validationUrl,
                'shareUrl' => $shareUrl,
                'deleteUrl' => $deleteUrl,
                'sharedMessage' => strlen($shareMessage) > 0 ? $shareMessage : null,
                'isOwner' => $isOwner,
            ];
        }

        $html = $this->twig->render('HopitalNumeriqueAutodiagBundle:widget:autodiagnostics.html.twig', [
            'data' => $data,
        ]);

        $title = $this->translator->trans('autodiagnostic.title', [], 'widget');

        $widget = new Widget('autodiagnostic', $title, $html);
        $widget->addExtension(new WidgetExtension('count', $this->twig->render(
            '@NewAccount/widget/extension/badge_number_extension.html.twig',
            ['number' => count($syntheses)]
        )));

        return $widget;
    }
}
