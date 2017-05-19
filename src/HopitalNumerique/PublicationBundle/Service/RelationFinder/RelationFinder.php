<?php

namespace HopitalNumerique\PublicationBundle\Service\RelationFinder;

use HopitalNumerique\PublicationBundle\Service\RelationFinder\Finders\FinderInterface;

/**
 * Cas traités dans les finders :
 *
 * A = objet source
 * A1 = contenu de l'objet A
 * A2 = contenu de l'objet A
 * A2.1 = contenu enfant du contenu A2 de l'objet A
 *
 * B = objet destination
 * B1 = contenu de l'objet B
 * B2 = contenu de l'objet B
 * B2.1 = contenu enfant du contenu B2 de l'objet B
 */
class RelationFinder
{
    protected $finders = [];

    /**
     * @param FinderInterface $finder
     */
    public function addFinder(FinderInterface $finder)
    {
        $this->finders[] = $finder;
    }

    /**
     * @param $data
     *
     * @return array
     */
    public function findRelations($data)
    {
        $relations = [];

        foreach ($this->finders as $finder) {
            if ($finder->support($data)) {
                $relations = array_merge($relations, $finder->find($data));
            }
        }

        $relations = array_map("unserialize", array_unique(array_map("serialize", $relations)));

        return $relations;
    }
}
