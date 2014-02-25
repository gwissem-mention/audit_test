<?php

namespace Nodevo\MenuBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

//Asserts Stuff
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Nodevo\ToolsBundle\Validator\Constraints as Nodevo;

/**
 * Menu
 *
 * @ORM\Table("core_menu")
 * @ORM\Entity(repositoryClass="Nodevo\MenuBundle\Repository\MenuRepository")
 * @UniqueEntity(fields="alias", message="Cet alias de menu existe déjà.")
 */
class Menu
{
    /**
     * @var integer
     *
     * @ORM\Column(name="mnu_id", type="integer", options = {"comment" = "ID du menu"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(message="Le nom ne peut pas être vide.")
     * @Assert\Length(
     *      min = "3",
     *      max = "100",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans le nom.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans le nom."
     * )
     * @Nodevo\Javascript(class="validate[required,minSize[3],maxSize[100]]")
     * @ORM\Column(name="mnu_name", type="string", length=100, options = {"comment" = "Nom du menu"})
     */
    private $name;

    /**
     * @var string
     * @Assert\NotBlank(message="L'alias ne peut pas être vide.")
     * @Assert\Length(
     *      min = "3",
     *      max = "255",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans l'alias.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans l'alias."
     * )
     * @Nodevo\Javascript(class="validate[required,minSize[3],maxSize[255]]")
     * @ORM\Column(name="mnu_alias", type="string", length=255, options = {"comment" = "Alias du menu"})
     */
    private $alias;

    /**
     * @ORM\OneToMany(targetEntity="Item", mappedBy="menu", cascade={"persist", "remove" })
     * @ORM\OrderBy({"order" = "ASC"})
     */
    private $items;

    /**
     * @var string
     * @Assert\Length(
     *      max = "32",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans la classe CSS."
     * )
     * @Nodevo\Javascript(class="validate[maxSize[32]]")
     * @ORM\Column(name="mnu_cssClass", type="string", length=32, nullable=true, options = {"comment" = "Classe CSS associée"})
     */
    private $cssClass;

    /**
     * @var string
     * @Assert\Length(
     *      max = "32",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans l'ID CSS."
     * )
     * @Nodevo\Javascript(class="validate[maxSize[32]]")
     * @ORM\Column(name="mnu_cssId", type="string", length=32, nullable=true, options = {"comment" = "ID CSS associé"})
     */
    private $cssId;

    /**
     * @var boolean
     *
     * @ORM\Column(name="mnu_lock", type="boolean", options = {"comment" = "Verrouillage du menu ?"})
     */
    private $lock;

    /**
     * @var string
     *
     */
    private $tree;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->items    = new \Doctrine\Common\Collections\ArrayCollection();
        $this->cssClass = null;
        $this->cssId    = null;
        $this->lock     = false;
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
     * @return Menu
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
     * Set alias
     *
     * @param string $alias
     * @return Menu
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
    
        return $this;
    }

    /**
     * Get alias
     *
     * @return string 
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Add items
     *
     * @param \Nodevo\MenuBundle\Entity\Item $items
     * @return Menu
     */
    public function addItem(\Nodevo\MenuBundle\Entity\Item $items)
    {
        $this->items[] = $items;
    
        return $this;
    }

    /**
     * Remove items
     *
     * @param \Nodevo\MenuBundle\Entity\Item $items
     */
    public function removeItem(\Nodevo\MenuBundle\Entity\Item $items)
    {
        $this->items->removeElement($items);
    }

    /**
     * Get items
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Get cssClass
     *
     * @return string $cssClass
     */
    public function getCssClass()
    {
        return $this->cssClass;
    }
    
    /**
     * Set cssClass
     *
     * @param string $cssClass
     */
    public function setCssClass($cssClass)
    {
        $this->cssClass = $cssClass;
    }
    
    /**
     * Get cssId
     *
     * @return string $cssId
     */
    public function getCssId()
    {
        return $this->cssId;
    }
    
    /**
     * Set cssId
     *
     * @param string $cssId
     */
    public function setCssId($cssId)
    {
        $this->cssId = $cssId;
    }
 
    /**
     * Get lock
     *
     * @return boolean $lock
     */
    public function getLock()
    {
        return $this->lock;
    }
    
    /**
     * Set lock
     *
     * @param boolean $lock
     */
    public function setLock($lock)
    {
        $this->lock = $lock;
    }
    
    
    public function getTree()
    {
        return null === $this->tree ? null : unserialize($this->tree);
    }

    public function setTree( $tree )
    {
        $this->tree = serialize($tree);
        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }
}