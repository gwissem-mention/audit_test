<?php

namespace Nodevo\FaqBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use APY\DataGridBundle\Grid\Mapping as GRID;

/**
 * Faq
 *
 * @ORM\Table(name="core_faq")
 * @ORM\Entity(repositoryClass="Nodevo\FaqBundle\Repository\FaqRepository")
 */
class Faq
{
    /**
     * @var integer
     *
     * @ORM\Column(name="faq_id", type="integer", options = {"comment" = "ID de la question/réponse FAQ"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="faq_question", type="text", options = {"comment" = "Intitulé de la question"})
     */
    private $question;

    /**
     * @var string
     *
     * @ORM\Column(name="faq_reponse", type="text", options = {"comment" = "Réponse à la question"})
     */
    private $reponse;

    /**
     * @var integer
     *
     * @ORM\Column(name="faq_order", type="integer", options = {"comment" = "Ordre de l element"})
     */
    private $order;

    /**
     * @ORM\ManyToOne(targetEntity="Categorie", cascade={"persist"})
     * @ORM\JoinColumn(name="cat_id", referencedColumnName="cat_id", onDelete="CASCADE")
     * 
     * @GRID\Column(field="categorie.name", filter="select", selectFrom="source", operatorsVisible=false)
     */
    protected $categorie;

    /**
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\DomaineBundle\Entity\Domaine", cascade={"persist"})
     * @ORM\JoinTable(name="hn_domaine_gestions_faq",
     *      joinColumns={ @ORM\JoinColumn(name="faq_id", referencedColumnName="faq_id", onDelete="CASCADE")},
     *      inverseJoinColumns={ @ORM\JoinColumn(name="dom_id", referencedColumnName="dom_id", onDelete="CASCADE")}
     * )
     */
    protected $domaines;

    public function __construct()
    {
        $this->order = 0;
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
     * Set question
     *
     * @param string $question
     * @return Faq
     */
    public function setQuestion($question)
    {
        $this->question = $question;

        return $this;
    }

    /**
     * Get question
     *
     * @return string 
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set reponse
     *
     * @param string $reponse
     * @return Faq
     */
    public function setReponse($reponse)
    {
        $this->reponse = $reponse;

        return $this;
    }

    /**
     * Get reponse
     *
     * @return string 
     */
    public function getReponse()
    {
        return $this->reponse;
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
    }

    /**
     * Get categorie
     *
     * @return Categorie $categorie
     */
    public function getCategorie()
    {
        return $this->categorie;
    }
    
    /**
     * Set categorie
     *
     * @param Categorie $categorie
     */
    public function setCategorie(Categorie $categorie)
    {
        $this->categorie = $categorie;
    }

    /**
     * Add domaines
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaines
     * @return Faq
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
}
