<?php

namespace HopitalNumerique\ContactBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class HopitalNumeriqueContactBundle extends Bundle
{
    public function getParent()
    {
        return 'NodevoContactBundle';
    }    
}