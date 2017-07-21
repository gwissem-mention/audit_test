<?php

namespace HopitalNumerique\ContextualNavigationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class LostController extends Controller
{
    /**
     * @param integer $entityType
     * @param integer $entityId
     * 
     * @return Response
     */
    public function lostAction($entityType, $entityId)
    {
        $entityService = $this->get('hopitalnumerique_core.dependency_injection.entity');
        $objectRepository = $this->get('hopitalnumerique_objet.repository.objet');
        $forumRepository = $this->get('hopitalnumerique_forum.repository.topic');

        $resourceDomain = $this->get('hopitalnumerique_domaine.repository.domaine')->find($this->getParameter('resource_domain_id'));

        $entity = $entityService->getEntityByTypeAndId($entityType, $entityId);

        $lastObjects = $objectRepository->getLastObject($resourceDomain, 3);
        $bestRatedObjects = $objectRepository->getBestRatedObject($resourceDomain, 3);
        $mostViewedObjects = $objectRepository->getMostViewedObject($resourceDomain, 3);

        $entityTitle = $entityService->getTitleByEntity($entity);
        $lastPublication = current($lastObjects);
        $bestRatedPublication = current($bestRatedObjects);
        $mostViewedPublication = current($mostViewedObjects);
        $randomPublication = $objectRepository->getRandomObject($resourceDomain);
        $randomAutodiag = $this->get('autodiag.repository.autodiag')->getRandomAutodiagForDomain($resourceDomain);

        $references = $this->get('hopitalnumerique_reference.repository.reference')->getParentsByCode('CATEGORIE_OBJET');

        $last = [
            'lastObjects' => $lastObjects,
            'bestRatedObjects' => $bestRatedObjects,
            'mostViewedObjects' => $mostViewedObjects,
            'mostCommentedObjects' => $objectRepository->getMostCommentedObject($resourceDomain, 3),
            'lastTopics' => $forumRepository->getLastTopic(3),
        ];

        $stats = [
            'methodsTools' => $objectRepository->getProductionsCount(),
            'users' => $this->get('hopitalnumerique_user.repository.user')->countAllUsers(),
            'forumTopics' => $forumRepository->countAllTopics(),
            'cdpMembers' => $this->get('hopitalnumerique_user.repository.user')->countAddCDPUsers(),
        ];

        return $this->render('HopitalNumeriqueContextualNavigationBundle:lost:lost.html.twig', [
            'entityTitle' => $entityTitle,
            'resourceDomain' => $resourceDomain,
            'lastPublication' => $lastPublication,
            'bestRatedPublication' => $bestRatedPublication,
            'mostViewedPublication' => $mostViewedPublication,
            'randomPublication' => $randomPublication,
            'randomAutodiag' => $randomAutodiag,
            'references' => $references,
            'stats' => $stats,
            'last' => $last,
        ]);
    }
}
