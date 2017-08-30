<?php

namespace HopitalNumerique\NotificationBundle\Service;

/**
 * Class Notification.
 */
class Notification
{
    /**
     * @var NotificationProviderAbstract[] $providers
     */
    protected $providers = [];

    /**
     * @param NotificationProviderAbstract $provider Notification provider
     * @param string                       $code     Notification provider code
     *
     * @throws \Exception
     */
    public function addProvider(NotificationProviderAbstract $provider, $code)
    {
        if (array_key_exists($code, $this->providers)) {
            throw new \Exception(sprintf("Provider already supplied for key '%s'", $code));
        }
        $this->providers[$code] = $provider;
    }

    /**
     * Returns existing notification providers.
     *
     * @param string|null $section Providers section (if omitted all providers will be returned)
     *
     * @return NotificationProviderAbstract[]
     */
    public function getProviders($section = null)
    {
        if (null === $section) {
            return $this->providers;
        } else {
            $return = [];
            foreach ($this->providers as $provider) {
                if ($provider::getSectionCode() == $section) {
                    $return[$provider::getNotificationCode()] = $provider;
                }
            }
            return $return;
        }
    }

    /**
     * Returns providers organized by section.
     *
     * @return NotificationProviderAbstract[]
     */
    public function getStructuredProviders()
    {
        $return = [];
        foreach ($this->providers as $provider) {
            $section = $provider::getSectionCode();
            if (!array_key_exists($section, $return)) {
                $return[$section] = [];
            }
            $return[$section][$provider::getNotificationCode()] = $provider;
        }
        return $return;
    }

    /**
     * @param string $code Notification provider code
     *
     * @return NotificationProviderAbstract|null
     */
    public function getProvider($code)
    {
        return array_key_exists($code, $this->providers) ? $this->providers[$code] : null;
    }
}
