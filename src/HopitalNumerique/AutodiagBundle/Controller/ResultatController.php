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
    public function exportChapitresCSVAction( Resultat $resultat, $priorise )
    {
        $chapitres = $this->get('hopitalnumerique_autodiag.manager.resultat')->formateResultat( $resultat );
        //Trier par note
        if($priorise == 1 && $resultat->getOutil()->isPlanActionPriorise())
        {
            uasort($chapitres, array($this,"triParNote"));
            foreach ($chapitres as $key => $chapitre) 
            {
                uasort($chapitre->questions, array($this,"triParNoteQuestion"));
                uasort($chapitre->childs, array($this,"triParNote"));
                foreach ($chapitre->childs as $child) 
                {
                    uasort($child->questions, array($this,"triParNoteQuestion"));
                }
            }
        }

        //Nettoyage des éléments dont il n'y aucun élément
        foreach ($chapitres as $key => $chapitre)
        {
            //Vide le chapitre courant si il a ni de question ni de sous chapitre
            if(empty($chapitre->questions) && empty($chapitre->childs))
            {
                unset($chapitres[$key]);
            }
            //Sinon on cherche parmis les sous chapitres
            elseif(!empty($chapitre->childs))
            {
                $hideChapitre = false;
                foreach ($chapitre->childs as $keyChild => $child) 
                {
                    if(empty($child->questions))
                    {
                        unset($chapitre->childs[$keyChild]);
                        if(empty($chapitre->childs))
                        {
                            $hideChapitre = true;
                        }
                    }
                }

                if($hideChapitre)
                {
                    unset($chapitres[$key]);
                }
            }
        }

        $colonnes = array();
        $datas    = array();

        $colonnes = array(
            'Chapitre',
            'Sous chapitre',
            'Question',
            'Réponse',
            'Action à mener',
            'Pilote',
            'Échéance',
            'État d\'avancement',
            'Indicateur',
            'Commentaire'
        );

        foreach ($chapitres as $chapitre) 
        {
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
                $row[9] = '';

                $datas[] = $row;
            }

            //Si il y a des sous chapitres
            if(count($chapitre->childs) > 0)
            {
                //Pour chaque sous chapitre du chapitre courant
                foreach ($chapitre->childs as $chapitreChild) 
                {
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
                        $row[9] = '';

                        $datas[] = $row;
                    }
                }
            }
        }

        $kernelCharset = $this->container->getParameter('kernel.charset');

        return $this->get('hopitalnumerique_module.manager.session')->exportCsv( $colonnes, $datas, 'export-analyse-resultats.csv', $kernelCharset );
    }










    

    /*
     * Trie par note une stdClass
     *
     * @param [type] $a [description]
     * @param [type] $b [description]
     *
     * @return [type]
     */
    private function triParNote($a, $b)
    {
        if($a->noteChapitre < $b->noteChapitre)
            return -1;
        if($a->noteChapitre > $b->noteChapitre)
            return 1;
        if($a->order > $b->order)
            return 1;
        else
            return -1;
    }
    /**
     * Trie par note une stdClass
     *
     * @param [type] $a [description]
     * @param [type] $b [description]
     *
     * @return [type]
     */
    private function triParNoteQuestion($a, $b)
    {
        if($a->value < $b->value)
            return -1;
        if($a->value > $b->value)
            return 1;
        if($a->order > $b->order)
            return 1;
        else
            return -1;
    }
}
