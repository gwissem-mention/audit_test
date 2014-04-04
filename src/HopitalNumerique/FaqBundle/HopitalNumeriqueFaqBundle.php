<?php

namespace HopitalNumerique\FaqBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class HopitalNumeriqueFaqBundle extends Bundle
{
    public function getParent()
    {
        return 'NodevoFaqBundle';
    }  
}