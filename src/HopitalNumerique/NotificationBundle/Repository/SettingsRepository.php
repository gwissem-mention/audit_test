<?php

namespace HopitalNumerique\NotificationBundle\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use HopitalNumerique\NotificationBundle\Entity\Settings;
use HopitalNumerique\NotificationBundle\Enum\NotificationFrequencyEnum;

/**
 * SettingsRepository.
 */
class SettingsRepository extends EntityRepository
{
    /**
     * Join $allUsersQueryBuilder query builder with notification settings.
     * This will return given default frequency / detail instead of user settings when they do not exist.
     *
     * @param $notificationCode
     * @param QueryBuilder|null $usersQueryBuilder
     * @param array $defaultSettings
     *
     * @return Settings[]
     *
     * @throws \Exception
     */
    public function getSubscriptions(
        $notificationCode,
        QueryBuilder $usersQueryBuilder = null,
        array $defaultSettings = []
    ) {
        $fields = [
            'user.id AS user_id'
        ];

        foreach ($defaultSettings as $key => $value) {
            $fields[$key] = sprintf("COALESCE(settings.%s, '%s') AS $key", $key, $value);
        }

        //No query builder supplied : prepare our own (all users).
        if (null === $usersQueryBuilder) {
            $usersQueryBuilder = $this->_em->createQueryBuilder()
                ->select('user.id')
                ->from('HopitalNumeriqueUserBundle:User', 'user');
        } else {
            //Query builder supplied : check we have 'user.id' column in select.
            $selectOk = false;
            foreach ($usersQueryBuilder->getDQLPart('select') as $select) {
                /** @var Expr\Select $select */
                foreach ($select->getParts() as $part) {
                    if ('user.id' === $part) {
                        $selectOk = true;
                        break 2;
                    }
                }
            }

            if (!$selectOk) {
                throw new \Exception("QueryBuilder must select 'user.id' column");
            }

            //Needed to make iterate() possible.
            $usersQueryBuilder->distinct();
        }

        //Take care here not overwriting incoming query parts by using things like setParameters() or having().
        $query = $usersQueryBuilder
            ->select($fields)
            ->leftJoin(
                Settings::class,
                'settings',
                Expr\Join::WITH,
                'user.id = settings.userId AND settings.notificationCode = :notificationCode'
            )
            ->setParameter('notificationCode', $notificationCode)
            ->groupBy('user.id')
            ->andHaving('frequency != :frequencyOff')
            ->setParameter('frequencyOff', NotificationFrequencyEnum::NOTIFICATION_FREQUENCY_OFF)
            ->getQuery()
            ->iterate()
        ;

        $return = [];
        foreach ($query as $row) {
            $row = current($row);
            $settingsRow = new Settings();
            $settingsRow->setUserId($row['user_id']);
            $settingsRow->setFrequency($row['frequency']);
            $settingsRow->setDetailLevel($row['detailLevel']);
            $settingsRow->setScheduleDay($row['scheduleDay']);
            $settingsRow->setScheduleHour($row['scheduleHour']);
            $return[] = $settingsRow;
        }

        return $return;
    }
}
