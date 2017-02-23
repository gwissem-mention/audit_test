<?php

namespace HopitalNumerique\ReferenceBundle\Domain\Command;

class SwitchReferenceCommand
{
    public $currentReference;

    public $targetReference;

    public $keepHistory = false;
}
