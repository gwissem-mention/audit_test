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

        $datas = $in = $alsoIn = array();

        $colonnes = array(
            'Chapitre',
            'Sous chapitre',
            'Question',
            'Réponse',
            'Synthèse',
            'Commentaire',
            'Acteurs',
            'Échéances',
            'État d\'avancement'
        );

        foreach ($chapitres as $chapitre) 
        {
            if( !in_array($chapitre->code, $in) ){
                $datas[] = array($chapitre->code, "", "", "", "", "", "", "", "");
                $in[] = $chapitre->code;
            }
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
                    if( !in_array($chapitreChild->code, $alsoIn) ){
                        $datas[] = array($chapitre->code, $chapitreChild->code, "", "", "", "", "", "", "");
                        $alsoIn[] = $chapitreChild->code;
                    }
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
            foreach ($chapitres as $chapitre) 
            {
                if( !in_array($chapitre->code, $in) ){
                    $datas[] = array($chapitre->code);
                    $in[] = $chapitre->code;
                }
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
                        if( !in_array($chapitreChild->code, $alsoIn) ){
                            $datas[] = array($chapitre->code, $chapitreChild->code);
                            $alsoIn[] = $chapitreChild->code;
                        }
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
            
            $user = $this->get('security.context')->getToken()->getUser();

            //Récupèration du fichier excel
            $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject(__ROOT_DIRECTORY__ . '/files/autodiag/autodiag.xls');
            $sheet = $phpExcelObject->getSheetByName('export_plan_actions_autodiag');
            
            $styleArray = array(
                'borders' => array(
                    'allborders' => array(
                        'style' => \PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            );
            
            $sheet->setCellValueByColumnAndRow(0, 1, 'Autodiagnostic "' . $resultat->getOutil()->getTitle() . '" - "' . $resultat->getName() . '"');
            $sheet->setCellValueByColumnAndRow(0, 2, "Plan d'actions exporté le " . date('d/m/Y') . " à " . date('H:i') . " par " . $user->getAppellation());
            
            $nbLigne = 4;
            foreach($datas as $data){
                for( $i = 0; $i < count($data); $i++ ){
                    $sheet->setCellValueByColumnAndRow($i, $nbLigne, $data[$i]);
                }
                $nbLigne++;
            }
            $sheet->getStyle("A4:I" . --$nbLigne)->applyFromArray($styleArray);
            $sheet->getStyle("A4:I" . $nbLigne)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT); 
            
            $sheet->getColumnDimension('B')->setAutoSize(true);
            $sheet->getColumnDimension('C')->setAutoSize(true);
            $sheet->getColumnDimension('D')->setAutoSize(true);
            $sheet->getColumnDimension('E')->setAutoSize(true);
            $sheet->getColumnDimension('F')->setAutoSize(true);
            $sheet->getColumnDimension('G')->setAutoSize(true);
            $sheet->getColumnDimension('H')->setAutoSize(true);
            $sheet->getColumnDimension('I')->setAutoSize(true);
            
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
