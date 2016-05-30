<?php

namespace HopitalNumerique\DomaineBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Domaine controller.
 */
class DomaineController extends Controller
{
    /**
     * Affiche la liste des Domaine.
     */
    public function indexAction()
    {
        $grid = $this->get('hopitalnumerique_domaine.grid.domaine');

        return $grid->render('HopitalNumeriqueDomaineBundle:Domaine:index.html.twig');
    }

    /**
     * Affiche le formulaire d'ajout de Domaine.
     */
    public function addAction()
    {
        $domaine = $this->get('hopitalnumerique_domaine.manager.domaine')->createEmpty();

        return $this->renderForm('hopitalnumerique_domaine_domaine', $domaine, 'HopitalNumeriqueDomaineBundle:Domaine:edit.html.twig' );
    }

    /**
     * Affiche le formulaire d'édition de Domaine.
     *
     * @param integer $id Id de Domaine.
     */
    public function editAction( $id )
    {
        //Récupération de l'entité passée en paramètre
        $domaine = $this->get('hopitalnumerique_domaine.manager.domaine')->findOneBy( array('id' => $id) );

        $referenceTreeOptions = $this->container->get('hopitalnumerique_reference.dependency_injection.reference.tree')->getOptions([$domaine]);

        return $this->renderForm('hopitalnumerique_domaine_domaine', $domaine, 'HopitalNumeriqueDomaineBundle:Domaine:edit.html.twig', [
            'referenceTreeOptions' => json_encode($referenceTreeOptions)
        ]);
    }

    /**
     * Affiche le Domaine en fonction de son ID passé en paramètre.
     * 
     * @param integer $id Id de Domaine.
     */
    public function showAction( $id )
    {
        //Récupération de l'entité en fonction du paramètre
        $domaine = $this->get('hopitalnumerique_domaine.manager.domaine')->findOneBy( array( 'id' => $id) );

        return $this->render('HopitalNumeriqueDomaineBundle:Domaine:show.html.twig', array(
            'domaine' => $domaine,
        ));
    }

    /**
     * Suppresion d'un Domaine.
     * 
     * @param integer $id Id de Domaine.
     * METHOD = POST|DELETE
     */
    public function deleteAction( $id )
    {
        $domaine = $this->get('hopitalnumerique_domaine.manager.domaine')->findOneBy( array( 'id' => $id) );

        //Suppression de l'entitée
        $this->get('hopitalnumerique_domaine.manager.domaine')->delete( $domaine );

        $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.' );

        return new Response('{"success":true, "url" : "'.$this->generateUrl('hopitalnumerique_domaine_admin_domaine').'"}', 200);
    }





    /**
     * Effectue le render du formulaire Domaine.
     *
     * @param string $formName Nom du service associé au formulaire
     * @param Domaine   $entity   Entité $domaine
     * @param string $view     Chemin de la vue ou sera rendu le formulaire
     *
     * @return Form | redirect
     */
    private function renderForm( $formName, $domaine, $view, $options = [])
    {
        //Création du formulaire via le service
        $form = $this->createForm( $formName, $domaine);

        $request = $this->get('request');
        
        // Si l'utilisateur soumet le formulaire
        if ('POST' == $request->getMethod()) {
            
            // On bind les données du form
            $form->handleRequest($request);

            //si le formulaire est valide
            if ($form->isValid()) {
                //test ajout ou edition
                $new = is_null($domaine->getId());

                $domaine->setDateLastUpdate(new \DateTime());

                //On utilise notre Manager pour gérer la sauvegarde de l'objet
                $this->get('hopitalnumerique_domaine.manager.domaine')->save($domaine);

                $admins = $this->get('hopitalnumerique_user.manager.user')->findUsersByRole('ROLE_ADMINISTRATEUR_1');

                if($new)
                {
                    foreach ($admins as $key => $admin) 
                    {
                        $admins[$key]->addDomaine($domaine);
                    }
                    $this->get('hopitalnumerique_user.manager.user')->save($admins);
                }
                
                // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
                $this->get('session')->getFlashBag()->add( ($new ? 'success' : 'info') , 'Domaine ' . ($new ? 'ajouté.' : 'mis à jour.') ); 
                
                //on redirige vers la page index ou la page edit selon le bouton utilisé
                $do = $request->request->get('do');
                return $this->redirect( ($do == 'save-close' ? $this->generateUrl('hopitalnumerique_domaine_admin_domaine') : $this->generateUrl('hopitalnumerique_domaine_admin_domaine_edit', array( 'id' => $domaine->getId() ) ) ) );
            }
        }

        $options['form'] = $form->createView();
        $options['domaine'] = $domaine;

        return $this->render($view , $options);
    }
}