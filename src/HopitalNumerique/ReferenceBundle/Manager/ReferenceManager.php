<?php

namespace HopitalNumerique\ReferenceBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\UserBundle\Manager\UserManager;
use HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire;

use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Manager de l'entité Reference.
 */
class ReferenceManager extends BaseManager
{
    protected $_class       = 'HopitalNumerique\ReferenceBundle\Entity\Reference';
    private $_tabReferences = array();
    private $_session;
    protected $_userManager;

    /**
     * Constructeur du manager gérant les références
     *
     * @param \Doctrine\ORM\EntityManager $entityManager EntityManager
     * @return void
     */
    public function __construct(EntityManager $entityManager, Session $session, UserManager $userManager)
    {
        parent::__construct($entityManager);

        $this->_session     = $session;
        $this->_userManager = $userManager;
    }

    /**
     * Formate et retourne l'arborescence des références
     *
     * @param  boolean $unlockedOnly     Retourne que les éléments non vérouillés
     * @param  boolean $fromDictionnaire Retourne que les éléments présent dans le dictionnaire
     * @param  boolean $fromRecherche    Retourne que les éléments présent dans la recherche
     *
     * @return array
     */
    public function getArbo( $unlockedOnly = false, $fromDictionnaire = false, $fromRecherche = false, $domaineIds = array() )
    {
        //get All References, and convert to ArrayCollection
        $datas = new ArrayCollection( $this->getRepository()->getArbo( $unlockedOnly, $fromDictionnaire, $fromRecherche, $domaineIds ) );

        //Récupère uniquement les premiers parents
        $criteria = Criteria::create()->where(Criteria::expr()->eq("parent", null) );
        $parents  = $datas->matching( $criteria );
        
        //call recursive function to handle all datas
        return $this->getArboRecursive($datas, $parents, array() );
    }

    /**
     * Récupère l'arbo depuis une référence
     *
     * @param Reference $reference [description]
     *
     * @return [type]
     */
    public function getArboFromAReference( Reference $reference )
    {
        //get All References, and convert to ArrayCollection
        $datas = new ArrayCollection( $this->getRepository()->getArbo( false, false, false ) );

        //Récupère uniquement les premiers parents
        $criteria = Criteria::create()->where( Criteria::expr()->eq("id", $reference->getId() ) );
        $parents  = $datas->matching( $criteria );
        
        //call recursive function to handle all datas
        return $this->getArboRecursive($datas, $parents, array() );
    }
    
    /**
     * Retourne l'arbo formatée
     *
     * @param  boolean $unlockedOnly     Retourne que les éléments non vérouillés
     * @param  boolean $fromDictionnaire Retourne que les éléments présent dans le dictionnaire
     * @param  boolean $fromRecherche    Retourne que les éléments présent dans la recherche
     *
     * @return array
     */
    public function getArboFormat( $unlockedOnly = false, $fromDictionnaire = false, $fromRecherche = false , $domaineIds = array() )
    {
        $arbo = $this->getArbo($unlockedOnly, $fromDictionnaire, $fromRecherche, $domaineIds );
        return $this->formatArbo($arbo);
    }
    
    /**
     * Override : Récupère les données pour le grid sous forme de tableau
     *
     * @return array
     */
    public function getDatasForGrid( \StdClass $condition = null )
    {
        $referencesForGrid = array();

        $domainesIds = $this->_userManager->getUserConnected()->getDomainesId();

        $references = $this->getRepository()->getDatasForGrid( $domainesIds, $condition )->getQuery()->getResult();

        foreach ($references as $reference) 
        {
            $reference['idReference'] = $reference['id'];
            if(!array_key_exists($reference['idReference'], $referencesForGrid))
            {
                $referencesForGrid[$reference['idReference']] = $reference;

            }
            else
            {
                $referencesForGrid[$reference['idReference']]['domaineNom'] .= ";" . $reference['domaineNom'];
            }
        }

        return array_values($referencesForGrid);
    }

    /**
     * Récupère les données pour l'export CSV
     *
     * @return array
     */
    public function getDatasForExport( $ids )
    {
        $referencesForExport = array();

        $references = $this->getRepository()->getDatasForExport( $ids )->getQuery()->getResult();

        foreach ($references as $reference) 
        {
            if(!array_key_exists($reference['id'], $referencesForExport))
            {
                $referencesForExport[$reference['id']] = $reference;
            }
            else
            {
                $referencesForExport[$reference['id']]['domaineNom'] .= "|" . $reference['domaineNom'];
            }
        }

        return array_values($referencesForExport);
    }

