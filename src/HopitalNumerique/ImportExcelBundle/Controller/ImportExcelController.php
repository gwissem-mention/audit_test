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
            return $this->get('igorw_file_serve.response_factory')->create( __ROOT_DIRECTORY__ . '/files/autodiag/Gabarit_autodiag.xlsx', 'application/vnd.ms-excel', $options);
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
        if( file_exists( __ROOT_DIRECTORY__ . '/files/autodiag/Gabarit_autodiag.xlsx') )
        {
            //Récupèration du fichier excel
            $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject(__ROOT_DIRECTORY__ . '/files/autodiag/Gabarit_autodiag.xlsx');
        
            //Récupèration de la feuille Catégorie
            $sheetCategorie = $phpExcelObject->getSheetByName('categorie');

            //Récupèration de la feuille Chapitre
            $sheetChapitres = $phpExcelObject->getSheetByName('chapitres');

            //Récupèration de la feuille Question
            $sheetQuestions = $phpExcelObject->getSheetByName('questions');

            //Récupèration de la feuille Résultat
            $sheetResultats = $phpExcelObject->getSheetByName('resultats');

            //Récupèration de la feuille Réponse
            $sheetReponses  = $phpExcelObject->getSheetByName('reponses');

            //Récupèration de la feuille Synthese
            $sheetSynthese  = $phpExcelObject->getSheetByName('syntheses');

            //Nettoyage des données sur l'autodiag courant
            $categories = $this->get('hopital_numerique_import_excel.manager.categorie')->findBy(array('outil' => $outil));
            $chapitres  = $this->get('hopital_numerique_import_excel.manager.chapitre')->findBy(array('outil' => $outil));
            $resultats  = $this->get('hopital_numerique_import_excel.manager.resultat')->findBy(array('outil' => $outil));

            $nbLigne = 2;
            foreach ($categories as $categorie) 
            {
                $sheetCategorie->setCellValueByColumnAndRow(0, $nbLigne, $categorie->getId());
                $sheetCategorie->setCellValueByColumnAndRow(1, $nbLigne, $categorie->getTitle());
                $sheetCategorie->setCellValueByColumnAndRow(2, $nbLigne, $categorie->getNote());

                $nbLigne++;
            }

            $nbLigne = 2;
            $nbLigneQuestion = 2;
            foreach ($chapitres as $chapitre)
            {
                $sheetChapitres->setCellValueByColumnAndRow(0, $nbLigne, $chapitre->getId());
                $sheetChapitres->setCellValueByColumnAndRow(1, $nbLigne, $chapitre->getCode());
                $sheetChapitres->setCellValueByColumnAndRow(2, $nbLigne, (!is_null($chapitre->getParent())) ? $chapitre->getParent()->getId() : '' );
                $sheetChapitres->setCellValueByColumnAndRow(3, $nbLigne, (!is_null($chapitre->getParent())) ? $chapitre->getParent()->getCode() : '' );
                $sheetChapitres->setCellValueByColumnAndRow(4, $nbLigne, (!is_null($chapitre->getIntro())) ? $chapitre->getIntro() : '' );
                $sheetChapitres->setCellValueByColumnAndRow(5, $nbLigne, (!is_null($chapitre->getTitle())) ? $chapitre->getTitle() : '' );
                $sheetChapitres->setCellValueByColumnAndRow(6, $nbLigne, (!is_null($chapitre->getDesc())) ? $chapitre->getDesc() : '' );
                $sheetChapitres->setCellValueByColumnAndRow(7, $nbLigne, (!is_null($chapitre->getNoteOptimale())) ? $chapitre->getNoteOptimale() : '' );
                $sheetChapitres->setCellValueByColumnAndRow(8, $nbLigne, (!is_null($chapitre->getNoteMinimale())) ? $chapitre->getNoteMinimale() : '' );
                $sheetChapitres->setCellValueByColumnAndRow(9, $nbLigne, (!is_null($chapitre->getSynthese())) ? $chapitre->getSynthese() : '' );
                $sheetChapitres->setCellValueByColumnAndRow(10, $nbLigne, (!is_null($chapitre->getLien())) ? $chapitre->getLien() : '' );
                $sheetChapitres->setCellValueByColumnAndRow(11, $nbLigne, (!is_null($chapitre->getDescriptionLien())) ? $chapitre->getDescriptionLien() : '' );
                $sheetChapitres->setCellValueByColumnAndRow(12, $nbLigne, (false === $chapitre->getAffichageRestitutionBarre() ? '0' : '1'));
                $sheetChapitres->setCellValueByColumnAndRow(13, $nbLigne, (false === $chapitre->getAffichageRestitutionRadar() ? '0' : '1'));
                $nbLigne++;

                foreach ($chapitre->getQuestions() as $question)
                {
                    $sheetQuestions->setCellValueByColumnAndRow(0, $nbLigneQuestion, $question->getId());
                    $sheetQuestions->setCellValueByColumnAndRow(1, $nbLigneQuestion, $question->getChapitre()->getId());
                    $sheetQuestions->setCellValueByColumnAndRow(2, $nbLigneQuestion, $question->getChapitre()->getCode());
                    $sheetQuestions->setCellValueByColumnAndRow(3, $nbLigneQuestion, $question->getCode());
                    $sheetQuestions->setCellValueByColumnAndRow(4, $nbLigneQuestion, (!is_null($question->getIntro())) ? $question->getIntro() : '' );
                    $sheetQuestions->setCellValueByColumnAndRow(5, $nbLigneQuestion, (!is_null($question->getTexte())) ? $question->getTexte() : '' );
                    $sheetQuestions->setCellValueByColumnAndRow(6, $nbLigneQuestion, (!is_null($question->getType())) ? $question->getType()->getLibelle() : '' );
                    $sheetQuestions->setCellValueByColumnAndRow(7, $nbLigneQuestion, (!is_null($question->getOptions())) ? $question->getOptions() : '' );
                    $sheetQuestions->setCellValueByColumnAndRow(8, $nbLigneQuestion, ( !is_null( $question->getNoteMinimale() ) ) ? $question->getNoteMinimale() : ( (!is_null( $question->getSeuil() ) ) ? $question->getSeuil() : '' ) );
                    $sheetQuestions->setCellValueByColumnAndRow(9, $nbLigneQuestion, (!is_null($question->getSynthese())) ? $question->getSynthese() : '' );
                    $sheetQuestions->setCellValueByColumnAndRow(10, $nbLigneQuestion, (!is_null($question->getColored())) ? $question->getColored() : '' );
                    $sheetQuestions->setCellValueByColumnAndRow(11, $nbLigneQuestion, (!is_null($question->getInfoBulle())) ? $question->getInfoBulle() : '' );
                    $sheetQuestions->setCellValueByColumnAndRow(12, $nbLigneQuestion, (!is_null($question->getCategorie())) ? $question->getCategorie()->getTitle() : '' );
                    $sheetQuestions->setCellValueByColumnAndRow(13, $nbLigneQuestion, (!is_null($question->getPonderation())) ? $question->getPonderation() : '' );
                    $sheetQuestions->setCellValueByColumnAndRow(14, $nbLigneQuestion, (!is_null($question->getOrder())) ? $question->getOrder() : '' );
                    $sheetQuestions->setCellValueByColumnAndRow(15, $nbLigneQuestion, (!is_null($question->getLien())) ? $question->getLien() : '' );
                    $sheetQuestions->setCellValueByColumnAndRow(16, $nbLigneQuestion, (!is_null($question->getDescriptionLien())) ? $question->getDescriptionLien() : '' );

                    $nbLigneQuestion++;
                }
            }

            $nbLigne = 2;
            $nbLigneReponse = 2;
            $nbLigneSynth   = 2;
            foreach ($resultats as $resultat)
            {
                $sheetResultats->setCellValueByColumnAndRow(0, $nbLigne, $resultat->getId());
                $sheetResultats->setCellValueByColumnAndRow(1, $nbLigne, $resultat->getName());
                $sheetResultats->setCellValueByColumnAndRow(2, $nbLigne, (is_null($resultat->getDateLastSave())) ? '' : $resultat->getDateLastSave()->format('Y-m-d'));
                $sheetResultats->setCellValueByColumnAndRow(4, $nbLigne, (is_null($resultat->getDateCreation())) ? '' : $resultat->getDateCreation()->format('Y-m-d'));
                $sheetResultats->setCellValueByColumnAndRow(3, $nbLigne, (is_null($resultat->getDateValidation())) ? '' : $resultat->getDateValidation()->format('Y-m-d'));
                $sheetResultats->setCellValueByColumnAndRow(5, $nbLigne, $resultat->getTauxRemplissage());
                $sheetResultats->setCellValueByColumnAndRow(6, $nbLigne, $resultat->getPdf());
                $sheetResultats->setCellValueByColumnAndRow(7, $nbLigne, $resultat->getRemarque());
                $sheetResultats->setCellValueByColumnAndRow(8, $nbLigne, (is_null($resultat->getStatut())) ? '' : $resultat->getStatut()->getId());
                $sheetResultats->setCellValueByColumnAndRow(9, $nbLigne, (is_null($resultat->getOutil())) ? '' : $resultat->getOutil()->getId());
                $sheetResultats->setCellValueByColumnAndRow(10, $nbLigne, (is_null($resultat->getUser())) ? '' : $resultat->getUser()->getId());
                $sheetResultats->setCellValueByColumnAndRow(11, $nbLigne, $resultat->getSynthese() ? 'Oui' : 'Non');

                $nbLigne++;

                $reponses = $this->get('hopital_numerique_import_excel.manager.reponse')->findBy(array('resultat' => $resultat->getId()));

                foreach ($reponses as $reponse)
                {
                    $sheetReponses->setCellValueByColumnAndRow(0, $nbLigneReponse, $reponse->getId());
                    $sheetReponses->setCellValueByColumnAndRow(1, $nbLigneReponse, $reponse->getValue());
                    $sheetReponses->setCellValueByColumnAndRow(2, $nbLigneReponse, $reponse->getRemarque());
                    $sheetReponses->setCellValueByColumnAndRow(3, (is_null($reponse->getResultat())) ? '' : $nbLigneReponse, $reponse->getResultat()->getId());
                    $sheetReponses->setCellValueByColumnAndRow(4, (is_null($reponse->getQuestion())) ? '' : $nbLigneReponse, $reponse->getQuestion()->getId());

                    $nbLigneReponse++;
                }

                foreach ($resultat->getResultats() as $resultSynth) 
                {
                    $sheetSynthese->setCellValueByColumnAndRow(0, $nbLigneSynth, $resultat->getId() );
                    $sheetSynthese->setCellValueByColumnAndRow(1, $nbLigneSynth, $resultSynth->getId() );

                    $nbLigneSynth++;
                }
            }
            
            $phpExcelObject = $this->fillSheetsProcessForExport($phpExcelObject, $outil);
            
            

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
     * Remplit l'onglet Process d'un fichier Excel.
     * 
     * @param \PHPExcel $phpExcel Fichier Excel
     * @param \HopitalNumerique\AutodiagBundle\Entity\Outil $outil Autodiag
     * @return \PHPExcel Fichier Excel
     */
    private function fillSheetsProcessForExport(\PHPExcel $phpExcel, Outil $outil)
    {
        $sheetProcess = $phpExcel->getSheetByName('process');
        $sheetProcessChapitre = $phpExcel->getSheetByName('process_chapitre');
        
        $numeroLigneProcess = 2;
        $numeroLigneProcessChapitre = 2;
        foreach ($outil->getProcess() as $outilProcess)
        {
            $sheetProcess->setCellValueByColumnAndRow(0, $numeroLigneProcess, $outilProcess->getId());
            $sheetProcess->setCellValueByColumnAndRow(1, $numeroLigneProcess, $outilProcess->getLibelle());
            $sheetProcess->setCellValueByColumnAndRow(2, $numeroLigneProcess, $outilProcess->getOrder());
            $numeroLigneProcess++;
            
            foreach ($outilProcess->getProcessChapitres() as $processChapitre)
            {
                $sheetProcessChapitre->setCellValueByColumnAndRow(0, $numeroLigneProcessChapitre, $outilProcess->getId());
                $sheetProcessChapitre->setCellValueByColumnAndRow(1, $numeroLigneProcessChapitre, $processChapitre->getChapitre()->getId());
                $sheetProcessChapitre->setCellValueByColumnAndRow(2, $numeroLigneProcessChapitre, $processChapitre->getOrder());
                $numeroLigneProcessChapitre++;
            }
        }

        return $phpExcel;
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
        //Gros import, fait sauter les limites de tailles/temps
        ini_set("memory_limit","512M");
        ini_set('max_execution_time', 0);
        
        $outil->setDernierImportUser($this->getUser());
        $outil->setDernierImportDate(new \DateTime());
        $this->container->get('hopitalnumerique_autodiag.manager.outil')->save($outil);
        

        $aujourdhui = new \DateTime();
        $aujourdhui = $aujourdhui->format('d_m_Y-H_m_s');
        $directory = __ROOT_DIRECTORY__ . '/web/medias/Autodiag';
        $filePath = '';

        $mimeTypeExcel = array(
            'application/vnd.ms-office',
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
            $this->get('session')->getFlashBag()->add( 'danger', 'Fichier non conforme, veuillez ne pas renommer la feuille "categorie".' );
            return $this->redirect( $this->generateUrl('hopitalnumerique_import_index', array('id' => $outil->getId())) );
        }

        //Récupèration de la feuille Chapitre
        $sheetChapitres = $phpExcelObject->getSheetByName('chapitres');

        if(is_null($sheetChapitres))
        {
            $this->get('session')->getFlashBag()->add( 'danger', 'Fichier non conforme, veuillez ne pas renommer la feuille "chapitres".' );
            return $this->redirect( $this->generateUrl('hopitalnumerique_import_index', array('id' => $outil->getId())) );
        }

        //Récupèration de la feuille Question
        $sheetQuestions = $phpExcelObject->getSheetByName('questions');

        if(is_null($sheetQuestions))
        {
            $this->get('session')->getFlashBag()->add( 'danger', 'Fichier non conforme, veuillez ne pas renommer la feuille "questions".' );
            return $this->redirect( $this->generateUrl('hopitalnumerique_import_index', array('id' => $outil->getId())) );
        }

        //Récupèration de la feuille Resultat
        $sheetResultats = $phpExcelObject->getSheetByName('resultats');

        //Récupèration de la feuille Réponses
        $sheetReponses  = $phpExcelObject->getSheetByName('reponses');

        //Récupèration de la feuille Synthese
        $sheetSynthese  = $phpExcelObject->getSheetByName('syntheses');

        //Nettoyage des données sur l'autodiag courant
        $categories = $this->get('hopital_numerique_import_excel.manager.categorie')->findBy(array('outil' => $outil));
        $this->get('hopital_numerique_import_excel.manager.categorie')->delete($categories);

        $chapitres  = $this->get('hopital_numerique_import_excel.manager.chapitre')->findBy(array('outil' => $outil));
        $this->get('hopital_numerique_import_excel.manager.chapitre')->delete($chapitres);

        $resultats  = $this->get('hopitalnumerique_autodiag.manager.resultat')->findBy(array('outil' => $outil));
        $this->get('hopitalnumerique_autodiag.manager.resultat')->delete($resultats);

        $this->get('hopitalnumerique_autodiag.manager.process')->delete($this->get('hopitalnumerique_autodiag.manager.process')->findBy(array('outil' => $outil)));

        //Récupération et ajouts des données importées
        //Méthode de Thomas : Si erreur générée c'est que le fichier n'est pas valide
        try 
        {
            // ~~~ Catégorie
            $arrayCategorie = $this->get('hopital_numerique_import_excel.manager.importexcel')->getCategImported($sheetCategorie);
            $this->get('hopital_numerique_import_excel.manager.categorie')->saveCategImported($arrayCategorie, $outil);

            // ~~~ Chapitres
            $arrayChapitres = $this->get('hopital_numerique_import_excel.manager.importexcel')->getChapitreImported($sheetChapitres);
            $arrayIdChapitres = $this->get('hopital_numerique_import_excel.manager.chapitre')->saveChapitreImported($arrayChapitres, $outil);

            // ~~~ Questions
            $arrayQuestions   = $this->get('hopital_numerique_import_excel.manager.importexcel')->getQuestionsImported($sheetQuestions);
            $arrayIdQuestions = $this->get('hopital_numerique_import_excel.manager.question')->saveQuestionImported($arrayQuestions, $outil, $arrayIdChapitres);

            // ~~~ Resultats
            if(!is_null($sheetResultats))
            {
                $arrayResultats   = $this->get('hopital_numerique_import_excel.manager.importexcel')->getResultatImported($sheetResultats);
                $arraySyntheses   = $this->get('hopital_numerique_import_excel.manager.importexcel')->getSyntheseImported($sheetSynthese);
                $arrayIdResultats = $this->get('hopital_numerique_import_excel.manager.resultat')->saveResultatImported($arrayResultats, $arraySyntheses, $outil);

                // ~~~ Reponses
                if(!is_null($sheetReponses))
                {
                    $arrayReponses  = $this->get('hopital_numerique_import_excel.manager.importexcel')->getReponsesImported($sheetReponses);
                    $this->get('hopital_numerique_import_excel.manager.reponse')->saveReponseImported($arrayReponses, $outil, $arrayIdResultats, $arrayIdQuestions);
                }
            }
            
            $this->container->get('hopital_numerique_import_excel.manager.process')->importSheetsProcess($phpExcelObject, $outil, $arrayIdChapitres);
        }
        catch (\Exception $e)
        {
            $this->get('session')->getFlashBag()->add( 'danger', 'Fichier non conforme, un ou plusieurs champ obligatoire n\'est ou ne sont pas renseigné(s).' );
            return $this->redirect( $this->generateUrl('hopitalnumerique_import_index', array('id' => $outil->getId())) );
        }

        // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
        $this->get('session')->getFlashBag()->add( 'info', 'Fichier importé avec succès.' );

        return $this->redirect( $this->generateUrl('hopitalnumerique_autodiag_outil') );
    }
}
