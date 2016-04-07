<?php

namespace HopitalNumerique\GlossaireBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Glossaire controller.
 */
class GlossaireController extends Controller
{
    /**
     * Affiche la liste des Glossaire.
     */
    public function indexAction()
    {
        $grid = $this->get('hopitalnumerique_glossaire.grid.glossaire');

        return $grid->render('HopitalNumeriqueGlossaireBundle:Glossaire:index.html.twig');
    }

    /**
     * Suppresion d'un Glossaire.
     * 
     * @param integer $id Id de Glossaire.
     * METHOD = POST|DELETE
     */
    public function deleteAction( $id )
    {
        $glossaire = $this->get('hopitalnumerique_glossaire.manager.glossaire')->findOneBy( array( 'id' => $id) );

        //Suppression de l'entitée
        $this->get('hopitalnumerique_glossaire.manager.glossaire')->delete( $glossaire );

        $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.' );

        return new Response('{"success":true, "url" : "'.$this->generateUrl('hopitalnumerique_glossaire_glossaire').'"}', 200);
    }

    /**
     * Suppression de masse des glossaires
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
            $rawDatas = $this->get('hopitalnumerique_glossaire.manager.glossaire')->getRawData();
            foreach($rawDatas as $data)
            {
                $primaryKeys[] = $data['id'];
            }
        }        

        $glossaires = $this->get('hopitalnumerique_glossaire.manager.glossaire')->findBy( array('id' => $primaryKeys) );

        $this->get('hopitalnumerique_glossaire.manager.glossaire')->delete( $glossaires );

        $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.' );

        return $this->redirect( $this->generateUrl('hopitalnumerique_glossaire_glossaire') );
    }

    /**
     * Export CSV de la liste des lignes du glossaire sélectionnés
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     */
    public function exportCsvAction( $primaryKeys, $allPrimaryKeys )
    {
        //get all selected Rows
        if($allPrimaryKeys == 1){
            $rawDatas = $this->get('hopitalnumerique_glossaire.grid.glossaire')->getRawData();
            foreach($rawDatas as $data)
                $primaryKeys[] = $data['id'];
        }
        $datas = $this->get('hopitalnumerique_glossaire.manager.glossaire')->getDatasForExport( $primaryKeys );

        $colonnes = array( 
                            'id'           => 'id', 
                            'mot'          => 'Mot',
                            'intitule'     => 'Intitulé',
                            'domaineNom'   => 'Domaine(s) associé(s)',
                            'description'  => 'Description',
                            'etatLibelle'  => 'Etat'
                        );

        $kernelCharset = $this->container->getParameter('kernel.charset');

        return $this->get('hopitalnumerique_glossaire.manager.glossaire')->exportCsv( $colonnes, $datas, 'export-glossaire.csv', $kernelCharset );
    }

    /**
     * Parse les publication à la recherche de mots du glossaire
     *
     * @return redirect
     */
    public function parsePublicationsAction()
    {
        $objets   = $this->get('hopitalnumerique_objet.manager.objet')->findAll();
        $contenus = $this->get('hopitalnumerique_objet.manager.contenu')->findAll();
        $this->get('hopitalnumerique_glossaire.manager.glossaire')->parsePublications( $objets, $contenus );

        //save changes
        $this->getDoctrine()->getManager()->flush();

        $this->get('session')->getFlashBag()->add('info', 'Publications parsées avec succès.' );

        return $this->redirect( $this->generateUrl('hopitalnumerique_glossaire_glossaire') );
    }
}