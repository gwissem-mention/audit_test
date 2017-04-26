<?php

namespace HopitalNumerique\StatBundle\Service;

use HopitalNumerique\AutodiagBundle\Repository\AutodiagRepository;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\DomaineBundle\Manager\DomaineManager;
use HopitalNumerique\ObjetBundle\Entity\Contenu;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\ObjetBundle\Manager\ContenuManager;
use HopitalNumerique\ObjetBundle\Manager\ObjetManager;
use HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire;
use HopitalNumerique\QuestionnaireBundle\Manager\QuestionnaireManager;
use HopitalNumerique\StatBundle\Entity\ErrorUrl;
use HopitalNumerique\StatBundle\Manager\ErrorUrlManager;
use HopitalNumerique\StatBundle\Repository\ErrorUrlRepository;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class UrlChecker
 */
class UrlChecker
{
    /**
     * @var ContenuManager $contentManager
     */
    private $contentManager;

    /**
     * @var ObjetManager $objectManager
     */
    private $objectManager;

    /**
     * @var DomaineManager $domainManager
     */
    private $domainManager;

    /**
     * @var ErrorUrlRepository $errorUrlRepository
     */
    private $errorUrlRepository;

    /**
     * @var ErrorUrlManager $errorUrlManager
     */
    private $errorUrlManager;

    /**
     * @var AutodiagRepository $autodiagRepository
     */
    private $autodiagRepository;

    /**
     * @var QuestionnaireManager $questionnaireManager
     */
    private $questionnaireManager;

    /**
     * UrlChecker constructor.
     *
     * @param ObjetManager         $objetManager
     * @param ContenuManager       $contenuManager
     * @param ErrorUrlRepository   $errorUrlRepository
     * @param DomaineManager       $domaineManager
     * @param ErrorUrlManager      $errorUrlManager
     * @param AutodiagRepository   $autodiagRepository
     * @param QuestionnaireManager $questionnaireManager
     */
    public function __construct(
        ObjetManager $objetManager,
        ContenuManager $contenuManager,
        ErrorUrlRepository $errorUrlRepository,
        DomaineManager $domaineManager,
        ErrorUrlManager $errorUrlManager,
        AutodiagRepository $autodiagRepository,
        QuestionnaireManager $questionnaireManager
    ) {
        $this->objectManager = $objetManager;
        $this->contentManager = $contenuManager;
        $this->errorUrlRepository = $errorUrlRepository;
        $this->domainManager = $domaineManager;
        $this->errorUrlManager = $errorUrlManager;
        $this->autodiagRepository = $autodiagRepository;
        $this->questionnaireManager = $questionnaireManager;
    }

