<?php

namespace HopitalNumerique\CartBundle\Service\ItemFactory\Factory;

use HopitalNumerique\CartBundle\Entity\Item;
use HopitalNumerique\CartBundle\Model\Item\GuidedSearch;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursDetails;
use HopitalNumerique\RechercheParcoursBundle\Repository\RechercheParcoursRepository;

class GuidedSearchFactory extends Factory
{
    /**
     * @var RechercheParcoursRepository $guidedSearchReferenceRepository
     */
    protected $guidedSearchReferenceRepository;

    /**
     * GuidedSearchFactory constructor.
     *
     * @param RechercheParcoursRepository $guidedSearchReferenceRepository
     */
    public function __construct(RechercheParcoursRepository $guidedSearchReferenceRepository)
    {
        $this->guidedSearchReferenceRepository = $guidedSearchReferenceRepository;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return Item::GUIDED_SEARCH_TYPE;
    }

    /**
     * @param $object
     *
     * @return GuidedSearch
     */
    public function build($object)
    {
        return new GuidedSearch($object);
    }

    /**
     * @param array $itemIds
     *
     * @return RechercheParcoursDetails[]
     */
    public function getMultiple($itemIds)
    {
        return $this->guidedSearchReferenceRepository->findBy(['id' => $itemIds]);
    }


    /**
     * @param $itemId
     *
     * @return null|RechercheParcoursDetails|object
     */
    public function get($itemId)
    {
        return $this->guidedSearchReferenceRepository->find($itemId);
    }
}
