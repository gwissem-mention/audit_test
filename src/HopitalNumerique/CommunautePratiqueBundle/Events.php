<?php

namespace HopitalNumerique\CommunautePratiqueBundle;

/**
 * Contain all events throwns in the communaute pratique bundle.
 */
final class Events
{
    /**
     * This event occurs when a user join the Communaute de pratique
     */
    const ENROLL_USER = 'communautepratique.enroll_user';

    /**
     * This event occurs when a user quit the Communaute de pratique
     */
    const DISENROLL_USER = 'communautepratique.disenroll_user';

    /**
     * Ocurs when a user submit/valid group registration form
     */
    const GROUP_REGISTRATION = 'communautepratique.group_registration';

    /**
     * Occurs when a message is posted into a cdp discussion
     */
    const DISCUSSION_MESSAGE_POSTED = 'communautepratique.discussion.message_posted';

    /**
     * Occurs when a discussion is created
     */
    const DISCUSSION_CREATED = 'communautepratique.discussion.discussion_created';
}
