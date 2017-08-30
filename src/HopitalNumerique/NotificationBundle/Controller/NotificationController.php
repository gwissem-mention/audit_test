<?php

namespace HopitalNumerique\NotificationBundle\Controller;

use HopitalNumerique\NotificationBundle\Domain\Command\UpdateNotificationSettingsCommand;
use HopitalNumerique\NotificationBundle\Enum\NotificationFrequencyEnum;
use HopitalNumerique\NotificationBundle\Service\NotificationProviderAbstract;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class NotificationController.
 */
class NotificationController extends Controller
{
    /**
     * Test providers count.
     */
    public function testCountProvidersAction()
    {
        $scheduleDateTime = new \DateTime(sprintf(
            '%s %d:00',
            'SUNDAY',
            10
        ));
        die($scheduleDateTime->format('d/m/Y H:i:s'));

        $providers = $this->get('hopitalnumerique\notificationbundle\service\notifications')->getProviders();
        echo "nb notification providers = ".count($providers);

        die('ok');
    }

    /**
     * Example of saving a new notification setting.
     */
    public function testSaveUserSettingAction()
    {
        $command = new UpdateNotificationSettingsCommand(
            $this->getUser(),
            'publication_updated',
            NotificationFrequencyEnum::NOTIFICATION_FREQUENCY_STRAIGHT,
            1
        );

        $this->get('hopitalnumerique\notificationbundle\domain\command\updatenotificationsettingshandler')
            ->handle($command);

        die('ok');
    }

    /**
     * Shows translated labels of notification providers, by section.
     */
    public function testShowProvidersAction()
    {
        $t = $this->get('translator');
        /**
         * @var NotificationProviderAbstract[][] $sections
         */
        $sections = $this->get('hopitalnumerique\notificationbundle\service\notifications')->getStructuredProviders();
        foreach ($sections as $sectionCode => $providers) {
            $first = false;
            foreach ($providers as $provider) {
                if ($first) {
                    $sectionTitle = $t->trans($provider->getConfigLabels()->getSectionLabel(), [], 'notification');
                    echo 'SECTION ' . $sectionTitle . ' (<b>'.$sectionCode.'</b>)<br>';
                    $first = false;
                }

                echo 'PROVIDER <b>'.$provider::getNotificationCode().'</b><br>';

                echo $t->trans($provider->getConfigLabels()->getTitleLabel(), [], 'notification').'<br>';
                echo $t->trans($provider->getConfigLabels()->getDetailLabel(), [], 'notification').'<br>';
                echo $t->trans($provider->getConfigLabels()->getDescriptionLabel(), [], 'notification').'<br><br>';
            }
        }

        die('ok');
    }
}
