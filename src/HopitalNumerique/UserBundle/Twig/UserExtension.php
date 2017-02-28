<?php

namespace HopitalNumerique\UserBundle\Twig;

use HopitalNumerique\EtablissementBundle\Manager\EtablissementManager;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;
use HopitalNumerique\QuestionnaireBundle\Manager\QuestionnaireManager;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class UserExtension extends \Twig_Extension
{
    /**
     * @var \Symfony\Component\Security\Csrf\CsrfTokenManagerInterface CsrfTokenManager
     */
    private $csrfTokenManagerInterface;

    private $_refManager;
    private $_etabManager;
    private $questionnaireManager;

    /**
     * Construit l'extension Twig.
     */
    public function __construct(CsrfTokenManagerInterface $csrfTokenManagerInterface, ReferenceManager $refManager, EtablissementManager $etabManager, QuestionnaireManager $questionnaireManager)
    {
        $this->csrfTokenManagerInterface = $csrfTokenManagerInterface;
        $this->_refManager = $refManager;
        $this->_etabManager = $etabManager;
        $this->questionnaireManager = $questionnaireManager;
    }

    /**
     * Retourne la liste des filtres custom pour cette extension.
     *
     * @return array
     */
    public function getFilters()
    {
        return [
            'informationsManquantes' => new \Twig_Filter_Method($this, 'informationsManquantes'),
            'formateHistoryValueUser' => new \Twig_Filter_Method($this, 'formateHistoryValueUser'),
            'getFrenchAction' => new \Twig_Filter_Method($this, 'getFrenchAction'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            'csrf_token' => new \Twig_Function_Method($this, 'getCsrfToken'),
        ];
    }

    public function getFrenchAction($data)
    {
        $value = [
            'create' => 'Création',
            'update' => 'Modification',
            'share' => 'Partage',
            'validate' => 'Validation',
            'unvalida' => 'Annulation validation',
            'inscript' => 'Inscription',
            'desinscr' => 'Déinscription',
            'evaluate' => 'Evalué',
            'accept' => 'Accepté',
            'request' => 'Demande',
            'remove' => 'Suppression',
        ];

        $frenchValue = $data;
        if (array_key_exists($data, $value)) {
            $frenchValue = $value[$data];
        }

        return $frenchValue;
    }

    /**
     * Vérifie que l'utilisateur a bien renseignés certains champs.
     *
     * @param User   $user                 User a vérifier
     * @param string $questionnaireLibelle Questionnaire qui nécessite la vérification
     *
     * @return bool
     */
    public function informationsManquantes($user, $questionnaireId = null)
    {
        $questionnaire = null;
        if (null !== $questionnaireId) {
            $questionnaire = $this->questionnaireManager->findOneById($questionnaireId);
        }

        $resultat = ['ok' => []];

        //Pour chacun des éléments ci-dessous, si sa valeur correspondante est nulle alors on créé un tableau contenant le label à afficher
        $resultat['telephoneDirect'] = (is_null($user->getTelephoneDirect())) ? ['label' => 'Téléphone direct'] : [];
        $resultat['region'] = (is_null($user->getRegion())) ? ['label' => 'Région'] : [];
        $resultat['departement'] = (is_null($user->getDepartement())) ? ['label' => 'Département'] : [];

        //Obligatoire uniquement pour l'ambassadeur
        if (null === $questionnaire || $questionnaire->getNom() !== 'Expert') {
            //Si 'structure de rattachement' n'est pas renseigné on vérifie le 'autre structure'
            $resultat['rattachementSante'] = (is_null($user->getEtablissementRattachementSante())) ? (is_null($user->getAutreStructureRattachementSante()) ? ['label' => 'Structure de rattachement / Nom de votre structure si non disponible dans la liste précédente'] : []) : [];
            $resultat['profilEtablissement'] = (is_null($user->getProfilEtablissementSante())) ? ['label' => 'Profil'] : [];
        }

        if (null !== $questionnaire && !$questionnaire->getLock()) {
            $resultat['ok'] = true;
        } else {
            //Si l'un des éléments ci-dessus est manquant
            foreach ($resultat as $res) {
                //Si au moins l'un des tableaux n'est pas vide alors il y a au moins un élément manquant
                if (!empty($res)) {
                    $resultat['ok'] = false;
                    break;
                }
                $resultat['ok'] = true;
            }
        }

        return $resultat;
    }

    /**
     * Retourne la donnée d'historique formatée correctement.
     *
     * @param array $datas La donnée
     *
     * @return string
     */
    public function formateHistoryValueUser($data, $field)
    {
        $return = '';

        if (is_array($data)) {
            //Ref handle
            if (isset($data['id'])) {
                if ($field == 'etablissementRattachementSante') {
                    $etab = $this->_etabManager->findOneBy(['id' => $data['id']]);
                    $return = $etab->getNom();
                } else {
                    $ref = $this->_refManager->findOneBy(['id' => $data['id']]);
                    if (null !== $ref) {
                        $return = $ref->getLibelle();
                    }
                }
            } else {
                $return = implode('; ', $data);
            }
        } elseif ($data instanceof \DateTime) {
            $return = $data->format('d/m/Y');
        } elseif (is_null($data)) {
            $return = 'NULL';
        } else {
            $return = $data;
        }

        return $return;
    }

    /**
     * Retourne le token CSRF pour le formulaire de connexion.
     *
     * @return string Token
     */
    public function getCsrfToken()
    {
        return $this->csrfTokenManagerInterface->getToken('authenticate')->getValue();
    }

    /**
     * Retourne le nom de l'extension : utilisé dans les services.
     *
     * @return string
     */
    public function getName()
    {
        return 'user_extension';
    }
}
