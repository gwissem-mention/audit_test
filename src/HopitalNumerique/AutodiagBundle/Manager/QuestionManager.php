<?php

namespace HopitalNumerique\AutodiagBundle\Manager;

use Doctrine\ORM\EntityManager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use HopitalNumerique\AutodiagBundle\Entity\Question;
use HopitalNumerique\UserBundle\Manager\UserManager;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;

/**
 * Manager de l'entité Question.
 */
class QuestionManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\AutodiagBundle\Entity\Question';
    protected $_userManager;
    protected $_referenceManager;
    
    /**
     * @var \HopitalNumerique\AutodiagBundle\Manager\ResultatManager Le manager de l'entité Resultat
     */
    private $resultatManager;

    /**
     * Constructeur du manager gérant les chapitres d'outil.
     *
     * @param \Doctrine\ORM\EntityManager $entityManager EntityManager
     * @return void
     */
    public function __construct(EntityManager $entityManager, UserManager $userManager, ReferenceManager $referenceManager)
    {
        parent::__construct($entityManager);
        $this->_userManager      = $userManager;
        $this->_referenceManager = $referenceManager;
    }

    /**
     * Compte le nombre de questions lié au chapitre
     *
     * @param Chapitre $chapitre chapitre
     *
     * @return integer
     */
    public function countQuestions( $chapitre )
    {
        return $this->getRepository()->countQuestions($chapitre)->getQuery()->getSingleScalarResult();
    }

    /**
     * Retourne la liste des questions dans le bon ordre
     *
     * @param Chapitre $chapitre chapitre
     *
     * @return integer
     */
    public function getQuestionsOrdered( $chapitre, $refPonderees )
    {
        $questions = $this->getRepository()->getQuestionsOrdered($chapitre)->getQuery()->getResult();

        $datas = array();
        foreach($questions as $one) {
            $question              = new \StdClass;
            $question->id          = $one->getId();
            $question->texte       = $one->getTexte();
            $question->code        = $one->getCode();
            $question->ponderation = $one->getPonderation();
            $question->note        = $this->getNoteReferencement( $one->getReferences(), $refPonderees );

            $datas[] = $question;
        }

        return $datas;
    }

    /**
     * Enregistre la question
     *
     * @param  Question $question La question
     *
     * @return empty
     */
    public function saveQuestion( Question $question )
    {
        if( $question->getType()->getId() == 417 ){
            $question->setOptions( null );
            $question->setNoteMinimale( null );
        }else{
            $question->setSeuil( null );
        }

        //cas nouveau
        if( is_null($question->getId()) ){
            $chapitre = $question->getChapitre();
            $question->setOrder( $this->calcOrder($chapitre) );
        }

        $this->save( $question );
    }

    /**
     * Formatte les références sous forme d'un unique tableau
     *
     * @param Question $question   La question concernée
     * @param array    $references Liste des références de type dictionnaire
     *
     * @return array
     */
    public function getReferences($question, $references)
    {
        $selectedReferences = $question->getReferences();

        //applique les références 
        foreach( $selectedReferences as $selected )
        {
            //on récupère l'élément que l'on va manipuler
            $ref = $references[ $selected->getReference()->getId() ];

            //on le met à jour 
            $ref->selected = true;
            $ref->primary  = $selected->getPrimary();

            //on remet l'élément à sa place
            $references[ $selected->getReference()->getId() ] = $ref;
        }

        $references = $this->filtreReferencesByDomaines($question->getChapitre()->getOutil(), $references);
        
        return $references;
    }
    
    /**
     * Met à jour l'ordre des questions
     *
     * @param array $elements Les éléments
     *
     * @return empty
     */
    public function reorder( $elements )
    {
        $order = 1;

        foreach($elements as $element) {
            $question = $this->findOneBy( array('id' => $element['id']) );
            $question->setOrder( $order );
            $order++;
        }
    }







    /**
     * Filtre les reférences en fonction de l'outil passés en paramètre
     *
     * @param [type] $outil      [description]
     * @param [type] $references [description]
     *
     * @return [type]
     */
    private function filtreReferencesByDomaines($outil, $references)
    {
        $referencesIds    = array();
        $domainesOutilIds = array();
        $userConnectedDomaineIds = $this->_userManager->getUserConnected()->getDomainesId();

        //Récupération des id de domaine de l'outil
        foreach ($outil->getDomaines() as $domaine) 
        {
            if(in_array($domaine->getId(), $userConnectedDomaineIds))
            {
                $domainesOutilIds[] = $domaine->getId();
            }
        }

        //Vérifie qu'il y a bien un domaine pour la publication courante
        if(count($domainesOutilIds) !== 0)
        {   
            //Récupération des id des références "stdClass" pour récupérer les entités correspondantes et donc les domaines
            foreach ($references as $reference) 
            {
                $referencesIds[] = $reference->id;
            }

            $referencesByIds = $this->_referenceManager->findBy(array('id'=> $referencesIds));

            //Parcourt la liste des entités de référence
            foreach ($referencesByIds as $reference) 
            {
                if(array_key_exists($reference->getId(), $references))
                {
                    $inArray = false;

                    foreach ($reference->getDomaines() as $domaine) 
                    {
                        if(in_array($domaine->getId(), $domainesOutilIds))
                        {
                            $inArray = true;
                            break;
                        }   
                    }

                    if(!$inArray)
                    {
                        unset($references[$reference->getId()]);
                    }
                }
            }
        }
        //Sinon vide les références, car une publication sans domaine ne peut pas être référencées
        else
        {
            $references = array();
        }

        return $references;
    }

    /**
     * Retourne la note des références
     *
     * @param array $references   Tableau des références
     * @param array $ponderations Tableau des pondérations
     *
     * @return integer
     */
    private function getNoteReferencement( $references, $refPonderees )
    {
        $note = 0;
        if(!is_null($references)){
            foreach($references as $reference){
                $id = $reference->getReference()->getId();

                if( isset($refPonderees[ $id ]) )
                    $note += $refPonderees[ $id ]['poids'];
            }
        }
        
        return $note;
    }

    /**
     * Calcul l'ordre de la question par rapport au chapitre
     *
     * @param Chapitre $chapitre Le chapitre
     *
     * @return integer
     */
    private function calcOrder( $chapitre )
    {
        return $this->countQuestions( $chapitre ) + 1;
    }
}