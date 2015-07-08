<?php

namespace HopitalNumerique\ObjetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use \Nodevo\ToolsBundle\Tools\Chaine;
use Doctrine\Common\Cache\ApcCache;

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
        if( $user->hasRole('ROLE_ADMINISTRATEUR_1') || $objet->getLockedBy() == $user ) 
        {
            $this->get('hopitalnumerique_objet.manager.objet')->unlock($objet);

            //si on à appellé l'action depuis le button du grid, on met un message à l'user, sinon pas besoin de message
            if( !is_null($message ) )
            {
                $this->get('session')->getFlashBag()->add( 'info' , 'Objet dévérouillé.' );
            }
        }
        else
        {
            $this->get('session')->getFlashBag()->add( 'danger' , 'Vous n\'avez pas l\'autorisation de déverrouiller cet objet.' );
        }
        
        return $this->redirect( $this->generateUrl('hopitalnumerique_objet_objet') );
    }

    /**
     * Affiche le formulaire d'ajout de Objet.
     */
    public function addAction( $type )
    {
        $objet = $this->get('hopitalnumerique_objet.manager.objet')->createEmpty();

        if( $type == 2 ) 
        {
          $objet->setArticle(true);
        }

        $options = array(
            'toRef' => 0,
            'note'  => 0
        );

        return $this->renderForm('hopitalnumerique_objet_objet', $objet, 'HopitalNumeriqueObjetBundle:Objet:edit.html.twig', $options );
    }

    /**
     * Affiche le formulaire d'édition de Objet.
     */
    public function editAction( $id, $infra, $toRef )
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
        $this->get('hopitalnumerique_objet.manager.contenu')->setRefPonderees( $this->get('hopitalnumerique_reference.manager.reference')->getReferencesPonderees($objet->getDomainesId()) );
        $contenus = $this->get('hopitalnumerique_objet.manager.contenu')->getArboForObjet( $id, $objet->getDomainesId() );

        //get Note referencement
        $refsPonderees = $this->get('hopitalnumerique_reference.manager.reference')->getReferencesPonderees($objet->getDomainesId());
        $note          = $this->get('hopitalnumerique_objet.manager.objet')->getNoteReferencement( $objet->getReferences(), $refsPonderees );

        //build Productions liées
        $productions = $this->get('hopitalnumerique_objet.manager.objet')->formatteProductionsLiees( $objet->getObjets() );

        $options = array(
            'contenus'    => $contenus,
            'infra'       => $infra,
            'toRef'       => $toRef,
            'note'        => $note,
            'productions' => $productions
        );

        return $this->renderForm('hopitalnumerique_objet_objet', $objet, 'HopitalNumeriqueObjetBundle:Objet:edit.html.twig', $options );
    }

    /**
     * Affiche le Objet en fonction de son ID passé en paramètre.
     */
    public function showAction( $id )
    {
        //Récupération de l'entité en fonction du paramètre
        $objet  = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy( array( 'id' => $id) );
        $outils = $this->get('hopitalnumerique_autodiag.manager.outil')->findBy( array( 'id' => $objet->getAutodiags() ));

        //get History
        $em   = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('Gedmo\Loggable\Entity\LogEntry');
        $logs = $repo->getLogEntries($objet);

        return $this->render('HopitalNumeriqueObjetBundle:Objet:show.html.twig', array(
            'objet'  => $objet,
            'outils' => $outils,
            'logs'   => $logs
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
     * Suppression de masse des objets
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Redirect
     */
    public function deleteMassAction( $primaryKeys, $allPrimaryKeys )
    {
        //get all selected Users
        if($allPrimaryKeys == 1)
        {
            $rawDatas = $this->get('hopitalnumerique_objet.manager.objet')->getRawData();
            foreach($rawDatas as $data)
            {
                $primaryKeys[] = $data['id'];
            }
        }      

        $objets = $this->get('hopitalnumerique_objet.manager.objet')->findBy( array('id' => $primaryKeys) );

        try
        {
            //Suppression de l'etablissement
            $this->get('hopitalnumerique_objet.manager.objet')->delete( $objets );
            $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.' );
        } 
        catch (\Exception $e)
        {
            $this->get('session')->getFlashBag()->add('danger', 'Suppression impossible, l\' objet est actuellement lié et ne peut pas être supprimé.');
        }

        return $this->redirect( $this->generateUrl('hopitalnumerique_objet_objet') );
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
    public function getObjetsAction()
    {
        $arbo = $this->get('hopitalnumerique_objet.manager.objet')->getObjetsAndContenuArbo();

        return $this->render('HopitalNumeriqueObjetBundle:Objet:getObjets.html.twig', array(
            'objet' => $arbo,
            'texte' => $this->get('request')->request->get('texte')
        ));
    }

    /**
     * POPIN : liste des publication (utilisé dans le menu item)
     */
    public function getPublicationsAction($articles)
    {
        if( $articles == 1 ){
            $types = $this->get('hopitalnumerique_reference.manager.reference')->findBy(array('code'=>'CATEGORIE_OBJET'));
            $arbo = $this->get('hopitalnumerique_objet.manager.objet')->getObjetsAndContenuArbo( $types );
        }else{
            $types = $this->get('hopitalnumerique_reference.manager.reference')->findBy(array('code'=>'CATEGORIE_ARTICLE'));
            $arbo  = $this->get('hopitalnumerique_objet.manager.objet')->getArticlesArbo( $types );
        }

        return $this->render('HopitalNumeriqueObjetBundle:Objet:getPublications.html.twig', array(
            'objets' => $arbo
        ));
    }

    /**
     * Génère les données requises pour les paramètres de l'url (type publication)
     */
    public function getPublicationDetailsForMenuAction()
    {
        $publication = explode(':', $this->get('request')->request->get('publication') );
        $result      = array('success' => true);

        if( isset($publication[0]) && isset($publication[1]) ) {
            if( $publication[0] === 'PUBLICATION' ) {
                $objet = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy( array('id' => $publication[1]) );

                //set URL to select
                $result['url'] = 'hopital_numerique_publication_publication_objet';

                //set params for URL
                $result['id']    = $objet->getId();
                $result['alias'] = $objet->getAlias();

            } else if( $publication[0] === 'INFRADOC' ) {
                $contenu = $this->get('hopitalnumerique_objet.manager.contenu')->findOneBy( array('id' => $publication[1]) );

                //set URL to select
                $result['url'] = 'hopital_numerique_publication_publication_contenu';

                //set params for URL
                $result['id']     = $contenu->getObjet()->getId();
                $result['alias']  = $contenu->getObjet()->getAlias();
                $result['idc']    = $contenu->getId();
                $result['aliasc'] = $contenu->getAlias();
            }else if( $publication[0] === 'ARTICLE' ) {
                $objet     = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy( array('id' => $publication[1]) );
                $types     = $objet->getTypes();
                $type      = $types[0];
                $categorie = '';

                if( $parent = $type->getParent() )
                    $categorie .= $parent->getLibelle().'-';
                $categorie .= $type->getLibelle();

                //clean categ
                $tool = new Chaine( $categorie );

                //set URL to select
                $result['url'] = 'hopital_numerique_publication_publication_article';

                //set params for URL
                $result['id']        = $objet->getId();
                $result['alias']     = $objet->getAlias();
                $result['categorie'] = $tool->minifie();

            }else
                $result['success'] = false;
        }else
            $result['success'] = false;
        
        return new Response(json_encode($result), 200);
    }

    /**
     * Generate the article feed (RSS)
     *
     * @return Response XML Feed
     */
    public function feedAction()
    {
        $actualites = $this->get('hopitalnumerique_objet.manager.objet')->getObjetsForRSS();

        $feed = $this->get('eko_feed.feed.manager')->get('objet');
        $feed->addFromArray($actualites);

        return new Response($feed->render('rss'));
    }










    /**
     * Effectue le render du formulaire Objet.
     */
    private function renderForm( $formName, $objet, $view, $options = array() )
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

            if( is_null($formTypes) ) {
                $this->get('session')->getFlashBag()->add('danger', 'Veuillez sélectionner un type d\'objet.' );
                return $this->render( $view , array(
                    'form'        => $form->createView(),
                    'objet'       => $objet,
                    'contenus'    => isset($options['contenus'])    ? $options['contenus']    : array(),
                    'infra'       => isset($options['infra'])       ? $options['infra']       : false,
                    'toRef'       => isset($options['toRef'])       ? $options['toRef']       : false,
                    'note'        => isset($options['note'])        ? $options['note']        : 0,
                    'productions' => isset($options['productions']) ? $options['productions'] : array()
                ));
            }

            //si le formulaire est valide
            if ($form->isValid()) {
                //test ajout ou edition
                $new = is_null($objet->getId());

                //si l'alias est vide, on le génère depuis le titre
                $tool = new Chaine( ( $objet->getAlias() == '' ? $objet->getTitre() : $objet->getAlias() ) );
                $objet->setAlias( $tool->minifie() );

                //Test if alias already exist
                if( $this->get('hopitalnumerique_objet.manager.objet')->testAliasExist( $objet, $new ) ){
                    $this->get('session')->getFlashBag()->add('danger', 'Cet Alias existe déjà.' );
                    return $this->render( $view , array(
                        'form'        => $form->createView(),
                        'objet'       => $objet,
                        'contenus'    => isset($options['contenus'])    ? $options['contenus']    : array(),
                        'infra'       => isset($options['infra'])       ? $options['infra']       : false,
                        'toRef'       => isset($options['toRef'])       ? $options['toRef']       : false,
                        'note'        => isset($options['note'])        ? $options['note']        : 0,
                        'productions' => isset($options['productions']) ? $options['productions'] : array()
                    ));
                }

                //Object security isArticle = false
                if( is_null($objet->isArticle()) )
                {
                    $objet->setArticle( false );
                }
                else
                {
                    $domaines = $this->get('hopitalnumerique_domaine.manager.domaine')->findBy(array('id' =>1 ));
                    $objet->setDomaines($domaines);
                }
                
                //Met à jour la date de modification
                $notify = $form->get("modified")->getData();
                if( $notify === "1")
                    $objet->setDateModification( new \DateTime() );
                
                //si on à choisis fermer et sauvegarder : on unlock l'user (unlock + save)
                $do = $request->request->get('do');
                $this->get('hopitalnumerique_objet.manager.objet')->unlock($objet);

                //Destruction du cache APC concernant l'objet
                $cacheDriver = new ApcCache();
                $cacheName = "_publication_objet_" . $objet->getId();
                $cacheDriver->delete($cacheName);
                
                //reload glossaire stuff
                $this->get('hopitalnumerique_glossaire.manager.glossaire')->parsePublications( array($objet) );
                $this->getDoctrine()->getManager()->flush();

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
            'form'        => $form->createView(),
            'objet'       => $objet,
            'contenus'    => isset($options['contenus'])    ? $options['contenus']    : array(),
            'infra'       => isset($options['infra'])       ? $options['infra']       : false,
            'toRef'       => isset($options['toRef'])       ? $options['toRef']       : false,
            'note'        => isset($options['note'])        ? $options['note']        : 0,
            'productions' => isset($options['productions']) ? $options['productions'] : array()
        ));
    }
}
