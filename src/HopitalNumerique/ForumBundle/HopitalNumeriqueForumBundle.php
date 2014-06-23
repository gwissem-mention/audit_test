<?php

namespace HopitalNumerique\ForumBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * 
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class HopitalNumeriqueForumBundle extends Bundle
{
    public function getParent()
    {
        return 'CCDNForumForumBundle';
    }    
}