<?php

namespace HopitalNumerique\ObjetBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use \Nodevo\ToolsBundle\Tools\Chaine;

/**
 * Objet controller.
 */
class ObjetController extends Controller
{
    /**
     * Affiche la liste des Objet.
     */
    public function indexAction()
    {
        $grid = $this->get('hopitalnumerique_objet.grid.objet');

        return $grid->render('HopitalNumeriqueObjetBundle:Objet:index.html.twig');
    }

    /**
     * Action Annuler, on dévérouille l'objet et on redirige vers l'index
     */
    public function cancelAction( $id, $message )
    {
        $objet = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy( array('id' => $id) );

        //On récupère l'user connecté et son role
        $user  = $this->get('security.context')->getToken()->getUser();

        
        //si l'user connecté est propriétaire de l'objet ou si l'user est admin : unlock autorisé
        if( $user->hasRole('ROLE_ADMINISTRATEUR_1') || $objet->getLockedBy() == $user ) {
            $this->get('hopitalnumerique_objet.manager.objet')->unlock($objet);

            //si on à appellé l'action depuis le button du grid, on met un message à l'user, sinon pas besoin de message
            if( !is_null($message ) )
                $this->get('session')->getFlashBag()->add( 'info' , 'Objet dévérouillé.' );
        }else
            $this->get('session')->getFlashBag()->add( 'danger' , 'Vous n\'avez pas l\'autorisation de dévérouiller cet objet.' );
        
        return $this->redirect( $this->generateUrl('hopitalnumerique_objet_objet') );
    }

    /**
     * Affiche le formulaire d'ajout de Objet.
     */
    public function addAction()
    {
        $objet = $this->get('hopitalnumerique_objet.manager.objet')->createEmpty();

        return $this->_renderForm('hopitalnumerique_objet_objet', $objet, 'HopitalNumeriqueObjetBundle:Objet:edit.html.twig' );
    }

    /**
     * Affiche le formulaire d'édition de Objet.
     */
    public function editAction( $id, $infra )
    {   
        //Récupération de l'entité passée en paramètre
        $objet = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy( array('id' => $id) );
        $user  = $this->get('security.context')->getToken()->getUser();

        // l'objet est locked, on redirige vers la home page
        if( $objet->getLock() && $objet->getLockedBy() && $objet->getLockedBy() != $user ){
            $this->get('session')->getFlashBag()->add( 'warning' , 'Cet objet est en cours d\'édition par '.$objet->getLockedBy()->getEmail().', il n\'est donc pas accessible pour le moment.' ); 
            return $this->redirect($this->generateUrl('hopitalnumerique_objet_objet'));
        }

        $objet = $this->get('hopitalnumerique_objet.manager.objet')->lock($objet, $user);

        //get Contenus
        $contenus = $this->get('hopitalnumerique_objet.manager.contenu')->getArboForObjet( $id );

        return $this->_renderForm('hopitalnumerique_objet_objet', $objet, 'HopitalNumeriqueObjetBundle:Objet:edit.html.twig', $contenus, $infra );
    }

    /**
     * Affiche le Objet en fonction de son ID passé en paramètre.
     */
    public function showAction( $id )
    {
        //Récupération de l'entité en fonction du paramètre
        $objet = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy( array( 'id' => $id) );

        return $this->render('HopitalNumeriqueObjetBundle:Objet:show.html.twig', array(
            'objet' => $objet,
        ));
    }

    /**
     * Suppresion d'un Objet.
     *
     * METHOD = POST|DELETE
     */
    public function deleteAction( $id )
    {
        $objet = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy( array( 'id' => $id) );

        //Suppression de l'entitée
        $this->get('hopitalnumerique_objet.manager.objet')->delete( $objet );

        $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.' );

        return new Response('{"success":true, "url" : "'.$this->generateUrl('hopitalnumerique_objet_objet').'"}', 200);
    }

    /**
     * Vérifie l'unicité du nom du fichier
     *
     * @return Response
     */
    public function isFileExistAction()
    {
        //get uploaded file name and parse it
        $fileName = $this->get('request')->request->get('fileName');
        $fileName = explode('\\',$fileName);

        //seek if the file already exist for objets
        $objet  = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy( array( 'path' => end($fileName) ) );
        $result = is_null( $objet ) ? 'false' : 'true';
        
        //return success.true si le fichier existe deja
        return new Response('{"success":'.$result.'}', 200);
    }

