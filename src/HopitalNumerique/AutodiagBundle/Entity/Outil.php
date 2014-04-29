<?php

namespace HopitalNumerique\AutodiagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

//Asserts Stuff
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Nodevo\ToolsBundle\Validator\Constraints as Nodevo;

/**
 * Outil
 *
 * @ORM\Table(name="hn_outil")
 * @ORM\Entity(repositoryClass="HopitalNumerique\AutodiagBundle\Repository\OutilRepository")
 * @UniqueEntity(fields="alias", message="Cet alias existe déjà.")
 */
class Outil
{
    /**
     * @var integer
     *
     * @ORM\Column(name="out_id", type="integer", options = {"comment" = "ID de l outil"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="out_title", type="string", length=255, options = {"comment" = "Titre de l outil"})
     * @Assert\NotBlank(message="Le titre ne peut pas être vide.")
     * @Assert\Length(
     *      min = "1",
     *      max = "255",
     *      minMessage = "Il doit y avoir au moins {{ limit }} caractères dans le titre.",
     *      maxMessage = "Il doit y avoir au maximum {{ limit }} caractères dans le titre."
     * )
     * @Nodevo\Javascript(class="validate[required,minSize[1],maxSize[255]]")
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="out_alias", type="string", length=255, options = {"comment" = "Alias de l outil"})
     * @Assert\Length(
     *      max = "255",
     *      maxMessage = "Il doit y avoir au maximum {{ limit }} caractères dans le titre."
     * )
     * @Nodevo\Javascript(class="validate[maxSize[255]]")
     */
    private $alias;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="out_date_creation", type="datetime", options = {"comment" = "Date de création de l outil"})
     */
    private $dateCreation;

    /**
     * @var boolean
     *
     * @ORM\Column(name="out_column_chart", type="boolean", options = {"comment" = "Afficher la restitution de l outil en graphique barres ?"})
     */
    private $columnChart;

    /**
     * @var string
     *
     * @ORM\Column(name="out_column_chart_label", type="string", length=255, nullable=true, options = {"comment" = "Libellé du résultat sur le graphique barre"})
     */
    private $columnChartLabel;

    /**
     * @var integer
     *
     * @ORM\Column(name="out_column_chart_axe", type="smallint", nullable=true, options = {"comment" = "Axes du graphique barres"})
     */
    private $columnChartAxe;

    /**
     * @var boolean
     *
     * @ORM\Column(name="out_radar_chart", type="boolean", options = {"comment" = "Afficher la restitution de l outil en graphique radar ?"})
     */
    private $radarChart;

    /**
     * @var string
     *
     * @ORM\Column(name="out_radar_chart_label", type="string", length=255, nullable=true, options = {"comment" = "Libellé du résultat sur le graphique radar"})
     */
    private $radarChartLabel;

    /**
     * @var integer
     *
     * @ORM\Column(name="out_radar_chart_axe", type="smallint", nullable=true, options = {"comment" = "Axes du graphique radar"})
     */
    private $radarChartAxe;

    /**
     * @var boolean
     *
     * @ORM\Column(name="out_table_chart", type="boolean", options = {"comment" = "Afficher la restitution de l outil sous forme de table ?"})
     */
    private $tableChart;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_statut", referencedColumnName="ref_id")
     */
    protected $statut;

    /**
     * Initialisation de l'entitée (valeurs par défaut)
     */
    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->columnChart  = false;
        $this->radarChart   = false;
        $this->tableChart   = false;
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
     * Set title
     *
     * @param string $title
     * @return Outil
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set alias
     *
     * @param string $alias
     * @return Outil
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * Get alias
     *
     * @return string 
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     * @return Outil
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * Get dateCreation
     *
     * @return \DateTime 
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * Set columnChart
     *
     * @param boolean $columnChart
     * @return Outil
     */
    public function setColumnChart($columnChart)
    {
        $this->columnChart = $columnChart;

        return $this;
    }

    /**
     * Get columnChart
     *
     * @return boolean 
     */
    public function isColumnChart()
    {
        return $this->columnChart;
    }

    /**
     * Set columnChartLabel
     *
     * @param string $columnChartLabel
     * @return Outil
     */
    public function setColumnChartLabel($columnChartLabel)
    {
        $this->columnChartLabel = $columnChartLabel;

        return $this;
    }

    /**
     * Get columnChartLabel
     *
     * @return string 
     */
    public function getColumnChartLabel()
    {
        return $this->columnChartLabel;
    }

    /**
     * Set columnChartAxe
     *
     * @param integer $columnChartAxe
     * @return Outil
     */
    public function setColumnChartAxe($columnChartAxe)
    {
        $this->columnChartAxe = $columnChartAxe;

        return $this;
    }

    /**
     * Get columnChartAxe
     *
     * @return integer 
     */
    public function getColumnChartAxe()
    {
        return $this->columnChartAxe;
    }

    /**
     * Set radarChart
     *
     * @param boolean $radarChart
     * @return Outil
     */
    public function setRadarChart($radarChart)
    {
        $this->radarChart = $radarChart;

        return $this;
    }

    /**
     * Get radarChart
     *
     * @return boolean 
     */
    public function isRadarChart()
    {
        return $this->radarChart;
    }

    /**
     * Set radarChartLabel
     *
     * @param string $radarChartLabel
     * @return Outil
     */
    public function setRadarChartLabel($radarChartLabel)
    {
        $this->radarChartLabel = $radarChartLabel;

        return $this;
    }

    /**
     * Get radarChartLabel
     *
     * @return string 
     */
    public function getRadarChartLabel()
    {
        return $this->radarChartLabel;
    }

    /**
     * Set radarChartAxe
     *
     * @param integer $radarChartAxe
     * @return Outil
     */
    public function setRadarChartAxe($radarChartAxe)
    {
        $this->radarChartAxe = $radarChartAxe;

        return $this;
    }

    /**
     * Get radarChartAxe
     *
     * @return integer 
     */
    public function getRadarChartAxe()
    {
        return $this->radarChartAxe;
    }

    /**
     * Set tableChart
     *
     * @param boolean $tableChart
     * @return Outil
     */
    public function setTableChart($tableChart)
    {
        $this->tableChart = $tableChart;

        return $this;
    }

    /**
     * Get tableChart
     *
     * @return boolean 
     */
    public function isTableChart()
    {
        return $this->tableChart;
    }

    /**
     * Get statut
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference $statut
     */
    public function getStatut()
    {
        return $this->statut;
    }
    
    /**
     * Set statut
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $statut
     */
    public function setStatut(\HopitalNumerique\ReferenceBundle\Entity\Reference $statut)
    {
        $this->statut = $statut;
        return $this;
    }
}
