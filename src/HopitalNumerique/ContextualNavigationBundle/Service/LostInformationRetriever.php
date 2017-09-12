<?php

namespace HopitalNumerique\ContextualNavigationBundle\Service;

use HopitalNumerique\DomaineBundle\DependencyInjection\CurrentDomaine;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\UserBundle\Repository\UserRepository;
use HopitalNumerique\CoreBundle\DependencyInjection\Entity;
use HopitalNumerique\ForumBundle\Repository\TopicRepository;
use HopitalNumerique\ObjetBundle\Repository\ObjetRepository;
use HopitalNumerique\DomaineBundle\Repository\DomaineRepository;
use HopitalNumerique\AutodiagBundle\Repository\AutodiagRepository;
use HopitalNumerique\ReferenceBundle\Repository\ReferenceRepository;

/**
 * Class LostInformationRetriever
 */
class LostInformationRetriever
{
    /**
     * @var Entity
     */
    protected $entityService;

    /**
     * @var ObjetRepository
     */
    protected $objectRepository;

    /**
     * @var TopicRepository
     */
    protected $topicRepository;

    /**
     * @var DomaineRepository
     */
    protected $domainRepository;

    /**
     * @var AutodiagRepository
     */
    protected $autodiagRepository;

    /**
     * @var ReferenceRepository
     */
    protected $referenceRepository;

    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var integer
     */
    protected $resourceDomainId;

    /**
     * LostInformationRetriever constructor.
     *
     * @param Entity $entityService
     * @param ObjetRepository $objetRepository
     * @param TopicRepository $topicRepository
     * @param DomaineRepository $domaineRepository
     * @param AutodiagRepository $autodiagRepository
     * @param ReferenceRepository $referenceRepository
     * @param UserRepository $userRepository
     * @param CurrentDomaine $currentDomaine
     */
    public function __construct(
        Entity $entityService,
        ObjetRepository $objetRepository,
        TopicRepository $topicRepository,
        DomaineRepository $domaineRepository,
        AutodiagRepository $autodiagRepository,
        ReferenceRepository $referenceRepository,
        UserRepository $userRepository,
        CurrentDomaine $currentDomaine
    ) {
        $this->entityService = $entityService;
        $this->objectRepository = $objetRepository;
        $this->topicRepository = $topicRepository;
        $this->domainRepository = $domaineRepository;
        $this->autodiagRepository = $autodiagRepository;
        $this->referenceRepository = $referenceRepository;
        $this->userRepository = $userRepository;
        $this->resourceDomainId = $currentDomaine->get()->getId();
    }

    /**
     * @param integer $entityType
     * @param integer $entityId
     *
     * @return array
     */
    public function getLostInformation($entityType = null, $entityId = null)
    {
        if (null !== $entityType && null !== $entityId) {
            $entity = $this->entityService->getEntityByTypeAndId($entityType, $entityId);
            $entityTitle = $this->entityService->getTitleByEntity($entity);
            $entityUrl = $this->entityService->getFrontUrlByEntity($entity);
        }

        /** @var Domaine $resourceDomain */
        $resourceDomain = $this->domainRepository->find($this->resourceDomainId);

        $lastObjects = $this->objectRepository->getLastObject($resourceDomain, 3);
        $bestRatedObjects = $this->objectRepository->getBestRatedObject($resourceDomain, 3);
        $mostViewedObjects = $this->objectRepository->getMostViewedObject($resourceDomain, 3);

        $lastPublication = current($lastObjects);
        $bestRatedPublication = current($bestRatedObjects);
        $mostViewedPublication = current($mostViewedObjects);
        $randomPublication = $this->objectRepository->getRandomObject($resourceDomain);
        $randomAutodiag = $this->autodiagRepository->getRandomAutodiagForDomain($resourceDomain);

        $references = $this->referenceRepository->getParentsByCode('CATEGORIE_OBJET');

        $last = [
            'lastObjects' => $lastObjects,
            'bestRatedObjects' => $bestRatedObjects,
            'mostViewedObjects' => $mostViewedObjects,
            'mostCommentedObjects' => $this->objectRepository->getMostCommentedObject($resourceDomain, 3),
            'lastTopics' => $this->topicRepository->getLastTopic(3),
        ];

        $stats = [
            'methodsTools' => $this->objectRepository->getProductionsCount(),
            'users' => $this->userRepository->countAllUsers(),
            'forumTopics' => $this->topicRepository->countAllTopics(),
            'cdpMembers' => $this->userRepository->countAddCDPUsers(),
        ];

        return [
            'discoverFields' => [
                'last_publication' => $lastPublication,
                'best_rated_publication' => $bestRatedPublication,
                'most_viewed_publication' => $mostViewedPublication,
                'random_publication' => $randomPublication,
            ],
            'entityTitle' => isset($entityTitle) ? $entityTitle : null,
            'entityUrl' => isset($entityUrl) ? $entityUrl : null,
            'resourceDomain' => $resourceDomain,
            'randomAutodiag' => $randomAutodiag,
            'references' => $references,
            'stats' => $stats,
            'last' => $last,
        ];
    }
}
