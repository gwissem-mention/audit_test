<?php

namespace HopitalNumerique\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class HopitalNumeriqueUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}