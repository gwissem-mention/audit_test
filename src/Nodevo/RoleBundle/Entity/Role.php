<?php

namespace Nodevo\RoleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Role\RoleInterface;

//Asserts Stuff
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Nodevo\ToolsBundle\Validator\Constraints as Nodevo;

/**
 * Role
 *
 * @ORM\Table(name="core_role")
 * @ORM\Entity(repositoryClass="Nodevo\RoleBundle\Repository\RoleRepository")
 * @UniqueEntity(fields="name", message="Ce groupe existe déjà.")
 */
class Role implements RoleInterface
{    
    /**
     * @var integer
     *
     * @ORM\Column(name="ro_id", type="integer", options = {"comment" = "ID du groupe"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @Assert\NotBlank(message="Le nom ne peut pas être vide.")
     * @Assert\Length(
     *      min = "3",
     *      max = "255",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans le nom.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans le nom."
     * )
     * @Nodevo\Javascript(class="validate[required,minSize[3],maxSize[255]]")
     * @ORM\Column(name="ro_name", type="string", length=255, options = {"comment" = "Nom du groupe"})
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="ro_role", type="string", length=70, unique=true, options = {"comment" = "Code du groupe"})
     */
    protected $role;

    /**
     * @var boolean
     *
     * @ORM\Column(name="ro_initial", type="boolean", options = {"comment" = "Le groupe est-il initial ?"})
     */
    protected $initial;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_etat", referencedColumnName="ref_id")
     */
    protected $etat;

    /**
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\UserBundle\Entity\User", mappedBy="roles")
     */
    protected $users;

    public function __construct()
    {
        $this->users   = new \Doctrine\Common\Collections\ArrayCollection();
        $this->initial = false;
    }

    public function __toString()
    {
        return (string) $this->role;
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
     * Set name
     *
     * @param string $name
     *
     * @return Role
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get role
     *
     * @return string $role
     */
    public function getRole()
    {
        return $this->role;
    }
    
    /**
     * Set role
     *
     * @param string $role
     */
    public function setRole($role)
    {
        $this->role = $role;
    }
    
    /**
     * Set initial
     *
     * @param boolean $initial
     *
     * @return Role
     */
    public function setInitial($initial)
    {
        $this->initial = $initial;

        return $this;
    }

    /**
     * Get initial
     *
     * @return boolean 
     */
    public function getInitial()
    {
        return $this->initial;
    }

    /**
     * Set etat
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $etat
     *
     * @return Role
     */
    public function setEtat(\HopitalNumerique\ReferenceBundle\Entity\Reference $etat)
    {
        $this->etat = $etat;

        return $this;
    }

    /**
     * Get etat
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference 
     */
    public function getEtat()
    {
        return $this->etat;
    }

    /**
     * Add user
     *
     * @param \HopitalNumerique\userBundle\Entity\User $user
     * @return Db
     */
    public function addUser(\HopitalNumerique\userBundle\Entity\User $user)
    {
        $this->users[] = $user;
    
        return $this;
    }

    /**
     * Remove user
     *
     * @param \HopitalNumerique\userBundle\Entity\User $user
     */
    public function removeUser(\HopitalNumerique\userBundle\Entity\User $user)
    {
        $this->users->removeElement($user);
    }

    /**
     * Set users
     *
     * @param \Doctrine\Common\Collections\Collection $users
     * @return Db
     */
    public function setUsers(\Doctrine\Common\Collections\Collection $users)
    {
        $this->users = $users;
    
        return $this;
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsers()
    {
        return $this->users;
    }
}