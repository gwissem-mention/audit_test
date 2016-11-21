<?php
namespace HopitalNumerique\RechercheBundle\DependencyInjection\Referencement\Exalead;

use HopitalNumerique\CoreBundle\DependencyInjection\Entity;
use HopitalNumerique\DomaineBundle\DependencyInjection\CurrentDomaine;
use HopitalNumerique\RechercheBundle\Manager\SearchManager;
use HopitalNumerique\RechercheBundle\Doctrine\Referencement\Reader as ReferencementReader;

/**
 * Recherche Exalead.
 */
class Search
{
    /**
     * @var \HopitalNumerique\RechercheBundle\Doctrine\Referencement\Reader ReferencementReader
     */
    private $referencementReader;


    /**
     * @var \HopitalNumerique\DomaineBundle\Entity\Domaine Domaine
     */
    private $domaine;

    /**
     * @var URL du XML d'Exalead
     */
    private $exaleadUrl;


    /**
     * @var array Résultats
     */
    private $results;

    /**
     * @var array<string> Expressions trouvées
     */
    private $foundWords;

    /**
     * @var array Objets
     */
    private $objetsProperties = null;

    /**
     * @var array Contenus
     */
    private $contenusProperties = null;


    /**
     * Constructeur.
     */
    public function __construct(CurrentDomaine $currentDomaine, ReferencementReader $referencementReader, SearchManager $searchManager)
    {
        $this->referencementReader = $referencementReader;

        $this->domaine = $currentDomaine->get();
        $this->exaleadUrl = $searchManager->getUrlRechercheTextuelle();

        $this->results = [];
        $this->foundWords = [];
    }


    /**
     * Effectue la recherche.
     *
     * @param string $searchedText Expression recherchée
     */
    public function setText($searchedText)
    {
        $xmlUrl = $this->exaleadUrl.urlencode($searchedText.' AND id_domaine').'='.$this->domaine->getId();
        $exaleadXml = simplexml_load_file($xmlUrl);

        if (false !== $exaleadXml) {
            if (null !== $exaleadXml->hits->Hit) {
                foreach ($exaleadXml->hits->Hit as $hit) {
                    $hitUrl = (string)$hit->attributes()->url;
                    $hitUrlExplode = explode('=', $hitUrl);
                    $properties = [
                        'entityId' => intval(substr($hitUrlExplode[1], 0, -1)),
                        'score' => intval($hit->attributes()->score)
                    ];

                    // YRO 10/02/2015 : les occurrences réellement trouvées dans les contenus
                    foreach ($hit->metas->Meta as $meta) {
                        if (in_array($meta->attributes()->name, ['text', 'title'])) {
                            foreach ($meta->MetaText as $metaText) {
                                $texte = '';
                                foreach ($metaText->TextSeg as $textSeg) {
                                    $texte .= (string) $textSeg;
                                    if ($textSeg->attributes()->highlighted == 'true') {
                                        $texteTrouve = (string) $textSeg;
                                        if (!in_array($texteTrouve, $this->foundWords)) {
                                            $this->foundWords[] = $texteTrouve;
                                        }
                                    }
                                }
                                if ('' != trim($texte)) {
                                    if ('text' == $meta->attributes()->name) {
                                        $properties['description'] = $texte;
                                    } elseif ('title' == $meta->attributes()->name) {
                                        $properties['title'] = $texte;
                                    }
                                }
                            }
                        }
                    }

                    if ($hitUrlExplode[0] == 'obj_id') {
                        $properties['entityType'] = Entity::ENTITY_TYPE_OBJET;
                        $this->results[] = $properties;
                    } elseif ($hitUrlExplode[0] == 'con_id') {
                        $properties['entityType'] = Entity::ENTITY_TYPE_CONTENU;
                        $this->results[] = $properties;
                    }
                }
            }
        }
    }

    /**
     * Retourne les expressions trouvées.
     *
     * @return array<string> Expressions
     */
    public function getFoundWords()
    {
        return $this->foundWords;
    }

