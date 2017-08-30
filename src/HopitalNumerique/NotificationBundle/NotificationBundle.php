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
    $sections = $this->get('HopitalNumerique\NotificationBundle\Service\Notifications')->getStructuredProviders();
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

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new NotificationCompilerPass());
    }

    public function boot()
    {

    }
}
