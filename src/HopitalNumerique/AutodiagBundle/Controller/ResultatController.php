<?php

namespace HopitalNumerique\AutodiagBundle\Controller;

use HopitalNumerique\AutodiagBundle\Entity\Outil;
use HopitalNumerique\AutodiagBundle\Entity\Resultat;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Resultat controller.
 */
class ResultatController extends Controller
{
    /**
     * Affiche la liste des Resultats.
     */
    public function indexAction(Outil $outil)
    {
        $grid = $this->get('hopitalnumerique_autodiag.grid.resultat');
        $grid->setSourceCondition('outil', $outil->getId() );

        return $grid->render('HopitalNumeriqueAutodiagBundle:Resultat:index.html.twig', array('outil'=>$outil));
    }

    /**
     * Affiche le détail d'un résultat
     */
    public function detailAction( Resultat $resultat )
    {
        $chapitres = $this->get('hopitalnumerique_autodiag.manager.resultat')->formateResultat( $resultat );

        return $this->render( 'HopitalNumeriqueAutodiagBundle:Resultat:detail.html.twig' , array(
            'resultat'  => $resultat,
            'chapitres' => $chapitres
        ));
    }
    
    /**
     * Export CSV des chapitres/question en fonction du résultat
     *
     * @return view
     */
    public function exportChapitresCSVAction( Resultat $resultat )
    {
        $chapitres = $this->get('hopitalnumerique_autodiag.manager.resultat')->formateResultat( $resultat );

        $colonnes = array();
        $datas    = array();

        $colonnes = array(
            'Chapitre',
            'Sous chapitre',
            'Question',
            'Réponse',
            'Synthèse',
            'Commentaire',
            'Acteurs',
            'Échances',
            'État d\'avancement'
        );

        foreach ($chapitres as $chapitre) 
        {
            $row = array();

            $row[0] = $chapitre->code;
            $row[1] = '';
            $row[2] = '';
            $row[3] = '';
            $row[4] = $chapitre->synthese;
            $row[5] = '';
            $row[6] = '';
            $row[7] = '';
            $row[8] = '';

            $datas[] = $row;

            foreach ($chapitre->questions as $question) 
            {
                $row = array();

                $row[0] = $chapitre->code;
                $row[1] = '';
                $row[2] = $question->question;
                $row[3] = '';
                //Set de la réponse
                if($question->initialValue == -1)
                {
                    $row[3] = 'Non concerné';
                }
                else
                {
                    foreach ($question->options as $option) 
                    {
                        $tab = explode(';', $option);
                        if($tab[0] == $question->initialValue)
                        {
                            $row[3] .= $tab[1];
                        }
                    }
                }
                $row[4] = $question->synthese;
                $row[5] = '';
                $row[6] = '';
                $row[7] = '';
                $row[8] = '';

                $datas[] = $row;
            }

            //Si il y a des sous chapitres
            if(count($chapitre->childs) > 0)
            {
                //Pour chaque sous chapitre du chapitre courant
                foreach ($chapitre->childs as $chapitreChild) 
                {
                    $row = array();

                    $row[0] = $chapitre->code;
                    $row[1] = $chapitreChild->code;
                    $row[2] = '';
                    $row[3] = '';
                    $row[4] = $chapitreChild->synthese;
                    $row[5] = '';
                    $row[6] = '';
                    $row[7] = '';
                    $row[8] = '';

                    $datas[] = $row;

                    foreach ($chapitreChild->questions as $question) 
                    {
                        $row = array();

                        $row[0] = $chapitre->code;
                        $row[1] = $chapitreChild->code;
                        $row[2] = $question->question;
                        $row[3] = '';
                        //Set de la réponse
                        if($question->initialValue == -1)
                        {
                            $row[3] = 'Non concerné';
                        }
                        else
                        {
                            foreach ($question->options as $option) 
                            {
                                $tab = explode(';', $option);
                                if($tab[0] == $question->initialValue)
                                {
                                    $row[3] .= $tab[1];
                                }
                            }
                        }
                        $row[4] = $question->synthese;
                        $row[5] = '';
                        $row[6] = '';
                        $row[7] = '';
                        $row[8] = '';

                        $datas[] = $row;
                    }
                }
            }
        }

        $kernelCharset = $this->container->getParameter('kernel.charset');

        return $this->get('hopitalnumerique_module.manager.session')->exportCsv( $colonnes, $datas, 'export-analyse-resultats.csv', $kernelCharset );
    }
}