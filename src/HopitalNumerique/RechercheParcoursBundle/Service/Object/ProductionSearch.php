<?php

namespace HopitalNumerique\RechercheParcoursBundle\Service\Object;

use HopitalNumerique\CoreBundle\Entity\ObjectIdentity\ObjectIdentity;
use HopitalNumerique\CoreBundle\Repository\ObjectIdentity\ObjectIdentityRepository;
use HopitalNumerique\ObjetBundle\Entity\Risk;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcours;
use Symfony\Component\Routing\RouterInterface;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\ObjetBundle\Repository\ObjetRepository;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearchStep;
use HopitalNumerique\DomaineBundle\DependencyInjection\CurrentDomaine;
use HopitalNumerique\RechercheParcoursBundle\DTO\Search\ProductionDTO;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursDetails;

class ProductionSearch
{
    /**
     * @var ObjetRepository $objectRepository
     */
    protected $objectRepository;

    /**
     * @var RouterInterface $router
     */
    protected $router;

    /**
     * @var CurrentDomaine $domainService
     */
    protected $domainService;

    /**
     * @var boolean $hotPoint
     */
    protected $hotPoint;

    /**
     * @var ObjectIdentityRepository $objectIdentityRepository
     */
    protected $objectIdentityRepository;

    /**
     * ProductionSearch constructor.
     *
     * @param ObjetRepository $objectRepository
     * @param RouterInterface $router
     * @param CurrentDomaine $domainService
     * @param ObjectIdentityRepository $objectIdentityRepository
     * @param boolean $hotPoint
     */
    public function __construct(
        ObjetRepository $objectRepository,
        RouterInterface $router,
        CurrentDomaine $domainService,
        ObjectIdentityRepository $objectIdentityRepository,
        $hotPoint = false
    ) {
        $this->objectRepository = $objectRepository;
        $this->router = $router;
        $this->domainService = $domainService;
        $this->hotPoint = $hotPoint;
        $this->objectIdentityRepository = $objectIdentityRepository;
    }

    /**
     * @param GuidedSearchStep $guidedSearchStep
     *
     * @return array
     */
    public function search(GuidedSearchStep $guidedSearchStep)
    {
        $parentReference = $guidedSearchStep->getGuidedSearch()->getGuidedSearchReference()->getRecherchesParcoursDetails()->filter(function (RechercheParcoursDetails $guidedSearchParent) use ($guidedSearchStep) {
            return $guidedSearchParent->getId() === $guidedSearchStep->getGuidedSearchParentReferenceId();
        })->first();

        $references = [$parentReference->getReference()->getId()];

        if ($guidedSearchStep->getGuidedSearchSubReferenceId()) {
            $references[] = $guidedSearchStep->getGuidedSearchSubReferenceId();
        }

        $objects = $this->objectRepository->getObjectForReferences($references, $this->getReferenceChildrenId($parentReference, $guidedSearchStep->getGuidedSearch()->getGuidedSearchReference()->getReference()));

        $productions = [];
        foreach ($objects as $object) {

            if (
                (!$this->hotPoint && in_array(Reference::CATEGORIE_OBJET_POINT_DUR_ID, $object->getTypeIds())) ||
                ($this->hotPoint && !in_array(Reference::CATEGORIE_OBJET_POINT_DUR_ID, $object->getTypeIds())) ||
                !$object->getDomaines()->contains($this->domainService->get())
            ) {
                continue;
            }

            $production = new ProductionDTO(
                $object->getTypeLabels(),
                $object->getTitre(),
                $object->getResume(),
                $this->router->generate('hopital_numerique_publication_publication_objet', ['id' => $object->getId()]),
                $object->isInfraDoc() ? $object->getContenus()->first()->getTitre() : null,
                $object->getSource()
            );
            $production->relatedRisks = $this->objectIdentityRepository->getRelatedObjects(ObjectIdentity::createFromDomainObject($object), [Risk::class]);
            $production->relatedHotPoints = $this->getProductionsForObject($object, true);
            $production->relatedProductions = $this->getProductionsForObject($object);

            $productions[] = $production;
        }

        return $productions;
    }

    /**
     * Get related hot points for object
     *
     * @param Objet $object
     * @param boolean $hotPoint
     *
     * @return array
     */
    private function getProductionsForObject(Objet $object, $hotPoint = false)
    {
        $relatedHotPoints = [];
        foreach ($object->getObjets() as $objectId) {
            list($type, $id) = explode(':', $objectId);

            if ($type !== 'PUBLICATION') {
                continue;
            }

            /** @var Objet $hotPoint */
            $relatedObject = $this->objectRepository->find($id);
            if (
                (
                    ($hotPoint && in_array(Reference::CATEGORIE_OBJET_POINT_DUR_ID, $relatedObject->getTypeIds())) ||
                    (!$hotPoint && !in_array(Reference::CATEGORIE_OBJET_POINT_DUR_ID, $relatedObject->getTypeIds()))
                ) &&
                in_array($this->domainService->get()->getId(), $relatedObject->getDomainesId())
            ) {
                $relatedHotPoints[] = $relatedObject;
            }
        }

        return $relatedHotPoints;
    }

    /**
     * @param Reference $reference
     *
     * @return array
     */
    private function getReferenceChildrenId(RechercheParcoursDetails $parentReference, Reference $reference)
    {
        if (!$parentReference->getShowChildren()) {
            return [];
        }
        
        $ids = [];
        $ids[] = $reference->getId();

        foreach ($reference->getEnfants() as $child) {
            $ids = array_merge($ids, $this->getReferenceChildrenId($parentReference, $child));
        }

        return $ids;
    }
}
