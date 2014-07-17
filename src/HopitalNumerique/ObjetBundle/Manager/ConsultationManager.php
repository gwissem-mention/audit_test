<?php
namespace HopitalNumerique\ObjetBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use Doctrine\ORM\EntityManager;

/**
 * Manager de l'entité Consultation.
 */
class ConsultationManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\ObjetBundle\Entity\Consultation';
    protected $_securityContext;

    /**
     * Construct
     *
     * @param EntityManager   $em              Entity Mangager de doctrine
     * @param SecurityContext $securityContext Security Context
     */
    public function __construct(EntityManager $em, $securityContext)
    {
        parent::__construct($em);
        $this->_securityContext = $securityContext;
    }

    /**
     * Retourne les dernières consultations de l'user $user
     *
     * @param User $user L'user connecté
     *
     * @return array
     */
    public function getLastsConsultations( $user )
    {
        return $this->getRepository()->getLastsConsultations( $user )->getQuery()->getResult();
    }

    /**
     * On met l'objet en consulté (création si first visite, ou update de la date)
     *
     * @param Objet $objet     La publication visitée
     * @param bool  $isContenu Is contenu ?
     *
     * @return empty
     */
    public function consulted($objet, $isContenu = false)
    {
        $user = $this->_securityContext->getToken()->getUser();

        if( $user != "anon.") {
            $consultation = $isContenu ? $this->findOneBy( array( 'objet'=>$objet->getObjet(), 'contenu'=>$objet, 'user'=>$user) ) : $this->findOneBy( array('objet'=>$objet, 'user'=>$user, 'contenu'=>null) );

            //new
            if( is_null($consultation) ){
                $consultation = $this->createEmpty();

                if( $isContenu ){
                    $consultation->setContenu( $objet );
                    $consultation->setObjet( $objet->getObjet() );
                }else
                    $consultation->setObjet( $objet );
                
                $consultation->setUser( $user );
            //update
            }else
                $consultation->setDateLastConsulted( new \DateTime() );
            
            $this->save( $consultation );
        }
    }

    /**
     * Met à jour le tableau d'objets/contenus avec les prod consultées par l'user connecté
     *
     * @param array $objets Liste des objets/contenus concernés
     *
     * @return array
     */
    public function updateObjetsWithConnectedUser( $objets, $user )
    {
        if( $user != "anon.") {
            //get date Inscription user
            $dateInscription = $user->getDateInscription();

            //get consulted objets and formate them
            $results   = $this->getLastsConsultations( $user );
            $consulted = array('objets' => array(), 'contenus' => array() );
            foreach($results as $one){
                //Cas objet
                if( is_null($one->getContenu()) ) {
                    //Si la date de dernière mise à jour de l'objet est postérieure à la dernière consultation de l'objet : Notif updated
                    $consulted['objets'][ $one->getObjet()->getId() ] = $one->getObjet()->getDateModification() > $one->getDateLastConsulted();
                //Cas contenu
                }else{
                    //Si la date de dernière mise à jour du contenu est postérieure à la dernière consultation du contenu : Notif updated
                    $consulted['contenus'][ $one->getContenu()->getId() ] = $one->getContenu()->getDateModification() > $one->getDateLastConsulted();
                }
            }

            //Parcours des objets retournés par la recherche
            foreach($objets as &$objet)
            {
                if( $objet['categ'] != 'forum' ){
                    $id          = $objet['id'];
                    $isConsulted = false;
                    $type        = is_null($objet['objet']) ? 'objets' : 'contenus';
        
                    //la publication fait partie des publications déjà consultées par l'utilisateur
                    if( isset( $consulted[$type][ $id ] ) ){
                        $isConsulted      = true;
                        $objet['updated'] = $consulted[$type][ $id ];
                    }

                    //Si la publication n'a jamais été consulté ET
                    //Si la date de création de l'objet est postérieure à la date d'inscription de l'utilisateur : Notif new
                    if( $isConsulted === false && ($objet['created'] > $dateInscription) )
                        $objet['new'] = true;
                }
            }
        }
        
        return $objets;
    }

    /**
     * Met à jour le tableau de productions avec les prod consultées par l'user connecté
     *
     * @param array $objets Liste des productions concernés
     *
     * @return array
     */
    public function updateProductionsWithConnectedUser( $productions, $user )
    {
        if( $user != "anon.") {
            //get date Inscription user
            $dateInscription = $user->getDateInscription();

            //get consulted objets and formate them
            $results   = $this->getLastsConsultations( $user );
            $consulted = array('objets' => array(), 'contenus' => array() );
            foreach($results as $one){
                //Cas objet
                if( is_null($one->getContenu()) ) {
                    //Si la date de dernière mise à jour de l'objet est postérieure à la dernière consultation de l'objet : Notif updated
                    $consulted['objets'][ $one->getObjet()->getId() ] = $one->getObjet()->getDateModification() > $one->getDateLastConsulted();
                //Cas contenu
                }else{
                    //Si la date de dernière mise à jour du contenu est postérieure à la dernière consultation du contenu : Notif updated
                    $consulted['contenus'][ $one->getContenu()->getId() ] = $one->getContenu()->getDateModification() > $one->getDateLastConsulted();
                }
            }

            //Parcours des objets retournés par la recherche
            foreach($productions as &$production)
            {
                $id          = $production->id;
                $isConsulted = false;
                $type        = $production->objet ? 'objets' : 'contenus';
                
                //la publication fait partie des publications déjà consultées par l'utilisateur
                if( isset( $consulted[$type][ $id ] ) ){
                    $isConsulted         = true;
                    $production->updated = $consulted[$type][ $id ];
                }

                //Si la publication n'a jamais été consulté ET
                //Si la date de création de l'objet est postérieure à la date d'inscription de l'utilisateur : Notif new
                if( $isConsulted === false && ($production->created > $dateInscription) )
                    $production->new = true;
            }
        }
        
        return $productions;
    }
}