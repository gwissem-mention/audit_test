<?php

namespace HopitalNumerique\ContactBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nodevo\ContactBundle\Entity\Contact as NodevoContact;

//Asserts Stuff
use Symfony\Component\Validator\Constraints as Assert;
use Nodevo\ToolsBundle\Validator\Constraints as Nodevo;


/**
 * Contact
 *
 * @ORM\Table("hn_contact")
 * @ORM\Entity(repositoryClass="HopitalNumerique\ContactBundle\Repository\ContactRepository")
 */
class Contact extends NodevoContact
{
    /**
     * @var string
     * @Assert\Length(
     *      min = "1",
     *      max = "255",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans la fonction de la structure.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans la fonction de la structure."
     * )
     * @Nodevo\Javascript(class="validate[minSize[1],maxSize[255]]")
     * @ORM\Column(name="contact_fonction_strucutre", type="string", length=255, nullable=true, options = {"comment" = "Fonction au sein de la structure"})
     */
    protected $fonctionStructure;
    
    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_civilite", referencedColumnName="ref_id")
     * @Assert\NotBlank(message="La civilité ne peut pas être vide.")
     * @Nodevo\Javascript(class="validate[required]")
     */
    protected $civilite;
    
    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_region", referencedColumnName="ref_id", nullable=true)
     */
    protected $region;
    
    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_departement", referencedColumnName="ref_id", nullable=true)
     */
    protected $departement;
    
    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_statut_etablissement_sante", referencedColumnName="ref_id")
     */
    protected $statutEtablissementSante;
    
    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\EtablissementBundle\Entity\Etablissement", inversedBy="usersRattachement", cascade={"persist"})
     * @ORM\JoinColumn(name="eta_etablissement_rattachement_sante", referencedColumnName="eta_id")
     */
    protected $etablissementRattachementSante;
    
    /**
     * @var string
     * @Assert\Length(
     *      min = "1",
     *      max = "255",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans l'autre structure de rattachement.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans l'autre structure de rattachement."
     * )
     * @Nodevo\Javascript(class="validate[minSize[1],maxSize[255]]")
     * @ORM\Column(name="usr_autre_rattachement_sante", type="string", length=255, nullable=true, options = {"comment" = "Autre structure de rattachement santé de l utilisateur"})
     */
    protected $autreStructureRattachementSante;

    /**
     * Get fonctionStructure
     *
     * @return string $fonctionStructure
     */
    public function getFonctionStructure()
    {
        return $this->fonctionStructure;
    }
    
    /**
     * Set fonctionStructure
     *
     * @param string $fonctionStructure
     */
    public function setFonctionStructure($fonctionStructure)
    {
        $this->fonctionStructure = $fonctionStructure;
    }
    
    /**
     * Get civilite
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference $civilite
     */
    public function getCivilite()
    {
        return $this->civilite;
    }
    
    /**
     * Set civilite
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $civilite
     */
    public function setCivilite($civilite)
    {
        if($civilite instanceof \HopitalNumerique\ReferenceBundle\Entity\Reference )
            $this->civilite = $civilite;
        else
            $this->civilite = null;
    }
    
    /**
     * Get region
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference $region
     */
    public function getRegion()
    {
        return $this->region;
    }
    
    /**
     * Set region
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $region
     */
    public function setRegion($region)
    {
        if($region instanceof \HopitalNumerique\ReferenceBundle\Entity\Reference )
            $this->region = $region;
        else
            $this->region = null;
    }
    
    /**
     * Get département
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference $departement
     */
    public function getDepartement()
    {
        return $this->departement;
    }
    
    /**
     * Set département
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $departement
     */
    public function setDepartement($departement)
    {
        if($departement instanceof \HopitalNumerique\ReferenceBundle\Entity\Reference )
            $this->departement = $departement;
        else
            $this->departement = null;
    }
    
    /**
     * Set statutEtablissementSante
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $statutEtablissementSante
     */
    public function setStatutEtablissementSante($statutEtablissementSante)
    {
        if($statutEtablissementSante instanceof \HopitalNumerique\ReferenceBundle\Entity\Reference )
            $this->statutEtablissementSante = $statutEtablissementSante;
        else
            $this->statutEtablissementSante = null;
    }
    
    /**
     * Get statutEtablissementSante
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference $statutEtablissementSante
     */
    public function getStatutEtablissementSante()
    {
        return $this->statutEtablissementSante;
    }
    
    /**
     * Get etablissementRattachementSante
     *
     * @return string $etablissementRattachementSante
     */
    public function getEtablissementRattachementSante()
    {
        return $this->etablissementRattachementSante;
    }
    
    /**
     * Set etablissementRattachementSante
     *
     * @param string $etablissementRattachementSante
     */
    public function setEtablissementRattachementSante($etablissementRattachementSante)
    {
        if($etablissementRattachementSante instanceof \HopitalNumerique\EtablissementBundle\Entity\Etablissement )
            $this->etablissementRattachementSante = $etablissementRattachementSante;
        else
            $this->etablissementRattachementSante = null;
    }
    
    /**
     * Get autreStructureRattachementSante
     *
     * @return string $autreStructureRattachementSante
     */
    public function getAutreStructureRattachementSante()
    {
        return $this->autreStructureRattachementSante;
    }
    
    /**
     * Set autreStructureRattachementSante
     *
     * @param string $autreStructureRattachementSante
     */
    public function setAutreStructureRattachementSante($autreStructureRattachementSante)
    {
        $this->autreStructureRattachementSante = $autreStructureRattachementSante;
    }
}
