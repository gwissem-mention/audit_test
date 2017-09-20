<?php

namespace HopitalNumerique\SearchBundle\Service\Indexable;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;

/**
 * Group type indexable.
 * This class is responsible of saying if a Group is indexable
 */
class GroupIndexable
{
    /**
     * @var string
     */
    protected $domaineSlug;

    /**
     * GroupIndexable constructor.
     * @param string $domaineSlug
     */
    public function __construct($domaineSlug)
    {
        $this->domaineSlug = $domaineSlug;
    }

    /**
     * Check if $group is indexable
     *
     * @param Groupe $group
     * @return bool
     */
    public function isIndexable(Groupe $group)
    {
        foreach ($group->getDomains() as $domain) {
            if ($domain->getSlug() === $this->domaineSlug) {
                return true;
            }
        }

        return false;
    }
}
