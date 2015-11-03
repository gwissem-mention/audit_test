<?php
namespace HopitalNumerique\QuestionnaireBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Occurrence.
 *
 * @ORM\Table("hn_questionnaire_occurrence")
 * @ORM\Entity()
 */
class Occurrence
{
    /**
     * @var integer
     *
     * @ORM\Column(name="occ_id", type="integer", options={"unsigned"="true"})
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @var string
     *
     * @ORM\Column(name="occ_libelle", type="string", length=255, nullable=false)
     */
    protected $libelle;


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
     * Set libelle
     *
     * @param string $libelle
     * @return Occurrence
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
}
