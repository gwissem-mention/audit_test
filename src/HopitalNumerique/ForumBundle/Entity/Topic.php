<?php

namespace HopitalNumerique\ForumBundle\Entity;

use CCDNForum\ForumBundle\Entity\Topic as BaseTopic;

/**
 * 
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class Topic extends BaseTopic
{
    /**
     * @return string
     */
    public function __toString()
    {
        return $this->title;
    }
}
