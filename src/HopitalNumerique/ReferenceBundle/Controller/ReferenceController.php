<?php

namespace HopitalNumerique\ReferenceBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Reference controller.
 */
class ReferenceController extends Controller
{
    /**
     * Affiche la liste des Reference.
     */
    public function indexAction()
    {
        $grid = $this->get('hopitalnumerique_reference.grid.reference');

        return $grid->render('HopitalNumeriqueReferenceBundle:Reference:index.html.twig');
    }

    /**
     * Affichage en arborescence
     */
    public function sitemapAction()
    {
        $references = $this->get('hopitalnumerique_reference.manager.reference')->getArboFormat();

        return $this->render('HopitalNumeriqueReferenceBundle:Reference:sitemap.html.twig', array(
            'references' => $references
        )); 
    }

    /**
     * Affiche le formulaire d'ajout de Reference.
     */
    public function addAction( $id = null, $mod = null )
    {
        $reference = $this->get('hopitalnumerique_reference.manager.reference')->createEmpty();

        if( !is_null($id)){
            $referenceBase = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => $id) );

            if (!is_null($mod)){
                $reference->setCode( $referenceBase->getCode() );
                if( $referenceBase->getParent() ){
                    $reference->setParent( $referenceBase->getParent() );
                }
            } else {
                if ( $referenceBase->getLock() ){
                    $this->get('session')->getFlashBag()->add('warning', 'Attention, l\'élément que vous avez choisi est verrouillé, il ne peut donc pas être sélectionné comme Item parent.' );
                } else {
                    $reference->setParent( $referenceBase );
                }
            }
        }

        return $this->_renderForm('hopitalnumerique_reference_reference', $reference, 'HopitalNumeriqueReferenceBundle:Reference:edit.html.twig' );
    }

    /**
     * Affiche le formulaire d'édition de Reference.
     */
    public function editAction( $id )
    {
        //Récupération de l'entité passée en paramètre
        $reference = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array('id' => $id) );

        return $this->_renderForm('hopitalnumerique_reference_reference', $reference, 'HopitalNumeriqueReferenceBundle:Reference:edit.html.twig' );
    }

    /**
     * Affiche le Reference en fonction de son ID passé en paramètre.
     */
    public function showAction( $id )
    {
        //Récupération de l'entité en fonction du paramètre
        $reference = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => $id) );

        return $this->render('HopitalNumeriqueReferenceBundle:Reference:show.html.twig', array(
            'reference' => $reference,
        ));
    }

    /**
     * Suppression d'un Reference.
     *
     * METHOD = POST|DELETE
     */
    public function deleteAction( $id )
    {
        $reference = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => $id) );
        
        if( $reference->getLock() )
            $this->get('session')->getFlashBag()->add('danger', 'Suppression impossible, la référence est verrouillée.');
        else{
            //Tentative de suppression si la référence est liée nulle part
            try
            {
                //Suppression de l'entité
                $this->get('hopitalnumerique_reference.manager.reference')->delete( $reference );
                $this->get('hopitalnumerique_reference.manager.reference')->refreshOrder();
                $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.' );
            }
            catch ( \Exception $e) 
            {
                $this->get('session')->getFlashBag()->add('danger', 'Suppression impossible, la référence est actuellement liée et ne peut être supprimée.');
            }
        }

        return new Response('{"success":true, "url" : "'.$this->generateUrl('hopitalnumerique_reference_reference').'"}', 200);
    }










    /**
     * Effectue le render du formulaire Reference.
     *
     * @param string    $formName Nom du service associé au formulaire
     * @param Reference $item     Entité Référence
     * @param string    $view     Chemin de la vue ou sera rendu le formulaire
     *
     * @return Form | redirect
     */
    private function _renderForm( $formName, $reference, $view )
    {
        //Création du formulaire via le service
        $form = $this->createForm( $formName, $reference);

        $request = $this->get('request');
        
        // Si l'utilisateur soumet le formulaire
        if ('POST' == $request->getMethod()) {
            //get uploaded form datas (used to manipulate parent next)
            $formDatas = $request->request->get('hopitalnumerique_reference_reference');

            // On bind les données du form
            $form->handleRequest($request);

            //si le formulaire est valide
            if ( $form->isValid() ) {

                $oldParent = $reference->getParent();

                if( isset($formDatas['parent']) && !is_null($formDatas['parent']) ){
                    $parent = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => $formDatas['parent'] ) );    
                    $reference->setParent( $parent );
                }
                
                //test ajout ou edition
                $new = is_null($reference->getId()) ? true : false;

                //On utilise notre Manager pour gérer la sauvegarde de l'objet
                // $this->get('hopitalnumerique_reference.manager.reference')->updateOrder($reference);
                $this->get('hopitalnumerique_reference.manager.reference')->save($reference);
                $this->get('hopitalnumerique_reference.manager.reference')->refreshOrder($oldParent);
                $this->get('hopitalnumerique_reference.manager.reference')->refreshOrder($reference->getParent());
                
                // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
                $this->get('session')->getFlashBag()->add( ($new ? 'success' : 'info') , 'Reference ' . ($new ? 'ajoutée.' : 'mise à jour.') ); 
                
                $do = $request->request->get('do');
                return $this->redirect( ($do == 'save-close' ? $this->generateUrl('hopitalnumerique_reference_reference') : $this->generateUrl('hopitalnumerique_reference_reference_edit', array( 'id' => $reference->getId() ) ) ) );
            }
        }

        return $this->render( $view , array(
            'form'      => $form->createView(),
            'reference' => $reference
        ));
    }
}