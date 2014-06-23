<?php

namespace HopitalNumerique\FlashBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Flash
 *
 * @ORM\Table(name="hn_flash")
 * @ORM\Entity(repositoryClass="HopitalNumerique\FlashBundle\Repository\FlashRepository")
 */
class Flash
{
    /**
     * @var integer
     *
     * @ORM\Column(name="fla_id", type="integer", options = {"comment" = "ID du flash message"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="fla_title", type="string", length=255, options = {"comment" = "Titre du flash message"})
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="fla_content", type="text", options = {"comment" = "Contenu du flash message"})
     */
    private $content;

    /**
     * @var boolean
     *
     * @ORM\Column(name="fla_is_published", type="boolean", options = {"comment" = "Le flash message est publie ?"})
     */
    private $isPublished;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fla_date_creation", type="datetime", options = {"comment" = "Date de création du flash"})
     */
    private $dateCreation;

    /**
     * @ORM\ManyToMany(targetEntity="\Nodevo\RoleBundle\Entity\Role")
     * @ORM\JoinTable(name="hn_flash_role",
     *      joinColumns={ @ORM\JoinColumn(name="fla_id", referencedColumnName="fla_id")},
     *      inverseJoinColumns={ @ORM\JoinColumn(name="ro_id", referencedColumnName="ro_id")}
     * )
     */
    protected $roles;

    /**
     * Initialisation de l'entitée (valeurs par défaut)
     */
    public function __construct()
    {
        $this->isPublished  = true;
        $this->dateCreation = new \DateTime;
        $this->roles        = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set title
     *
     * @param string $title
     * @return Flash
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set content
     *
     * @param string $content
     * @return Flash
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string 
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set isPublished
     *
     * @param boolean $isPublished
     * @return Flash
     */
    public function setIsPublished($isPublished)
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    /**
     * Get isPublished
     *
     * @return boolean 
     */
    public function getIsPublished()
    {
        return $this->isPublished;
    }

    /**
     * Get dateCreation
     *
     * @return \DateTime $dateCreation
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }
    
    /**
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    /**
     * Add role
     *
     * @param \Nodevo\RoleBundle\Entity\Role $role
     * @return Flash
     */
    public function addRole(\Nodevo\RoleBundle\Entity\Role $role)
    {
        $this->roles[] = $role;
    
        return $this;
    }

    /**
     * Remove role
     *
     * @param \Nodevo\RoleBundle\Entity\Role $role
     */
    public function removeRole($role)
    {
        $this->roles->removeElement($role);
    }

    /**
     * Set roles
     *
     * @param \Doctrine\Common\Collections\Collection $roles
     * @return Flash
     */
    public function setRoles(array $roles)
    {        
        $this->roles = $roles;
    
        return $this;
    }

    /**
     * Get roles
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRoles()
    {
        return $this->roles;
    }
}
