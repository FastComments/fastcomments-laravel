<?php

namespace FastComments\Laravel\Tests\Unit;

use FastComments\Client\Api\DefaultApi;
use FastComments\Client\Api\PublicApi;
use FastComments\Laravel\FastCommentsManager;
use FastComments\Laravel\Tests\TestCase;

class FastCommentsManagerTest extends TestCase
{
    public function test_tenant_id(): void
    {
        $manager = $this->app->make(FastCommentsManager::class);
        $this->assertSame('test-tenant-id', $manager->tenantId());
    }

    public function test_region_defaults_to_null(): void
    {
        $manager = $this->app->make(FastCommentsManager::class);
        $this->assertNull($manager->region());
    }

    public function test_region_eu(): void
    {
        $this->app['config']->set('fastcomments.region', 'eu');

        // Rebuild the singleton with new config
        $this->app->forgetInstance(FastCommentsManager::class);
        $manager = $this->app->make(FastCommentsManager::class);

        $this->assertSame('eu', $manager->region());
    }

    public function test_cdn_host_us(): void
    {
        $manager = $this->app->make(FastCommentsManager::class);
        $this->assertSame('https://cdn.fastcomments.com', $manager->cdnHost());
    }

    public function test_cdn_host_eu(): void
    {
        $this->app['config']->set('fastcomments.region', 'eu');
        $this->app->forgetInstance(FastCommentsManager::class);

        $manager = $this->app->make(FastCommentsManager::class);
        $this->assertSame('https://cdn-eu.fastcomments.com', $manager->cdnHost());
    }

    public function test_api_host_us(): void
    {
        $manager = $this->app->make(FastCommentsManager::class);
        $this->assertSame('https://fastcomments.com', $manager->apiHost());
    }

    public function test_api_host_eu(): void
    {
        $this->app['config']->set('fastcomments.region', 'eu');
        $this->app->forgetInstance(FastCommentsManager::class);

        $manager = $this->app->make(FastCommentsManager::class);
        $this->assertSame('https://eu.fastcomments.com', $manager->apiHost());
    }

    public function test_widget_config_includes_tenant_id(): void
    {
        $manager = $this->app->make(FastCommentsManager::class);
        $config = $manager->widgetConfig();

        $this->assertSame('test-tenant-id', $config['tenantId']);
    }

    public function test_widget_config_merges_overrides(): void
    {
        $manager = $this->app->make(FastCommentsManager::class);
        $config = $manager->widgetConfig(['urlId' => 'my-page', 'readonly' => true]);

        $this->assertSame('test-tenant-id', $config['tenantId']);
        $this->assertSame('my-page', $config['urlId']);
        $this->assertTrue($config['readonly']);
    }

    public function test_widget_config_merges_defaults(): void
    {
        $this->app['config']->set('fastcomments.widget_defaults', ['locale' => 'fr_fr']);
        $this->app->forgetInstance(FastCommentsManager::class);

        $manager = $this->app->make(FastCommentsManager::class);
        $config = $manager->widgetConfig();

        $this->assertSame('fr_fr', $config['locale']);
        $this->assertSame('test-tenant-id', $config['tenantId']);
    }

    public function test_widget_config_overrides_take_precedence(): void
    {
        $this->app['config']->set('fastcomments.widget_defaults', ['locale' => 'fr_fr']);
        $this->app->forgetInstance(FastCommentsManager::class);

        $manager = $this->app->make(FastCommentsManager::class);
        $config = $manager->widgetConfig(['locale' => 'de_de']);

        $this->assertSame('de_de', $config['locale']);
    }

    public function test_widget_config_includes_region_when_set(): void
    {
        $this->app['config']->set('fastcomments.region', 'eu');
        $this->app->forgetInstance(FastCommentsManager::class);

        $manager = $this->app->make(FastCommentsManager::class);
        $config = $manager->widgetConfig();

        $this->assertSame('eu', $config['region']);
    }

    public function test_widget_config_omits_region_when_null(): void
    {
        $manager = $this->app->make(FastCommentsManager::class);
        $config = $manager->widgetConfig();

        $this->assertArrayNotHasKey('region', $config);
    }

    public function test_admin_returns_default_api(): void
    {
        $manager = $this->app->make(FastCommentsManager::class);
        $this->assertInstanceOf(DefaultApi::class, $manager->admin());
    }

    public function test_public_api_returns_public_api(): void
    {
        $manager = $this->app->make(FastCommentsManager::class);
        $this->assertInstanceOf(PublicApi::class, $manager->publicApi());
    }
}
