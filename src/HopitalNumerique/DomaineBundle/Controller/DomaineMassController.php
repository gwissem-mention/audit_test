<?php

namespace HopitalNumerique\DomaineBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DomaineMassController extends Controller
{
    /**
     * Suppression de masse des domaines
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Redirect
     */
    public function deleteMassAction( $primaryKeys, $allPrimaryKeys )
    {
        //get all selected Users
        if($allPrimaryKeys == 1){
            $rawDatas = $this->get('hopitalnumerique_domaine.manager.domaine')->getRawData();
            foreach($rawDatas as $data)
            {
                $primaryKeys[] = $data['id'];
            }
        }   

        $domaines = $this->get('hopitalnumerique_domaine.manager.domaine')->findBy( array('id' => $primaryKeys) );

        $this->get('hopitalnumerique_domaine.manager.domaine')->delete( $domaines );

        $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.' );

        return $this->redirect( $this->generateUrl('hopitalnumerique_domaine_admin_domaine') );
    }

}
