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
     * Affiche le formulaire d'édition de Glossaire.
     *
     * @param integer $id Id de Glossaire.
     */
    public function editAction( $id )
    {
        //Récupération de l'entité passée en paramètre
        $glossaire = $this->get('hopitalnumerique_glossaire.manager.glossaire')->findOneBy( array('id' => $id) );

        return $this->renderForm('hopitalnumerique_glossaire_glossaire', $glossaire, 'HopitalNumeriqueGlossaireBundle:Glossaire:edit.html.twig' );
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











    /**
     * Effectue le render du formulaire Glossaire.
     *
     * @param string $formName Nom du service associé au formulaire
     * @param Glossaire   $entity   Entité $glossaire
     * @param string $view     Chemin de la vue ou sera rendu le formulaire
     *
     * @return Form | redirect
     */
    private function renderForm( $formName, $glossaire, $view )
    {
        //Création du formulaire via le service
        $form = $this->createForm( $formName, $glossaire);

        $request = $this->get('request');
        
        // Si l'utilisateur soumet le formulaire
        if ('POST' == $request->getMethod()) {
            
            // On bind les données du form
            $form->handleRequest($request);

            //si le formulaire est valide
            if ($form->isValid()) {
                //test ajout ou edition
                $new = is_null($glossaire->getId());

                //On utilise notre Manager pour gérer la sauvegarde de l'objet
                $this->get('hopitalnumerique_glossaire.manager.glossaire')->save($glossaire);
                
                // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
                $this->get('session')->getFlashBag()->add( ($new ? 'success' : 'info') , 'Glossaire ' . ($new ? 'ajouté.' : 'mis à jour.') ); 
                
                //on redirige vers la page index ou la page edit selon le bouton utilisé
                $do = $request->request->get('do');
                return $this->redirect( ($do == 'save-close' ? $this->generateUrl('hopitalnumerique_glossaire_glossaire') : $this->generateUrl('hopitalnumerique_glossaire_glossaire_edit', array( 'id' => $glossaire->getId() ) ) ) );
            }
        }

        return $this->render( $view , array(
            'form'      => $form->createView(),
            'glossaire' => $glossaire
        ));
    }
}