    /**
     * Action appelée dans le plugin "Publication" pour tinymce
     */
    public function getObjetsAction(){
        $objets = $this->get('hopitalnumerique_objet.manager.objet')->findAll();
        $return = array();
        $ids = array();
        foreach( $objets as $one ){
            $ids[] = $one->getId();
        }
        $contenus = $this->get('hopitalnumerique_objet.manager.contenu')->getArboForObjets($ids);
        foreach( $objets as $one ){
            $return[] = array(
                "text" => $one->getTitre(), "value" => "PUBLICATION:" . $one->getId()
            );
            if( !isset($contenus[ $one->getId() ]) || count( $contenus[ $one->getId() ] ) <= 0 ){
                continue;
            }
            foreach( $contenus[ $one->getId() ] as $content ){
                $return[] = array(
                    "text" => "|--" . $content->titre, "value" => "INFRADOC:" . $content->id
                );
                $this->getObjetsChilds($return, $content, 2);
            }
        }
        return $this->render('HopitalNumeriqueObjetBundle:Objet:getObjets.html.twig', array(
            'objet' => $return,
            'texte' => $this->get('request')->request->get('texte')
        ));
    }










    /**
     * Ajoute les enfants de $objet dans $return, formatées en fonction de $level
     * 
     * @param array    $return
     * @param stdClass $objet
     * @param integer  $level
     * 
     * @return void
     */
    private function getObjetsChilds( &$return, $objet, $level = 1 ){
        if( count($objet->childs) > 0 ){
            foreach( $objet->childs as $child ){
                $texte = str_pad($child->titre, strlen($child->titre) + ($level*3), "|--", STR_PAD_LEFT);
                $return[] = array(
                    "text" => $texte, "value" => "INFRADOC:" . $child->id
                );
                $this->getObjetsChilds($return, $child, $level + 1);
            }
        }
    }

    /**
     * Effectue le render du formulaire Objet.
     */
    private function _renderForm( $formName, $objet, $view, $contenus = array(), $infra = false )
    {
        //Création du formulaire via le service
        $form = $this->createForm( $formName, $objet);

        $request = $this->get('request');
        
        // Si l'utilisateur soumet le formulaire
        if ('POST' == $request->getMethod()) {
            
            // On bind les données du form
            $form->handleRequest($request);

            //Vérification de la présence rôle et des types
            $formTypes = $form->get("types")->getData();
            $formType  = !is_null($formTypes) ? $formTypes[0] : null;

            if( is_null($formType) ) {
                $this->get('session')->getFlashBag()->add('danger', 'Veuillez sélectionner un type d\'objet.' );
                return $this->render( $view , array(
                    'form'     => $form->createView(),
                    'objet'    => $objet,
                    'contenus' => $contenus,
                    'infra'    => $infra
                ));
            }

            //si le formulaire est valide
            if ($form->isValid()) {
                //test ajout ou edition
                $new = is_null($objet->getId()) ? true : false;

                //si l'alias est vide, on le génère depuis le titre
                $tool = new Chaine( ( $objet->getAlias() == '' ? $objet->getTitre() : $objet->getAlias() ) );
                $objet->setAlias( $tool->minifie() );

                //Test if alias already exist
                if( $this->get('hopitalnumerique_objet.manager.objet')->testAliasExist( $objet, $new ) ){
                    $this->get('session')->getFlashBag()->add('danger', 'Cet Alias existe déjà.' );
                    return $this->render( $view , array(
                        'form'     => $form->createView(),
                        'objet'    => $objet,
                        'contenus' => $contenus,
                        'infra'    => $infra
                    ));
                }

                //Met à jour la date de modification
                $notify = $form->get("modified")->getData();
                if( $notify === "1")
                    $objet->setDateModification( new \DateTime() );
                
                //si on à choisis fermer et sauvegarder : on unlock l'user (unlock + save)
                $do = $request->request->get('do');
                $this->get('hopitalnumerique_objet.manager.objet')->unlock($objet);
                
                // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
                if( $do == "save-auto" )
                    $this->get('session')->getFlashBag()->add( 'info' , 'Objet sauvegardé automatiquement.' ); 
                else
                    $this->get('session')->getFlashBag()->add( ($new ? 'success' : 'info') , 'Objet ' . ($new ? 'ajouté.' : 'mis à jour.') ); 
                
                // On redirige vers la home page
                return $this->redirect( ($do == 'save-close' ? $this->generateUrl('hopitalnumerique_objet_objet') : $this->generateUrl('hopitalnumerique_objet_objet_edit', array('id'=>$objet->getId())) ) );
            }
        }

        return $this->render( $view , array(
            'form'     => $form->createView(),
            'objet'    => $objet,
            'contenus' => $contenus,
            'infra'    => $infra
        ));
    }
}