    /**
     * Récupère les données pour l'export CSV
     *
     * @return array
     */
    public function getAllRefCode(Questionnaire $questionnaire)
    {
        $domainesQuestionnaireId = $questionnaire->getDomainesId();

        return $this->getRepository()->getAllRefCode($domainesQuestionnaireId)->getQuery()->getResult();
    }

    /**
     * Récupération des domaines triés par réference
     *
     * @return [type]
     */
    public function getDomainesOrderedByReference()
    {
        $references                 = $this->getRepository()->getReferencesWithDomaine()->getQuery()->getResult();
        $domainesOrderedByReference = array();

        foreach ($references as $reference) 
        {
            foreach ($reference->getDomaines() as $domaine) 
            {
                if(!array_key_exists($reference->getId(), $domainesOrderedByReference))
                {
                    $domainesOrderedByReference[$reference->getId()] = array();    
                }

                $domainesOrderedByReference[$reference->getId()][] = $domaine;
            }
        }

        return $domainesOrderedByReference;
    }

    /**
     * Réordonne les options en mode parent |-- enfants
     *
     * @param array $options Tableau d'options
     *
     * @return array
     */
    public function reorder( $options )
    {
        $this->reorderChilds( $this->getArbo( true ) );
        $orderedOptions = array();

        foreach($this->_tabReferences as $orderedElement) {
            if( isset($options[ $orderedElement ]) )
                $orderedOptions[ $orderedElement ] = $options[ $orderedElement ];
        }

        return $orderedOptions;
    }

    /**
     * Mise à jour de l'order des référentiels
     *
     * @param $referentiel \HopitalNumerique\ReferenceBundle\Entity\Reference Référentiel à mettre à jour
     */
    public function updateOrder( Reference $referentiel )
    {
        
        //get All References
        $datas = new ArrayCollection($this->getRepository()->findBy(array('lock'=>0)));
        
        //Récupération des réferentiels
        $criteria = Criteria::create()
                                    ->where(Criteria::expr()->eq("parent", $referentiel->getParent()))
                                    ->andWhere(Criteria::expr()->gte("order", $referentiel->getOrder()))
                                    ->orderBy( array("order" => Criteria::ASC) );
        $childs = $datas->matching( $criteria );

        //On décale les référentiels dont l'order est supérieur ou égal au référentiel courant
        $newOrder = $referentiel->getOrder();
        foreach ($childs as $referentielAModifier) {
            if ($referentielAModifier === $referentiel)
                continue;

            $newOrder++;
            $referentielAModifier->setOrder( $newOrder );
            $this->save($referentielAModifier);
        }
    }

    /**
     * Retourne la liste des objets ordonnées pour les références des objets
     *
     * @return array
     */
    public function getRefsForGestionObjets()
    {
        $this->convertAsFlatArray( $this->getArbo(false, true), 1, array() );
        
        return $this->_tabReferences;
    }

    /**
     * Formatte la liste des domaines fonctionnels de l'user
     *
     * @param User $user User
     *
     * @return Array
     */
    public function getDomainesForUser ( $user )
    {
        $results = $this->findByCode('PERIMETRE_FONCTIONNEL_DOMAINES_FONCTIONNELS');

        $domaines = array(
            'ids'      => array(),
            'domaines' => array()
        );
        foreach ($results as $result) {
            if (count($result->getParents()) > 0) {
                $parentId = $result->getParentIds()[0];
                
                if(!array_key_exists($parentId, $domaines['domaines']))
                {
                    $domaines['domaines'][$parentId] = array();
                }

                $tmp           = new \stdClass;
                $tmp->id       = $result->getId();
                $tmp->libelle  = $result->getLibelle();
                $tmp->code     = $result->getCode();
                $tmp->parent   = $result->getParents()[0]->getLibelle();
                $tmp->selected = false;

                $domaines['domaines'][$parentId][] = $tmp;
                $domaines['ids'][] = $result->getId();
            }
        }

        return $domaines;
    }

