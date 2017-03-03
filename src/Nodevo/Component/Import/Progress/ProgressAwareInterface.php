<?php

namespace Nodevo\Component\Import\Progress;

interface ProgressAwareInterface
{
    public function setProgress(Progress $progress);
}
