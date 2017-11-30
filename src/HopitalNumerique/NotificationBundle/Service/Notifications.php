<?php

namespace HopitalNumerique\NotificationBundle\Service;

/**
 * Class Notifications.
 */
class Notifications
{
    const NOTIFICATION_ALLOWED_FREQUENCIES = ['daily', 'weekly', 'straight', 'off'];

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

        if (!in_array($provider::getDefaultFrequency(), self::NOTIFICATION_ALLOWED_FREQUENCIES)) {
            throw new \Exception(sprintf(
                "Provider '%s' default frequency is invalid, one of ['%s'] expected",
                implode("', '", self::NOTIFICATION_ALLOWED_FREQUENCIES),
                $code
            ));
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
     * @return NotificationProviderAbstract[][]
     */
    public function getStructuredProviders()
    {
        $unordered = [];
        $ordered = [];
        foreach ($this->providers as $provider) {
            $section = $provider::getSectionCode();
            $sectionOrder[$provider::getSectionPosition()] = $provider::getSectionCode();
            $notifOrder[$provider::getSectionCode()][$provider::getNotifPosition()] = $provider::getNotificationCode();
            if (!array_key_exists($section, $unordered)) {
                $unordered[$section] = [];
            }
            $unordered[$section][$provider::getNotificationCode()] = $provider;
        }
        ksort($sectionOrder);
        foreach ($sectionOrder as $item) {
            $ordered[$item] = $unordered[$item];
            $tmpNotifOrdered = [];
            ksort($notifOrder[$item]);
            foreach ($notifOrder[$item] as $notifPos => $notifCode) {
                $tmpNotifOrdered[$notifCode] = $ordered[$item][$notifCode];
            }
            $ordered[$item] = $tmpNotifOrdered;
        }

        return $ordered;
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
