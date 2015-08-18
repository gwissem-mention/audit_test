<?php

namespace Nodevo\TexteDynamiqueBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Code
 *
 * @ORM\Table(name="core_textedynamique_code")
 * @ORM\Entity(repositoryClass="Nodevo\TexteDynamiqueBundle\Repository\CodeRepository")
 */
class Code
{
    /**
     * @var integer
     *
     * @ORM\Column(name="txt_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="txt_code", type="string", length=255)
     */
    protected $code;

    /**
     * @var string
     *
     * @ORM\Column(name="txt_texte", type="text")
     */
    protected $texte;

    /**
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\DomaineBundle\Entity\Domaine", cascade={"persist"})
     * @ORM\JoinTable(name="core_textedynamique_code_domaine",
     *      joinColumns={ @ORM\JoinColumn(name="txt_id", referencedColumnName="txt_id", onDelete="CASCADE")},
     *      inverseJoinColumns={ @ORM\JoinColumn(name="dom_id", referencedColumnName="dom_id", onDelete="CASCADE")}
     * )
     */
    protected $domaines;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->domaines = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set code
     *
     * @param string $code
     * @return Code
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string 
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set texte
     *
     * @param string $texte
     * @return Code
     */
    public function setTexte($texte)
    {
        $this->texte = $texte;

        return $this;
    }

    /**
     * Get texte
     *
     * @return string 
     */
    public function getTexte()
    {
        return $this->texte;
    }

    /**
     * Add domaines
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaines
     * @return Code
     */
    public function addDomaine(\HopitalNumerique\DomaineBundle\Entity\Domaine $domaines)
    {
        $this->domaines[] = $domaines;

        return $this;
    }

    /**
     * Remove domaines
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaines
     */
    public function removeDomaine(\HopitalNumerique\DomaineBundle\Entity\Domaine $domaines)
    {
        $this->domaines->removeElement($domaines);
    }

    /**
     * Get domaines
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDomaines()
    {
        return $this->domaines;
    }

    /**
     * Set domaines
     *
     * @return Code
     */
    public function setDomaines($domaines = array())
    {
        $this->domaines = $domaines;

        return $this;
    }
}
