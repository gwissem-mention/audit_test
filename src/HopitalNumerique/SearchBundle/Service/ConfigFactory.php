<?php

namespace HopitalNumerique\SearchBundle\Service;

use HopitalNumerique\DomaineBundle\DependencyInjection\CurrentDomaine;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\ReferenceBundle\Repository\ReferenceRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class ConfigFactory
{
    /**
     * @var IndexManager
     */
    protected $indexManager;

    /**
     * @var CurrentDomaine
     */
    protected $domainStorage;

    /**
     * @var ReferenceRepository
     */
    protected $referenceRepository;

    /**
     * @var TokenStorage
     */
    protected $tokenStorage;

    protected $aggregationParameter;

    /**
     * ConfigFactory constructor.
     * @param IndexManager $indexManager
     * @param CurrentDomaine $domainStorage
     * @param ReferenceRepository $referenceRepository
     * @param TokenStorage $tokenStorage
     * @param $aggregationParameter
     */
    public function __construct(
        IndexManager $indexManager,
        CurrentDomaine $domainStorage,
        ReferenceRepository $referenceRepository,
        TokenStorage $tokenStorage,
        $aggregationParameter
    ) {
        $this->indexManager = $indexManager;
        $this->domainStorage = $domainStorage;
        $this->referenceRepository = $referenceRepository;
        $this->tokenStorage = $tokenStorage;
        $this->aggregationParameter = $aggregationParameter;
    }

    /**
     * Create search configuration array
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'index' => $this->indexManager->getIndexNameByDomaine($this->domainStorage->get()),
            'aggregation' => $this->aggregationParameter,
            'texts' => $this->getTexts(),
            'options' => [
                'showCart' => is_object($this->tokenStorage->getToken()->getUser()),
            ],
        ];
    }

    /**
     * Get all angular app texts
     *
     * @return array
     */
    protected function getTexts()
    {
        $data = $this->referenceRepository->findByCode('CATEGORIE_OBJET');
        $labels = [];
        foreach ($data as $reference) {
            /** @var Reference $reference */
            $labels[$reference->getId()] = $reference->getLibelle();
        }

        return [
            'type' => [
                'object' => 'Objet',
                'content' => 'Contenu',
                'forum_post' => 'Message du forum',
                'forum_topic' => $labels[1995],
                'cdp_groups' => $labels[1998],
                'cdp_message' => $labels[4000],
                'person' => $labels[1996],
                'autodiag' => $labels[671],
            ],
            'hot' => 'Points durs',
            'productions' => 'Productions',
        ];
    }
}