    /**
     * Récupère les références Pondérées
     *
     * @return array
     */
    public function getReferencesPonderees( $domaineIds = array() )
    {
        $references = $this->getArboFormat( false, false, true, $domaineIds);

        if(!array_key_exists('CATEGORIES_RECHERCHE', $references))
        {
            return array();
        }
        
        $references = $references['CATEGORIES_RECHERCHE'];
        $results    = array();

        foreach($references as &$reference)
        {
            $reference['poids'] = 100;

            if( $reference['childs'] )
            {
                $keys        = array_keys($reference['childs']);
                $childs      = $reference['childs'][$keys[0]];
                $childWeight = 100 / count($childs);
                
                foreach($childs as &$child)
                {
                    $child['poids'] = $childWeight;
                    if( $child['childs'] )
                    {
                        $keys             = array_keys($child['childs']);
                        $smallChilds      = $child['childs'][$keys[0]];
                        $smallChildWeight = $childWeight / count($smallChilds);

                        foreach ($smallChilds as &$smallChild)
                        {
                            $smallChild['poids'] = $smallChildWeight;
                            if( $smallChild['childs'] )
                            {
                                $keys                  = array_keys($smallChild['childs']);
                                $verySmallChilds       = $smallChild['childs'][$keys[0]];
                                $verySmallChildsWeight = $smallChildWeight / count($verySmallChilds);

                                foreach($verySmallChilds as &$verySmallChild)
                                {
                                    $verySmallChild['poids']          = $verySmallChildsWeight;
                                    $results[ $verySmallChild['id'] ] = $verySmallChild;
                                }
                            }else
                                $results[ $smallChild['id'] ] = $smallChild;
                        }
                    }else
                        $results[ $child['id'] ] = $child;
                }
            }
        }

        return $results;
    }

    public function getRefsByDomaineByParent($idParent, $idDomaine)
    {
        return $this->getRepository()->getRefsByDomaineByParent($idParent, $idDomaine)->getQuery()->getResult();
    }

    public function delete( $references )
    {
        try 
        {
            parent::delete($references);
            $this->_session->getFlashBag()->add('info', 'Suppression effectuée avec succès.' );
        }
        catch (\Exception $e)
        {
            $this->_session->getFlashBag()->add('danger', 'Cette référence semble avoir une ou plusieurs liaison(s). Vous ne pouvez pas la supprimer.' );
        }
    }

    /**
     * Retourne les régions.
     * 
     * @return array<\HopitalNumerique\ReferenceBundle\Entity\Reference> Régions
     */
    public function findRegions()
    {
        return $this->findByCode('REGION');
    }

    /**
     * Retourne les types d'ES.
     * 
     * @return array<\HopitalNumerique\ReferenceBundle\Entity\Reference> Types
     */
    public function findEtablissementSanteTypes()
    {
        return $this->findByCode('CONTEXTE_TYPE_ES');
    }

    /**
     * Retourne les profils des ES.
     * 
     * @return array<\HopitalNumerique\ReferenceBundle\Entity\Reference> Profils
     */
    public function findEtablissementSanteProfils()
    {
        return $this->findByCode('CONTEXTE_METIER_INTERNAUTE');
    }

    /**
     * Retourne les types d'activité.
     * 
     * @return array<\HopitalNumerique\ReferenceBundle\Entity\Reference> Types
     */
    public function findActiviteTypes()
    {
        return $this->findByCode('CONTEXT_SPECIALITE_ES');
    }

    /**
     * Retourne les références enfants.
     *
     * @return array<\HopitalNumerique\ReferenceBundle\Entity\Reference> Références
     */
    public function findByParent(Reference $parent)
    {
        return $this->getRepository()->findByParent($parent);
    }

    public function findByCode($code, $isActif = true)
    {
        return $this->getRepository()->findByCode($code, $isActif);
    }

    public function findByCodeParent($code, $idParent, $isActif = true)
    {
        return $this->getRepository()->findByCodeParent($code, $idParent, $isActif);
    }

    /**
     * Retourne un code selon son code.
     *
     * @param string  $code    Code
     * @param boolean $isActif Actif
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference Référence
     */
    public function findOneByCode($code, $isActif = true)
    {
        $byCode = $this->findByCode($code, $isActif);

        return (count($byCode) > 0 ? $byCode[0] : null);
    }

    /**
     * Retourne les références selon des domaines.
     *
     * @param array<\HopitalNumerique\DomaineBundle\Entity\Domaine> $domaines   Domaines
     * @param boolean                                               $lock       Lock
     * @param boolean                                               $parentable Parentable
     * @param boolean                                               $reference  Reference
     * @return array<\HopitalNumerique\ReferenceBundle\Entity\Reference> Références
     */
    public function findByDomaines($domaines = null, $actif = null, $lock = null, $parentable = null, $reference = null)
    {
        return $this->getRepository()->findByDomaines($domaines, $actif, $lock, $parentable, $reference);
    }








