<?php

namespace HopitalNumerique\StatBundle\Manager;

use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\StatBundle\Entity\ErrorUrl;
use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité ErrorUrl.
 */
class ErrorUrlManager extends BaseManager
{
    protected $class = 'HopitalNumerique\StatBundle\Entity\ErrorUrl';

    /**
     * @param $url string
     * @param Domaine $domain
     *
     * @return ErrorUrl
     */
    public function existErrorByUrl($url, Domaine $domain)
    {
        /** @var ErrorUrl $errorUrl */
        $errorUrl = is_null($this->findOneBy([
            'checkedUrl' => $url,
            'domain' => $domain,
        ]))
            ? $this->createEmpty()
            : $this->findOneBy(
                ['checkedUrl' => $url]
            );

        if (is_null($errorUrl->getId()) || $errorUrl->getId() == 0) {
            $errorUrl->setCheckedUrl($url);
        }

        return $errorUrl;
    }

    /**
     * @return array
     */
    public function getStateByUrl()
    {
        $errorUrls = $this->getRepository()->findAll();

        $states = [];

        /** @var ErrorUrl $errorUrl */
        foreach ($errorUrls as $errorUrl) {
            $states[$errorUrl->getCheckedUrl()] = $errorUrl->getState();
        }

        return $states;
    }
}
