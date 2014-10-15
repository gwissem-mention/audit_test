<?php

namespace HopitalNumerique\ImportExcelBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use HopitalNumerique\AutodiagBundle\Entity\Outil; 
use Symfony\Component\HttpFoundation\Request;


class ImportExcelController extends Controller
{

    public function indexAction(Outil $outil)
    {
        return $this->render( 'HopitalNumeriqueImportExcelBundle:ImportExcel:index.html.twig' , array(
            'outil'     => $outil
        ));
    }

    /**
     * Download le gabarit.
     *
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function downloadGabaritAction()
    {
        $options = array(
            'serve_filename' => 'Gabarit_autodiag.xlsx',
            'absolute_path'  => false,
            'inline'         => false,
        );
    
        if( file_exists( __ROOT_DIRECTORY__ . '/files/autodiag/Gabarit_autodiag.xlsx') )
        {
            return $this->get('igorw_file_serve.response_factory')->create( __ROOT_DIRECTORY__ . '/files/autodiag/Gabarit_autodiag.xlsx', 'application/pdf', $options);
        }
        else
        {
            // On envoi une 'flash' pour indiquer à l'utilisateur que le fichier n'existe pas: suppression manuelle sur le serveur
            $this->get('session')->getFlashBag()->add( ('danger') , 'Le document n\'existe plus sur le serveur.' );
    
            return $this->redirect( $this->generateUrl('hopitalnumerique_import_index') );
        }
    }

    /**
     * Download l'export excel.
     * 
     * @param Outil $outil [description]
     *
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function downloadExportAction(Outil $outil)
    {
        $options = array(
            'serve_filename' => 'Gabarit_autodiag.xlsx',
            'absolute_path'  => false,
            'inline'         => false,
        );
    
        if( file_exists( __ROOT_DIRECTORY__ . '/files/autodiag/Gabarit_autodiag.xlsx') )
        {
            //Récupèration du fichier excel
            $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject(__ROOT_DIRECTORY__ . '/files/autodiag/Gabarit_autodiag.xlsx');
        
            //Récupèration de la feuille Catégorie
            $sheetCategorie = $phpExcelObject->getSheetByName('categorie');

            //Récupèration de la feuille Catégorie
            $sheetChapitres = $phpExcelObject->getSheetByName('chapitres');

            //Récupèration de la feuille Catégorie
            $sheetQuestions = $phpExcelObject->getSheetByName('questions');

            //Nettoyage des données sur l'autodiag courant
            $categories = $this->get('hopital_numerique_import_excel.manager.categorie')->findBy(array('outil' => $outil));
            $chapitres  = $this->get('hopital_numerique_import_excel.manager.chapitre')->findBy(array('outil' => $outil));

            $arrayCategorie = array();

            $nbLigne = 2;
            foreach ($categories as $categorie) 
            {
                $sheetCategorie->setCellValueByColumnAndRow(0, $nbLigne, $categorie->getTitle());
                $sheetCategorie->setCellValueByColumnAndRow(1, $nbLigne, $categorie->getNote());

                $nbLigne++;
            }

            $nbLigne = 2;
            $nbLigneQuestion = 2;
            foreach ($chapitres as $chapitre) 
            {
                $sheetChapitres->setCellValueByColumnAndRow(0, $nbLigne, $chapitre->getCode());
                $sheetChapitres->setCellValueByColumnAndRow(1, $nbLigne, (!is_null($chapitre->getParent())) ? $chapitre->getParent()->getCode() : '' );
                $sheetChapitres->setCellValueByColumnAndRow(2, $nbLigne, (!is_null($chapitre->getIntro())) ? $chapitre->getIntro() : '' );
                $sheetChapitres->setCellValueByColumnAndRow(3, $nbLigne, (!is_null($chapitre->getTitle())) ? $chapitre->getTitle() : '' );
                $sheetChapitres->setCellValueByColumnAndRow(4, $nbLigne, (!is_null($chapitre->getDesc())) ? $chapitre->getDesc() : '' );
                $sheetChapitres->setCellValueByColumnAndRow(5, $nbLigne, (!is_null($chapitre->getNoteOptimale())) ? $chapitre->getNoteOptimale() : '' );
                $sheetChapitres->setCellValueByColumnAndRow(6, $nbLigne, (!is_null($chapitre->getNoteMinimale())) ? $chapitre->getNoteMinimale() : '' );
                $sheetChapitres->setCellValueByColumnAndRow(7, $nbLigne, (!is_null($chapitre->getSynthese())) ? $chapitre->getSynthese() : '' );
                $sheetChapitres->setCellValueByColumnAndRow(8, $nbLigne, (!is_null($chapitre->getLien())) ? $chapitre->getLien() : '' );
                $sheetChapitres->setCellValueByColumnAndRow(9, $nbLigne, (!is_null($chapitre->getDescriptionLien())) ? $chapitre->getDescriptionLien() : '' );

                $nbLigne++;

                foreach ($chapitre->getQuestions() as $question)
                {
                    $sheetQuestions->setCellValueByColumnAndRow(0, $nbLigneQuestion, $question->getChapitre()->getCode());
                    $sheetQuestions->setCellValueByColumnAndRow(1, $nbLigneQuestion, $question->getCode());
                    $sheetQuestions->setCellValueByColumnAndRow(2, $nbLigneQuestion, (!is_null($question->getIntro())) ? $question->getIntro() : '' );
                    $sheetQuestions->setCellValueByColumnAndRow(3, $nbLigneQuestion, (!is_null($question->getTexte())) ? $question->getTexte() : '' );
                    $sheetQuestions->setCellValueByColumnAndRow(4, $nbLigneQuestion, (!is_null($question->getType())) ? $question->getType()->getLibelle() : '' );
                    $sheetQuestions->setCellValueByColumnAndRow(5, $nbLigneQuestion, (!is_null($question->getOptions())) ? $question->getOptions() : '' );
                    $sheetQuestions->setCellValueByColumnAndRow(6, $nbLigneQuestion, ( !is_null( $question->getNoteMinimale() ) ) ? $question->getNoteMinimale() : ( (!is_null( $question->getSeuil() ) ) ? $question->getSeuil() : '' ) );
                    $sheetQuestions->setCellValueByColumnAndRow(7, $nbLigneQuestion, (!is_null($question->getSynthese())) ? $question->getSynthese() : '' );
                    $sheetQuestions->setCellValueByColumnAndRow(8, $nbLigneQuestion, (!is_null($question->getColored())) ? ($question->getColored() ? '1' : '0' ) : '' );
                    $sheetQuestions->setCellValueByColumnAndRow(9, $nbLigneQuestion, (!is_null($question->getInfoBulle())) ? $question->getInfoBulle() : '' );
                    $sheetQuestions->setCellValueByColumnAndRow(10, $nbLigneQuestion, (!is_null($question->getCategorie())) ? $question->getCategorie()->getTitle() : '' );
                    $sheetQuestions->setCellValueByColumnAndRow(11, $nbLigneQuestion, (!is_null($question->getPonderation())) ? $question->getPonderation() : '' );
                    $sheetQuestions->setCellValueByColumnAndRow(12, $nbLigneQuestion, (!is_null($question->getOrder())) ? $question->getOrder() : '' );
                    $sheetQuestions->setCellValueByColumnAndRow(13, $nbLigneQuestion, (!is_null($question->getLien())) ? $question->getLien() : '' );
                    $sheetQuestions->setCellValueByColumnAndRow(14, $nbLigneQuestion, (!is_null($question->getDescriptionLien())) ? $question->getDescriptionLien() : '' );

                    $nbLigneQuestion++;
                }
            }

            $writer   = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
            $response = $this->get('phpexcel')->createStreamedResponse($writer);

            // adding headers
            $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
            $response->headers->set('Content-Disposition', 'attachment;filename=Export-Autodiag-'. $outil->getId() .'.xls');
            $response->headers->set('Pragma', 'public');
            $response->headers->set('Cache-Control', 'maxage=1');

            return $response;
        }
        else
        {
            // On envoi une 'flash' pour indiquer à l'utilisateur que le fichier n'existe pas: suppression manuelle sur le serveur
            $this->get('session')->getFlashBag()->add( ('danger') , 'Le document n\'existe plus sur le serveur.' );
    
            return $this->redirect( $this->generateUrl('hopitalnumerique_import_index') );
        }
    }

    /**
     * Lecture et insert en base d'un fichier
     *
     * @param Outil $outil [description]
     *
     * @return [type]
     */
    public function readAndSaveAction(Request $request, Outil $outil)
    {
        $aujourdhui = new \DateTime();
        $aujourdhui = $aujourdhui->format('d_m_Y-H_m_s');
        $directory = __ROOT_DIRECTORY__ . '/web/medias/Autodiag';
        $filePath = '';

        $mimeTypeExcel = array(
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
            'application/vnd.ms-excel.sheet.macroEnabled.12',
            'application/vnd.ms-excel.template.macroEnabled.12',
            'application/vnd.ms-excel.addin.macroEnabled.12',
            'application/vnd.ms-excel.sheet.binary.macroEnabled.12'
        );

        foreach($request->files as $uploadedFile) 
        {
            if( !in_array( $uploadedFile->getMimeType(), $mimeTypeExcel))
            {
                $this->get('session')->getFlashBag()->add( 'danger', 'Fichier non conforme, votre fichier n\'est pas un document excel.' );
                return $this->redirect( $this->generateUrl('hopitalnumerique_import_index', array('id' => $outil->getId())) );
            }

            $name = 'autodiag_' . $outil->getId() . '_' . $aujourdhui . '.xlsx';
            $file = $uploadedFile->move($directory, $name);

            $filePath = $file->getPathName();
        }

        //Récupèration du fichier excel
        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject($filePath);

        //Récupèration de la feuille Catégorie
        $sheetCategorie = $phpExcelObject->getSheetByName('categorie');

        if(is_null($sheetCategorie))
        {
            $this->get('session')->getFlashBag()->add( 'danger', 'Fichier non conforme, veuillez ne pas renomer la feuille "categorie".' );
            return $this->redirect( $this->generateUrl('hopitalnumerique_import_index', array('id' => $outil->getId())) );
        }

        //Récupèration de la feuille Catégorie
        $sheetChapitres = $phpExcelObject->getSheetByName('chapitres');

        if(is_null($sheetCategorie))
        {
            $this->get('session')->getFlashBag()->add( 'danger', 'Fichier non conforme, veuillez ne pas renomer la feuille "chapitres".' );
            return $this->redirect( $this->generateUrl('hopitalnumerique_import_index', array('id' => $outil->getId())) );
        }

        //Récupèration de la feuille Catégorie
        $sheetQuestions = $phpExcelObject->getSheetByName('questions');

        if(is_null($sheetQuestions))
        {
            $this->get('session')->getFlashBag()->add( 'danger', 'Fichier non conforme, veuillez ne pas renomer la feuille "questions".' );
            return $this->redirect( $this->generateUrl('hopitalnumerique_import_index', array('id' => $outil->getId())) );
        }

        //Nettoyage des données sur l'autodiag courant
        $categories = $this->get('hopital_numerique_import_excel.manager.categorie')->findBy(array('outil' => $outil));
        $this->get('hopital_numerique_import_excel.manager.categorie')->delete($categories);

        $chapitres  = $this->get('hopital_numerique_import_excel.manager.chapitre')->findBy(array('outil' => $outil));
        $this->get('hopital_numerique_import_excel.manager.chapitre')->delete($chapitres);

        //Récupération et ajouts des données importées
        //Méthode de Thomas : Si erreur générée c'est que le fichier n'est pas valide
        try 
        {
            // ~~~ Catégorie
            $arrayCategorie = $this->get('hopital_numerique_import_excel.manager.importexcel')->getCategImported($sheetCategorie);
            $this->get('hopital_numerique_import_excel.manager.categorie')->saveCategImported($arrayCategorie, $outil);

            // ~~~ Chapitres
            $arrayChapitres = $this->get('hopital_numerique_import_excel.manager.importexcel')->getChapitreImported($sheetChapitres);
            $this->get('hopital_numerique_import_excel.manager.chapitre')->saveChapitreImported($arrayChapitres, $outil);

            // ~~~ Questions
            $arrayQuestions = $this->get('hopital_numerique_import_excel.manager.importexcel')->getQuestionsImported($sheetQuestions);
            $this->get('hopital_numerique_import_excel.manager.question')->saveQuestionImported($arrayQuestions, $outil);
            
        } catch (Exception $e) 
        {
            $this->get('session')->getFlashBag()->add( 'danger', 'Fichier non conforme, un ou plusieurs champ obligatoire n\'est ou ne sont pas renseigné(s).' );
            return $this->redirect( $this->generateUrl('hopitalnumerique_import_index', array('id' => $outil->getId())) );
        }

        
        // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
        $this->get('session')->getFlashBag()->add( 'info', 'Fichier importé avec succès.' );
        
        return $this->redirect( $this->generateUrl('hopitalnumerique_autodiag_outil') );
    }
}
