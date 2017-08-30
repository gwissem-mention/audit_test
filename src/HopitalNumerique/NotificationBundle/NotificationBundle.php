<?php

namespace HopitalNumerique\NotificationBundle;

use HopitalNumerique\NotificationBundle\DependencyInjection\Compiler\NotificationCompilerPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class NotificationBundle.
 *
 * Below is an example on how to read providers :
 *
    $sections = $this->get('notification.aggregator')->getStructuredProviders();
    foreach ($sections as $sectionCode => $providers) {
        $sectionTitle = current($providers)->getConfigLabels()->getSectionLabel();
        echo 'SECTION ' . $sectionTitle . ' (<b>'.$sectionCode.'</b>)<br>';
        foreach ($providers as $provider) {
            $configLabels = $provider->getConfigLabels();
            echo 'PROVIDER <b>'.$provider::getNotificationCode().'</b><br>';
            echo $configLabels->getTitleLabel().'<br>';
            echo $configLabels->getDetailLabel().'<br>';
            echo $configLabels->getDescriptionLabel().'<br><br>';
        }
    }
 *
 */
class NotificationBundle extends Bundle
{
    /**
     * Used to define text size limit for notification titles.
     */
    const LIMIT_NOTIFY_TITLE_LENGTH = 30;

    /**
     * Used to define text size limit for notification descriptions.
     */
    const LIMIT_NOTIFY_DESC_LENGTH = 300;

    /**
     * Notification frequency mode 'daily' (send notifications once per day).
     */
    const NOTIFICATION_FREQUENCY_DAILY = 'daily';

    /**
     * Notification frequency mode 'daily' (send notifications once per week).
     */
    const NOTIFICATION_FREQUENCY_WEEKLY = 'weekly';

    /**
     * Notification frequency mode 'daily' (send notifications immediately).
     */
    const NOTIFICATION_FREQUENCY_STRAIGHT = 'straight';

    /**
     * Notification frequency mode 'daily' (do not send notification).
     */
    const NOTIFICATION_FREQUENCY_OFF = 'off';

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new NotificationCompilerPass());
    }

    public function boot()
    {

    }
}
