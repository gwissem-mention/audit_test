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
    public static $ROLE_CMSI_LABEL = 'ROLE_ARS_CMSI_4';
    public static $ROLE_DIRECTEUR_LABEL = 'ROLE_ADMINISTRATEUR_1';
    public static $ROLE_AMBASSADEUR_LABEL = 'ROLE_AMBASSADEUR_7';
    
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

    public function __construct()
    {
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
}
