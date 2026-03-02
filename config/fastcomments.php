<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tenant ID
    |--------------------------------------------------------------------------
    |
    | Your FastComments tenant ID (found in your FastComments dashboard).
    |
    */
    'tenant_id' => env('FASTCOMMENTS_TENANT_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | API Key
    |--------------------------------------------------------------------------
    |
    | Your FastComments API key (found in your FastComments dashboard).
    | Required for server-side API calls. Not needed for widget-only usage.
    |
    */
    'api_key' => env('FASTCOMMENTS_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Region
    |--------------------------------------------------------------------------
    |
    | Set to 'eu' to use FastComments EU region. Leave null for US (default).
    |
    */
    'region' => env('FASTCOMMENTS_REGION', null),

    /*
    |--------------------------------------------------------------------------
    | SSO Configuration
    |--------------------------------------------------------------------------
    */
    'sso' => [

        // Enable SSO integration
        'enabled' => env('FASTCOMMENTS_SSO_ENABLED', false),

        // SSO mode: 'secure' (HMAC-SHA256, recommended) or 'simple'
        'mode' => env('FASTCOMMENTS_SSO_MODE', 'secure'),

        // Login/logout URLs shown to unauthenticated users in the widget.
        // Defaults to Laravel's login/logout routes if null.
        'login_url' => env('FASTCOMMENTS_SSO_LOGIN_URL', null),
        'logout_url' => env('FASTCOMMENTS_SSO_LOGOUT_URL', null),

        // Map FastComments SSO fields to your User model attributes.
        // Supports: string attribute names, dot notation, or callables.
        // Set a value to null to skip that field.
        'user_map' => [
            'id' => 'id',
            'email' => 'email',
            'username' => 'name',
            'avatar' => null,
            'display_name' => null,
            'website_url' => null,
        ],

        // Callables receiving the User model. Return the appropriate value.
        // Set to null to skip.
        'is_admin' => null,
        'is_moderator' => null,
        'group_ids' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Widget Defaults
    |--------------------------------------------------------------------------
    |
    | Default configuration passed to all FastComments widgets. These can be
    | overridden per-component via props/attributes.
    |
    | See https://docs.fastcomments.com/guide-customizations-and-configuration.html
    |
    */
    'widget_defaults' => [
        // 'locale' => 'en_us',
        // 'hasDarkBackground' => false,
        // 'defaultSortDirection' => 'MR',
    ],

];
