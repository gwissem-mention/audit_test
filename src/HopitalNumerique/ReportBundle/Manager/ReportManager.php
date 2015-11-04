<?php

namespace HopitalNumerique\ReportBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

use HopitalNumerique\UserBundle\Manager\UserManager;

/**
 * Manager de l'entité Report.
 */
class ReportManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\ReportBundle\Entity\Report';

    /**
     * Adresses mails en Copie Caché de l'anap
     * @var array() Tableau clé: Nom affiché => valeur : Adresse mail
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    protected $_mailsReport;

    /**
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    protected $_userManager;
    
    /**
     * Constructeur du manager, on lui passe l'entity Manager de doctrine, un booléen si on peut ajouter des mails
     *
     * @param EntityManager $em Entity      Manager de Doctrine
     * @param Array         $options        Tableau d'options
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function __construct($em, UserManager $userManager, $options = array())
    {
        parent::__construct($em);
        
        $this->_mailsReport = isset($options['mailsReport']) ? $options['mailsReport'] : array();
        $this->_userManager = $userManager;
    }
    
    /**
     * Renvoie la liste des mails dans le config.yml
     * 
     * @return array(string)
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function getMailsReport()
    {
        return $this->_mailsReport;
    }

    /**
     * Override : Récupère les données pour le grid sous forme de tableau
     *
     * @return array
     * 
     * @author Alexis MELCHILSEN
     * @copyright Nodevo
     */
    public function getDatasForGrid( \StdClass $condition = null )
    {
        $reports = array();

        $domainesIds = $this->_userManager->getUserConnected()->getDomainesId();
        
        $results = $this->getRepository()->getDatasForGrid( $domainesIds, $condition )->getQuery()->getResult();
        
        foreach ($results as $key => $result)
        {
            if(!array_key_exists($result['id'], $reports))
            {
                $reports[ $result['id'] ] = $result;
            }
            else
            {
                $reports[ $result['id'] ]['domaineNom'] .= ";" . $result['domaineNom'];
            }
            
            // ----Traitement pour transformer le prénom "Jean-luc robert" en "Jean-Luc Robert"
            //Récupération du prénom
            $prenom = strtolower($result['userPrenom']);
            //Découpage du prénom sur le tiret
            $tempsPrenom = explode('-', $prenom);
            //Unsset de la variable
            $prenom = "";
            //Pour chaque bout on met une MAJ sur la première lettre de chaque mot, si il y en plusieurs c'est qu'il y avait un -
            foreach ($tempsPrenom as $key => $tempPrenom)
            {
                $prenom .= ("" !== $prenom) ? ('-' . ucwords($tempPrenom)) : ucwords($tempPrenom);
            }
            
            // ----Mise en majuscule du nom
            $nom = strtoupper($result['userNom']);

            //Suppression du nom et prenom
            unset($reports[$result['id']]['userNom']);
            unset($reports[$result['id']]['userPrenom']);
            unset($reports[$result['id']]['date']);
            
            //Ajout de la colonne "Prenom NOM"
            $reports[ $result['id'] ]['nomPrenom'] = $prenom.' '.$nom;
            $reports[ $result['id'] ]['repDate'] = !is_null($result['date']) ? $result['date']->format('Y-m-d H:i:s') : '';
        }

        return array_values($reports);
    }
}