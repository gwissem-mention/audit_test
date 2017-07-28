<?php

namespace HopitalNumerique\RechercheParcoursBundle\Service\Widget;

use Nodevo\ToolsBundle\Tools\Chaine;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;
use HopitalNumerique\NewAccountBundle\Model\Widget\Widget;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearch;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\WidgetAbstract;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\DomainAwareTrait;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\DomainAwareInterface;
use HopitalNumerique\RechercheParcoursBundle\Repository\GuidedSearchRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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
     * GuidedSearchWidget constructor.
     *
     * @param \Twig_Environment      $twig
     * @param TokenStorageInterface  $tokenStorage
     * @param TranslatorInterface    $translator
     * @param RouterInterface        $router
     * @param GuidedSearchRepository $guidedSearchRepository
     */
    public function __construct(
        \Twig_Environment $twig,
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator,
        RouterInterface $router,
        GuidedSearchRepository $guidedSearchRepository
    ) {
        parent::__construct($twig, $tokenStorage, $translator);

        $this->router = $router;
        $this->guidedSearchRepository = $guidedSearchRepository;
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
            $sharedWith = null;

            if ($guidedSearch->getShares()->count() > 0) {
                $userList = array_filter(array_map(
                    function ($share) {
                        /** @var User $share */
                        if ($this->tokenStorage->getToken()->getUser()->getId() !== $share->getId()) {
                            return $share->getFirstname() . " " . $share->getLastname();
                        }

                        return null;
                    },
                    $guidedSearch->getShares()->toArray()
                ));

                $sharedWith = count($userList) > 0 ? $this->translator->trans('guided_search.shared_with', [], 'widget') . " " . implode(", ", $userList) : null;
            }

            $data[] = [
                'information' => [
                    'creationDate' => $guidedSearch->getCreatedAt()->format('d/m/y'),
                    'name' => $guidedSearch->getGuidedSearchReference()->getReference()->getLibelle(),
                    'status' => $guidedSearch->getShares()->count() > 0
                        ? $this->translator->trans('guided_search.status.shared', [], 'widget')
                        : $this->translator->trans('guided_search.status.private', [], 'widget')
                    ,
                ],
                'sharedWith' => $sharedWith,
                'actions' => [
                    'continue' => $this->router->generate('hopital_numerique_guided_search_show_guided_search', [
                        'guidedSearchReference'      => $guidedSearchReference->getId(),
                        'guidedSearchReferenceAlias' => (new Chaine($guidedSearchReference->getReference()->getLibelle()))->minifie(),
                        'guidedSearch' => $guidedSearch->getId()
                    ]),
                    'send' => $this->router->generate('hopital_numerique_guided_search_send', [
                        'guidedSearch' => $guidedSearch->getId(),
                    ]),
                    'delete' => $this->router->generate('hopital_numerique_guided_search_delete', [
                        'guidedSearch' => $guidedSearch->getId(),
                    ]),
                ],
            ];
        }

        $html = $this->twig->render('HopitalNumeriqueRechercheParcoursBundle:widget:guided_search.html.twig', [
            'data' => $data,
        ]);

        $title = $this->translator->trans('guided_search.title', [], 'widget');

        return new Widget('guided-search', $title, $html);
    }
}
