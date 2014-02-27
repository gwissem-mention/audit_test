<?php

namespace HopitalNumerique\QuestionnaireBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\QuestionnaireBundle\Entity\Reponse;

class ReponseController extends Controller
{
    /**
     * Téléchargement des fichiers attaché au questionnaire expert.
     *
     * @param int $id Id de la réponse du fichier à télécharger
     */
    public function dowloadReponseAction( Reponse $reponse )
    {
        $option = $this->get('hopitalnumerique_questionnaire.manager.reponse')->download($reponse);
        
        return $this->get('igorw_file_serve.response_factory')->create( $this->get('hopitalnumerique_questionnaire.manager.question')->getUploadRootDir($reponse->getQuestion()->getQuestionnaire()->getNomMinifie()) . '/'. $reponse->getReponse(), 'application/pdf', $option);
    }
}
