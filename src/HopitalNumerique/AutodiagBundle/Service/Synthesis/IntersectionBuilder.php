<?php

namespace HopitalNumerique\AutodiagBundle\Service\Synthesis;

use HopitalNumerique\AutodiagBundle\Entity\AutodiagEntry;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\AutodiagBundle\Repository\AutodiagEntry\ValueRepository;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * Handle intersection creation betwheen two syntheses.
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
     * between $synthesis and $reference.
     *
     * @param User      $user
     * @param Synthesis $synthesis
     * @param Synthesis $reference
     *
     * @return Synthesis
     */
    public function build(User $user, Synthesis $synthesis, Synthesis $reference)
    {
        $attributeIds = $this->valueRepository->findAttributeIdsIntersection($synthesis, $reference);

        $intersection = Synthesis::create($synthesis->getAutodiag(), $user);
        $intersection->setName($synthesis->getName());
        foreach ($synthesis->getEntries() as $entry) {
            /** @var AutodiagEntry $clonedEntry */
            $clonedEntry = clone $entry;
            foreach ($clonedEntry->getValues() as $value) {
                if (!array_key_exists($value->getAttribute()->getId(), $attributeIds)) {
                    $clonedEntry->removeValue($value);
                }
            }

            $intersection->addEntry($clonedEntry);
            $clonedEntry->addSynthesis($intersection);
        }

        $intersection->setCreatedFrom($synthesis);

        return $intersection;
    }
}
