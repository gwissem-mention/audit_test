<?php

namespace HopitalNumerique\ForumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RefTopic
 *
 * @ORM\Table("hn_forum_topic_reference")
 * @ORM\Entity(repositoryClass="HopitalNumerique\ForumBundle\Repository\RefTopicRepository")
 */
class RefTopic
{
    /**
     * @var integer
     *
     * @ORM\Column(name="reftop_id", type="integer", options = {"comment" = "ID de la référence du topic"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference")
     * @ORM\JoinColumn(name="ref_id", referencedColumnName="ref_id", onDelete="CASCADE")
     */
    private $reference;
    
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Topic", inversedBy="references")
     * @ORM\JoinColumn(name="topic_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $topic;

    /**
     * @var boolean
     *
     * @ORM\Column(name="reftop_primary", type="boolean", options = {"comment" = "La référence est de type primaire ?"})
     */
    private $primary;

    public function __construct()
    {
        $this->primary = true;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get reference
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference $reference
     */
    public function getReference()
    {
        return $this->reference;
    }
    
    /**
     * Set reference
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $reference
     */
    public function setReference(\HopitalNumerique\ReferenceBundle\Entity\Reference $reference)
    {
        $this->reference = $reference;
    }
    
    /**
     * Get topic
     *
     * @return Topic $topic
     */
    public function getTopic()
    {
        return $this->topic;
    }
    
    /**
     * Set topic
     *
     * @param Topic $topic
     */
    public function setTopic(\HopitalNumerique\ForumBundle\Entity\Topic $topic)
    {
        $this->topic = $topic;
    }
    
    /**
     * Get primary
     *
     * @return boolean $primary
     */
    public function getPrimary()
    {
        return $this->primary;
    }
    
    /**
     * Set primary
     *
     * @param boolean $primary
     */
    public function setPrimary($primary)
    {
        $this->primary = $primary;
    }
}