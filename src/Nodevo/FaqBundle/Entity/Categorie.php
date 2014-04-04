<?php

namespace Nodevo\FaqBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Categorie
 *
 * @ORM\Table(name="core_faq_categorie")
 * @ORM\Entity(repositoryClass="Nodevo\FaqBundle\Repository\CategorieRepository")
 */
class Categorie
{
    /**
     * @var integer
     *
     * @ORM\Column(name="cat_id", type="integer", options = {"comment" = "ID de la catégorie de FAQ"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="cat_name", type="string", length=255, options = {"comment" = "Nom de la catégorie"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="cat_icon", type="string", length=255, options = {"comment" = "Icone de la catégorie"}, nullable=true)
     */
    private $icon;

    public function __construct()
    {
        $this->icon = null;
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
     * @return Categorie
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
     * Set icon
     *
     * @param string $icon
     * @return Categorie
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Get icon
     *
     * @return string 
     */
    public function getIcon()
    {
        return $this->icon;
    }
}
