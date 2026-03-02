<?php

namespace FastComments\Laravel;

use FastComments\Client\Api\DefaultApi;
use FastComments\Client\Api\PublicApi;
use FastComments\Laravel\SSO\SSOManager;

class FastCommentsManager
{
    public function __construct(
        protected DefaultApi $adminApi,
        protected PublicApi $publicApi,
        protected SSOManager $ssoManager,
        protected string $tenantId,
        protected ?string $region,
        protected array $widgetDefaults,
    ) {
    }

    /**
     * Get the admin API client (requires API key).
     */
    public function admin(): DefaultApi
    {
        return $this->adminApi;
    }

    /**
     * Get the public API client.
     */
    public function publicApi(): PublicApi
    {
        return $this->publicApi;
    }

    /**
     * Get the SSO manager.
     */
    public function sso(): SSOManager
    {
        return $this->ssoManager;
    }

    /**
     * Get the tenant ID.
     */
    public function tenantId(): string
    {
        return $this->tenantId;
    }

    /**
     * Get the configured region (null for US, 'eu' for EU).
     */
    public function region(): ?string
    {
        return $this->region;
    }

    /**
     * Get the CDN host URL for the configured region.
     */
    public function cdnHost(): string
    {
        return $this->region === 'eu'
            ? 'https://cdn-eu.fastcomments.com'
            : 'https://cdn.fastcomments.com';
    }

    /**
     * Get the API host URL for the configured region.
     */
    public function apiHost(): string
    {
        return $this->region === 'eu'
            ? 'https://eu.fastcomments.com'
            : 'https://fastcomments.com';
    }

    /**
     * Build widget configuration by merging tenantId, region, defaults, and overrides.
     *
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    public function widgetConfig(array $overrides = []): array
    {
        $config = array_merge(
            $this->widgetDefaults,
            ['tenantId' => $this->tenantId],
            $overrides,
        );

        if ($this->region !== null) {
            $config['region'] = $this->region;
        }

        return $config;
    }
}
