<?php

namespace HopitalNumerique\RechercheBundle\Service\Widget;

use HopitalNumerique\NewAccountBundle\Model\Widget\WidgetExtension;
use Symfony\Component\Routing\RouterInterface;
use HopitalNumerique\RechercheBundle\Entity\Requete;
use Symfony\Component\Translation\TranslatorInterface;
use HopitalNumerique\NewAccountBundle\Model\Widget\Widget;
use HopitalNumerique\RechercheBundle\Repository\RequeteRepository;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\WidgetAbstract;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\DomainAwareTrait;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\DomainAwareInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class SavedSearches
 */
class SavedSearchesWidget extends WidgetAbstract implements DomainAwareInterface
{
    use DomainAwareTrait;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var RequeteRepository
     */
    protected $requestRepository;

    /**
     * SavedSearchesWidget constructor.
     *
     * @param \Twig_Environment     $twig
     * @param TokenStorageInterface $tokenStorage
     * @param TranslatorInterface   $translator
     * @param RouterInterface       $router
     * @param RequeteRepository     $requestRepository
     */
    public function __construct(
        \Twig_Environment $twig,
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator,
        RouterInterface $router,
        RequeteRepository $requestRepository
    ) {
        parent::__construct($twig, $tokenStorage, $translator);

        $this->router = $router;
        $this->requestRepository = $requestRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getWidget()
    {
        $searches = $this->requestRepository->getSavedSearchesByUser(
            $this->tokenStorage->getToken()->getUser(),
            $this->domains
        );

        $title = $this->translator->trans('saved_searches.title', [], 'widget');

        $data = [];

        /** @var Requete $search */
        foreach ($searches as $search) {
            $resourceTypes = implode(array_map(function ($type) {
                return implode($type, ',');
            }, $search->getCategPointDur()), ',');

            $data[] = [
                'information' => [
                    'name' => $search->getNom(),
                    'dateSave' => $search->getDateSave(),
                ],
                'actions' => [
                    'edit' => $this->router->generate(
                        'hopital_numerique_requete_change_name',
                        ['search' => $search->getId()]
                    ),
                    'show' => $this->router->generate(
                        'hopitalnumerique_recherche_referencement_requete_popindetail',
                        ['requete' => $search->getId()]
                    ),
                    'launch' => $this->router->generate(
                        'hopital_numerique_recherche_homepage_requete_generator',
                        [
                            'refs' => implode($search->getRefs(), ','),
                            'q'    => $search->getRechercheTextuelle(),
                            'type' => $resourceTypes ?: 'null',
                        ]
                    ),
                    'send' => $this->router->generate(
                        'hopital_numerique_recherche_send',
                        ['search' => $search->getId()]
                    ),
                    'delete' => $this->router->generate(
                        'hopital_numerique_requete_delete',
                        ['search' => $search->getId()]
                    ),
                ],
            ];
        }

        $html = $this->twig->render('HopitalNumeriqueRechercheBundle:widget:saved_searches.html.twig', [
            'data' => $data,
        ]);

        $widget = new Widget('saved-searches', $title, $html);
        $widget->addExtension(new WidgetExtension('count', $this->twig->render(
            '@NewAccount/widget/extension/badge_number_extension.html.twig',
            ['number' => count($data)]
        )));

        return $widget;
    }
}
