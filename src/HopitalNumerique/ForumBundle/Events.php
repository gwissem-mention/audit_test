<?php

namespace HopitalNumerique\ForumBundle;

/**
 * Contains events thrown in the forum bundle.
 */
final class Events
{
    /**
     * This event occurs when a message is posted
     */
    const POST_PUBLISHED = 'hopitalnumerique.post.published';

    /**
     * This event occurs when a new post in created.
     * The event listener method receives a
     * CCDNForum\ForumBundle\Component\Dispatcher\Event\UserPostEvent instance.
     *
     * @Event
     */
    const POST_CREATED = 'hopitalnumerique.user.post.create.success';
}
