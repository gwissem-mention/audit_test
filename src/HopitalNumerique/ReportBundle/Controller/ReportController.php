<?php

namespace HopitalNumerique\ReportBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Report controller.
 */
class ReportController extends Controller
{
    /**
    * BackOffice
    */

    /**
     * Affiche la liste des Report.
     */
    public function indexAction()
    {
        $grid = $this->get('hopitalnumerique_report.grid.report');

        return $grid->render('HopitalNumeriqueReportBundle:Report:index.html.twig');
    }

    /**
     * Affiche le Report en fonction de son ID passé en paramètre.
     * 
     * @param integer $id Id de Report.
     */
    public function showAction( $id )
    {
        //Récupération de l'entité en fonction du paramètre
        $report = $this->get('hopitalnumerique_report.manager.report')->findOneBy( array( 'id' => $id) );
        $user = $report->getUser();
        $etablissement = $user->getEtablissementRattachementSante();

        return $this->render('HopitalNumeriqueReportBundle:Report:show.html.twig', array(
            'report' => $report,
            'user' => $user,
            'etablissement' => $etablissement,
        ));
    }

    /**
    * FrontOffice
    */

    /**
    * Affiche le formulaire de signalement de bug
    *
    */
    public function signalerAction()
    {
        //Récupération de l'entité passée en paramètre
        $report = $this->get('hopital_numerique_report.manager.report')->createEmpty();
        
        $formName         = 'hopitalnumerique_reportbundle_report';
        $view             = 'HopitalNumeriqueReportBundle:Report:signaler.html.twig';
        
        return $this->renderForm( $formName, $report, $view );
    }

    /**
     * Passe le signalement de bug à "archivé".
     *
     * @param \HopitalNumerique\ReportBundle\Entity\Report $report Signalement de bug à archiver/désarchiver
     */
    public function archiverAction( \HopitalNumerique\ReportBundle\Entity\Report $report )
    {
        $report->setArchive(!$report->getArchive());

        //Suppression de l'entité
        $this->get('hopitalnumerique_report.manager.report')->save( $report );

        $this->get('session')->getFlashBag()->add('info', 'Le signalement de bug ' . ($report->getArchive() ? ' est archivé.' : 'n\'est plus archivé.'));

        return $this->redirect( $this->generateUrl('hopitalnumerique_report_admin_report') );
    }






    /**
     * Effectue le render du formulaire Report.
     *
     * @param string $formName Nom du service associé au formulaire
     * @param Report   $entity   Entité $report
     * @param string $view     Chemin de la vue ou sera rendu le formulaire
     *
     * @return Form | redirect
     */
    private function renderForm( $formName, $report, $view )
    {
        //Création du formulaire via le service
        $form = $this->createForm( $formName, $report);

        $request = $this->get('request');
        
        // Si l'utilisateur soumet le formulaire
        if ('POST' == $request->getMethod()) {
            
            // On bind les données du form
            $form->handleRequest($request);

            //si le formulaire est valide
            if ($form->isValid()) {
                //On utilise notre Manager pour gérer la sauvegarde de l'objet
                $this->get('hopitalnumerique_report.manager.report')->save($report);
                
                // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
                $this->get('session')->getFlashBag()->add( 'success' , 'Bug signalé.' ); 
                
                //on redirige vers la page index
                return $this->redirect( $this->generateUrl('hopitalnumerique_homepage') );
            }
        }

        return $this->render( $view , array(
            'form'             => $form->createView(),
            'report' => $report
        ));
    }
}