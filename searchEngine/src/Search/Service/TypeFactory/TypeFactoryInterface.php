<?php

namespace Search\Service\TypeFactory;

use Elastica\Query\AbstractQuery;
use Search\Model\Query;
use Search\Model\User;

/**
 * Type factory interface
 *
 * Each implementation is responsible of producing type specific query
 */
interface TypeFactoryInterface
{
    /**
     * Get type specific query
     *
     * @param Query $query
     * @param User $user
     *
     * @return AbstractQuery
     */
    public function getQuery(Query $query, User $user);
}
