<?php
namespace HopitalNumerique\QuestionnaireBundle\Twig;

class QuestionnaireExtension extends \Twig_Extension
{
    /**
     * Retourne la liste des filtres custom pour cette extension
     *
     * @return array
     */
    public function getFilters()
    {
        return array(
            'hadReponseForQuestionnaire' => new \Twig_Filter_Method($this, 'hadReponseForQuestionnaire')
        );
    }

    /**
     * Vérifie que l'utilisateur a bien renseignés certains champs
     *
     * @param Reponses[] $reponses         Listes des réponses
     * @param int        $questionnaireId  Questionnaire à vérifier
     * @param int        $paramId          Clé étrangère à vérifier
     *
     * @return boolean
     */
    public function hadReponseForQuestionnaire( $reponses, $questionnaireId ,$paramId = 0 )
    {
        //Pour l'ensemble des réponses du filtres on vérifie qu'il existe une réponse pour l'un des param
        foreach ($reponses as $reponse)
        {
            //Le quetionnaire
            if($questionnaireId === $reponse->getQuestion()->getQuestionnaire()->getId())
            {
                //Le param si il y en a un à vérifier
                if(0 !== $paramId)
                {
                    if($reponse->getParamId() === $paramId)
                    {
                        return true;
                    }
                }
                else
                {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Retourne le nom de l'extension : utilisé dans les services
     *
     * @return string
     */
    public function getName()
    {
        return 'questionnaire_extension';
    }
}