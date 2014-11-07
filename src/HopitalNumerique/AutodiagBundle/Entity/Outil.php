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
     * @var boolean Afficher la restitution par processus ?
     *
     * @ORM\Column
     * (
     *   name = "out_process_chart",
     *   type = "boolean",
     *   options =
     *   {
     *     "comment" = "Afficher la restitution par processus ?"
     *   }
     * )
     */
    private $processChart;

    /**
     * @var boolean
     *
     * @ORM\Column(name="out_plan_action_priorise", type="boolean", options = {"comment" = "Definir un plan d action priorise pour cet outil ?"})
     */
    private $planActionPriorise;
    
    /**
     * @var string Libellé du résultat par processus
     * 
     * @ORM\Column
     * (
     *   name = "out_process_chart_label",
     *   type = "string",
     *   length = 255,
     *   nullable = true,
     *   options =
     *   {
     *     "comment" = "Libellé du résultat par processus"
     *   }
     * )
     */
    private $processChartLabel;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_statut", referencedColumnName="ref_id")
     */
    protected $statut;

    /**
     * @ORM\OneToMany(targetEntity="Chapitre", mappedBy="outil")
     */
    private $chapitres;

    /**
     * @ORM\OneToMany(targetEntity="Categorie", mappedBy="outil")
     */
    private $categories;

    /**
     * @ORM\OneToMany(targetEntity="\HopitalNumerique\AutodiagBundle\Entity\Resultat", mappedBy="outil", cascade={"persist"})
     */
    protected $resultats;
    
    /**
     * @var \Doctrine\Common\Collections\Collection Éléments de restitution par process
     * 
     * @ORM\OneToMany(
     *   targetEntity = "Process",
     *   mappedBy = "outil",
     *   cascade = { "persist" }
     * )
     * @ORM\OrderBy({ "order":"ASC" })
     */
    protected $process;

    /**
     * @var boolean
     *
     * @ORM\Column(name="out_100pourcent_reponse_obligatoire", type="boolean", options = {"comment" = "Forcer acces au reponses uniquement si toutes les reponses sont renseignees"})
     */
    private $centPourcentReponseObligatoire;

    /**
     * @var boolean
     *
     * @ORM\Column(name="out_masquer_analyse", type="boolean", options = {"comment" = "Masquer l onglet Analyse"})
     */
    private $masquerAnalyse;

    /**
     * @var boolean
     *
     * @ORM\Column(name="out_masquer_reponse", type="boolean", options = {"comment" = "Masquer l onglet Reponses"})
     */
    private $masquerReponse;

    /**
     * @var string
     *
     * @ORM\Column(name="out_instructions", type="text", nullable=true, options = {"comment" = "Instructions fournies pour cet outil"})
     */
    private $instruction;

    /**
     * @var string
     *
     * @ORM\Column(name="out_commentaire_restitution", type="text", nullable=true, options = {"comment" = "Commentaire affiché lors de la restitution pour cet outil"})
     */
    private $commentaireRestitution;

    /**
     * @var string
     *
     * @ORM\Column(name="out_commentaire_grah_barre", type="text", nullable=true, options = {"comment" = "Commentaire affiché lors de la restitution pour cet outil dans la partie graphique barre"})
     */
    private $commentaireGraphBarre;

    /**
     * @var string
     *
     * @ORM\Column(name="out_commentaire_grah_processus", type="text", nullable=true, options = {"comment" = "Commentaire affiché lors de la restitution pour cet outil dans la partie graphique processus"})
     */
    private $commentaireGraphPrecessus;

    /**
     * @var string
     *
     * @ORM\Column(name="out_commentaire_grah_radar", type="text", nullable=true, options = {"comment" = "Commentaire affiché lors de la restitution pour cet outil dans la partie graphique radar"})
     */
    private $commentaireGraphRadar;

    /**
     * @var string
     *
     * @ORM\Column(name="out_commentaire_analyse_resultat", type="text", nullable=true, options = {"comment" = "Commentaire affiché lors de la restitution pour cet outil dans la partie analyse résultat"})
     */
    private $commentaireAnalyseResultat;

    /**
     * @var string
     *
     * @ORM\Column(name="out_commentaire_reponses", type="text", nullable=true, options = {"comment" = "Commentaire affiché lors de la restitution pour cet outil dans la partie reponses"})
     */
    private $commentaireReponses;

    /**
     * Initialisation de l'entitée (valeurs par défaut)
     */
    public function __construct()
    {
        $this->dateCreation                   = new \DateTime();
        $this->columnChart                    = false;
        $this->radarChart                     = false;
        $this->tableChart                     = false;
        $this->centPourcentReponseObligatoire = false;
        $this->masquerAnalyse                 = false;
        $this->masquerReponse                 = false;
        $this->chapitres                      = new \Doctrine\Common\Collections\ArrayCollection();
        $this->categories                     = new \Doctrine\Common\Collections\ArrayCollection();
        $this->process                        = new \Doctrine\Common\Collections\ArrayCollection();
        $this->processChapitres               = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set processChart
     *
     * @param boolean $processChart
     * @return Outil
     */
    public function setProcessChart($processChart)
    {
        $this->processChart = $processChart;
    
        return $this;
    }
    
    /**
     * Get processChart
     *
     * @return boolean
     */
    public function isProcessChart()
    {
        return $this->processChart;
    }
    
    /**
     * Set instruction
     *
     * @param string $instruction
     * @return Outil
     */
    public function setInstruction($instruction)
    {
        $this->instruction = $instruction;
    
        return $this;
    }
    
    /**
     * Get instruction
     *
     * @return string
     */
    public function getInstruction()
    {
        return $this->instruction;
    }
    
    /**
     * Set commentaireRestitution
     *
     * @param string $commentaireRestitution
     * @return Outil
     */
    public function setCommentaireRestitution($commentaireRestitution)
    {
        $this->commentaireRestitution = $commentaireRestitution;
    
        return $this;
    }
    
    /**
     * Get commentaireRestitution
     *
     * @return string
     */
    public function getCommentaireRestitution()
    {
        return $this->commentaireRestitution;
    }
    
    /**
     * Set commentaireGraphBarre
     *
     * @param string $commentaireGraphBarre
     * @return Outil
     */
    public function setCommentaireGraphBarre($commentaireGraphBarre)
    {
        $this->commentaireGraphBarre = $commentaireGraphBarre;
    
        return $this;
    }
    
    /**
     * Get commentaireGraphBarre
     *
     * @return string
     */
    public function getCommentaireGraphBarre()
    {
        return $this->commentaireGraphBarre;
    }
    
    /**
     * Set commentaireGraphPrecessus
     *
     * @param string $commentaireGraphPrecessus
     * @return Outil
     */
    public function setCommentaireGraphPrecessus($commentaireGraphPrecessus)
    {
        $this->commentaireGraphPrecessus = $commentaireGraphPrecessus;
    
        return $this;
    }
    
    /**
     * Get commentaireGraphPrecessus
     *
     * @return string
     */
    public function getCommentaireGraphPrecessus()
    {
        return $this->commentaireGraphPrecessus;
    }
    
    /**
     * Set commentaireGraphRadar
     *
     * @param string $commentaireGraphRadar
     * @return Outil
     */
    public function setCommentaireGraphRadar($commentaireGraphRadar)
    {
        $this->commentaireGraphRadar = $commentaireGraphRadar;
    
        return $this;
    }
    
    /**
     * Get commentaireGraphRadar
     *
     * @return string
     */
    public function getCommentaireGraphRadar()
    {
        return $this->commentaireGraphRadar;
    }
    
    /**
     * Set commentaireAnalyseResultat
     *
     * @param string $commentaireAnalyseResultat
     * @return Outil
     */
    public function setCommentaireAnalyseResultat($commentaireAnalyseResultat)
    {
        $this->commentaireAnalyseResultat = $commentaireAnalyseResultat;
    
        return $this;
    }
    
    /**
     * Get commentaireAnalyseResultat
     *
     * @return string
     */
    public function getCommentaireAnalyseResultat()
    {
        return $this->commentaireAnalyseResultat;
    }
    
    /**
     * Set commentaireReponses
     *
     * @param string $commentaireReponses
     * @return Outil
     */
    public function setCommentaireReponses($commentaireReponses)
    {
        $this->commentaireReponses = $commentaireReponses;
    
        return $this;
    }
    
    /**
     * Get commentaireReponses
     *
     * @return string
     */
    public function getCommentaireReponses()
    {
        return $this->commentaireReponses;
    }
    
    /**
     * Set radarProcessLabel
     *
     * @param string $processChartLabel
     * @return Outil
     */
    public function setProcessChartLabel($processChartLabel)
    {
        $this->processChartLabel = $processChartLabel;
    
        return $this;
    }
    
    /**
     * Get processChartLabel
     *
     * @return string
     */
    public function getProcessChartLabel()
    {
        return $this->processChartLabel;
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
     * Set centPourcentReponseObligatoire
     *
     * @param boolean $centPourcentReponseObligatoire
     * @return Outil
     */
    public function setCentPourcentReponseObligatoire($centPourcentReponseObligatoire)
    {
        $this->centPourcentReponseObligatoire = $centPourcentReponseObligatoire;

        return $this;
    }

    /**
     * Get centPourcentReponseObligatoire
     *
     * @return boolean 
     */
    public function isCentPourcentReponseObligatoire()
    {
        return $this->centPourcentReponseObligatoire;
    }

    /**
     * Set planActionPriorise
     *
     * @param boolean $planActionPriorise
     * @return Outil
     */
    public function setPlanActionPriorise($planActionPriorise)
    {
        $this->planActionPriorise = $planActionPriorise;

        return $this;
    }

    /**
     * Get planActionPriorise
     *
     * @return boolean 
     */
    public function isPlanActionPriorise()
    {
        return $this->planActionPriorise;
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
     * Set masquerAnalyse
     *
     * @param boolean $masquerAnalyse
     * @return Outil
     */
    public function setMasquerAnalyse($masquerAnalyse)
    {
        $this->masquerAnalyse = $masquerAnalyse;

        return $this;
    }

    /**
     * Get masquerAnalyse
     *
     * @return boolean 
     */
    public function isMasquerAnalyse()
    {
        return $this->masquerAnalyse;
    }

    /**
     * Set masquerReponse
     *
     * @param boolean $masquerReponse
     * @return Outil
     */
    public function setMasquerReponse($masquerReponse)
    {
        $this->masquerReponse = $masquerReponse;

        return $this;
    }

    /**
     * Get masquerReponse
     *
     * @return boolean 
     */
    public function isMasquerReponse()
    {
        return $this->masquerReponse;
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

    /**
     * Get chapitres
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getChapitres()
    {
        return $this->chapitres;
    }

    /**
     * Get categories
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Get resultats
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getResultats()
    {
        return $this->resultats;
    }
    
    /**
     * Set resultats
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $resultats
     */
    public function setResultats(\Doctrine\Common\Collections\ArrayCollection $resultats)
    {
        $this->resultats = $resultats;
        return $this;
    }

    /**
     * Get process
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProcess()
    {
        return $this->process;
    }
    
    /**
     * Set process
     *
     * @param \Doctrine\Common\Collections\Collection $process
     */
    public function setProcess(\Doctrine\Common\Collections\Collection $process)
    {
        foreach ($process as $unProcess)
            $unProcess->setOutil($this);
        $this->process = $process;
        return $this;
    }
}
