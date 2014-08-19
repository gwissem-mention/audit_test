<?php

namespace HopitalNumerique\ObjetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MassCommentaireController extends Controller
{
    /**
     * Export CSV de la liste des sessions 
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     */
    public function exportCsvAction( $primaryKeys, $allPrimaryKeys )
    {
        //get all selected inscription
        if($allPrimaryKeys == 1){
            $rawDatas = $this->get('hopitalnumerique_objet.grid.commentaire')->getRawData();
            foreach($rawDatas as $data)
                $primaryKeys[] = $data['id'];
        }
        $inscriptions = $this->get('hopitalnumerique_objet.manager.commentaire')->findBy( array('id' => $primaryKeys) );

        $colonnes = array( 
                            'id'                        => 'id', 
                            'user.nom'                  => 'Nom', 
                            'user.prenom'               => 'Prénom', 
                            'user.username'             => 'Identifiant (login)', 
                            'user.email'                => 'Adresse e-mail',
                            'texte'                     => 'Commentaire',
                            'dateCreationString'        => 'Date du création commentaire',
                            'objet.titre'               => 'Titre de l\'objet concerné',
                            'contenu.titre'             => 'Titre de l\'infradoc concerné',
                            'publier'                   => 'Publier'
                        );

        $kernelCharset = $this->container->getParameter('kernel.charset');

        return $this->get('hopitalnumerique_objet.manager.commentaire')->exportCsv( $colonnes, $inscriptions, 'export-commentaire-objet.csv', $kernelCharset );
    }
    
    /**
     * Action de masse sur la publication : publier
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Redirect
     */
    public function publierMassAction( $primaryKeys, $allPrimaryKeys )
    {
        //get all selected inscription
        if($allPrimaryKeys == 1){
            $rawDatas = $this->get('hopitalnumerique_objet.grid.commentaire')->getRawData();
            foreach($rawDatas as $data)
                $primaryKeys[] = $data['id'];
        }
        $commentaires = $this->get('hopitalnumerique_objet.manager.commentaire')->findBy( array('id' => $primaryKeys) );
        
        $this->get('hopitalnumerique_objet.manager.commentaire')->publierEtatCommentaire( $commentaires );
        $this->get('session')->getFlashBag()->add('info', 'Commentaire(s) publié(s).' );
    
        return $this->redirect( $this->generateUrl('hopitalnumerique_objet_admin_commentaire') );
    }
    
    /**
     * Action de masse sur la publication : dépublier
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Redirect
     */
    public function depublierMassAction( $primaryKeys, $allPrimaryKeys )
    {
        //get all selected inscription
        if($allPrimaryKeys == 1){
            $rawDatas = $this->get('hopitalnumerique_objet.grid.commentaire')->getRawData();
            foreach($rawDatas as $data)
                $primaryKeys[] = $data['id'];
        }
        $commentaires = $this->get('hopitalnumerique_objet.manager.commentaire')->findBy( array('id' => $primaryKeys) );
        
        $this->get('hopitalnumerique_objet.manager.commentaire')->depublierEtatCommentaire( $commentaires );
        $this->get('session')->getFlashBag()->add('info', 'Commentaire(s) dépublié(s).' );
    
        return $this->redirect( $this->generateUrl('hopitalnumerique_objet_admin_commentaire') );
    }
    
    /**
     * Action de masse sur la publication : publier
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Redirect
     */
    public function deleteMassAction( $primaryKeys, $allPrimaryKeys )
    {
        //get all selected inscription
        if($allPrimaryKeys == 1){
            $rawDatas = $this->get('hopitalnumerique_objet.grid.commentaire')->getRawData();
            foreach($rawDatas as $data)
                $primaryKeys[] = $data['id'];
        }
        $commentaires = $this->get('hopitalnumerique_objet.manager.commentaire')->findBy( array('id' => $primaryKeys) );
        
        $this->get('hopitalnumerique_objet.manager.commentaire')->delete( $commentaires );
        $this->get('session')->getFlashBag()->add('info', 'Commentaire(s) supprimé(s).' );
    
        return $this->redirect( $this->generateUrl('hopitalnumerique_objet_admin_commentaire') );
    }

}
