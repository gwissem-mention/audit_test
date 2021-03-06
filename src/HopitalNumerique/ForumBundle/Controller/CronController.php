<?php

namespace HopitalNumerique\ForumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class CronController extends Controller
{
    /**
     * Cron de suppression des topics.
     */
    public function cronAction($id)
    {
        if ($id == '34QHWURJYILOLP24FKGMEROX2EZFQSOSUXVRT') {
            //---Topic
            //Récupération de tout les topics supprimé de manière soft
            $topics = $this->get('hopitalnumerique_forum.manager.topic')->findBy(['isDeleted' => true]);

            //Suppression des posts liés aux topics à delete
            $postsToDelete = [];
            $topicToDelete = [];
            foreach ($topics as $topic) {
                foreach ($topic->getPosts() as $post) {
                    $postsToDelete[$post->getId()] = $post;
                    $this->get('hopitalnumerique_forum.service.logger.cronlogger')->addLog('Post ' . $post->getId() . ' supprimé.');
                }

                $topicToDelete[$topic->getId()] = $topic;

                $this->get('hopitalnumerique_forum.service.logger.cronlogger')->addLog('Topic ' . $topic->getId() . ' - ' . $topic->getTitle() . ' supprimé.');
            }
            $this->get('hopitalnumerique_forum.manager.post')->delete($postsToDelete);

            //---Post
            //Récupération de tout les posts supprimé de manière soft
            $posts = $this->get('hopitalnumerique_forum.manager.post')->findBy(['isDeleted' => true]);
            //Suppression des topics si il n'y a plus de posts de dedans
            $topicsTemp = [];
            foreach ($posts as $post) {
                $topicTemp = $post->getTopic();
                //Dans le cas où le topic a déjà été supprimé
                if (is_null($topicTemp)) {
                    continue;
                }
                $topicsTemp[$topicTemp->getId()] = $topicTemp;
                $this->get('hopitalnumerique_forum.service.logger.cronlogger')->addLog('Post ' . $post->getId() . ' supprimé.');
            }

            //Suppression des posts
            $this->get('hopitalnumerique_forum.manager.post')->delete($posts);

            foreach ($topicsTemp as $topic) {
                if (count($topic->getPosts()) == 0 && !array_key_exists($topic->getId(), $topicToDelete)) {
                    $topicToDelete[] = $topic;
                    $this->get('hopitalnumerique_forum.service.logger.cronlogger')->addLog('Topic ' . $topic->getId() . ' - ' . $topic->getTitle() . ' supprimé.');
                }
            }

            //---Abonnement
            //Suppression des abonnements liés au topics supprimés
            $this->get('hopitalnumerique_forum.manager.subscription')->deleteSubscriptionByTopicsArray($topicToDelete);

            //Suppression des topics pour finir
            $this->get('hopitalnumerique_forum.manager.topic')->delete($topicToDelete);

            return new Response($this->get('hopitalnumerique_forum.service.logger.cronlogger')->getHtml() . '<p>Fin du traitement : OK.</p>');
        }

        return new Response('Clef invalide.');
    }
}
