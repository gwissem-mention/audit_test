<?php

namespace Nodevo\AclBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Acl
 *
 * @ORM\Table(name="core_acl")
 * @ORM\Entity(repositoryClass="Nodevo\AclBundle\Repository\AclRepository")
 */
class Acl
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="\Nodevo\RoleBundle\Entity\Role", cascade={"persist"})
     * @ORM\JoinColumn(name="ro_id", referencedColumnName="ro_id")
     */
    protected $role;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Ressource", cascade={"persist"})
     * @ORM\JoinColumn(name="res_id", referencedColumnName="res_id")
     */
    protected $ressource;

    /**
     * @var boolean
     *
     * @ORM\Column(name="acl_read", type="boolean", options = {"comment" = "Autoriser la lecture"})
     */
    private $read;

    /**
     * @var boolean
     *
     * @ORM\Column(name="acl_write", type="boolean", options = {"comment" = "Autoriser l écriture"})
     */
    private $write;

    public function __construct()
    {
        $this->read  = false;
        $this->write = false;
    }

    /**
     * Set role
     *
     * @param $role
     * @return Acl
     */
    public function setRole(\Nodevo\RoleBundle\Entity\Role $role)
    {
        $this->role = $role;
    
        return $this;
    }

    /**
     * Get role
     *
     * @return \Nodevo\RoleBundle\Entity\Role 
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Get ressource
     *
     * @return $ressource
     */
    public function getRessource()
    {
        return $this->ressource;
    }
    
    /**
     * Set ressource
     *
     * @param Ressource $ressource
     */
    public function setRessource( Ressource $ressource)
    {
        $this->ressource = $ressource;
    }
    
    /**
     * Get read
     *
     * @return boolean $read
     */
    public function getRead()
    {
        return $this->read;
    }
    
    /**
     * Set read
     *
     * @param boolean $read
     */
    public function setRead($read)
    {
        $this->read = $read;
    }

    /**
     * Get write
     *
     * @return boolean $write
     */
    public function getWrite()
    {
        return $this->write;
    }
    
    /**
     * Set write
     *
     * @param boolean $write
     */
    public function setWrite($write)
    {
        $this->write = $write;
    }   
}