    /**
     * Converti l'arbo sous forme de tableau unique avec l'arbo représentée dans le nom de l'élément
     * On remplis ici un tableau global, pour écomiser des ressources via un array_merge et conserver les cléfs
     *
     * @param array   $childs Liste des éléments à ajouter au tableau
     * @param integer $level  Level de profondeur
     *
     * @return array
     */
    private function convertAsFlatArray( $childs, $level, $parent )
    {
        //affiche le level sous forme de |--
        $sep = '';
        if( $level > 1 ) {
            for( $i = 2; $i <= $level; $i++ )
                $sep .= '|----';
            $sep .= ' ';
        }

        //create Child Ids table : permet de lister les ID de tous les enfants de l'élément : selection récursive des références
        $childsIds = array();

        //pour chaque element, on le transforme on objet simple avec son niveau de profondeur affiché dans le nom
        foreach($childs as $child)
        {
            //populate childsIds
            $childsIds[] = $child->id;

            //create object
            $ref           = new \stdClass;
            $ref->id       = $child->id;
            $ref->nom      = $sep . $child->code . ' - ' . $child->libelle;
            $ref->libelle  = $sep . $child->libelle;
            $ref->selected = false;
            $ref->primary  = false;
            $ref->childs   = null;
            $ref->parents  = json_encode($parent);
            $ref->level    = $level;

            //add element parent first
            $this->_tabReferences[ $child->id ] = $ref;

            //if childs
            if ( !empty($child->childs) ){
                //met à jour le tab parent
                $tmp   = $parent;
                $tmp[] = $child->id;

                //on met à jour sa liste d'enfants
                $newChilds = $this->convertAsFlatArray( $child->childs, $level + 1, $tmp );

                //récupère temporairement l'élément que l'on vien d'ajouter
                $tmp = $this->_tabReferences[ $child->id ];
                $tmp->childs   = json_encode( $newChilds );
                $tmp->nbChilds = count( $newChilds );

                //on remet l'élément parent à sa place
                $this->_tabReferences[ $child->id ] = $tmp;

                //merge les enfants
                $childsIds = array_merge($childsIds, $newChilds);
            }
        }

        return $childsIds;
    }

    /**
     *  Retourne la liste des enfants de manière récursive
     *
     *  @param array  $childs Liste des elements à parcourir
     *
     *  @return empty
     */
    private function reorderChilds( $childs )
    {
        foreach($childs as $child)
        {
            $this->_tabReferences[ '0' . $child->id ] = $child->id;

            if ( !empty($child->childs) )
                $this->reorderChilds( $child->childs );
        }
    }

    /**
     * Fonction récursive qui parcourt l'ensemble des références en ajoutant l'element et recherchant les éventuels enfants
     *
     * @param ArrayCollection $items    Données d'origine
     * @param array           $elements Tableau d'élements à ajouter
     * @param array           $tab      Tableau de données vide à remplir puis retourner
     *
     * @return array
     */
    private function getArboRecursive( ArrayCollection $items, $elements, $tab)
    {
        foreach($elements as $element) {
            //construction de l'element current
            $id = $element['id'];
            $item          = new \stdClass;
            $item->libelle = $element['libelle'];
            $item->code    = $element['code'];
            $item->id      = $id;

            //add childs : filter items with current element
            $criteria     = Criteria::create()->where(Criteria::expr()->eq("parent", $id))->orderBy(array("order"=>Criteria::ASC));
            $childs       = $items->matching( $criteria );
            $item->childs = $this->getArboRecursive($items, $childs, array() );

            //add current item to big table
            $tab[] = $item;
        }

        //return big table
        return $tab;
    }

    /**
     * Formatte de manière récursive l'arborescence des références
     *
     * @param array $arbo [description]
     *
     * @return array
     */
    private function formatArbo($arbo)
    {
        $retour = array();
        foreach( $arbo as $key => $ref ){
            $retour[ $ref->code ][ $key ]['libelle'] = $ref->libelle;
            $retour[ $ref->code ][ $key ]['id']      = $ref->id;
            if( $ref->childs ){
                $retour[ $ref->code ][ $key ]['childs'] = $this->formatArbo( $ref->childs );
            } else {
                $retour[ $ref->code ][ $key ]['childs'] = false;
            }
        }
        return $retour;
    }
}