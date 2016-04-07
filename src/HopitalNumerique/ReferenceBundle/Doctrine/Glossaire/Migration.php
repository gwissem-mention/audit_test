<?php
namespace HopitalNumerique\ReferenceBundle\Doctrine\Glossaire;

use Doctrine\DBAL\Driver\Connection;
use HopitalNumerique\DomaineBundle\Manager\DomaineManager;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;

/**
 * Migration de l'ancien glossaire vers les références.
 */
class Migration
{
    /**
     * @var \Doctrine\DBAL\Driver\Connection Database connection
     */
    private $databaseConnection;

    /**
     * @var \HopitalNumerique\ReferenceBundle\Manager\ReferenceManager ReferenceManager
     */
    private $referenceManager;

    /**
     * @var \HopitalNumerique\DomaineBundle\Manager\DomaineManager DomaineManager
     */
    private $domaineManager;


    /**
     * Tous les domaines.
     *
     * @var array<\HopitalNumerique\DomaineBundle\Entity\Domaine> Domaines
     */
    private static $DOMAINES = null;
    
    /**
     * État actif.
     *
     * @var \HopitalNumerique\ReferenceBundle\Entity\Reference Actif
     */
    private static $ETAT_ACTIF = null;
    
    /**
     * État inactif.
     *
     * @var \HopitalNumerique\ReferenceBundle\Entity\Reference Inactif
     */
    private static $ETAT_INACTIF = null;


    /**
     * Constructeur.
     */
    public function __construct(Connection $databaseConnection, ReferenceManager $referenceManager, DomaineManager $domaineManager)
    {
        $this->databaseConnection = $databaseConnection;
        $this->referenceManager = $referenceManager;
        $this->domaineManager = $domaineManager;
    }

    /**
     * Initialisation.
     */
    private function init()
    {
        self::$DOMAINES = $this->domaineManager->getEntitiesKeyedById($this->domaineManager->findAll());
        self::$ETAT_ACTIF = $this->referenceManager->findOneById(Reference::STATUT_ACTIF_ID);
        self::$ETAT_INACTIF = $this->referenceManager->findOneById(Reference::STATUT_INACTIF_ID);
    }


    /**
     * Lance la migration.
     */
    public function execute()
    {
        $this->init();
        $this->saveGlossaire();
    }

    /**
     * Retourne tous les éléments du glossaire.
     *
     * @return array Glossaire
     */
    private function getGlossaireData()
    {
        $glossaireData = [];

        $sql = "
            SELECT *
            FROM hn_glossaire AS glossaire
            LEFT JOIN hn_domaine_gestions_glossaire AS glossaire_domaine ON glossaire.glo_id = glossaire_domaine.glo_id
            ORDER BY glossaire.glo_id ASC
        ";
        $results = $this->databaseConnection->query($sql)->fetchAll();

        foreach ($results as $result) {
            $glossaireId = intval($result['glo_id']);
            $domaine = &self::$DOMAINES[intval($result['dom_id'])];

            if (!array_key_exists($glossaireId, $glossaireData)) {
                $etat = (Reference::STATUT_ACTIF_ID == $result['ref_statut'] ? self::$ETAT_ACTIF : (Reference::STATUT_INACTIF_ID == $result['ref_statut'] ? self::$ETAT_INACTIF : null));
                $mot = trim($result['glo_mot']);
                $intitule = trim($result['glo_intitule']);
                $description = strip_tags($result['glo_description']);
                $sensitive = boolval($result['glo_sensitive']);

                $glossaireData[$glossaireId] = [
                    'etat' => $etat,
                    'mot' => $mot,
                    'intitule' => $intitule,
                    'description' => $description,
                    'casseSensible' => $sensitive,
                    'domaines' => []
                ];
            }

            $glossaireData[$glossaireId]['domaines'][] = $domaine;
        }

        return $glossaireData;
    }

    /**
     * Sauvegarde tout le glossaire.
     */
    private function saveGlossaire()
    {
        foreach ($this->getGlossaireData() as $glossaireRow) {
            $reference = $this->referenceManager->createEmpty();

            $reference->setReference(false);
            $reference->setInGlossaire(true);
            $reference->setEtat($glossaireRow['etat']);
            $reference->setLibelle('' != $glossaireRow['intitule'] ? $glossaireRow['intitule'] : $glossaireRow['mot']);
            if ('' != $glossaireRow['intitule']) {
                $reference->setSigle($glossaireRow['mot']);
            }
            $reference->setDescriptionCourte($glossaireRow['description']);
            $reference->setCasseSensible($glossaireRow['casseSensible']);
            foreach ($glossaireRow['domaines'] as $domaine) {
                $reference->addDomaine($domaine);
            }

            $this->referenceManager->save($reference);
        }
    }
}
