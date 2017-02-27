<?php

namespace HopitalNumerique\ObjetBundle\Manager;

use HopitalNumerique\ObjetBundle\Entity\Contenu;
use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use Nodevo\ToolsBundle\Tools\Chaine;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\Criteria;
use HopitalNumerique\UserBundle\Manager\UserManager;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;

/**
 * Manager de l'entité Contenu.
 */
class ContenuManager extends BaseManager
{
    protected $class = 'HopitalNumerique\ObjetBundle\Entity\Contenu';
    protected $_userManager;
    protected $_referenceManager;
    private $_refPonderees;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\Session Session
     */
    private $_session;

    /**
     * Construct.
     *
     * @param EntityManager $em Entity Mangager de doctrine
     */
    public function __construct(EntityManager $em, UserManager $userManager, ReferenceManager $referenceManager)
    {
        parent::__construct($em);

        $this->_userManager = $userManager;
        $this->_referenceManager = $referenceManager;
    }

    /**
     * [setRefPonderees description].
     *
     * @param [type] $refPonderees [description]
     */
    public function setRefPonderees($refPonderees)
    {
        $this->_refPonderees = $refPonderees;
    }

    /**
     * Retourne l'arbo des contenu de l'objet (ou des objets).
     *
     * @param int|array $id ID de(s) l'objet(s)
     *
     * @return array
     */
    public function getArboForObjet($id, $domaineIds = [])
    {
        $originalDatas = $this->getRepository()->getArboForObjet($id, $domaineIds)->getQuery()->getResult();

        $contents = [];

        foreach ($originalDatas as $content) {
            /** @var Contenu $content */
            $item = new \stdClass();
            $item->entity = $content;
            $item->titre = $content->getTitre();
            $item->alias = $content->getAlias();
            $item->id = $content->getId();
            $item->nbVue = $content->getNbVue();
            $item->hasContent = $content->getContenu() == '' ? false : true;
            $item->order = $content->getOrder();
            $item->objet = $content->getParent() === null ? $content->getObjet()->getId() : null;
            $item->childs = [];

            $contents[$item->entity->getId()] = $item;
        }

        $root = [];
        foreach ($contents as $data) {
            if (null !== $data->entity->getParent()) {
                $contents[$data->entity->getParent()->getId()]->childs[] = $data;
            } else {
                $root[$data->entity->getId()] = $data;
            }
        }

        return $root;
    }

    /**
     * Retourne le nombre des contenus ayant le même alias.
     *
     * @param Contenu $contenu Objet contenu
     *
     * @return int
     */
    public function countAlias($contenu)
    {
        return $this->getRepository()->countAlias($contenu)->getQuery()->getSingleScalarResult();
    }

    /**
     * Compte le nombre de contenu parents lié à l'objet.
     *
     * @param Objet $objet Objet
     *
     * @return int
     */
    public function countContenu($objet)
    {
        return $this->getRepository()->countContenu($objet)->getQuery()->getSingleScalarResult();
    }

    /**
     * Retourne le prefix du contenu.
     *
     * @param Contenu $contenu Contenu
     *
     * @return string
     */
    public function getPrefix($contenu)
    {
        return $this->getPrefixRecursif($contenu, '');
    }

    /**
     * [parseCsv description].
     *
     * @param [type] $csv   [description]
     * @param [type] $objet [description]
     *
     * @return [type]
     */
    public function parseCsv($csv, $objet)
    {
        //parse Str CSV and convert to array
        $lines = explode("\n", $csv);
        if (empty($lines)) {
            return false;
        }

        //gestion des erreurs lors du parse du CSV
        $sommaire = [];
        foreach ($lines as $line) {
            $tmp = str_getcsv($line, ';');
            if (isset($tmp[0]) && isset($tmp[1]) && !isset($tmp[2])) {
                $sommaire[] = $tmp;
            } else {
                return false;
            }
        }

        //ajout des éléments parents only
        $parents = [];
        foreach ($sommaire as $element) {
            $elem = &$parents;
            list($chapitre, $titre) = $element;
            $numeroChapitre = explode('.', $chapitre);
            foreach ($numeroChapitre as $key => $one) {
                if ($key == count($numeroChapitre) - 1) {
                    $elem = &$elem[$one];
                } else {
                    $elem = &$elem[$one]['childs'];
                }
            }
            $elem['titre'] = $titre;
        }

        // clean elements sans titre
        $parents = $this->cleanSansTitre($parents);
        $this->saveContenusCSV($objet, $parents);

        return true;
    }

    /**
     * Retourne le contenu précédant immédiatemment le contenu $contenu dans la liste $contenus.
     */
    public function getPrecedent($contenus, $contenu)
    {
        reset($contenus);
        while (current($contenus) !== false && current($contenus)->getId() !== $contenu->getId()) {
            next($contenus);
        }

        return prev($contenus) == false ? null : current($contenus);
    }

