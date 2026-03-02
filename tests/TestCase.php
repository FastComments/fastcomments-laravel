<?php

namespace FastComments\Laravel\Tests;

use FastComments\Laravel\FastCommentsServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            FastCommentsServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'FastComments' => \FastComments\Laravel\Facades\FastComments::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('fastcomments.tenant_id', 'test-tenant-id');
        $app['config']->set('fastcomments.api_key', 'test-api-key');
        $app['config']->set('fastcomments.region', null);
        $app['config']->set('fastcomments.sso.enabled', false);
        $app['config']->set('fastcomments.sso.mode', 'secure');
        $app['config']->set('fastcomments.sso.login_url', 'https://example.com/login');
        $app['config']->set('fastcomments.sso.logout_url', 'https://example.com/logout');
        $app['config']->set('fastcomments.sso.user_map', [
            'id' => 'id',
            'email' => 'email',
            'username' => 'name',
            'avatar' => null,
        ]);
        $app['config']->set('fastcomments.sso.is_admin', null);
        $app['config']->set('fastcomments.sso.is_moderator', null);
        $app['config']->set('fastcomments.sso.group_ids', null);
        $app['config']->set('fastcomments.widget_defaults', []);
    }
}
