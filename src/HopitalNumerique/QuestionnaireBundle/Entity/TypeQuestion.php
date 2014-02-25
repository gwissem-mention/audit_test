<?php

namespace HopitalNumerique\QuestionnaireBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TypeQuestion
 *
 * @ORM\Table("hn_questionnaire_type_question")
 * @ORM\Entity(repositoryClass="HopitalNumerique\QuestionnaireBundle\Repository\TypeQuestionRepository")
 */
class TypeQuestion
{
    /**
     * @var integer
     *
     * @ORM\Column(name="typ_id", type="integer", options = {"comment" = "ID du type de question"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=255, options = {"comment" = "Libellé du type de question"})
     */
    private $libelle;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=255, options = {"comment" = "Nom du type de question"})
     */
    private $nom;


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
     * Set string
     *
     * @param string $string
     * @return TypeQuestion
     */
    public function setString($string)
    {
        $this->string = $string;

        return $this;
    }

    /**
     * Get string
     *
     * @return string 
     */
    public function getString()
    {
        return $this->string;
    }

    /**
     * Set libelle
     *
     * @param string $libelle
     * @return TypeQuestion
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
     * Set nom
     *
     * @param string $nom
     * @return TypeQuestion
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string 
     */
    public function getNom()
    {
        return $this->nom;
    }
}
