<?php

namespace HopitalNumerique\UserBundle;

/**
 * Contains all events thrown in the UserBundle
 */
final class UserEvents
{
    /**
     * The USER_PRE_UPDATE event occurs before a user gets updated
     *
     * @Event("HopitalNumerique\UserBundle\Event\UserEvent")
     *
     * @var string
     */
    const USER_PRE_UPDATE = 'user.user_pre_update';

    /**
     * The USER_UPDATED event occurs when a user is updated
     *
     * @Event("HopitalNumerique\UserBundle\Event\UserEvent")
     *
     * @var string
     */
    const USER_UPDATED = 'user.user_updated';

    /**
     * The USER_DELETED event occurs when a user is deleted
     *
     * @Event("HopitalNumerique\UserBundle\Event\UserEvent")
     *
     * @var string
     */
    const USER_DELETED = 'user.user_deleted';

    /**
     * The TOKEN_CREATED event occurs when a new token is created
     *
     * @Event("HopitalNumerique\UserBundle\Event\TokenEvent")
     *
     * @var string
     */
    const TOKEN_CREATED = 'user.token_created';

    /**
     * The TOKEN_DELETED event occurs when a token is deleted
     *
     * @Event("HopitalNumerique\UserBundle\Event\TokenEvent")
     *
     * @var string
     */
    const TOKEN_DELETED = 'user.token_deleted';

    /**
     * The USER_ROLE_UPDATED occurs when a user is created or when the role of a user is modified.
     */
    const USER_ROLE_UPDATED = 'user.role_updated';
}
