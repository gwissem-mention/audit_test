<?php

namespace HopitalNumerique\RechercheBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de la recherche.
 */
class SearchManager extends BaseManager
{
    private $_production = 175;
    private $_ressource = 183;
    private $_pointDur = 184;
    private $_refsPonderees = null;
    private $_ccdnAuthorizer = null;
    private $_urlRechercheTextuelle = '';
    private $_activationExalead = false;

    /**
     * Override du contrct d'un manager normal : ce manager n'est lié à aucune entitée.
     */
    public function __construct($ccdnAuthorizer, $options = [])
    {
        $this->_ccdnAuthorizer = $ccdnAuthorizer;

        $this->_urlRechercheTextuelle = isset($options['urlRechercheTextuelle']) ? $options['urlRechercheTextuelle'] : '';
        $this->_activationExalead = isset($options['activationExalead']) ? $options['activationExalead'] : '';
    }

    /**
     * Permet de récuperer les options du parameter.yml.
     *
     * @return [type]
     */
    public function getUrlRechercheTextuelle()
    {
        return $this->_urlRechercheTextuelle;
    }

    /**
     * Permet de récuperer les options du parameter.yml.
     *
     * @return bool
     */
    public function getActivationExalead()
    {
        return $this->_activationExalead;
    }

    /**
     * GetMeta (desc+keywords) : for référencement.
     *
     * @param array  $references Liste des références
     * @param string $desc       Resume|contenu
     *
     * @return array
     */
    public function getMetas($references, $desc)
    {
        $meta = [];

        //description
        $tab = explode('<!-- pagebreak -->', $desc);
        $meta['desc'] = html_entity_decode(strip_tags($tab[0]));
        $meta['hasPageBreak'] = strpos($desc, '<!-- pagebreak -->') !== false;

        //keywords
        $meta['keywords'] = [];
        foreach ($references as $reference) {
            $ref = $reference->getReference();
            $meta['keywords'][] = $ref->getLibelle();
        }

        return $meta;
    }

    /**
     * Formatte les objets issues de la recherche : ne récupére que 10 résultats (autour de l'élément sélectionné).
     *
     * @param array         $objets      Liste des objets
     * @param Objet|Contenu $publication La publication (objet|contenu)
     *
     * @return array
     */
    public function formatForPublication($objets, $publication)
    {
        $results = [];
        foreach ($objets as $item) {
            $results[$item['categ']][] = $item;
        }

        $tabToReturn = [];
        foreach ($results as $categ) {
            if (count($categ) > 10) {
                $i = 1;
                $maxResult = null;
                $toAdd = [];

                foreach ($categ as $item) {
                    //objet Found here
                    if ((array_key_exists('objet', $item) && is_null($item['objet']) && $item['id'] == $publication->getId()) || (array_key_exists('objet', $item) && !is_null($item['objet']) && $item['id'] == $publication->getId())) {
                        //si i < 5 : on prend ceux d'avant, et on met à jour le max result
                        if ($i < 5) {
                            $maxResult = $i + (10 - $i);

                            //on ajoute tous ceux d'avant
                            for ($j = 1; $j <= $i; ++$j) {
                                $toAdd[] = $categ[$j];
                            }
                        }

                        //si i > 5 : on prend les 5 d'avants, et on met à jour le max result pour prendre les 5 suivants
                        if ($i >= 5) {
                            $maxResult = $i + 5;

                            //on ajoute les 5 d'avant
                            for ($j = ($i - 5); $j < $i; ++$j) {
                                $toAdd[] = $categ[$j];
                            }
                        }
                    //Objet found before
                    } elseif (!is_null($maxResult)) {
                        if ($i <= $maxResult) {
                            $toAdd[] = $item;
                        }

                        //on break une fois 10 atteint
                        if ($i == $maxResult) {
                            break;
                        }
                    }

                    ++$i;
                }

                //objet never found
                if (count($toAdd) != 10) {
                    for ($i = 0; $i < (10 - count($toAdd)); ++$i) {
                        $toAdd[] = $categ[$i];
                    }
                }

                $tabToReturn = array_merge($toAdd, $tabToReturn);
            } else {
                $tabToReturn = array_merge($categ, $tabToReturn);
            }
        }

        usort($tabToReturn, [$this, 'sortObjets']);

        return $tabToReturn;
    }

    private function sortObjets($objet1, $objet2)
    {
        if ($objet1['primary'] > $objet2['primary']) {
            return -1;
        } elseif ($objet1['primary'] < $objet2['primary']) {
            return 1;
        }

        return ($objet1['countRef'] > $objet2['countRef']) ? -1 : (($objet1['countRef'] < $objet2['countRef']) ? 1 : 0);
    }

    /**
     * Calcul la note de la publication/contenu basée sur ses références.
     *
     * @param array $references Liste des références
     *
     * @return int
     */
    private function getNoteReferencement($references)
    {
        $note = 0;
        foreach ($references as $reference) {
            $id = $reference->getReference()->getId();

            if (isset($this->_refsPonderees[$id])) {
                $note += $this->_refsPonderees[$id]['poids'];
            }
        }

        return $note;
    }

    /**
     * Extrait la catégorie et le(s) type(s) de l'objet.
     *
     * @param Objet $objet Entitée objet
     *
     * @return array
     */
    private function getTypeAndCateg($objet)
    {
        $type = [];
        $categ = '';
        $types = $objet->getTypes();

        foreach ($types as $one) {
            //pas de parent : check ressource / point dur
            if (is_null($one->getFirstParent())) {
                if ($one->getId() == $this->_ressource) {
                    $categ = 'ressource';
                    $type[] = $one->getLibelle();
                } elseif ($one->getId() == $this->_pointDur) {
                    $categ = 'point-dur';
                    $type[] = $one->getLibelle();
                }
            //parent : check production / forum
            } else {
                $parent = $one->getFirstParent();
                if ($parent->getId() == $this->_production) {
                    $categ = 'production';
                    $type[] = $one->getLibelle();
                }
            }
        }
        //reformatte proprement les types
        $type = implode(' ♦ ', $type);

        return ['categ' => $categ, 'type' => $type];
    }
}
