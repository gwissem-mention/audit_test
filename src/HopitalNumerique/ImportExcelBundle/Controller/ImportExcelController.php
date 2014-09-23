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
            $this->get('hopital_numerique_import_excel.manager.question')->saveQuestionImported($arrayQuestions, $outil));
            
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
