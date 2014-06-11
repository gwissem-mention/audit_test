<?php

namespace HopitalNumerique\ForumBundle\Entity;

use CCDNForum\ForumBundle\Entity\Topic as BaseTopic;
use Doctrine\ORM\Mapping as ORM;

/**
 * 
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class Topic extends BaseTopic
{
    /**
     * @ORM\OneToMany(targetEntity="\HopitalNumerique\ForumBundle\Entity\RefTopic", mappedBy="topic", cascade={"persist", "remove" })
     */
    protected $references;

    /**
     * Get references
     *
     * @return \Doctrine\Common\Collections\ArrayCollection $references
     */
    public function getReferences()
    {
        return is_null($this->references) ? array() : $this->references;
    }

    /**
     * Set references
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $references
     * @return Topic
     */
    public function setReferences(\Doctrine\Common\Collections\ArrayCollection $references)
    {        
        $this->references = $references;
    
        return $this;
    }
}