    /**
     * Checks the HTTP code for each URL contained in an object or content.
     *
     * @return bool
     */
    public function check()
    {
        $this->errorUrlRepository->truncate();
        $results = $this->getAllUrlObjects();
        $domains = $this->domainManager->findAll();

        $urlsChecked = [];

        /** @var Domaine $domain */
        foreach ($domains as $domain) {
            $urlsChecked[$domain->getId()] = [];
            foreach ($results['urls'] as $categoryName => $categoryUrls) {
                foreach ($categoryUrls as $objectId => $objectUrls) {
                    foreach ($objectUrls as $contentId => $urls) {
                        foreach ($urls as $url) {
                            /** @var Objet $object */
                            $object = $this->objectManager->findOneBy(['id' => $objectId]);

                            if ($object != null) {
                                $domainObjects = $object->getDomaines();

                                /** @var Domaine $domainObject */
                                foreach ($domainObjects as $domainObject) {
                                    if ($domainObject->getId() === $domain->getId()) {
                                        if (strpos($url, 'http') === false &&
                                            strpos($url, 'www.') === false
                                        ) {
                                            $url = $domainObject->getUrl() . $url;
                                        }

                                        if (isset($urlsChecked[$domain->getId()][$url])) {
                                            continue;
                                        }

                                        $state = true;
                                        $handle = curl_init(str_replace(' ', '%20', $url));
                                        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
                                        curl_exec($handle);
                                        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);

                                        if ($httpCode >= 400 || $httpCode === 0) {
                                            $state = false;
                                        }

                                        $urlsChecked[$domain->getId()][$url] = $state;

                                        curl_close($handle);

                                        /** @var ErrorUrl $errorUrl */
                                        $errorUrl = $this->errorUrlManager->existErrorByUrl($url, $domain);
                                        $errorUrl->setLastCheckDate(new \DateTime());
                                        $errorUrl->setState($state);
                                        $errorUrl->setCode($httpCode);
                                        $errorUrl->setDomain($domain);
                                        $errorUrl->setObject($object);

                                        if ($contentId !== 'objet') {
                                            /** @var Contenu $content */
                                            $content = $this->contentManager->findOneById($contentId);
                                            $errorUrl->setContent($content);
                                        }

                                        $this->errorUrlManager->save($errorUrl);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return true;
    }

    /**
     * Returns all platform URLs grouped by type and object (id).
     *
     * @return array
     */
    private function getAllUrlObjects()
    {
        $urls = [
            'PUBLICATION' => [],
            'INFRADOC' => [],
            'ARTICLE' => [],
            'AUTODIAG' => [],
            'QUESTIONNAIRE' => [],
            'URL' => [],
            'FICHIER' => [],
        ];

        $objects = $this->objectManager->findAll();
        $objectsArray = [];

        /**
         * @var int
         * @var Objet $object
         */
        foreach ($objects as $key => $object) {
            $urls = $this->getUrlByObject($object, $urls);
            $objectsArray[$object->getId()] = $object;
        }

        return [
            'urls' => $urls,
            'objets' => $objectsArray,
            'oksByUrl' => $this->errorUrlManager->getStateByUrl(),
        ];
    }

    /**
     * Adds all URLs of the current object to the URLs array.
     *
     * @param Objet $object
     * @param       $urls
     *
     * @return array
     */
    private function getUrlByObject(Objet $object, $urls)
    {
        if (null !== $object->getPath()) {
                $url = '/' . $object->getWebPath(1);
                $urls['FICHIER'][$object->getId()]['objet'][] = $url;
        }
        if (null !== $object->getPath2()) {
                $url = '/' . $object->getWebPath(2);
                $urls['FICHIER'][$object->getId()]['objet'][] = $url;
        }

        $urls = $this->findLink($object->getSynthese(), $object->getId(), $urls);
        $urls = $this->findLink($object->getResume(), $object->getId(), $urls);

        foreach ($object->getContenus() as $key => $content) {
            $urls = $this->findLink($content->getContenu(), $object->getId(), $urls, true, $content->getId());
        }

        return $urls;
    }

    /**
     * @param      $text
     * @param      $objectId
     * @param      $urls
     * @param bool $isContent
     * @param int  $contentId
     *
     * @return array
     */
    private function findLink($text, $objectId, $urls, $isContent = false, $contentId = 0)
    {
        $crawler = new Crawler();
        $crawler->addHtmlContent($text);

        $links = $crawler
            ->filter('a')
            ->filter('*:not([href^="#"])')
            ->filter('*:not([href^="mailto"])')
            ->each(
                function (Crawler $node) {
                    return $node->attr('href');
                }
            )
        ;

        if (count($links) > 0) {
            foreach ($links as $link) {
                if (!is_null($link)) {
                    if (!array_key_exists($objectId, $urls['URL'])) {
                        $urls['URL'][$objectId] = [
                            'objet'    => [],
                            $contentId => [],
                        ];
                    }
                    if ($isContent) {
                        $urls['URL'][$objectId][$contentId][] = trim($link);
                    } else {
                        $urls['URL'][$objectId]['objet'][] = trim($link);
                    }
                }
            }
        }

        $pattern = '/\[([a-zA-Z]+)\:(\d+)\;(([a-zA-Z0-9àáâãäåçèéêëìíîïðòóôõöùúûüýÿ\&\'\`\"\<\>\!\:\?\,\;\.\%\#\@\_\-\+]| )*)\;([a-zA-Z0-9]+)\]/';
        preg_match_all($pattern, $text, $matches);

        // matches[0] tableau des chaines completes trouvée
        // matches[1] tableau des chaines avant les : trouvé
        // matches[2] tableau des ID après les : trouvé
        if (is_array($matches[1])) {
            foreach ($matches[1] as $key => $value) {
                switch ($value) {
                    case 'PUBLICATION':
                        /** @var Objet $object */
                        $object = $this->objectManager->findOneBy(['id' => $matches[2][$key]]);
                        if ($object) {
                            if (!array_key_exists($objectId, $urls['PUBLICATION'])) {
                                //Tableau
                                $urls['PUBLICATION'][$objectId] = [
                                    'objet' => [],
                                    $contentId => [],
                                ];
                            }
                            if ($isContent) {
                                $urls['PUBLICATION'][$objectId][$contentId][$object->getId()]
                                    = '/publication/' . $matches[2][$key] . '-' . $object->getAlias();
                            } else {
                                $urls['PUBLICATION'][$objectId]['objet'][$object->getId()]
                                    = '/publication/' . $matches[2][$key] . '-' . $object->getAlias();
                            }
                        }
                        break;
                    case 'INFRADOC':
                        /** @var Contenu $content */
                        $content = $this->contentManager->findOneBy(['id' => $matches[2][$key]]);
                        if ($content) {
                            if (!array_key_exists($objectId, $urls['INFRADOC'])) {
                                $urls['INFRADOC'][$objectId] = [
                                    'objet' => [],
                                    $contentId => [],
                                ];
                            }
                            $object = $content->getObjet();
                            if ($isContent) {
                                $urls['INFRADOC'][$objectId][$contentId][$content->getId()]
                                    = '/publication/' . $object->getId() . '-' . $object->getAlias()
                                      . '/'
                                      . $matches[2][$key] . '-' . $content->getAlias()
                                ;
                            } else {
                                $urls['INFRADOC'][$objectId]['objet'][$content->getId()]
                                    = '/publication/' . $object->getId() . '-' . $object->getAlias()
                                      . '/'
                                      . $matches[2][$key] . '-' . $content->getAlias()
                                ;
                            }
                        }
                        break;
                    case 'ARTICLE':
                        /** @var Objet $object */
                        $object = $this->objectManager->findOneBy(['id' => $matches[2][$key]]);
                        if ($object) {
                            if (!array_key_exists($objectId, $urls['ARTICLE'])) {
                                $urls['ARTICLE'][$objectId] = [
                                    'objet' => [],
                                    $contentId => [],
                                ];
                            }
                            if ($isContent) {
                                $urls['ARTICLE'][$objectId][$contentId][$object->getId()]
                                    = '/publication/article/' . $matches[2][$key] . '-' . $object->getAlias();
                            } else {
                                $urls['ARTICLE'][$objectId]['objet'][$object->getId()]
                                    = '/publication/article/' . $matches[2][$key] . '-' . $object->getAlias();
                            }
                        }
                        break;
                    case 'AUTODIAG':
                        //cas Outil
                        $outil = $this->autodiagRepository->findOneBy(['id' => $matches[2][$key]]);
                        if ($outil) {
                            if (!array_key_exists($objectId, $urls['AUTODIAG'])) {
                                $urls['AUTODIAG'][$objectId] = [
                                    'objet' => [],
                                    $contentId => [],
                                ];
                            }
                            if ($isContent) {
                                $urls['AUTODIAG'][$objectId][$contentId][$outil->getId()]
                                    = '/autodiagnostic/' . $outil->getId();
                            } else {
                                $urls['AUTODIAG'][$objectId]['objet'][$outil->getId()]
                                    = '/autodiagnostic/' . $outil->getId();
                            }
                        }
                        break;
                    case 'QUESTIONNAIRE':
                        /** @var Questionnaire $questionnaire */
                        $questionnaire = $this->questionnaireManager->findOneBy(['id' => $matches[2][$key]]);
                        if ($questionnaire) {
                            if (!array_key_exists($objectId, $urls['QUESTIONNAIRE'])) {
                                $urls['QUESTIONNAIRE'][$objectId] = [
                                    'objet' => [],
                                    $contentId => [],
                                ];
                            }
                            if ($isContent) {
                                $urls['QUESTIONNAIRE'][$objectId][$contentId][$questionnaire->getId()]
                                    = '/questionnaire/edit/' . $questionnaire->getId();
                            } else {
                                $urls['QUESTIONNAIRE'][$objectId]['objet'][$questionnaire->getId()]
                                    = '/questionnaire/edit/' . $questionnaire->getId();
                            }
                        }
                        break;
                }
            }
        }

        return $urls;
    }
}
