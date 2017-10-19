<?php

namespace HopitalNumerique\ObjetBundle\Service\Widget;

use HopitalNumerique\NewAccountBundle\Model\Widget\WidgetExtension;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\ObjetBundle\Entity\Subscription;
use HopitalNumerique\ObjetBundle\Repository\SubscriptionRepository;
use Symfony\Component\Routing\RouterInterface;
use HopitalNumerique\ObjetBundle\Entity\Contenu;
use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Component\Translation\TranslatorInterface;
use HopitalNumerique\NewAccountBundle\Model\Widget\Widget;
use HopitalNumerique\DomaineBundle\Service\BaseUrlProvider;
use HopitalNumerique\ObjetBundle\Repository\ObjetRepository;
use HopitalNumerique\ObjetBundle\Repository\ContenuRepository;
use HopitalNumerique\DomaineBundle\DependencyInjection\CurrentDomaine;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\WidgetAbstract;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\DomainAwareTrait;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\DomainAwareInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class ViewedObjectWidget
 */
class ViewedObjectWidget extends WidgetAbstract implements DomainAwareInterface
{
    use DomainAwareTrait;

    /**
     * @var ObjetRepository $objectRepository
     */
    protected $objectRepository;

    /**
     * @var ContenuRepository $contentRepository
     */
    protected $contentRepository;

    /**
     * @var RouterInterface $router
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
     * @var SubscriptionRepository $subscriptionRepository
     */
    protected $subscriptionRepository;

    /**
     * ViewedObjectWidget constructor.
     *
     * @param \Twig_Environment $twig
     * @param TokenStorageInterface $tokenStorage
     * @param TranslatorInterface $translator
     * @param ObjectRepository $objectRepository
     * @param ContenuRepository $contentRepository
     * @param RouterInterface $router
     * @param BaseUrlProvider $baseUrlProvider
     * @param CurrentDomaine $currentDomainService
     * @param SubscriptionRepository $subscriptionRepository
     */
    public function __construct(
        \Twig_Environment $twig,
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator,
        ObjectRepository $objectRepository,
        ContenuRepository $contentRepository,
        RouterInterface $router,
        BaseUrlProvider $baseUrlProvider,
        CurrentDomaine $currentDomainService,
        SubscriptionRepository $subscriptionRepository
    ) {
        parent::__construct($twig, $tokenStorage, $translator);
        $this->objectRepository = $objectRepository;
        $this->contentRepository = $contentRepository;
        $this->router = $router;
        $this->baseUrlProvider = $baseUrlProvider;
        $this->currentDomainService = $currentDomainService;
        $this->subscriptionRepository = $subscriptionRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getWidget()
    {
        $user = $this->tokenStorage->getToken()->getUser();

        $objects = $this->objectRepository->getViewedObjects(
            $user,
            $this->domains
        );

        $contents = $this->contentRepository->getViewedContent(
            $this->tokenStorage->getToken()->getUser(),
            $this->domains
        );

        $currentDomainUrl = $this->currentDomainService->get()->getUrl();

        $objectsData = [];
        /** @var Objet $object */
        foreach ($objects as $object) {
            $baseUrl = $this->baseUrlProvider->getBaseUrl(
                [$object->getConsultations()->first()->getDomaine()],
                $this->domains
            );

            $objectsData[$object->getId()] = [
                'shortTitle' => $object->getTitre(),
                'fullTitle' => $object->getTitre(),
                'modificationDate' => $object->getDateModification(),
                'consultationId' => $object->getConsultations()->first()->getId(),
                'consultationDate' => $object->getConsultations()->first()->getDateLastConsulted(),
                'showLink' => $baseUrl . $this->router->generate(
                    'hopital_numerique_publication_publication_objet',
                    ['id' => $object->getId(), 'alias' => $object->getAlias()]
                ),
                'recommendationLink' => $this->router->generate(
                    'hopital_numerique_publication_publication_object_recommendation',
                    ['object' => $object->getId()]
                ),
                'sameDomain' => $baseUrl === $currentDomainUrl,
                'subscription' => [
                    'objectId' => $object->getId(),
                    'contentId' => null,
                    'subscribed' => false,
                ],
            ];
        }

        $contentData = [];

        /** @var Contenu $content */
        foreach ($contents as $content) {
            $baseUrl = $this->baseUrlProvider->getBaseUrl(
                [$content->getConsultations()->first()->getDomaine()],
                $this->domains
            );

            $contentData[$content->getId()] = [
                'shortTitle' => $content->getShortTitle(),
                'fullTitle' => $content->getFullTitle(),
                'modificationDate' => $content->getDateModification(),
                'consultationId' => $content->getConsultations()->first()->getId(),
                'consultationDate' => $content->getConsultations()->first()->getDateLastConsulted(),
                'showLink' => $baseUrl . $this->router->generate(
                    'hopital_numerique_publication_publication_contenu',
                    [
                        'id'    => $content->getObjet()->getId(),
                        'alias' => $content->getObjet()->getAlias(),
                        'idc'   => $content->getId(),
                        'aliasc' => $content->getAlias(),
                    ]
                ),
                'recommendationLink' => $baseUrl = $this->router->generate(
                    'hopital_numerique_publication_publication_content_recommendation',
                    ['content' => $content->getId()]
                ),
                'sameDomain' => $baseUrl === $currentDomainUrl,
                'subscription' => [
                    'objectId' => $content->getObjet()->getId(),
                    'contentId' => $content->getId(),
                    'subscribed' => false,
                ],
            ];
        }

        $subscriptions = $this->subscriptionRepository->findByUser($user);
        foreach ($subscriptions as $subscription) {
            /* @var Subscription $subscription */
            if (null !== $subscription->getContenu()) {
                $contentData[$subscription->getContenu()->getId()]['subscription']['subscribed'] = true;
            } elseif (null !== $subscription->getObjet()) {
                $objectsData[$subscription->getObjet()->getId()]['subscription']['subscribed'] = true;
            }
        }

        $data = array_merge($objectsData, $contentData);

        usort($data, function ($a, $b) {
            return $a['consultationDate'] < $b['consultationDate'];
        });

        $html = $this->twig->render('HopitalNumeriqueObjetBundle:widget:viewed_objects.html.twig', [
            'data' => $data,
        ]);

        $title = $this->translator->trans('viewed_objects.title', [], 'widget');

        $widget = new Widget('viewed-objects', $title, $html);
        $widget->addExtension(new WidgetExtension('count', $this->twig->render(
            '@NewAccount/widget/extension/badge_number_extension.html.twig',
            ['number' => count($data)]
        )));

        return $widget;
    }
}
