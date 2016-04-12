<?php

namespace HopitalNumerique\RechercheParcoursBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * RechercheParcoursGestion controller.
 */
class RechercheParcoursGestionController extends Controller
{
    /**
     * Affiche la liste des RechercheParcoursGestion.
     */
    public function indexAction()
    {
        $grid = $this->get('hopitalnumerique_rechercheparcours.grid.rechercheparcoursgestion');

        return $grid->render('HopitalNumeriqueRechercheParcoursBundle:RechercheParcoursGestion:index.html.twig');
    }

    /**
     * Affiche le formulaire d'ajout de RechercheParcoursGestion.
     */
    public function addAction()
    {
        $rechercheparcoursgestion = $this->get('hopitalnumerique_rechercheparcours.manager.rechercheparcoursgestion')->createEmpty();

        return $this->renderForm($rechercheparcoursgestion, 'HopitalNumeriqueRechercheParcoursBundle:RechercheParcoursGestion:edit.html.twig' );
    }

    /**
     * Affiche le formulaire d'édition de RechercheParcoursGestion.
     *
     * @param integer $id Id de RechercheParcoursGestion.
     */
    public function editAction( $id )
    {
        //Récupération de l'entité passée en paramètre
        $rechercheparcoursgestion = $this->get('hopitalnumerique_rechercheparcours.manager.rechercheparcoursgestion')->findOneBy( array('id' => $id) );

        return $this->renderForm($rechercheparcoursgestion, 'HopitalNumeriqueRechercheParcoursBundle:RechercheParcoursGestion:edit.html.twig' );
    }

    /**
     * Affiche le RechercheParcoursGestion en fonction de son ID passé en paramètre.
     * 
     * @param integer $id Id de RechercheParcoursGestion.
     */
    public function showAction( $id )
    {
        //Récupération de l'entité en fonction du paramètre
        $rechercheparcoursgestion = $this->get('hopitalnumerique_rechercheparcours.manager.rechercheparcoursgestion')->findOneBy( array( 'id' => $id) );

        return $this->render('HopitalNumeriqueRechercheParcoursBundle:RechercheParcoursGestion:show.html.twig', array(
            'rechercheparcoursgestion' => $rechercheparcoursgestion,
        ));
    }

    /**
     * Suppresion d'un RechercheParcoursGestion.
     * 
     * @param integer $id Id de RechercheParcoursGestion.
     * METHOD = POST|DELETE
     */
    public function deleteAction( $id )
    {
        $rechercheparcoursgestion = $this->get('hopitalnumerique_rechercheparcours.manager.rechercheparcoursgestion')->findOneBy( array( 'id' => $id) );

        //Suppression de l'entitée
        $this->get('hopitalnumerique_rechercheparcours.manager.rechercheparcoursgestion')->delete( $rechercheparcoursgestion );

        $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.' );

        return new Response('{"success":true, "url" : "'.$this->generateUrl('hopitalnumerique_rechercheparcours_admin_recherche-par-parcours_gestion').'"}', 200);
    }

    /**
     * Actuib de masse de suppression
     *
     * @param [type] $primaryKeys    [description]
     * @param [type] $allPrimaryKeys [description]
     *
     * @return [type]
     */
    public function deleteMassAction( $primaryKeys, $allPrimaryKeys )
    {
        //get all selected Users
        if($allPrimaryKeys == 1){
            $rawDatas = $this->get('hopitalnumerique_rechercheparcours.manager.rechercheparcoursgestion')->getRawData();
            foreach($rawDatas as $data)
            {
                $primaryKeys[] = $data['id'];
            }
        }   

        $rechercheParcoursGestion = $this->get('hopitalnumerique_rechercheparcours.manager.rechercheparcoursgestion')->findBy( array('id' => $primaryKeys) );

        $this->get('hopitalnumerique_rechercheparcours.manager.rechercheparcoursgestion')->delete( $rechercheParcoursGestion );

        $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.' );

        return $this->redirect( $this->generateUrl('hopitalnumerique_rechercheparcours_admin_recherche-par-parcours_gestion') );
    }





    /**
     * Effectue le render du formulaire RechercheParcoursGestion.
     *
     * @param RechercheParcoursGestion   $entity   Entité $rechercheparcoursgestion
     * @param string $view     Chemin de la vue ou sera rendu le formulaire
     *
     * @return Form | redirect
     */
    private function renderForm($rechercheparcoursgestion, $view )
    {
        $isCreation = (null === $rechercheparcoursgestion->getId());
        $form = $this->createForm('hopitalnumerique_rechercheparcours_rechercheparcoursgestion', $rechercheparcoursgestion);

        $request = $this->get('request');
        
        // Si l'utilisateur soumet le formulaire
        if ('POST' == $request->getMethod()) {
            // On bind les données du form
            $form->handleRequest($request);

            //si le formulaire est valide
            if ($form->isValid()) {
                if (!$isCreation) {
                    $referencesParentes    = $form->get('referencesParentes')->getData();
                    $referencesVentilation = $form->get('referencesVentilations')->getData();

                    //Vérif à la mano php pour select2
                    if(count($referencesParentes) === 0 || count($referencesVentilation) === 0)
                    {
                        $this->get('session')->getFlashBag()->add( 'danger', 'Les références sont obligatoires, veuillez remplir ces champs avant de sauvegarder à nouveau.' ); 

                        return $this->render( $view , array(
                            'form'             => $form->createView(),
                            'rechercheparcoursgestion' => $rechercheparcoursgestion
                        ));
                    }
                }

                //On utilise notre Manager pour gérer la sauvegarde de l'objet
                $this->get('hopitalnumerique_rechercheparcours.manager.rechercheparcoursgestion')->save($rechercheparcoursgestion);

                $rechercheParcoursFilsNew = array();
                $rechercheParcoursFils    = $this->get('hopitalnumerique_recherche_parcours.manager.recherche_parcours')->getRechercheParcoursFils($rechercheparcoursgestion);
                
                foreach ($rechercheparcoursgestion->getReferencesParentes() as $refParente) 
                {
                    if(!array_key_exists($refParente->getId(), $rechercheParcoursFils))
                    {
                        $rechercheParcours = $this->get('hopitalnumerique_recherche_parcours.manager.recherche_parcours')->createEmpty();
                        $rechercheParcours->setReference($refParente);
                        $rechercheParcours->setRecherchesParcoursGestion($rechercheparcoursgestion);
                        $rechercheParcours->setOrder(count($rechercheParcoursFilsNew) + 1);

                        $rechercheParcoursFilsNew[] = $rechercheParcours; 
                    }
                }

                $this->get('hopitalnumerique_recherche_parcours.manager.recherche_parcours')->save($rechercheParcoursFilsNew);
                
                // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
                $this->get('session')->getFlashBag()->add( ($isCreation ? 'success' : 'info') , 'Gestionnaire de recherche par parcours ' . ($isCreation ? 'ajouté.' : 'mis à jour.') ); 
                
                //on redirige vers la page index ou la page edit selon le bouton utilisé
                $do = $request->request->get('do');
                return $this->redirect( ($do == 'save-close' ? $this->generateUrl('hopitalnumerique_rechercheparcours_admin_recherche-par-parcours_gestion') : $this->generateUrl('hopitalnumerique_rechercheparcours_admin_recherche-par-parcours_gestion_edit', array( 'id' => $rechercheparcoursgestion->getId() ) ) ) );
            }
        }

        return $this->render( $view , array(
            'form'             => $form->createView(),
            'rechercheparcoursgestion' => $rechercheparcoursgestion
        ));
    }
}