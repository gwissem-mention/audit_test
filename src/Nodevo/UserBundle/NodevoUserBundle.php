<?php

namespace Nodevo\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class NodevoUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}