    /**
     * Retourne les ID des objets trouvés.
     *
     * @return array<integer> IDs
     */
    public function getObjetIds()
    {
        $entityIds = [];

        foreach ($this->results as $entity) {
            if (Entity::ENTITY_TYPE_OBJET == $entity['entityType']) {
                $entityIds[] = $entity['entityId'];
            }
        }

        return $entityIds;
    }

    /**
     * Retourne les ID des contenus trouvés.
     *
     * @return array<integer> IDs
     */
    public function getContenuIds()
    {
        $entityIds = [];

        foreach ($this->results as $entity) {
            if (Entity::ENTITY_TYPE_CONTENU == $entity['entityType']) {
                $entityIds[] = $entity['entityId'];
            }
        }

        return $entityIds;
    }


    /**
     * Retourne les entités objets.
     *
     * @return array Entités
     */
    private function getObjetsProperties()
    {
        if (null !== $this->objetsProperties) {
            return $this->objetsProperties;
        }

        $this->objetsProperties = [];
        $entitiesPropertiesByObjetIds = $this->referencementReader->getEntitiesPropertiesByObjetIds($this->getObjetIds());

        foreach ($this->results as $objet) {
            foreach ($entitiesPropertiesByObjetIds as $i => $objetProperties) {
                if ($objet['entityType'] == Entity::ENTITY_TYPE_OBJET && $objetProperties['entityId'] == $objet['entityId']) {
                    $this->objetsProperties[$i] = $objetProperties;
                    //$this->objetsProperties[$i]['title'] = $objet['title'];
                    $this->objetsProperties[$i]['description'] = $objet['description'];
                    $this->objetsProperties[$i]['pertinenceNiveau'] = $this->getScore($objet['score']);
                    break;
                }
            }
        }

        return $this->objetsProperties;
    }

    private function getScore($score)
    {
        if ($score <= 333) {
            $value = 3;
        } elseif ($score >= 333 and $score <= 666) {
            $value = 2;
        } elseif ($score >= 666) {
            $value = 1;
        }

        return $value;
    }

    /**
     * Retourne les entités contenus.
     *
     * @return array Entités
     */
    private function getContenusProperties()
    {
        if (null !== $this->contenusProperties) {
            return $this->contenusProperties;
        }

        $this->contenusProperties = [];
        $entitiesPropertiesByContenuIds = $this->referencementReader->getEntitiesPropertiesByContenuIds($this->getContenuIds());

        foreach ($this->results as $contenu) {
            foreach ($entitiesPropertiesByContenuIds as $i => $contenuProperties) {
                if ($contenu['entityType'] == Entity::ENTITY_TYPE_CONTENU && $contenuProperties['entityId'] == $contenu['entityId']) {
                    $this->contenusProperties[$i] = $contenuProperties;
                    //$this->contenusProperties[$i]['title'] = $contenu['title'];
                    if (array_key_exists('description', $contenu)) {
                        $this->contenusProperties[$i]['description'] = $contenu['description'];
                    }
                    break;
                }
            }
        }

        return $this->contenusProperties;
    }

    /**
     * Retourne les entités par groupe.
     *
     * @return array Entités
     */
    public function getEntitiesPropertiesByGroup()
    {
        $entitiesPropertiesByGroup = [
            'points-durs' => [],
            'productions' => []
        ];

        foreach ($this->getObjetsProperties() as $objetProperties) {
            $group = ($objetProperties['pointDur'] ? 'points-durs' : 'productions');
            $entitiesPropertiesByGroup[$group][] = $objetProperties;
        }
        foreach ($this->getContenusProperties() as $contenuProperties) {
            $group = ($contenuProperties['pointDur'] ? 'points-durs' : 'productions');
            $entitiesPropertiesByGroup[$group][] = $contenuProperties;
        }

        return $entitiesPropertiesByGroup;
    }

