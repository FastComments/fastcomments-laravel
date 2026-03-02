<?php

namespace FastComments\Laravel;

use FastComments\Client\Api\DefaultApi;
use FastComments\Client\Api\PublicApi;
use FastComments\Client\Configuration;
use FastComments\Laravel\SSO\SSOManager;
use FastComments\Laravel\SSO\SSOUserMapper;
use FastComments\Laravel\View\Components;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class FastCommentsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/fastcomments.php', 'fastcomments');

        $this->app->singleton(Configuration::class, function ($app) {
            $config = new Configuration();
            $apiKey = $app['config']->get('fastcomments.api_key', '');

            if ($apiKey !== '') {
                $config->setApiKey('x-api-key', $apiKey);
            }

            $region = $app['config']->get('fastcomments.region');
            if ($region === 'eu') {
                $config->setHost('https://eu.fastcomments.com');
            }

            return $config;
        });

        $this->app->singleton(DefaultApi::class, function ($app) {
            return new DefaultApi(new Client(), $app->make(Configuration::class));
        });

        $this->app->singleton(PublicApi::class, function ($app) {
            return new PublicApi(new Client(), $app->make(Configuration::class));
        });

        $this->app->singleton(SSOUserMapper::class, function ($app) {
            $ssoConfig = $app['config']->get('fastcomments.sso', []);

            return new SSOUserMapper(
                userMap: $ssoConfig['user_map'] ?? [],
                isAdmin: $ssoConfig['is_admin'] ?? null,
                isModerator: $ssoConfig['is_moderator'] ?? null,
                groupIds: $ssoConfig['group_ids'] ?? null,
            );
        });

        $this->app->singleton(SSOManager::class, function ($app) {
            $ssoConfig = $app['config']->get('fastcomments.sso', []);

            return new SSOManager(
                mapper: $app->make(SSOUserMapper::class),
                apiKey: $app['config']->get('fastcomments.api_key', ''),
                enabled: (bool) ($ssoConfig['enabled'] ?? false),
                mode: $ssoConfig['mode'] ?? 'secure',
                loginUrl: $ssoConfig['login_url'] ?? null,
                logoutUrl: $ssoConfig['logout_url'] ?? null,
            );
        });

        $this->app->singleton(FastCommentsManager::class, function ($app) {
            return new FastCommentsManager(
                adminApi: $app->make(DefaultApi::class),
                publicApi: $app->make(PublicApi::class),
                ssoManager: $app->make(SSOManager::class),
                tenantId: $app['config']->get('fastcomments.tenant_id', ''),
                region: $app['config']->get('fastcomments.region'),
                widgetDefaults: $app['config']->get('fastcomments.widget_defaults', []),
            );
        });

        $this->app->alias(FastCommentsManager::class, 'fastcomments');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/fastcomments.php' => config_path('fastcomments.php'),
        ], 'fastcomments-config');

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'fastcomments');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/fastcomments'),
        ], 'fastcomments-views');

        Blade::component('fastcomments', Components\FastComments::class);
        Blade::component('fastcomments-live-chat', Components\FastCommentsLiveChat::class);
        Blade::component('fastcomments-comment-count', Components\FastCommentsCommentCount::class);

        if (class_exists(\Illuminate\Foundation\Console\AboutCommand::class)) {
            \Illuminate\Foundation\Console\AboutCommand::add('FastComments', fn () => [
                'Tenant ID' => config('fastcomments.tenant_id') ?: '<not set>',
                'Region' => config('fastcomments.region') ?? 'US',
                'SSO' => config('fastcomments.sso.enabled') ? 'Enabled (' . config('fastcomments.sso.mode') . ')' : 'Disabled',
            ]);
        }
    }
}
