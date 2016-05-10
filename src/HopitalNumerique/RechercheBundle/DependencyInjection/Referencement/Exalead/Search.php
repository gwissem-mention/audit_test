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
        $this->results['objets'] = [];
        $this->results['contenus'] = [];
        $xmlUrl = $this->exaleadUrl.urlencode($searchedText.' AND id_domaine').'='.$this->domaine->getId();
        $exaleadXml = simplexml_load_file($xmlUrl);

        if (false !== $exaleadXml) {
            if (null !== $exaleadXml->hits->Hit) {
                foreach ($exaleadXml->hits->Hit as $hit) {
                    $hitUrl = (string)$hit->attributes()->url;
                    $hitUrlExplode = explode('=', $hitUrl);
                    $properties = [
                        'entityId' => intval(substr($hitUrlExplode[1], 0, -1))
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
                        $this->results['objets'][] = $properties;
                    } elseif ($hitUrlExplode[0] == 'con_id') {
                        $this->results['contenus'][] = $properties;
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
        $objetIds = [];

        foreach ($this->results['objets'] as $objet) {
            $objetIds[] = $objet['entityId'];
        }

        return $objetIds;
    }

    /**
     * Retourne les ID des contenus trouvés.
     *
     * @return array<integer> IDs
     */
    public function getContenuIds()
    {
        $contenuIds = [];

        foreach ($this->results['contenus'] as $contenu) {
            $contenuIds[] = $contenu['entityId'];
        }

        return $contenuIds;
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

        $this->objetsProperties = $this->referencementReader->getEntitiesPropertiesByObjetIds($this->getObjetIds());

        foreach ($this->objetsProperties as $i => $objetProperties) {
            foreach ($this->results['objets'] as $objet) {
                if ($objetProperties['entityId'] == $objet['entityId']) {
                    $this->objetsProperties[$i]['title'] = $objet['title'];
                    $this->objetsProperties[$i]['description'] = $objet['description'];
                    break;
                }
            }
        }

        return $this->objetsProperties;
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

        $this->contenusProperties = $this->referencementReader->getEntitiesPropertiesByContenuIds($this->getContenuIds());

        foreach ($this->contenusProperties as $i => $contenuProperties) {
            foreach ($this->results['contenus'] as $contenu) {
                if ($contenuProperties['entityId'] == $contenu['entityId']) {
                    $this->contenusProperties[$i]['title'] = $contenu['title'];
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
    private function getEntityPropertiesByTypeAndId($entityType, $entityId)
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
    }

    /**
     * Retourne les propriétés fusionnées avec celles d'Exalead.
     *
     * @param array $entityProperties Entity properties
     * @return array Propriétés
     */
    public function mergeEntityProperties($entityProperties)
    {
        $exaleadEntityProperties = $this->getEntityPropertiesByTypeAndId($entityProperties['entityType'], $entityProperties['entityId']);

        if (null !== $exaleadEntityProperties) {
            return array_merge($entityProperties, $exaleadEntityProperties);
        }

        return $entityProperties;
    }
}
