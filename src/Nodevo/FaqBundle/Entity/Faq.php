<?php

namespace Nodevo\FaqBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

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
     * @ORM\ManyToOne(targetEntity="Categorie", cascade={"persist"})
     * @ORM\JoinColumn(name="cat_id", referencedColumnName="cat_id", onDelete="CASCADE")
     */
    protected $categorie;

    public function __construct()
    {

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
}
