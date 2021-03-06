<?php

namespace HopitalNumerique\QuestionnaireBundle\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\EntityRepository;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\QuestionnaireBundle\Entity\Occurrence;
use HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire;

/**
 * ReponseRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ReponseRepository extends EntityRepository
{
    /**
     * Récupère les réponses pour l'utilisateur en fonction du questionnaire passés en param.
     *
     * @param $idQuestionnaire
     * @param $idUser
     * @param Occurrence|null $occurrence
     * @param null            $paramId
     *
     * @return Query
     */
    public function reponsesByQuestionnaireByUser(
        $idQuestionnaire,
        $idUser,
        Occurrence $occurrence = null,
        $paramId = null
    ) {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('reponse')
            ->from('\HopitalNumerique\QuestionnaireBundle\Entity\Reponse', 'reponse')
            ->leftJoin('reponse.question', 'question')
            ->leftJoin('reponse.user', 'user')
            ->where('user.id = :idUser')
            ->setParameter('idUser', $idUser)
        ;

        if ($paramId != null) {
            $qb->andWhere('reponse.paramId = :paramId')
                ->setParameter('paramId', $paramId)
            ;
        }

        $qb->leftJoin('reponse.reference', 'reference')
            ->innerJoin('question.questionnaire', 'questionnaire', 'WITH', 'questionnaire.id = :idQuestionnaire')
            ->setParameter('idQuestionnaire', $idQuestionnaire)
            ->leftJoin('question.typeQuestion', 'typeQuestion')
        ;

        if (null !== $occurrence) {
            $qb
                ->andWhere('reponse.occurrence = :occurrence')
                ->setParameter('occurrence', $occurrence)
            ;
        }

        $qb->addOrderBy('question.ordre', 'ASC');

        return $qb->getQuery();
    }

    /**
     * Récupère les réponses du questionnaire passés en param.
     *
     * @param $idQuestionnaire
     * @param null $paramId
     *
     * @return Query
     */
    public function reponsesByQuestionnaire($idQuestionnaire, $paramId = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('reponse')
            ->from('\HopitalNumerique\QuestionnaireBundle\Entity\Reponse', 'reponse')
            ->leftJoin('reponse.question', 'question')
        ;

        if ($paramId != null) {
            $qb->andWhere('reponse.paramId = :paramId')
                ->setParameter('paramId', $paramId)
            ;
        }

        $qb->leftJoin('reponse.reference', 'reference')
            ->innerJoin('question.questionnaire', 'questionnaire', 'WITH', 'questionnaire.id = :idQuestionnaire')
            ->setParameter('idQuestionnaire', $idQuestionnaire)
            ->leftJoin('question.typeQuestion', 'typeQuestion')
        ;

        return $qb->getQuery();
    }

    /**
     * Récupère les réponses pour l'utilisateur en fonction du questionnaire passés en param
     * pour les questions de type 'file'.
     *
     * @param $idQuestionnaire
     * @param $idUser
     * @param Occurrence|null $occurrence
     *
     * @return Query
     */
    public function reponsesByQuestionnaireByUserByFileQuestion(
        $idQuestionnaire,
        $idUser,
        Occurrence $occurrence = null
    ) {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('reponse')
            ->from('\HopitalNumerique\QuestionnaireBundle\Entity\Reponse', 'reponse')
            ->leftJoin('reponse.question', 'question')
            ->leftJoin('reponse.user', 'user')
            ->where('user.id = :idUser')
            ->setParameter('idUser', $idUser)
            ->innerJoin('question.questionnaire', 'questionnaire', 'WITH', 'questionnaire.id = :idQuestionnaire')
            ->setParameter('idQuestionnaire', $idQuestionnaire)
            ->innerJoin('question.typeQuestion', 'typeQuestion', 'WITH', 'typeQuestion.libelle = :libTypeQuestion')
            ->setParameter('libTypeQuestion', 'file')
        ;

        if (null !== $occurrence) {
            $qb
                ->andWhere('reponse.occurrence = :occurrence')
                ->setParameter('occurrence', $occurrence)
            ;
        }

        return $qb->getQuery();
    }

    /**
     * Récupère les réponses du questionnaire passés en param.
     *
     * @param $idQuestionnaire
     *
     * @return Query
     */
    public function getReponsesForQuestionnaireOrderByUser($idQuestionnaire)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('reponse')
            ->from('\HopitalNumerique\QuestionnaireBundle\Entity\Reponse', 'reponse')
            ->leftJoin('reponse.question', 'question')
            ->innerJoin('question.questionnaire', 'questionnaire', 'WITH', 'questionnaire.id = :idQuestionnaire')
            ->setParameter('idQuestionnaire', $idQuestionnaire)
        ;

        return $qb->getQuery();
    }

    /**
     * Récupère les réponses pour l'utilisateur en fonction des questionnaires passés en param.
     *
     * @return Query
     */
    public function reponseExiste()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('reponse.id as repId, user.id as userId, questionnaire.id as questId')
            ->from('\HopitalNumerique\QuestionnaireBundle\Entity\Reponse', 'reponse')
            ->leftJoin('reponse.question', 'question')
            ->leftJoin('reponse.user', 'user')
            ->leftJoin('question.questionnaire', 'questionnaire')
            ->groupBy('user.id, questionnaire.id')
        ;

        return $qb->getQuery();
    }

    /**
     * Affecte une occurrence à toutes les réponses d'un questionnaire répondu par un utilisateur.
     *
     * @param Occurrence    $occurrence    Occurrence
     * @param Questionnaire $questionnaire Questionnaire
     * @param User          $user          User
     */
    public function setOccurrenceByQuestionnaireAndUser(
        Occurrence $occurrence,
        Questionnaire $questionnaire,
        User $user
    ) {
        $query = $this->createQueryBuilder('reponse');

        $query
            ->update()
            ->set('reponse.occurrence', $occurrence->getId())
            ->where($query->expr()->in('reponse.question', $questionnaire->getQuestionIds()))
            ->andWhere('reponse.user = :user')
            ->setParameter('user', $user)
        ;

        $query->getQuery()->execute();
    }

    /**
     * @param User $user
     * @param Domaine[]|null $domains
     *
     * @return array
     */
    public function findByUserOrderedBySurveyNameAndResponseUpdate(User $user, $domains = null)
    {
        $qb = $this->createQueryBuilder('response')
            ->addSelect('question', 'entry', 'survey')
            ->join('response.user', 'user', Expr\Join::WITH, 'user.id = :userId')
            ->setParameter('userId', $user->getId())
            ->leftJoin('response.question', 'question')
            ->leftJoin('question.questionnaire', 'survey')
            ->leftJoin('response.occurrence', 'entry')
        ;

        if (null !== $domains) {
            $qb->join('survey.domaines', 'domains', Expr\Join::WITH, 'domains IN (:domains)')
                ->setParameter('domains', $domains)
                ->addSelect('domains')
            ;
        }

        return $qb->addOrderBy('survey.nom')
            ->addOrderBy('response.dateUpdate', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param User          $user
     * @param Questionnaire $survey
     *
     * @return array
     */
    public function findByUserAndSurvey(User $user, Questionnaire $survey)
    {
        return $this->createQueryBuilder('response')
            ->addSelect('question')
            ->addSelect('survey')
            ->join('response.user', 'user', Expr\Join::WITH, 'user = :userId')
            ->setParameter('userId', $user->getId())
            ->join('response.question', 'question')
            ->join('question.questionnaire', 'survey', Expr\Join::WITH, 'survey = :surveyId')
            ->setParameter('surveyId', $survey->getId())
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param User          $user
     * @param Questionnaire $survey
     * @param Occurrence    $entry
     *
     * @return array
     */
    public function findByUserAndSurveyAndEntry(User $user, Questionnaire $survey, Occurrence $entry)
    {
        return $this->createQueryBuilder('response')
            ->addSelect('question')
            ->addSelect('survey')
            ->join('response.user', 'user', Expr\Join::WITH, 'user = :userId')
            ->setParameter('userId', $user->getId())
            ->join('response.question', 'question')
            ->join('question.questionnaire', 'survey', Expr\Join::WITH, 'survey = :surveyId')
            ->setParameter('surveyId', $survey->getId())
            ->join('response.occurrence', 'entry', Expr\Join::WITH, 'entry = :entryId')
            ->setParameter('entryId', $entry->getId())
            ->getQuery()
            ->getResult()
        ;
    }
}
