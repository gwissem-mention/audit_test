<?php
namespace HopitalNumerique\ObjetBundle\Manager;

use Nodevo\AdminBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Consultation.
 */
class ConsultationManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\ObjetBundle\Entity\Consultation';
    protected $_securityContext;

    public function __construct($em, $securityContext)
    {
        parent::__construct($em);
        $this->_securityContext = $securityContext;
    }

    /**
     * On met l'objet en consulté (création si first visite, ou update de la date)
     *
     * @param Objet $objet La publication visitée
     *
     * @return empty
     */
    public function consulted($objet)
    {
        $user = $this->_securityContext->getToken()->getUser();

        if( $user != "anon.") {
            $consultation = $this->findOneBy( array('objet'=>$objet, 'user'=>$user) );

            //new
            if( is_null($consultation) ){
                $consultation = $this->createEmpty();
                $consultation->setobjet( $objet );
                $consultation->setUser( $user );
            //update
            }else
                $consultation->setDateLastConsulted( new \DateTime() );
            
            $this->save( $consultation );
        }
    }
}