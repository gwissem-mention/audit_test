<?php

namespace HopitalNumerique\QuestionnaireBundle\Service\Widget;

use HopitalNumerique\DomaineBundle\DependencyInjection\CurrentDomaine;
use HopitalNumerique\NewAccountBundle\Model\Widget\WidgetExtension;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;
use HopitalNumerique\QuestionnaireBundle\Entity\Reponse;
use HopitalNumerique\NewAccountBundle\Model\Widget\Widget;
use HopitalNumerique\DomaineBundle\Service\BaseUrlProvider;
use HopitalNumerique\QuestionnaireBundle\Repository\ReponseRepository;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\WidgetAbstract;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\DomainAwareTrait;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\DomainAwareInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class SurveyWidget
 */
class SurveyWidget extends WidgetAbstract implements DomainAwareInterface
{
    use DomainAwareTrait;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var ReponseRepository
     */
    protected $responseRepository;

    /**
     * @var BaseUrlProvider
     */
    protected $baseUrlProvider;

    /**
     * @var CurrentDomaine $currentDomainService
     */
    protected $currentDomainService;

    /**
     * SurveyWidget constructor.
     *
     * @param \Twig_Environment     $twig
     * @param TokenStorageInterface $tokenStorage
     * @param TranslatorInterface   $translator
     * @param RouterInterface       $router
     * @param ReponseRepository     $responseRepository
     * @param BaseUrlProvider       $baseUrlProvider
     * @param CurrentDomaine $currentDomainService
     */
    public function __construct(
        \Twig_Environment $twig,
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator,
        RouterInterface $router,
        ReponseRepository $responseRepository,
        BaseUrlProvider $baseUrlProvider,
        CurrentDomaine $currentDomainService
    ) {
        parent::__construct($twig, $tokenStorage, $translator);

        $this->router = $router;
        $this->responseRepository = $responseRepository;
        $this->baseUrlProvider = $baseUrlProvider;
        $this->currentDomainService = $currentDomainService;
    }

    /**
     * {@inheritdoc}
     */
    public function getWidget()
    {
        $user = $this->tokenStorage->getToken()->getUser();

        $responses = $this->responseRepository->findByUserOrderedBySurveyNameAndResponseUpdate($user, $this->domains);

        $data = [];

        $currentDomainUrl = $this->currentDomainService->get()->getUrl();

        /** @var Reponse $response */
        foreach ($responses as $response) {
            $entry = $response->getOccurrence();
            $question = $response->getQuestion();
            $survey = $question->getQuestionnaire();
            $baseUrl = $this->baseUrlProvider->getBaseUrl($survey->getDomaines()->toArray(), $this->domains);

            if (!isset($data[$survey->getId()])) {
                $data[$survey->getId()] = [
                    'survey' => [
                        'id' => $survey->getId(),
                        'name' => $survey->getNom(),
                        'update' => $response->getDateUpdate(),
                        'actions' => [],
                    ],
                    'responses' => [],
                    'sameDomain' => $currentDomainUrl === $baseUrl,
                ];

                if (null === $entry) {
                    $data[$survey->getId()]['survey']['actions']['show'] = $baseUrl . $this->router->generate(
                        'hopitalnumerique_questionnaire_edit_front_gestionnaire',
                        ['id' => $survey->getId()]
                    );

                    $data[$survey->getId()]['survey']['actions']['delete'] = $this->router->generate(
                        'hopitalnumerique_reponse_delete',
                        ['survey' => $survey->getId()]
                    );
                } else {
                    $data[$survey->getId()]['survey']['actions']['add'] = $baseUrl . $this->router->generate(
                        'hopitalnumerique_questionnaire_occurrence_add',
                        ['questionnaire' => $survey->getId()]
                    );
                }
            }

            if (null !== $entry && !isset($data[$survey->getId()['responses'][$entry->getId()]])) {
                $data[$survey->getId()]['responses'][$entry->getId()] = [
                    'id' => $entry->getId(),
                    'name' => $entry->getLibelle(),
                    'update' => $response->getDateUpdate(),
                    'actions' => [
                        'show' => $baseUrl . $this->router->generate(
                            'hopitalnumerique_questionnaire_edit_front_gestionnaire_occurrence',
                            ['questionnaire' => $survey->getId(), 'occurrence' => $entry->getId()]
                        ),
                        'delete' => $this->router->generate(
                            'hopitalnumerique_reponse_delete',
                            ['survey' => $survey->getId(), 'entry' => $entry->getId()]
                        ),
                    ]
                ];
            }
        }

        if (empty($data)) {
            return null;
        }

        $html = $this->twig->render('HopitalNumeriqueQuestionnaireBundle:widget:survey.html.twig', [
            'data' => $data,
            'user' => $user,
        ]);

        $title = $this->translator->trans('survey.title', [], 'widget');

        $widget = new Widget('survey', $title, $html);
        $widget->addExtension(new WidgetExtension('count', count($data)));

        return $widget;
    }
}