    /**
     * Retourne le contenu suivant immédiatemment le contenu $contenu dans la liste $contenus.
     */
    public function getSuivant($contenus, $contenu)
    {
        reset($contenus);
        while (current($contenus) !== false && current($contenus)->getId() !== $contenu->getId()) {
            next($contenus);
        }

        return next($contenus) == false ? null : current($contenus);
    }

    /**
     * Retourne la liste des contenus qui ont un contenu non vide, triés par ordre (ex : Chapitre 1 - Chapitre 1.1 - Chapitre 1.2 - Chapitre 2 - Chapitre 2.1).
     */
    public function getContenusNonVidesTries($objet)
    {
        $criteria = Criteria::create()->orderBy(['parent' => Criteria::ASC, 'order' => Criteria::ASC]);
        //Récupération de l'ensemble des contenus triés par Parent puis Order
        $contenus = $objet->getContenus()->matching($criteria);

        //Récupération des contnus de premier niveau
        $contenusParent = array_filter($contenus->toArray(), function ($item) {
            return $item->getParent() == null;
        });

        $elements = [];

        foreach ($contenusParent as $key => $item) {
            $this->sortContenusRescursively($item, $elements, $contenus);
        }

        $elements = array_filter($elements, function ($item) {
            return $item->getContenu() != '';
        });

        return $elements;
    }

    /**
     * Retourne l'ordre complet d'un contenu (ex : retourne 2.1.1 si le contenu est le premier enfant du premier enfant du deuxième élément).
     */
    public function getFullOrder($contenu)
    {
        if (!isset($contenu)) {
            return null;
        }

        $order = $contenu->getOrder();
        $parent = $contenu;

        while (($parent = $parent->getParent()) != null) {
            $order = $parent->getOrder() . '.' . $order;
        }

        return $order;
    }

    /**
     * Retourne le prefix du contenu.
     *
     * @param Contenu $contenu Contenu
     * @param string  $prefix  Prefix
     *
     * @return string
     */
    private function getPrefixRecursif($contenu, $prefix)
    {
        if (is_null($contenu)) {
            return $prefix;
        }

        $prefix = $contenu->getOrder() . '.' . $prefix;

        if (!is_null($contenu->getParent())) {
            $prefix = $this->getPrefixRecursif($contenu->getParent(), $prefix);
        }

        return $prefix;
    }

    /**
     * [getChilds description].
     *
     * @param [type] $retour [description]
     * @param [type] $elem   [description]
     *
     * @return [type]
     */
    private function getChilds(&$retour, $elem)
    {
        if (isset($elem['childs']) && count($elem['childs'])) {
            $childs = [];
            foreach ($elem['childs'] as $key => $one) {
                $childs[$one] = $retour[$one];
                $petitsEnfants = $this->getChilds($retour, $childs[$one]);
                if ($petitsEnfants) {
                    $childs[$one]['childs'] = $petitsEnfants;
                    unset($retour[$one]);
                } else {
                    unset($retour[$one]);
                }
            }

            return $childs;
        } else {
            return false;
        }
    }

    /**
     * Fonction qui renvoie uniquement les éléments du tableau ayant l'index "titre" qui existe, de manière récursive.
     *
     * @param array $elements
     *
     * @return array
     */
    private function cleanSansTitre($elements)
    {
        $retour = [];
        foreach ($elements as $cle => $elem) {
            if (isset($elem['childs'])) {
                $elem['childs'] = $this->cleanSansTitre($elem['childs']);
            }

            if (isset($elem['titre'])) {
                $retour[$cle] = $elem;
            }
        }

        return $retour;
    }

    /**
     * Fonctionne qui sauvegarde tous les éléments du sommaire.
     *
     * @param type                     $objet
     * @param type                     $contenus
     * @param type                     $objects
     * @param type                     $parent
     * @param bool doit-on sauvegarder $objects
     */
    private function saveContenusCSV($objet, $contenus, &$objects = [], $parent = null, $save = true)
    {
        foreach ($contenus as $ordre => $content) {
            //créer un contenu (set titre, generate alias, set objet)
            $contenu = $this->createEmpty();
            $contenu->setObjet($objet);
            $contenu->setTitre($content['titre']);
            $tool = new Chaine($content['titre']);
            $contenu->setAlias($tool->minifie());
            $contenu->setOrder($ordre);

            if ($parent) {
                $contenu->setParent($parent);
            }

            $objects[] = $contenu;
            if (isset($content['childs'])) {
                $this->saveContenusCSV($objet, $content['childs'], $objects, $contenu, false);
            }
        }

        if ($save) {
            $this->save($objects);
        }
    }

    /**
     * Fonction pour trier les contenus récursivement.
     */
    private function sortContenusRescursively($parent, &$elements, $contenus)
    {
        $elements[] = $parent;

        $childs = array_filter($contenus->toArray(), function ($item) use ($parent) {
            return $item->getParent() == $parent;
        });

        foreach ($childs as $key => $child) {
            $this->sortContenusRescursively($child, $elements, $contenus);
        }
    }
}
