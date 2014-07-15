<?php

namespace HopitalNumerique\RechercheBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ExpBesoin
 *
 * @ORM\Table(name="hn_recherche_expbesoin")
 * @ORM\Entity(repositoryClass="HopitalNumerique\RechercheBundle\Repository\ExpBesoinRepository")
 */
class ExpBesoin
{
    
    /**
     * @var integer
     *
     * @ORM\Column(name="expb_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="expb_order", type="smallint", options = {"comment" = "Ordre de la question"})
     */
    protected $order;

    /**
     * @var string
     *
     * @ORM\Column(name="expb_libelle", type="string", length=255)
     */
    protected $libelle;


    /**
     * Liste des inscriptions liées au module
     *
     * @var /HopitalNumerique/RechercheBundle/Entity/ExpBesoinReponses
     *
     * @ORM\OneToMany(targetEntity="ExpBesoinReponses", mappedBy="question", cascade={"persist", "remove" })
     * @ORM\OrderBy({"order" = "ASC"})
     */
    protected $reponses;


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
     * Get order
     *
     * @return integer $order
     */
    public function getOrder()
    {
        return $this->order;
    }
    
    /**
     * Set order
     *
     * @param integer $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * Set libelle
     *
     * @param string $libelle
     * @return ExpBesoin
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * Get libelle
     *
     * @return string 
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * Add reponses
     *
     * @param \HopitalNumerique\RechercheBundle\Entity\ExpBesoinReponses $reponses
     * @return Menu
     */
    public function addReponse(\HopitalNumerique\RechercheBundle\Entity\ExpBesoinReponses $reponses)
    {
        $this->reponses[] = $reponses;
    
        return $this;
    }

    /**
     * Remove reponses
     *
     * @param \HopitalNumerique\RechercheBundle\Entity\ExpBesoinReponses $reponses
     */
    public function removeReponse(\HopitalNumerique\RechercheBundle\Entity\ExpBesoinReponses $reponses)
    {
        $this->reponses->removeElement($reponses);
    }

    /**
     * Get reponses
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getReponses()
    {
        return $this->reponses;
    }
}
