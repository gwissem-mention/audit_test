<?php

namespace HopitalNumerique\AutodiagBundle\Service\Synthesis;

use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\AutodiagBundle\Repository\AutodiagEntry\ValueRepository;

/**
 * Handle intersection creation betwheen two syntheses
 *
 * @package HopitalNumerique\AutodiagBundle\Service\Synthesis
 */
class IntersectionBuilder
{
    /** @var ValueRepository */
    protected $valueRepository;

    /**
     * IntersectionBuilder constructor.
     *
     * @param ValueRepository $valueRepository
     */
    public function __construct(ValueRepository $valueRepository)
    {
        $this->valueRepository = $valueRepository;
    }

    /**
     * Create new Synthesis from $synthesis, only with entryValues of common answered autodiag attributes
     * between $synthesis and $reference
     *
     * @param Synthesis $synthesis
     * @param Synthesis $reference
     * @return Synthesis
     */
    public function build(Synthesis $synthesis, Synthesis $reference)
    {
        $attributeIds = $this->valueRepository->findAttributeIdsIntersection($synthesis, $reference);

        $intersection = Synthesis::create($synthesis->getAutodiag(), $synthesis->getUser());
        foreach ($synthesis->getEntries() as $entry) {
            $clonedEntry = clone($entry);
            foreach ($clonedEntry->getValues() as $value) {
                if (!array_key_exists($value->getAttribute()->getId(), $attributeIds)) {
                    $clonedEntry->removeValue($value);
                }
            }

            $intersection->addEntry($clonedEntry);
        }

        return $intersection;
    }
}
