<?php
namespace HopitalNumerique\ObjetBundle\Manager;

use Nodevo\AdminBundle\Manager\Manager as BaseManager;
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
            $consultation = $isContenu ? $this->findOneBy( array('contenu'=>$objet, 'user'=>$user) ) : $this->findOneBy( array('objet'=>$objet, 'user'=>$user) );

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
}