<?php

namespace FastComments\Laravel\SSO\Contracts;

/**
 * Implement this interface on your User model to control how it maps
 * to FastComments SSO user data. When implemented, this takes precedence
 * over the config-based user_map.
 */
interface MapsToFastCommentsUser
{
    /**
     * Return an array of FastComments SSO fields.
     *
     * Supported keys: id, email, username, avatar, display_name, website_url,
     * is_admin, is_moderator, group_ids.
     *
     * @return array<string, mixed>
     */
    public function toFastCommentsUserData(): array;
}