    /**
     * Retourne les propriétés de l'entité.
     *
     * @param integer $entityType Type
     * @param integer $entityId   Id
     * @return array|null Propriétés
     */
    /*private function getEntityPropertiesByTypeAndId($entityType, $entityId)
    {
        switch ($entityType) {
            case Entity::ENTITY_TYPE_OBJET:
                $objetsProperties = $this->getObjetsProperties();
                foreach ($objetsProperties as $objetProperties) {
                    if ($objetProperties['entityId'] == $entityId) {
                        return $objetProperties;
                    }
                }
                break;
            case Entity::ENTITY_TYPE_CONTENU:
                $contenusProperties = $this->getContenusProperties();
                foreach ($contenusProperties as $contenuProperties) {
                    if ($contenuProperties['entityId'] == $entityId) {
                        return $contenuProperties;
                    }
                }
                break;
        }

        return null;
    }*/

    /**
     * Retourne les propriétés fusionnées avec celles d'Exalead.
     *
     * @param array $entityProperties Entity properties
     * @return array Propriétés
     */
    /*public function mergeEntityProperties($entityProperties)
    {
        $exaleadEntityProperties = $this->getEntityPropertiesByTypeAndId($entityProperties['entityType'], $entityProperties['entityId']);

        $exaleadEntityProperties['pertinenceNiveau'] = $entityProperties['pertinenceNiveau'];
        $exaleadEntityProperties['categoryIds'] = $entityProperties['categoryIds'];

        return $exaleadEntityProperties;
    }*/
    
    public function mergeEntitiesPropertiesByGroup($exaleadEntitiesPropertiesByGroup, $dbEntitiesPropertiesByGroup, $deleteIfNotReferenced = false)
    {
        foreach ($exaleadEntitiesPropertiesByGroup as $group => $exaleadEntitiesProperties) {
            foreach ($exaleadEntitiesProperties as $i => $exaleadEntityProperties) {
                $entityPresent = false;
                foreach ($dbEntitiesPropertiesByGroup[$group] as $entityProperties) {
                    if ($exaleadEntityProperties['entityId'] == $entityProperties['entityId'] && $exaleadEntityProperties['entityType'] == $entityProperties['entityType']) {
                        //$exaleadEntitiesPropertiesByGroup[$group][$i]['title'] = $entityProperties['title'];
                        //$exaleadEntitiesPropertiesByGroup[$group][$i]['subtitle'] = $entityProperties['subtitle'];
                        $exaleadEntitiesPropertiesByGroup[$group][$i]['pertinenceNiveau'] = $exaleadEntityProperties['pertinenceNiveau'];
                        $exaleadEntitiesPropertiesByGroup[$group][$i]['categoryIds'] = $entityProperties['categoryIds'];
                        /*if (array_key_exists('categoryLabels', $entityProperties)) {
                            $exaleadEntitiesPropertiesByGroup[$group][$i]['categoryLabels'] = $entityProperties['categoryLabels'];
                        }*/
                        $entityPresent = true;
                        break;
                    }
                }
                if ($deleteIfNotReferenced && !$entityPresent) {
                    unset($exaleadEntitiesPropertiesByGroup[$group][$i]);
                }
            }
            $exaleadEntitiesPropertiesByGroup[$group] = array_values($exaleadEntitiesPropertiesByGroup[$group]);
        }
        return $exaleadEntitiesPropertiesByGroup;
    }

    public function deleteOthersCategoriesFromEntitiesPropertiesByGroup($exaleadEntitiesPropertiesByGroup, array $publicationCategoryIds)
    {
        foreach ($exaleadEntitiesPropertiesByGroup as $group => $exaleadEntitiesProperties) {
            foreach ($exaleadEntitiesProperties as $i => $exaleadEntityProperties) {
                $oneCategoryPresent = false;
                foreach ($publicationCategoryIds as $publicationCategoryId) {
                    if (in_array($publicationCategoryId, $exaleadEntityProperties['categoryIds'])) {
                        $oneCategoryPresent = true;
                        break;
                    }
                }
                if (!$oneCategoryPresent) {
                    unset($exaleadEntitiesPropertiesByGroup[$group][$i]);
                }
            }
            $exaleadEntitiesPropertiesByGroup[$group] = array_values($exaleadEntitiesPropertiesByGroup[$group]);
        }

        return $exaleadEntitiesPropertiesByGroup;
    }
}
