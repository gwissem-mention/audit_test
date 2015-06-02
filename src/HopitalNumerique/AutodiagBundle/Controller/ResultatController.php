<?php

namespace HopitalNumerique\AutodiagBundle\Controller;

use HopitalNumerique\AutodiagBundle\Entity\Outil;
use HopitalNumerique\AutodiagBundle\Entity\Resultat;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
        $questionnairePrealableQuestions = array();
        if (null !== $resultat->getOutil()->getQuestionnairePrealable())
        {
            $questionnairePrealableQuestions = $this->container->get('hopitalnumerique_questionnaire.manager.questionnaire')->getQuestionsReponses($resultat->getOutil()->getQuestionnairePrealable()->getId(), $this->getUser()->getId());
        }
        
        $chapitres = $this->get('hopitalnumerique_autodiag.manager.resultat')->formateResultat( $resultat );

        return $this->render( 'HopitalNumeriqueAutodiagBundle:Resultat:detail.html.twig' , array(
            'resultat'  => $resultat,
            'chapitres' => $chapitres,
            'questionnairePrealableQuestions' => $questionnairePrealableQuestions
        ));
    }

    /**
     * POPIN : Partage de resultat
     */
    public function shareAction( Resultat $resultat )
    {
        return $this->render( 'HopitalNumeriqueAutodiagBundle:Resultat:Fancy/fancy_front.html.twig' , array(
            'resultat' => $resultat
        ));
    }

    /**
     * Permet de duppliquer un résultat via la pop-in de shareAction
     *
     * @param Request $request [description]
     *
     * @return [type]
     */
    public function shareDuplicationAction( Request $request )
    {
        $resultats              = array();
        $idUser                 = $request->request->get('user');
        $idResultatAdDuppliquer = $request->request->get('resultat');

        $user                = $this->get('security.context')->getToken()->getUser();
        $userPartageAvec     = $this->get('hopitalnumerique_user.manager.user')->findOneById($idUser);
        $resultatADuppliquer = $this->get('hopitalnumerique_autodiag.manager.resultat')->findOneById($idResultatAdDuppliquer);

        if(is_null($resultatADuppliquer->getResultatSharedBy()) && is_null($resultatADuppliquer->getResultatSharedFor()))
        {
            //MAJ de la date de publication
            $resultatADuppliquer->setDatePartage( new \DateTime());

            //Duppliquation
            $resultat = clone $resultatADuppliquer;
            $resultat->setId(null);

            //MAJ des identifiants partageur/partagé avec
            $resultat->setResultatSharedBy($resultatADuppliquer);
            $resultat->setUser($userPartageAvec);
            $resultatADuppliquer->setResultatSharedFor($resultat);

            $resultats[] = $resultat;
            $resultats[] = $resultatADuppliquer;

            $this->get('hopitalnumerique_autodiag.manager.resultat')->save( $resultats );

            //Clone des réponses du résultat
            $reponses = array();
            foreach ($resultatADuppliquer->getReponses() as $reponseADuppliquer) 
            {
                $reponse = clone $reponseADuppliquer;
                $reponse->setId(null);
                $reponse->setResultat($resultat);

                $reponses[] = $reponse;
            }

            $this->get('hopitalnumerique_autodiag.manager.reponse')->save( $reponses );
            $this->container->get('nodevo_mail.manager.mail')->sendPartageAutodiagnostic($user, $userPartageAvec, $resultat->getOutil());

            $success = true;
        }
        else
        {
            $success = false;
        }

        return new Response('{"success":'.($success ? 'true' : 'false').'}', 200);
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
            //<-- Suppression si colored vaut -1
            foreach ($chapitre->childs as $chapitreChildId => $chapitreChild)
            {
                foreach ($chapitreChild->questions as $questionIndex => $question)
                {
                    if (-1 == $question->colored)
                        unset($chapitres[$key]->childs[$chapitreChildId]->questions[$questionIndex]);
                }
            }
            //-->
                
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

        $datas = $in = $alsoIn = array();
        
        if( !$resultat->getSynthese() )
        {
            $colonnes = array(
                'Chapitre',
                'Sous chapitre',
                'Question',
                'Réponse',
                'Synthèse',
                'Commentaire',
                'Acteur',
                'Échéance',
                'État d\'avancement'
            );
        }
        else
        {
            $colonnes = array(
                'Chapitre',
                'Sous chapitre',
                'Question',
                'Synthèse',
                'Commentaire',
                'Acteur',
                'Échéance',
                'État d\'avancement'
            );
        }

        foreach ($chapitres as $chapitre) 
        {
            if( !in_array($chapitre->code, $in) )
            {
//                $datas[] = array($chapitre->code, "", "", "", "", "", "", "", "");
                $in[] = $chapitre->code;
            }
            foreach ($chapitre->questions as $question) 
            {
                $row = array();
                $i = 0;
                $row[$i++] = $chapitre->code;
                $row[$i++] = '';
                $row[$i++] = $question->question;
                //Set de la réponse
                if( !$resultat->getSynthese() )
                {
                    $row[$i] = '';
                    if($question->initialValue == -1)
                    {
                        $row[$i] = 'Non concerné';
                    }
                    else
                    {
                        foreach ($question->options as $option) 
                        {
                            $tab = explode(';', $option);
                            if($tab[0] == $question->initialValue)
                            {
                                $row[$i] .= $tab[1];
                            }
                        }
                    }
                    $i++;
                }
                $row[$i++] = $question->synthese;
                $row[$i++] = '';
                $row[$i++] = '';
                $row[$i++] = '';
                $row[$i++] = '';

                $datas[] = $row;
            }

            //Si il y a des sous chapitres
            if(count($chapitre->childs) > 0)
            {
                //Pour chaque sous chapitre du chapitre courant
                foreach ($chapitre->childs as $chapitreChild) 
                {
                    if( !in_array($chapitreChild->code, $alsoIn) )
                    {
//                        $datas[] = array($chapitre->code, $chapitreChild->code, "", "", "", "", "", "", "");
                        $alsoIn[] = $chapitreChild->code;
                    }
                    foreach ($chapitreChild->questions as $question) 
                    {
                        $row = array();
                        $i = 0;
                        $row[$i++] = $chapitre->code;
                        $row[$i++] = $chapitreChild->code;
                        $row[$i++] = $question->question;
                        //Set de la réponse
                        if( !$resultat->getSynthese() )
                        {
                            $row[$i] = '';
                            if($question->initialValue == -1)
                            {
                                $row[$i] = 'Non concerné';
                            }
                            else
                            {
                                foreach ($question->options as $option) 
                                {
                                    $tab = explode(';', $option);
                                    if($tab[0] == $question->initialValue)
                                    {
                                        $row[$i] .= $tab[1];
                                    }
                                }
                            }
                            $i++;
                        }
                        $row[$i++] = $question->synthese;
                        $row[$i++] = '';
                        $row[$i++] = '';
                        $row[$i++] = '';
                        $row[$i++] = '';
                        $row[$i++] = '';

                        $datas[] = $row;
                    }
                }
            }
        }

        $kernelCharset = $this->container->getParameter('kernel.charset');
        $user = $this->get('security.context')->getToken()->getUser();

        return $this->get('hopitalnumerique_autodiag.manager.resultat')->exportCsvCustom( $resultat, $user, $colonnes, $datas, 'export-analyse-resultats.csv', $kernelCharset );
    }
    
    /**
     * Export CSV des chapitres/question en fonction du résultat
     *
     * @return view
     */
    public function exportChapitresExcelAction( Resultat $resultat, $priorise )
    {   
        if( file_exists( __ROOT_DIRECTORY__ . '/files/autodiag/autodiag.xls') )
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
                //<-- Suppression si colored vaut -1
                foreach ($chapitre->childs as $chapitreChildId => $chapitreChild)
                {
                    foreach ($chapitreChild->questions as $questionIndex => $question)
                    {
                        if (-1 == $question->colored)
                            unset($chapitres[$key]->childs[$chapitreChildId]->questions[$questionIndex]);
                    }
                }
                //-->
                
                //Vide le chapitre courant si il a ni de question ni de sous chapitre
                if (empty($chapitre->questions) && empty($chapitre->childs))
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

            $datas = $in = $alsoIn = array();
            foreach ($chapitres as $chapitre) 
            {
                if( !in_array($chapitre->code, $in) )
                {
//                    $datas[] = array($chapitre->code);
                    $in[] = $chapitre->code;
                }
                foreach ($chapitre->questions as $question) 
                {
                    $row = array();
                    $i = 0;
                    $row[$i++] = $chapitre->code;
                    $row[$i++] = '';
                    $row[$i++] = $question->question;
                    //Set de la réponse
                    if( !$resultat->getSynthese() )
                    {
                        $row[$i] = '';
                        if($question->initialValue == -1)
                        {
                            $row[$i] = 'Non concerné';
                        }
                        else
                        {
                            foreach ($question->options as $option) 
                            {
                                $tab = explode(';', $option);
                                if($tab[0] == $question->initialValue)
                                {
                                    $row[$i] .= $tab[1];
                                }
                            }
                        }
                        $i++;
                    }
                    $row[$i++] = $question->synthese;
                    $row[$i++] = '';
                    $row[$i++] = '';
                    $row[$i++] = '';
                    $row[$i++] = '';
                    $row[$i++] = '';

                    $datas[] = $row;
                }

                //Si il y a des sous chapitres
                if(count($chapitre->childs) > 0)
                {
                    //Pour chaque sous chapitre du chapitre courant
                    foreach ($chapitre->childs as $chapitreChild) 
                    {
                        if( !in_array($chapitreChild->code, $alsoIn) )
                        {
//                            $datas[] = array($chapitre->code, $chapitreChild->code);
                            $alsoIn[] = $chapitreChild->code;
                        }
                        foreach ($chapitreChild->questions as $question) 
                        {
                            $row = array();
                            $i = 0;
                            $row[$i++] = $chapitre->code;
                            $row[$i++] = $chapitreChild->code;
                            $row[$i++] = $question->question;
                            //Set de la réponse
                            if( !$resultat->getSynthese() )
                            {
                                $row[$i] = '';
                                if($question->initialValue == -1)
                                {
                                    $row[$i] = 'Non concerné';
                                }
                                else
                                {
                                    foreach ($question->options as $option) 
                                    {
                                        $tab = explode(';', $option);
                                        if($tab[0] == $question->initialValue)
                                        {
                                            $row[$i] .= $tab[1];
                                        }
                                    }
                                }
                                $i++;
                            }
                            $row[$i++] = $question->synthese;
                            $row[$i++] = '';
                            $row[$i++] = '';
                            $row[$i++] = '';
                            $row[$i++] = '';
                            $row[$i++] = '';

                            $datas[] = $row;
                        }
                    }
                }
            }       
            
            $user = $this->get('security.context')->getToken()->getUser();

            //Récupèration du fichier excel
            if( !$resultat->getSynthese() )
            {
                $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject(__ROOT_DIRECTORY__ . '/files/autodiag/autodiag.xls');
                $lettreMax = "I";
            }
            else
            {
                $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject(__ROOT_DIRECTORY__ . '/files/autodiag/autodiag_synthese.xls');
                $lettreMax = "H";
            }
            $sheet = $phpExcelObject->getSheetByName('export_plan_actions_autodiag');
            
            $styleArray = array(
                'borders' => array(
                    'allborders' => array(
                        'style' => \PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            );
            
            $sheet->setCellValueByColumnAndRow(0, 1, 'Autodiagnostic "' . $resultat->getOutil()->getTitle() . '" - "' . $resultat->getName() . '"');
            
            $userName = ($user == 'anon.') ? '' :  (" par " . $user->getAppellation());
            $sheet->setCellValueByColumnAndRow(0, 2, "Plan d'actions exporté le " . date('d/m/Y') . " à " . date('H:i') . $userName);
            
            $nbLigne = 4;
            foreach($datas as $data)
            {
                for( $i = 0; $i < count($data); $i++ )
                {
                    $sheet->setCellValueByColumnAndRow($i, $nbLigne, $data[$i]);
                }
                $nbLigne++;
            }
            $sheet->getStyle("A4:" . $lettreMax . --$nbLigne)->applyFromArray($styleArray);
            $sheet->getStyle("A4:" . $lettreMax . $nbLigne)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT); 
            
            $sheet->getColumnDimension('B')->setAutoSize(true);
            $sheet->getColumnDimension('C')->setAutoSize(true);
            $sheet->getColumnDimension('D')->setAutoSize(true);
            $sheet->getColumnDimension('E')->setAutoSize(true);
            $sheet->getColumnDimension('F')->setAutoSize(true);
            $sheet->getColumnDimension('G')->setAutoSize(true);
            $sheet->getColumnDimension('H')->setAutoSize(true);
            if( !$resultat->getSynthese() )
            {
                $sheet->getColumnDimension('I')->setAutoSize(true);
            }
            
            $writer   = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
            $response = $this->get('phpexcel')->createStreamedResponse($writer);

            // adding headers
            $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
            $response->headers->set('Content-Disposition', 'attachment;filename=export-analyse-resultats.xls');
            $response->headers->set('Pragma', 'public');
            $response->headers->set('Cache-Control', 'maxage=1');

            return $response;
        }
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
        {
            return -1;
        }
        
        if($a->noteChapitre > $b->noteChapitre)
        {
            return 1;
        }
        
        if($a->order > $b->order)
        {
            return 1;
        }
        else
        {
            return -1;
        }
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
        {
            return -1;
        }
        
        if($a->value > $b->value)
        {
            return 1;
        }
        
        if($a->order > $b->order)
        {
            return 1;
        }
        else
        {
            return -1;
        }
    }
}
