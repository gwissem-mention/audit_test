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
     * Occurs when a message is created into a cdp discussion in group
     */
    const DISCUSSION_MESSAGE_CREATED_IN_GROUP = 'communautepratique.discussion.message_created_in_group';

    /**
     * Occurs when a message is created into a cdp discussion
     */
    const DISCUSSION_MESSAGE_CREATED = 'communautepratique.discussion.message_created';

    /**
     * Occurs when a message is validated by a moderator into a cdp discussion
     */
    const DISCUSSION_MESSAGE_VALIDATED = 'communautepratique.discussion.message_validated';

    /**
     * Occurs when a discussion is created
     */
    const DISCUSSION_CREATED = 'communautepratique.discussion.discussion_created';

    /**
     * Occurs when a discussion is created in group
     */
    const DISCUSSION_CREATED_IN_GROUP = 'communautepratique.discussion.discussion_created_in_group';

    /**
     * Occurs when a discussion was set in public
     */
    const DISCUSSION_PUBLIC = 'communautepratique.discussion.discussion_public';

    /**
     * Occurs when a discussion was viewed
     */
    const DISCUSSION_VIEWED = 'communautepratique.discussion.discussion_viewed';

    /**
     * Occurs when a discussion was moved
     */
    const DISCUSSION_MOVED = 'communautepratique.discussion.discussion_moved';

    // Legacy :

    /**
     * This event occurs when a comment is left on a group.
     */
    const GROUP_COMMENT_CREATED = 'communautepratique.group_comment_created';

    /**
     * This event occurs when a comment is left on a form in a group.
     */
    const FORM_COMMENT_CREATED = 'communautepratique.form_comment_created';

    /**
     * This event occurs when a document is uploaded in a group.
     */
    const GROUP_DOCUMENT_CREATED = 'communautepratique.group_document_created';

    /**
     * This event occurs when a new user has joined a group.
     */
    const GROUP_USER_JOINED = 'communautepratique.group_user_joined';

    /**
     * This event occurs when a new group has been created.
     */
    const GROUP_CREATED = 'communautepratique.group_created';
}
