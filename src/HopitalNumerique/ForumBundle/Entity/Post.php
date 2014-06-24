<?php

namespace HopitalNumerique\ForumBundle\Entity;

use CCDNForum\ForumBundle\Entity\Post as BasePost;

/**
 * 
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class Post extends BasePost
{

    /**
     * Get body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }
        
}