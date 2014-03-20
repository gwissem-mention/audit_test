<?php

namespace HopitalNumerique\EtablissementBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

//Asserts Stuff
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Nodevo\ToolsBundle\Validator\Constraints as Nodevo;

/**
 * Etablissement
 *
 * @ORM\Table(name="hn_etablissement")
 * @ORM\Entity(repositoryClass="HopitalNumerique\EtablissementBundle\Repository\EtablissementRepository")
 * @UniqueEntity(fields="nom", message="Cet établissement existe déjà.")
 */
class Etablissement
{
    /**
     * @var integer
     *
     * @ORM\Column(name="eta_id", type="integer", options = {"comment" = "ID de l établissement"})
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
     * @ORM\Column(name="eta_nom", type="string", length=255, options = {"comment" = "Nom de l établissement"})
     */
    private $nom;

    /**
     * @var string
     * @Assert\NotBlank(message="Le finess ne peut pas être vide.")
     * @Assert\Length(
     *      min = "9",
     *      max = "9",
     *      exactMessage="Il doit y avoir {{ limit }} caractères dans le finess."
     * )
     * @Nodevo\Javascript(class="validate[required,custom[onlyNumberSp],minSize[9],maxSize[9]]")
     * @ORM\Column(name="eta_finess", type="string", length=9, options = {"comment" = "Finesse géographique de l établissement"})
     */
    private $finess;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_type_organisme", referencedColumnName="ref_id")
     * @Assert\NotBlank(message="Le type d'organisme ne peut pas être vide.")
     * @Nodevo\Javascript(class="validate[required]")
     */
    private $typeOrganisme;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_region", referencedColumnName="ref_id")
     * @Assert\NotBlank(message="La civilité ne peut pas être vide.")
     * @Nodevo\Javascript(class="validate[required]")
     */
    private $region;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_departement", referencedColumnName="ref_id")
     * @Assert\NotBlank(message="La civilité ne peut pas être vide.")
     * @Nodevo\Javascript(class="validate[required]")
     */
    private $departement;

    /**
     * @var string
     * @Assert\NotBlank(message="L'adresse ne peut pas être vide.")
     * @Assert\Length(
     *      min = "1",
     *      max = "512",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans l'adresse.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans l'adresse."
     * )
     * @Nodevo\Javascript(class="validate[required,minSize[1],maxSize[512]]")
     * @ORM\Column(name="eta_adresse", type="string", length=512, options = {"comment" = "Adresse de l établissement"})
     */
    private $adresse;

    /**
     * @var string
     * @Assert\NotBlank(message="La ville ne peut pas être vide.")
     * @Assert\Length(
     *      min = "1",
     *      max = "255",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans la ville.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans la ville."
     * )
     * @Nodevo\Javascript(class="validate[required,minSize[1],maxSize[255]]")
     * @ORM\Column(name="eta_ville", type="string", length=255, options = {"comment" = "Ville de l établissement"})
     */
    private $ville;

    /**
     * @var string
     * @Assert\NotBlank(message="Le code postal ne peut pas être vide.")
     * @Assert\Length(
     *      min = "5",
     *      max = "5",
     *      exactMessage="Il doit y avoir {{ limit }} caractères dans le code postal."
     * )
     * @Nodevo\Javascript(class="validate[required,custom[onlyNumberSp],minSize[5],maxSize[5]]")
     * @ORM\Column(name="eta_codepostal", type="string", length=5, options = {"comment" = "Code postal de l établissement"})
     */
    private $codepostal;
    

    // ^ -------- Utilisateur -------- ^    

    /**
     * @ORM\OneToMany(targetEntity="\HopitalNumerique\UserBundle\Entity\User", mappedBy="etablissementRattachementSante", cascade={"persist"})
     */
    private $usersRattachement;    

    /**
     * Set usersRattachement
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $usersRattachement
     * @return Etablissement
     */
    public function setUsersRattachement(\HopitalNumerique\UserBundle\Entity\User $usersRattachement = null)
    {
        $this->usersRattachement = $usersRattachement;
    
        return $this;
    }
    
    /**
     * Get usersRattachement
     *
     * @return \HopitalNumerique\UserBundle\Entity\User
     */
    public function getUsersRattachement()
    {
        return $this->usersRattachement;
    }
    
    function getUsersAffichage()
    {
        return sprintf('%s - %s', $this->nom, $this->finess);
    }
    
    // v -------- Utilisateur  -------- v    

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
     * Set nom
     *
     * @param string $nom
     * @return Etablissement
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

    /**
     * Set finess
     *
     * @param string $finess
     * @return Etablissement
     */
    public function setFiness($finess)
    {
        $this->finess = $finess;

        return $this;
    }

    /**
     * Get finess
     *
     * @return string 
     */
    public function getFiness()
    {
        return $this->finess;
    }

    /**
     * Get typeOrganisme
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference $typeOrganisme
     */
    public function getTypeOrganisme()
    {
        return $this->typeOrganisme;
    }
    
    /**
     * Set typeOrganisme
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $typeOrganisme
     */
    public function setTypeOrganisme(\HopitalNumerique\ReferenceBundle\Entity\Reference $typeOrganisme)
    {
        $this->typeOrganisme = $typeOrganisme;
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
    public function setRegion(\HopitalNumerique\ReferenceBundle\Entity\Reference $region)
    {
        $this->region = $region;
    }
    
    /**
     * Get departement
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference $departement
     */
    public function getDepartement()
    {
        return $this->departement;
    }
    
    /**
     * Set departement
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $departement
     */
    public function setDepartement(\HopitalNumerique\ReferenceBundle\Entity\Reference $departement)
    {
        $this->departement = $departement;
    }

    /**
     * Set adresse
     *
     * @param string $adresse
     * @return Etablissement
     */
    public function setAdresse($adresse)
    {
        $this->adresse = $adresse;

        return $this;
    }

    /**
     * Get adresse
     *
     * @return string 
     */
    public function getAdresse()
    {
        return $this->adresse;
    }

    /**
     * Set ville
     *
     * @param string $ville
     * @return Etablissement
     */
    public function setVille($ville)
    {
        $this->ville = $ville;

        return $this;
    }

    /**
     * Get ville
     *
     * @return string 
     */
    public function getVille()
    {
        return $this->ville;
    }

    /**
     * Set codepostal
     *
     * @param string $codepostal
     * @return Etablissement
     */
    public function setCodepostal($codepostal)
    {
        $this->codepostal = $codepostal;

        return $this;
    }

    /**
     * Get codepostal
     *
     * @return string 
     */
    public function getCodepostal()
    {
        return $this->codepostal;
    }

    /**
     * Retourne l'appellation de l'établissement pour l'affichage (nom - FINESS).
     * 
     * @return string Appellation de l'établissement
     */
    public function getAppellation()
    {
        return $this->nom.' - '.$this->finess;
    }
}
