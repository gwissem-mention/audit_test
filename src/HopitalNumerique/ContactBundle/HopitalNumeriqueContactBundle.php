<?php

namespace HopitalNumerique\ContactBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * 
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class HopitalNumeriqueContactBundle extends Bundle
{
    public function getParent()
    {
        return 'NodevoContactBundle';
    }    
}