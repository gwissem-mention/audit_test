<?php

namespace HopitalNumerique\AideBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use HopitalNumerique\AideBundle\Entity\Aide;

/**
 * Aide controller.
 */
class AideController extends Controller
{
    /**
     * Affiche la liste des Aides.
     */
    public function indexAction()
    {
        $grid = $this->get('hopitalnumerique_aide.grid.aide');

        return $grid->render('HopitalNumeriqueAideBundle:Aide:index.html.twig');
    }

    /**
     * Affiche le formulaire d'ajout d'Aide.
     */
    public function addAction(Request $request)
    {
        $route = $request->query->get('route');
        $aide = $this->get('hopitalnumerique_aide.manager.aide')->createEmpty()->setRoute($route);

        return $this->renderForm('hopitalnumerique_aide_aide', $aide, 'HopitalNumeriqueAideBundle:Aide:edit.html.twig' );
    }

    /**
     * Affiche le formulaire d'édition d'Aide.
     */
    public function editAction( $id )
    {
        $aide = $this->get('hopitalnumerique_aide.manager.aide')->findOneBy( array('id' => $id) );

        return $this->renderForm('hopitalnumerique_aide_aide', $aide, 'HopitalNumeriqueAideBundle:Aide:edit.html.twig' );
    }


    /**
     * Affiche l'Aide en fonction de son ID passé en paramètre.
     */
    public function showAction( $id )
    {
        //Récupération de l'entité en fonction du paramètre
        $aide = $this->get('hopitalnumerique_aide.manager.aide')->findOneBy( array( 'id' => $id) );

        return $this->render('HopitalNumeriqueAideBundle:Aide:show.html.twig', array(
            'aide' => $aide,
        ));
    }

    /**
     * Suppression d'une Aide.
     *
     * METHOD = POST|DELETE
     */
    public function deleteAction( $id )
    {
        $aide = $this->get('hopitalnumerique_aide.manager.aide')->findOneBy( array( 'id' => $id) );

            try
            {
                //Suppression de l'entité
                $this->get('hopitalnumerique_aide.manager.aide')->delete( $aide );
                $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.' );
            }
            catch ( \Exception $e)
            {
                $this->get('session')->getFlashBag()->add('danger', 'Suppression impossible.');
            }

        return new Response('{"success":true, "url" : "'.$this->generateUrl('hopitalnumerique_aide_aide').'"}', 200);
    }

    /**
     * Suppression de masse des Aides
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Redirect
     */
    public function deleteMassAction( $primaryKeys, $allPrimaryKeys )
    {
        if($allPrimaryKeys == 1){
            $rawDatas = $this->get('hopitalnumerique_aide.manager.aide')->getRawData();
            foreach($rawDatas as $data)
            {
                $primaryKeys[] = $data['id'];
            }
        }

        $aides = $this->get('hopitalnumerique_aide.manager.aide')->findBy( array('id' => $primaryKeys) );

        $this->get('hopitalnumerique_aide.manager.aide')->delete( $aides );

        $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.' );

        return $this->redirect( $this->generateUrl('hopitalnumerique_aide_aide') );
    }




    /**
     * Effectue le render du formulaire Aide.
     *
     * @param string    $formName Nom du service associé au formulaire
     * @param Aide $item     Entité Aide
     * @param string    $view     Chemin de la vue ou sera rendu le formulaire
     *
     * @return Form | redirect
     */
    private function renderForm( $formName, $aide, $view )
    {
        //Création du formulaire via le service
        $form = $this->createForm( $formName, $aide);

        $request = $this->get('request');

        // Si l'utilisateur soumet le formulaire
        if ('POST' == $request->getMethod()) {

            // On bind les données du form
            $form->handleRequest($request);

            //si le formulaire est valide
            if ($form->isValid()) {
                //test ajout ou edition
                $new = is_null($aide->getId()) ? true : false;

                // On utilise notre Manager pour gérer la sauvegarde de l'objet
                $this->get('hopitalnumerique_aide.manager.aide')->save($aide);

                // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
                $this->get('session')->getFlashBag()->add( ($new ? 'success' : 'info') , 'Aide ' . ($new ? 'ajoutée.' : 'mise à jour.') );

                $do = $request->request->get('do');

                // On redirige vers la home page
                return $this->redirect( ($do == 'save-close' ? $this->generateUrl('hopitalnumerique_aide_aide') : $this->generateUrl('hopitalnumerique_aide_aide_edit', array( 'id' => $aide->getId() ) ) ) );
            }
        }

        return $this->render( $view , array(
            'form' => $form->createView(),
            'aide' => $aide
        ));
    }

    /**
     * POPIN : Affiche le message d'aide correspondant à la page
     */
    public function aideAction(Request $request)
    {
        $route = $request->query->get('route');

        //Récupération de l'entité en fonction du paramètre
        $aide = $this->get('hopitalnumerique_aide.manager.aide')->findOneBy( array( 'route' => $route) );

        return $this->render('HopitalNumeriqueAideBundle:Aide:aide.html.twig', array(
            'aide' => $aide,
            'route' => $route
        ));
    }
}
