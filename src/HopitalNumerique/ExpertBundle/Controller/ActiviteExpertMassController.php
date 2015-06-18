<?php

namespace HopitalNumerique\ExpertBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ActiviteExpertMassController extends Controller
{
    /**
     * Suppression de masse des activités experts
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Redirect
     */
    public function deleteMassAction( $primaryKeys, $allPrimaryKeys )
    {
        if($allPrimaryKeys == 1){
            $rawDatas = $this->get('hopitalnumerique_expert.grid.activiteexpert')->getRawData();
            foreach($rawDatas as $data)
                $primaryKeys[] = $data['id'];
        }

        //get all selected activiteExperts
        $activiteExperts = $this->get('hopitalnumerique_expert.manager.activiteexpert')->findBy( array('id' => $primaryKeys) );
        $this->get('hopitalnumerique_expert.manager.activiteexpert')->delete( $activiteExperts );

        //inform user connected
        $this->get('session')->getFlashBag()->add('info', 'Supression effectuée avec succès.' );

        return $this->redirect( $this->generateUrl('hopitalnumerique_expert_expert_activite') );
    }
}
