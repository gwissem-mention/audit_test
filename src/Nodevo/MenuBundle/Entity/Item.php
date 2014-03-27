<?php

namespace Nodevo\MenuBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Doctrine\Common\Collections\Criteria;

//Asserts Stuff
use Symfony\Component\Validator\Constraints as Assert;
use Nodevo\ToolsBundle\Validator\Constraints as Nodevo;

/**
 * Menu
 *
 * @ORM\Table("core_menu_item")
 * @ORM\Entity(repositoryClass="Nodevo\MenuBundle\Repository\ItemRepository")
 */
class Item
{
    /**
     * @var integer
     *
     * @ORM\Column(name="itm_id", type="integer", options = {"comment" = "ID du lien"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(message="Le nom ne peut pas être vide.")
     * @Assert\Length(
     *      min = "1",
     *      max = "255",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans le nom.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans le nom."
     * )
     * @Nodevo\Javascript(class="validate[required,minSize[1],maxSize[255]]")
     * @ORM\Column(name="itm_name", type="string", length=255, options = {"comment" = "Nom du lien"})
     */
    private $name;

    /**
     * @var string
     * 
     * @ORM\Column(name="itm_route", type="string", length=255, nullable=true, options = {"comment" = "Route associée"})
     */
    private $route;

    /**
     * @var string
     *
     * @ORM\Column(name="itm_route_parameters", type="string", length=255, nullable=true, options = {"comment" = "Paramètres de la route"})
     */
    private $routeParameters;

    /**
     * @var boolean
     *
     * @ORM\Column(name="itm_route_absolute", type="boolean", nullable=true, options = {"comment" = "Le lien est-il absolu ?"})
     */
    private $routeAbsolute;

    /**
     * @var string
     * @Assert\Length(
     *      max = "255",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans le nom."
     * )
     * @ORM\Column(name="itm_uri", type="string", length=255, nullable=true, options = {"comment" = "Lien extérieur"})
     */
    private $uri;

    /**
     * @var string
     *
     * @ORM\Column(name="itm_icon", type="string", length=32, nullable=true, options = {"comment" = "Icône du lien"})
     */
    private $icon;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Item")
     * @ORM\JoinColumn(name="itm_parent", referencedColumnName="itm_id", nullable=true)
     */
    private $parent;

    /**
     * @var boolean
     *
     * @ORM\Column(name="itm_display", type="boolean", nullable=true, options = {"comment" = "Afficher le lien ?"})
     */
    private $display;

    /**
     * @var boolean
     *
     * @ORM\Column(name="itm_display_children", type="boolean", nullable=false, options = {"comment" = "Afficher les enfants ?"})
     */
    private $displayChildren;

    /**
     * @var string
     *
     * @ORM\Column(name="itm_role", type="string", length=255, options = {"comment" = "Rôle pouvant visualiser le lien"})
     */
    private $role;

    /**
     * @var integer
     * @Assert\NotBlank(message="L'ordre ne peut pas être vide.")
     * @Nodevo\Javascript(class="validate[required]")
     * @ORM\Column(name="itm_order", type="integer", options = {"comment" = "Ordre du lien"})
     */
    private $order;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Menu", inversedBy="items")
     * @ORM\JoinColumn(name="mnu_menu", referencedColumnName="mnu_id")
     */
    private $menu;

    public function __construct()
    {
        $this->displayChildren = false;
        $this->order           = 0;
        $this->icon            = null;
        $this->role            = 'IS_AUTHENTICATED_ANONYMOUSLY';
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
     * Set order
     *
     * @return integer 
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order
     *
     * @return integer 
     */
    public function getOrder()
    {
        return $this->order;
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
     * Set route
     *
     * @param string $route
     * @return Menu
     */
    public function setRoute($route)
    {
        $this->route = $route;
    
        return $this;
    }

    /**
     * Get route
     *
     * @return string 
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Set parent
     *
     * @param integer $parent
     * @return Menu
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    
        return $this;
    }

    /**
     * Get parent
     *
     * @return integer 
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set role
     *
     * @param string $role
     * @return Menu
     */
    public function setRole($role)
    {
        $this->role = $role;
    
        return $this;
    }

    /**
     * Get role
     *
     * @return string 
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Get icon
     *
     * @return string $icon
     */
    public function getIcon()
    {
        return $this->icon;
    }
    
    /**
     * Set icon
     *
     * @param string $icon
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    /**
     * Set uri
     *
     * @param string $uri
     * @return Item
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
    
        return $this;
    }

    /**
     * Get uri
     *
     * @return string 
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Set routeParameters
     *
     * @param string $routeParameters
     *
     * @return Item
     */
    public function setRouteParameters($routeParameters)
    {
        $this->routeParameters = $routeParameters;

        return $this;
    }

    /**
     * Get routeParameters
     *
     * @return string 
     */
    public function getRouteParameters()
    {
        return $this->routeParameters;
    }

    /**
     * Set display
     *
     * @param boolean $display
     *
     * @return Item
     */
    public function setDisplay($display)
    {
        $this->display = $display;

        return $this;
    }

    /**
     * Get display
     *
     * @return boolean 
     */
    public function getDisplay()
    {
        return $this->display;
    }
    
    /**
     * Set display
     *
     * @param boolean $display
     *
     * @return Item
     */
    public function toggleDisplay()
    {
        $this->setDisplay(!$this->getdisplay());

        return $this;
    }

    /**
     * Set displayChildren
     *
     * @param boolean $displayChildren
     *
     * @return Item
     */
    public function setDisplayChildren($displayChildren)
    {
        $this->displayChildren = $displayChildren;

        return $this;
    }

    /**
     * Get displayChildren
     *
     * @return boolean 
     */
    public function getDisplayChildren()
    {
        return $this->displayChildren;
    }

    /**
     * Set routeAbsolute
     *
     * @param boolean $routeAbsolute
     *
     * @return Item
     */
    public function setRouteAbsolute($routeAbsolute)
    {
        $this->routeAbsolute = $routeAbsolute;

        return $this;
    }

    /**
     * Get routeAbsolute
     *
     * @return boolean 
     */
    public function getRouteAbsolute()
    {
        return $this->routeAbsolute;
    }

    public function getMenu()
    {
        return $this->menu;
    }

    public function getMenuId()
    {
        return $this->menu->getId();
    }

    public function setMenu( Menu $menu )
    {
        $this->menu = $menu;
    }

    public function getChildsFromCollection( PersistentCollection $collection )
    {
        $criteria = Criteria::create()
                                    ->where(Criteria::expr()->eq("parent", $this))
                                    ->orderBy( array("order" => Criteria::ASC) );
        return $collection->matching( $criteria );
    }

    public function __toString()
    {
        return $this->name.'';
    }
}