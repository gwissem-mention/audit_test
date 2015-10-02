<?php

namespace HopitalNumerique\ModuleBundle\Controller\Front;

use HopitalNumerique\ModuleBundle\Entity\Session;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class SessionFrontController extends Controller
{
    /**
     * Affiche la description d'une session dans une popin
     *
     * @param Session $session Session à afficher
     */
    public function descriptionAction( \HopitalNumerique\ModuleBundle\Entity\Session $session )
    {
        $connaissances = $session->getConnaissances();
        $connaissancesOrderedByParent = array();

        foreach ($connaissances as $connaissance) 
        {
            if(!array_key_exists($connaissance->getParent()->getId(), $connaissancesOrderedByParent))
            {
                $connaissancesOrderedByParent[$connaissance->getParent()->getId()] = array();
            }

            $connaissancesOrderedByParent[$connaissance->getParent()->getId()][] = $connaissance;
        }


        return $this->render('HopitalNumeriqueModuleBundle:Front/Session:description.html.twig', array(
                'session'       => $session,
                'connaissances' => $connaissancesOrderedByParent
        ));
    }

    /**
     * Liste toutes les informations de la session
     *
     * @param HopitalNumeriqueModuleBundleEntitySession $session [description]
     *
     * @return [type]
     */
    public function informationAction( \HopitalNumerique\ModuleBundle\Entity\Session $session )
    {
        //On récupère l'utilisateur qui est connecté
        $user = $this->get('security.context')->getToken()->getUser();

        if( ( $session->getNombrePlaceDisponible() - count($session->getInscriptions()) ) == 0 && !$session->userIsInscrit($user) )
        {
            // On envoi une 'flash' pour indiquer à l'utilisateur que le fichier n'existe pas: suppression manuelle sur le serveur
            $this->get('session')->getFlashBag()->add( ('danger') , 'Cette session est complète, vous ne pouvez pas vous inscrire. Veuillez-choisir une autre session de ce module thèmatique.' );
        }

        return $this->render('HopitalNumeriqueModuleBundle:Front/Session:index.html.twig', array(
                'session'           => $session,
                'moduleSelectionne' => $session->getModule()
        ));
    }

    /**
     * Envoie un mail de rappel à tout les utilisateurs inscrits et acceptés de la session
     *
     * @param HopitalNumeriqueModuleBundleEntitySession $session [description]
     *
     * @return [type]
     */
    public function mailRappelAction( \HopitalNumerique\ModuleBundle\Entity\Session $session )
    {
        //récupérations des inscriptions acceptées
        $inscriptions = $session->getInscriptionsAccepte();

        //Envoyer mail de refus de l'inscription
        $mails = $this->get('nodevo_mail.manager.mail')->sendRappelInscriptionMail($inscriptions,array());
        foreach ($mails as $mail)
        {
            $this->get('mailer')->send($mail);
        }
        
        // On envoi une 'flash' pour indiquer à l'utilisateur que le fichier n'existe pas: suppression manuelle sur le serveur
        $this->get('session')->getFlashBag()->add( ('success') , 'Mails de rappel envoyé aux utilisateurs acceptés à cette session.' );

        return new Response('Mails de rappel envoyés.');
    }

    /**
     * Compte HN : Génère le fichier CSV des formulaires d'évaluation
     *
     * @return view
     */
    public function evaluationAction( Session $session )
    {
        $colonnes = array();
        $datas    = array();

        $inscriptions = $session->getInscriptionsAccepte();
        foreach($inscriptions as $inscription)
        {
            $hasReponses = false;
            $user     = $inscription->getUser();
            $reponses = $this->get('hopitalnumerique_questionnaire.manager.reponse')->reponsesByQuestionnaireByUser( 4, $user->getId() , true, $session->getId() );
            $row      = array();

            foreach($reponses as $reponse)
            {
                $question   = $reponse->getQuestion();
                $idQuestion = $question->getId();

                //ajoute la question si non présente dans les colonnes
                if( !isset($colonnes[$idQuestion]) )
                {
                    $colonnes[$idQuestion] = $question->getLibelle();
                }

                //handle la réponse
                switch($question->getTypeQuestion()->getLibelle())
                {
                    case 'checkbox':
                        $row[$idQuestion] = ('1' == $reponse->getReponse() ? 'Oui' : 'Non' );
                        break;
                    case 'entityradio':
                        $question = $reponse->getQuestion();

                        $referenceReponse = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => $reponse->getReponse()) );

                        if(!is_null($referenceReponse))
                        {
                            $row[$idQuestion] = $referenceReponse->getLibelle();
                        }
                        else
                        {
                            $row[$idQuestion] = 'Non renseigné';
                        }
                        break;
                    default:
                        $row[$idQuestion] = $reponse->getReponse();
                        break;
                }

                $hasReponses = true;
            }

            if(!$hasReponses)
                continue;

            ksort($row);

            $datas[] = $row;
        }

        if(empty($datas))
        {
            $colonnes = array(0 => "Aucune donnée");
            $datas[] = array(0 => "");
        }

        //reorder colonnes
        ksort($colonnes);

        $kernelCharset = $this->container->getParameter('kernel.charset');

        return $this->get('hopitalnumerique_module.manager.session')->exportCsv( $colonnes, $datas, 'export-evaluations.csv', $kernelCharset );
    }

    /**
     * POPIN : Partage de resultat
     */
    public function parametrageAction(Session $session)
    {
        return $this->render( 'HopitalNumeriqueModuleBundle:Front/Inscription:fancy.html.twig' , array(
            'session' => $session
        ));
    }
    
    /**
     * POPIN : gestion de la présence des experts
     */
    public function parametrageSaveAction(Session $session, Request $request)
    {
        //Mise à jour de la présence des experts
        $inscriptionsId = json_decode( $this->get('request')->request->get('inscriptions') );

        $inscriptions        = $this->get('hopitalnumerique_module.manager.inscription')->findBy(array('session' => $session));
        $refParticipation    = $this->get('hopitalnumerique_reference.manager.reference')->findOneById(411);
        $refPasParticipation = $this->get('hopitalnumerique_reference.manager.reference')->findOneById(412);

        foreach ($inscriptions as &$inscription) {
            if(in_array($inscription->getId(), $inscriptionsId)){
                $inscription->setEtatParticipation($refParticipation);
            }
            else{
                $inscription->setEtatParticipation($refPasParticipation);
            }
        }

        $this->get('hopitalnumerique_module.manager.inscription')->save($inscriptions);

        return new Response('{"success":true}', 200);
    }

    /**
     * Compte HN : Génère le fichier CSV des formulaires d'évaluation
     *
     * @return view
     */
    public function exportCommentaireCSVAction( \HopitalNumerique\UserBundle\Entity\User $user )
    {
        $colonnes = array();
        $datas    = array();

        //get sessions terminées where user connected == formateur
        $sessions = $this->get('hopitalnumerique_module.manager.session')->getSessionsForFormateur( $user );

        $colonnes = array(
            'Module',
            'Date de la session',
            'Utilisateur',
            'Date de l\'inscription',
            'Statut inscription',
            'Commentaire'
        );

        //Pour chaque session, on parcourt les inscriptions pour les lister
        foreach ($sessions as $session) 
        {
            foreach ($session->getInscriptions() as $inscription) 
            {
                $row = array();

                $row[0] = $session->getModule()->getTitre();
                $row[1] = $session->getDateSession()->format('d/m/Y');
                $row[2] = $inscription->getUser()->getAppellation();
                $row[3] = $inscription->getDateInscription()->format('d/m/Y');
                $row[4] = $inscription->getEtatInscription()->getLibelle();
                $row[5] = $inscription->getCommentaire();

                $datas[] = $row;
            }
        }

        $kernelCharset = $this->container->getParameter('kernel.charset');

        return $this->get('hopitalnumerique_module.manager.session')->exportCsv( $colonnes, $datas, 'export-commentaire-formateur.csv', $kernelCharset );
    }

    /**
     * Compte HN : Génère le fichier CSV des formulaires d'évaluation par session
     *
     * @return view
     */
    public function exportCommentaireCSVBySessionAction( Session $session )
    {
        $colonnes = array();
        $datas    = array();

        $colonnes = array(
            'Module',
            'Date de la session',
            'Utilisateur',
            'Etablissement',
            'Région',
            'Adresse mail',
            'Fonction',
            'Libellé de la fonction',
            'Date de l\'inscription',
            'Statut inscription',
            'Commentaire'
        );

        foreach ($session->getInscriptions() as $inscription) 
        {
            $row = array();

            $row[0] = $session->getModule()->getTitre();
            $row[1] = $session->getDateSession()->format('d/m/Y');
            $row[2] = $inscription->getUser()->getAppellation();
            $row[3] = !is_null($inscription->getUser()->getEtablissementRattachementSante()) ? $inscription->getUser()->getEtablissementRattachementSante()->getNom() :  ($inscription->getUser()->getAutreStructureRattachementSante());
            $row[4] = !is_null($inscription->getUser()->getRegion()) ? $inscription->getUser()->getRegion()->getLibelle() : '-';
            $row[5] = $inscription->getUser()->getEmail();
            $row[6] = !is_null($inscription->getUser()->getFonctionDansEtablissementSanteReferencement()) ? $inscription->getUser()->getFonctionDansEtablissementSanteReferencement()->getLibelle() : '-';
            $row[7] = $inscription->getUser()->getFonctionDansEtablissementSante();
            $row[8] = $inscription->getDateInscription()->format('d/m/Y');
            $row[9] = $inscription->getEtatInscription()->getLibelle();
            $row[10] = $inscription->getCommentaire();

            $datas[] = $row;
        }
    

        $kernelCharset = $this->container->getParameter('kernel.charset');

        return $this->get('hopitalnumerique_module.manager.session')->exportCsv( $colonnes, $datas, 'export-commentaire-formateur-session.csv', $kernelCharset );
    }
}
