<?php

namespace HopitalNumerique\ForumBundle\Manager;

use Doctrine\ORM\EntityManager;
use HopitalNumerique\ForumBundle\Entity\Forum;
use HopitalNumerique\ForumBundle\Entity\Topic;
use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

use HopitalNumerique\UserBundle\Manager\UserManager;
use HopitalNumerique\DomaineBundle\Manager\DomaineManager;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;

/**
 * Manager de l'entité Topic.
 */
class TopicManager extends BaseManager
{
    protected $class = '\HopitalNumerique\ForumBundle\Entity\Topic';
    protected $_userManager;
    protected $_domaineManager;
    protected $_referenceManager;

    /**
     * TopicManager constructor.
     *
     * @param EntityManager    $entityManager
     * @param UserManager      $userManager
     * @param DomaineManager   $domaineManager
     * @param ReferenceManager $referenceManager
     */
    public function __construct(
        EntityManager $entityManager,
        UserManager $userManager,
        DomaineManager $domaineManager,
        ReferenceManager $referenceManager
    ) {
        parent::__construct($entityManager);

        $this->_userManager      = $userManager;
        $this->_domaineManager   = $domaineManager;
        $this->_referenceManager = $referenceManager;
    }

    /**
     * @param $retour
     * @param $elem
     *
     * @return array|bool
     */
    private function getChilds(&$retour, $elem)
    {
        if (isset($elem['childs']) && count($elem['childs'])) {
            $childs = [];
            foreach ($elem["childs"] as $key => $one) {
                $childs[$one] = $retour[$one];
                $petitsEnfants = $this->getChilds($retour, $childs[$one]);
                if ($petitsEnfants) {
                    $childs[$one]['childs'] = $petitsEnfants;
                    unset($retour[$one]);
                } else {
                    unset($retour[$one]);
                }
            }

            return $childs;
        } else {
            return false;
        }
    }

    /**
     * Récupère les derniers topics commentés par type de forum
     *
     * @param $id int
     * @param $limit int
     *
     * @return Topic[] Liste des topics
     */
    public function getLastTopicsForum($id, $limit = null, $role = null)
    {
        return $this->getRepository()->getLastTopicsForum($id, $limit, $role)->getQuery()->getResult();
    }

    /**
     * Récupère les derniers topics commentés par type de forum et affiche les epinglés en premier
     * si le nombre d'epinglés est inferieur a la limit alors on recupere les non epinglés
     *
     * @param $id
     * @param $limit
     * @param $idCat
     *
     * @return Topic[] Liste des topics
     */
    public function getLastTopicsForumEpingle($id, $limit = null, $idCat)
    {
        $topicEpingle = $this->getRepository()->getLastTopicsForumEpingle($id, $limit, true, $idCat)->getQuery()
                             ->getResult();

        if ($limit > count($topicEpingle)) {
            $topic        = $this->getRepository()->getLastTopicsForumEpingle(
                $id,
                $limit - count($topicEpingle),
                false,
                $idCat
            )->getQuery()->getResult();
            $topicEpingle = array_merge($topicEpingle, $topic);
        }

        return $topicEpingle;
    }

    /**
     * Retourne les topics d'un forum.
     *
     * @param \HopitalNumerique\ForumBundle\Entity\Forum $forum Forum
     * @return array<\HopitalNumerique\ForumBundle\Entity\Topic> Topics
     */
    public function findByForum(Forum $forum)
    {
        return $this->getRepository()->findByForum($forum);
    }

    /**
     * Retourne le nombre de fils d'un forum.
     *
     * @param integer $forumId ID du forum
     * @return integer Nombre
     */
    public function getCountForForum($forumId)
    {
        return $this->getRepository()->getCountForForum($forumId);
    }

    /**
     * Retourne un tableau de topics ordonné par date de création du dernier post
     *
     * @param array $topicsByCategories Tableau des topics groupés par catégorie
     *
     * @return array
     */
    public function formatTopics($topicsByCategories)
    {
        $topics = [];

        foreach ($topicsByCategories as $topicsByCategory) {
            if (count($topicsByCategory) > 0) {
                foreach ($topicsByCategory['topics'] as $value) {
                    $topics[] = [
                        'topic'        => $value,
                        'categoryId'   => $topicsByCategory['categoryId'],
                        'categoryName' => $topicsByCategory['categoryName'],
                        'forumName'    => $topicsByCategory['forumName'],
                    ];
                }
            }
        }

        usort($topics, function ($a, $b) {
            if ($a['topic']->isSticky()) {
                return false;
            } elseif ($b['topic']->isSticky()) {
                return true;
            }

            return $a['topic']->getLastPost()->getCreatedDate() < $b['topic']->getLastPost()->getCreatedDate();
        });

        return array_slice($topics, 0, 4);
    }
}
