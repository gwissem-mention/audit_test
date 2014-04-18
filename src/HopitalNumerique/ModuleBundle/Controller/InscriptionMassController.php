<?php

namespace HopitalNumerique\ModuleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Inscription des actions de mass controller.
 * 
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class InscriptionMassController extends Controller
{
    /**
     * Action de masse sur l'état d'inscription
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Redirect
     */
    public function accepterInscriptionMassAction( $primaryKeys, $allPrimaryKeys )
    {
        return $this->toggleEtatInscription($primaryKeys, $allPrimaryKeys, 332);
    }
    
    /**
     * Action de masse sur l'état d'inscription
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Redirect
     */
    public function refuserInscriptionMassAction( $primaryKeys, $allPrimaryKeys )
    {
        return $this->toggleEtatInscription($primaryKeys, $allPrimaryKeys, 333);
    }
    
    /**
     * Action de masse sur l'état d'inscription
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Redirect
     */
    public function annulerInscriptionMassAction( $primaryKeys, $allPrimaryKeys )
    {
        return $this->toggleEtatInscription($primaryKeys, $allPrimaryKeys, 334);
    }
    
    
    
    
    
    
    
    
    /**
     * Action de masse sur l'état d'inscription
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     * @param int   $idReference    Id de la référence à mettre à la place
     *
     * @return Redirect
     */
    private function toggleEtatInscription( $primaryKeys, $allPrimaryKeys, $idReference )
    {
        //check connected user ACL
        $user = $this->get('security.context')->getToken()->getUser();
    
        if( $this->get('nodevo_acl.manager.acl')->checkAuthorization( $this->generateUrl('hopital_numerique_user_delete', array('id'=>1)) , $user ) == -1 ){
            $this->get('session')->getFlashBag()->add('warning', 'Vous n\'avez pas les droits suffisants pour activer des utilisateurs.' );
            return $this->redirect( $this->generateUrl('hopitalnumerique_module_module') );
        }
    
        //get all selected Users
        $inscriptions = $this->get('hopitalnumerique_module.manager.inscription')->findBy( array('id' => $primaryKeys) );
    
        //get ref and Toggle State
        $ref = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => $idReference) );
        $this->get('hopitalnumerique_module.manager.inscription')->toogleEtatInscription( $inscriptions, $ref );
    
        //inform user connected
        $this->get('session')->getFlashBag()->add('info', 'Activation effectuée avec succès.' );
        
        return $this->redirect( $this->generateUrl('hopitalnumerique_module_module') );
    }
}