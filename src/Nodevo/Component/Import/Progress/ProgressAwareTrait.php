<?php

namespace Nodevo\Component\Import\Progress;

/**
 * Basic Implementation of ProgressAwareInterface.
 */
trait ProgressAwareTrait
{
    /** @var Progress */
    protected $progress;

    /**
     * Sets progress.
     *
     * @param Progress $progress
     */
    public function setProgress(Progress $progress)
    {
        $this->progress = $progress;
    }
}
