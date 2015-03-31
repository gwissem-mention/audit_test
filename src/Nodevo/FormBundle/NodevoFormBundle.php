<?php

namespace Nodevo\FormBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class NodevoFormBundle extends Bundle
{
    public function getParent()
    {
        return 'GenemuFormBundle';
    }
}