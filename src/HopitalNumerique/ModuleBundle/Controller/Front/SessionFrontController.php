<?php

namespace HopitalNumerique\ModuleBundle\Controller\Front;

use HopitalNumerique\ModuleBundle\Entity\Session;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SessionFrontController extends Controller
{
    /**
     * Affiche la description d'une session dans une popin
     *
     * @param Session $session Session à afficher
     */
    public function descriptionAction( \HopitalNumerique\ModuleBundle\Entity\Session $session )
    {
        return $this->render('HopitalNumeriqueModuleBundle:Front/Session:description.html.twig', array(
                'session' => $session
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
            $user     = $inscription->getUser();
            $reponses = $this->get('hopitalnumerique_questionnaire.manager.reponse')->reponsesByQuestionnaireByUser( 4, $user->getId() , true, $session->getId() );
            $row      = array();

            foreach($reponses as $reponse)
            {
                $question   = $reponse->getQuestion();
                $idQuestion = $question->getId();

                //ajoute la question si non présente dans les colonnes
                if( !isset($colonnes[$idQuestion]) )
                    $colonnes[$idQuestion] = $question->getLibelle();

                //handle la réponse
                switch($question->getTypeQuestion()->getLibelle())
                {
                    case 'checkbox':
                        $row[$idQuestion] = ('1' == $reponse->getReponse() ? 'Oui' : 'Non' );
                        break;
                    default:
                        $row[$idQuestion] = $reponse->getReponse();
                        break;
                }
            }

            ksort($row);

            $datas[] = $row;
        }

        //reorder colonnes
        ksort($colonnes);

        $kernelCharset = $this->container->getParameter('kernel.charset');

        return $this->get('hopitalnumerique_module.manager.session')->customExportCsv( $colonnes, $datas, 'export-evaluations.csv', $kernelCharset );
    }
}
