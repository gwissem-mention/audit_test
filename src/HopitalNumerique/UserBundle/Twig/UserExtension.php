<?php
namespace HopitalNumerique\UserBundle\Twig;

use HopitalNumerique\EtablissementBundle\Manager\EtablissementManager;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;
use HopitalNumerique\QuestionnaireBundle\Manager\QuestionnaireManager;

class UserExtension extends \Twig_Extension
{
    private $_refManager;
    private $_etabManager;
    private $questionnaireManager;

    /**
     * Construit l'extension Twig
     */
    public function __construct( ReferenceManager $refManager, EtablissementManager $etabManager, QuestionnaireManager $questionnaireManager )
    {
        $this->_refManager  = $refManager;
        $this->_etabManager = $etabManager;
        $this->questionnaireManager = $questionnaireManager;
    }

    /**
     * Retourne la liste des filtres custom pour cette extension
     *
     * @return array
     */
    public function getFilters()
    {
        return array(
            'informationsManquantes'  => new \Twig_Filter_Method($this, 'informationsManquantes'),
            'formateHistoryValueUser' => new \Twig_Filter_Method($this, 'formateHistoryValueUser')
        );
    }

    /**
     * Vérifie que l'utilisateur a bien renseignés certains champs
     *
     * @param User   $user                 User a vérifier
     * @param string $questionnaireLibelle Questionnaire qui nécessite la vérification
     *
     * @return boolean
     */
    public function informationsManquantes( $user, $questionnaireId = null)
    {
        $questionnaire = null;
        if (null !== $questionnaireId)
            $questionnaire = $this->questionnaireManager->findOneById($questionnaireId);

        $resultat = array('ok' => array());
        
        //Pour chacun des éléments ci-dessous, si sa valeur correspondante est nulle alors on créé un tableau contenant le label à afficher
        $resultat['telephoneDirect']       = (is_null($user->getTelephoneDirect())) ? array('label' => 'Téléphone direct') : array();
        $resultat['region']                = (is_null($user->getRegion())) ? array('label' => 'Région') : array();
        $resultat['departement']           = (is_null($user->getDepartement())) ? array('label' => 'Département') : array();
        
            
        //Obligatoire uniquement pour l'ambassadeur
        if (null === $questionnaire || $questionnaire->getNom() !== 'Expert')
        {
            //Si 'etablissement de rattachement' n'est pas renseigné on vérifie le 'autre structure' 
            $resultat['rattachementSante']     = (is_null($user->getEtablissementRattachementSante())) ? (is_null($user->getAutreStructureRattachementSante()) ? array('label' => 'Etablissement de rattachement / Nom de votre établissement si non disponible dans la liste précédente') : array()) : array();
            $resultat['fonctionEtablissement'] = (is_null($user->getFonctionDansEtablissementSante())) ? array('label' => 'Fonction dans l\'établissement') : array();
            $resultat['profilEtablissement']   = (is_null($user->getProfilEtablissementSante())) ? array('label' => 'Profil') : array();
        }
        
        if (null !== $questionnaire && !$questionnaire->getLock())
            $resultat['ok'] = true;
        else
        {
            //Si l'un des éléments ci-dessus est manquant
            foreach ($resultat as $res)
            {
                //Si au moins l'un des tableaux n'est pas vide alors il y a au moins un élément manquant
                if(!empty($res))
                {
                    $resultat['ok'] = false;
                    break; 
                }
                $resultat['ok'] = true; 
            }
        }

        return $resultat;
    }

    /**
     * Retourne la donnée d'historique formatée correctement
     *
     * @param array $datas La donnée
     *
     * @return string
     */
    public function formateHistoryValueUser( $data, $field )
    {
        $return = '';

        if( is_array($data) ) {
            //Ref handle
            if( isset($data['id']) ){
                if( $field == 'etablissementRattachementSante' ){
                    $etab = $this->_etabManager->findOneBy( array('id' => $data['id']) );
                    $return = $etab->getNom();
                }else{
                    $ref    = $this->_refManager->findOneBy( array('id' => $data['id']) );
                    $return = $ref->getLibelle();
                }
            }else
                $return = implode('; ', $data);
        }else if( $data instanceof \DateTime ){
            $return = $data->format('d/m/Y');
        }else if( is_null($data) ){
            $return = 'NULL';
        }else
            $return = $data;

        return $return;
    }

    /**
     * Retourne le nom de l'extension : utilisé dans les services
     *
     * @return string
     */
    public function getName()
    {
        return 'user_extension';
    }
}