<?php

namespace HopitalNumerique\RechercheParcoursBundle\Service\Widget;

use HopitalNumerique\NewAccountBundle\Model\Widget\WidgetExtension;
use Nodevo\ToolsBundle\Tools\Chaine;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;
use HopitalNumerique\NewAccountBundle\Model\Widget\Widget;
use HopitalNumerique\UserBundle\Service\ShareMessageGenerator;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearch;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\WidgetAbstract;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\DomainAwareTrait;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\DomainAwareInterface;
use HopitalNumerique\RechercheParcoursBundle\Repository\GuidedSearchRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use HopitalNumerique\RechercheParcoursBundle\Form\Type\GuidedSearch\ShareGuidedSearchType;
use HopitalNumerique\RechercheParcoursBundle\Domain\Command\GuidedSearch\ShareGuidedSearchCommand;

/**
 * Class GuidedSearchWidget
 */
class GuidedSearchWidget extends WidgetAbstract implements DomainAwareInterface
{
    use DomainAwareTrait;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var GuidedSearchRepository
     */
    protected $guidedSearchRepository;

    /**
     * @var FormFactory
     */
    protected $formFactory;

    protected $shareMessageGenerator;

    /**
     * GuidedSearchWidget constructor.
     *
     * @param \Twig_Environment      $twig
     * @param TokenStorageInterface  $tokenStorage
     * @param TranslatorInterface    $translator
     * @param RouterInterface        $router
     * @param GuidedSearchRepository $guidedSearchRepository
     * @param FormFactory            $formFactory
     * @param ShareMessageGenerator  $shareMessageGenerator
     */
    public function __construct(
        \Twig_Environment $twig,
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator,
        RouterInterface $router,
        GuidedSearchRepository $guidedSearchRepository,
        FormFactory $formFactory,
        ShareMessageGenerator $shareMessageGenerator
    ) {
        parent::__construct($twig, $tokenStorage, $translator);

        $this->router = $router;
        $this->guidedSearchRepository = $guidedSearchRepository;
        $this->formFactory = $formFactory;
        $this->shareMessageGenerator = $shareMessageGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function getWidget()
    {
        $guidedSearches = $this->guidedSearchRepository->findByUserWithShares(
            $this->tokenStorage->getToken()->getUser(),
            $this->domains
        );

        if (empty($guidedSearches)) {
            return null;
        }

        $data = [];

        /** @var GuidedSearch $guidedSearch */
        foreach ($guidedSearches as $guidedSearch) {
            $guidedSearchReference = $guidedSearch->getGuidedSearchReference();

            $shareMessage = $this->shareMessageGenerator->getShareMessage(
                $guidedSearch->getShares(),
                $guidedSearch->getOwner(),
                $this->tokenStorage->getToken()->getUser()
            );

            $continueLink = $this->router->generate('hopital_numerique_guided_search_continue_guided_search', [
                'guidedSearchReference'      => $guidedSearchReference->getId(),
                'guidedSearchReferenceAlias' => (new Chaine($guidedSearchReference->getReference()->getLibelle()))->minifie(),
                'guidedSearch' => $guidedSearch->getId()
            ]);

            $shareForm = $this->formFactory->create(
                ShareGuidedSearchType::class,
                new ShareGuidedSearchCommand($guidedSearch, $this->tokenStorage->getToken()->getUser())
            )->createView();

            $data[] = [
                'information' => [
                    'creationDate' => $guidedSearch->getCreatedAt()->format('d/m/y'),
                    'name' => '<a href="' . $continueLink .'">' . $guidedSearch->getGuidedSearchReference()->getReference()->getLibelle() . '</a>',
                    'status' => $guidedSearch->getShares()->count() > 0
                        ? $this->translator->trans('guided_search.status.shared', [], 'widget')
                        : $this->translator->trans('guided_search.status.private', [], 'widget')
                    ,
                ],
                'shareMessage' => strlen($shareMessage) > 0 ? $shareMessage : null,
                'actions' => [
                    'continue' => $continueLink,
                    'send' => $this->router->generate('hopital_numerique_guided_search_send', [
                        'guidedSearch' => $guidedSearch->getId(),
                    ]),
                    'delete' => $this->router->generate('hopital_numerique_guided_search_delete', [
                        'guidedSearch' => $guidedSearch->getId(),
                    ]),
                    'share' => [
                        'stepId' => $guidedSearch->getSteps()->count() > 0 ? $guidedSearch->getSteps()->first()->getId() : null,
                        'guidedSearch' => $guidedSearch,
                        'shareForm' => $shareForm,
                    ]
                ],
            ];
        }

        $html = $this->twig->render('HopitalNumeriqueRechercheParcoursBundle:widget:guided_search.html.twig', [
            'data' => $data,
        ]);

        $title = $this->translator->trans('guided_search.title', [], 'widget');

        $widget = new Widget('guided-search', $title, $html);
        $widget->addExtension(new WidgetExtension('count', count($data)));

        return $widget;
    }
}
