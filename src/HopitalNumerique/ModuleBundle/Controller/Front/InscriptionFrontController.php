<?php

namespace HopitalNumerique\ModuleBundle\Controller\Front;

use HopitalNumerique\ModuleBundle\Entity\Inscription;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class InscriptionFrontController extends Controller
{
    /**
     * Liste toutes les informations de la session
     *
     * @param HopitalNumeriqueModuleBundleEntitySession $session Session à laquelle l'inscription doit etre faite
     *
     * @return [type]
     */
    public function addAction(Request $request, \HopitalNumerique\ModuleBundle\Entity\Session $session )
    {
        //On récupère l'utilisateur qui est connecté
        $user = $this->get('security.context')->getToken()->getUser();

        //Création d'une nouvelle inscription
        $inscription = $this->get('hopitalnumerique_module.manager.inscription')->createEmpty();
        $inscription->setUser( $user );
        $inscription->setSession( $session );
        $inscription->setEtatInscription(   $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array('id' => 406) ) );
        $inscription->setEtatParticipation( $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array('id' => 410) ) );
        $inscription->setEtatEvaluation(    $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array('id' => 27) ) );

        $form = $this->createForm('hopitalnumerique_module_inscription', $inscription);

        $request = $this->get('request');

        if ( $form->handleRequest($request)->isValid() ) 
        {
            $this->get('hopitalnumerique_module.manager.inscription')->save($inscription);

            if($session->getModule()->getMailAccuseInscription())
            { 
                //envoi du mail de confirmation d'inscription
                $options = array(
                    'module' => $session->getModule()->getTitre(),
                    'date'   => $session->getDateSession()->format('d/m/Y')
                );
                $mail = $this->get('nodevo_mail.manager.mail')->sendInscriptionSession($user, $options);
                $this->get('mailer')->send($mail);
            }

            // On envoi une 'flash' pour indiquer à l'utilisateur que le fichier n'existe pas: suppression manuelle sur le serveur
            $this->get('session')->getFlashBag()->add( ('success') , 'Votre inscription a été prise en compte.' );

            return $this->redirect($this->generateUrl('hopitalnumerique_module_session_informations_front', array( 'id' => $inscription->getSession()->getId() ) ));
        }

        return $this->render('HopitalNumeriqueModuleBundle:Front/Inscription:add.html.twig', array(
            'form'    => $form->createView(),
            'session' => $session
        ));
    }

    /**
     * Compte HN : Affiche la liste des inscriptions de l'utilisateur connecté
     *
     * @return view
     */
    public function indexAction()
    {
        //On récupère l'utilisateur qui est connecté
        $user = $this->get('security.context')->getToken()->getUser();

        //get all inscriptions
        $inscriptions = $this->get('hopitalnumerique_module.manager.inscription')->getInscriptionsForUser( $user );

        //get sessions terminées where user connected == formateur
        $sessions = $this->get('hopitalnumerique_module.manager.session')->getSessionsForFormateur( $user );

        return $this->render('HopitalNumeriqueModuleBundle:Front/Inscription:index.html.twig', array(
            'inscriptions' => $inscriptions,
            'sessions'     => $sessions
        ));
    }

    /**
     * Compte HN : Affiche l'attestation de présence de l'utilisateur connecté
     *
     * @return view
     */
    public function attestationAction(Inscription $inscription)
    {
        //On récupère l'utilisateur qui est connecté
        $user = $this->get('security.context')->getToken()->getUser();

        $html = $this->renderView('HopitalNumeriqueModuleBundle:Front/Pdf:attestation-presence.html.twig', array(
            'inscription' => $inscription,
            'user'        => $user
        ));

        $options = array(
            'margin-bottom' => 10,
            'margin-left'   => 4,
            'margin-right'  => 4,
            'margin-top'    => 10,
            'encoding'      => 'UTF-8'
        );

        return new Response(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html, $options, true),
            200,
            array(
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="Attestation_presence.pdf"'
            )
        );
    }

    /**
     * Compte HN : Télécharge la liste des participants de la session de l'inscription
     *
     * @return view
     */
    public function exportListeParticipantAction(Inscription $inscription)
    {
        $colonnes = array();
        $datas    = array();

        $session = $inscription->getSession();

        $colonnes = array(
            'Nom',
            'Prénom',
            'Région',
            'Établissement',
            'Fonction',
            'Téléphone direct',
            'Téléphone portable',
            'Mail'
        );

        //Pour chaque session, on parcourt les inscriptions pour les lister
        foreach ($session->getInscriptions() as $inscription) 
        {
            //On prend uniquement les "a participé"
            if($inscription->getEtatParticipation()->getId() === 411)
            {
                $row = array();

                $user = $inscription->getUser();

                $row[0] = $user->getNom();
                $row[1] = $user->getPrenom();
                $row[2] = $user->getRegion()->getLibelle();
                $row[3] = $user->getEtablissementRattachementSante() ? $user->getEtablissementRattachementSante()->getNom() : $user->getAutreStructureRattachementSante();
                $row[4] = $user->getFonctionStructure();
                $row[5] = $user->getTelephoneDirect();
                $row[6] = $user->getTelephonePortable();
                $row[7] = $user->getEmail();

                $datas[] = $row;   
            }
        }

        $kernelCharset = $this->container->getParameter('kernel.charset');

        return $this->get('hopitalnumerique_module.manager.session')->exportCsv( $colonnes, $datas, 'export-liste-participant-session.csv', $kernelCharset );
    }

    public function annulationInscriptionAction(Inscription $inscription)
    {
        //On récupère l'utilisateur qui est connecté
        $user = $this->get('security.context')->getToken()->getUser();

        if($user->getId() === $inscription->getUser()->getId())
        {
            $this->get('hopitalnumerique_module.manager.inscription')->toogleEtatInscription( array($inscription), $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => 409) ) );
            $this->get('hopitalnumerique_module.manager.inscription')->toogleEtatParticipation( array($inscription), $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => 412) ) );
            $this->get('hopitalnumerique_module.manager.inscription')->toogleEtatEvaluation( array($inscription), $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => 430) ) );   
            
            $this->get('session')->getFlashBag()->add( ('success') , 'Votre inscription à la session "'. $inscription->getSession()->getModule()->getTitre() .'" été annulée.' );
        }
        else
        {
            $this->get('session')->getFlashBag()->add( ('danger') , 'Vous ne pouvez annuler que les inscriptions vous concernant.' );
        }

        return new Response('{"success":true}', 200);
    }
}
