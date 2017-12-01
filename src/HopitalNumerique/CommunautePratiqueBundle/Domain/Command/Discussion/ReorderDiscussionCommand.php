<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Domain\Command\Discussion;

/**
 * Class ReorderDiscussionCommand
 */
class ReorderDiscussionCommand
{
    /**
     * @var array $order
     */
    public $order;

    /**
     * ReorderDiscussionCommand constructor.
     *
     * @param array $order
     */
    public function __construct(array $order)
    {
        $this->order = $order;
    }
}
