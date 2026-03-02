<?php

namespace FastComments\Laravel\SSO;

use FastComments\SSO\FastCommentsSSO;
use FastComments\SSO\SecureSSOUserData;
use FastComments\SSO\SimpleSSOUserData;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

class SSOManager
{
    public function __construct(
        protected SSOUserMapper $mapper,
        protected string $apiKey,
        protected bool $enabled,
        protected string $mode,
        protected ?string $loginUrl,
        protected ?string $logoutUrl,
    ) {
    }

    /**
     * Check if SSO is enabled.
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Generate an SSO payload array for widget configuration.
     *
     * Returns null if SSO is disabled. Auto-reads Auth::user() if no user passed.
     *
     * @return array<string, mixed>|null
     */
    public function forWidget(?Authenticatable $user = null): ?array
    {
        if (!$this->enabled) {
            return null;
        }

        $result = [];

        $loginUrl = $this->loginUrl ?? $this->defaultLoginUrl();
        $logoutUrl = $this->logoutUrl ?? $this->defaultLogoutUrl();

        if ($loginUrl !== null) {
            $result['loginURL'] = $loginUrl;
        }

        if ($logoutUrl !== null) {
            $result['logoutURL'] = $logoutUrl;
        }

        $user = $user ?? Auth::user();

        if ($user === null) {
            // Unauthenticated — return just login/logout URLs
            return $result;
        }

        $userData = $this->mapper->map($user);

        if ($this->mode === 'secure') {
            $ssoPayload = $this->buildSecurePayload($userData);
            $result['sso'] = $ssoPayload;
        } else {
            $result['simpleSSO'] = $this->buildSimplePayload($userData);
        }

        return $result;
    }

    /**
     * Generate a raw secure SSO token string for API use.
     */
    public function tokenFor(Authenticatable $user): string
    {
        $userData = $this->mapper->map($user);
        $ssoUserData = $this->buildSecureSSOUserData($userData);
        $sso = FastCommentsSSO::createSecure($this->apiKey, $ssoUserData);

        return $sso->prepareToSend();
    }

    /**
     * Build a secure SSO payload (JSON token string with loginURL/logoutURL).
     *
     * @param array<string, mixed> $userData
     * @return string JSON token
     */
    protected function buildSecurePayload(array $userData): string
    {
        $ssoUserData = $this->buildSecureSSOUserData($userData);
        $sso = FastCommentsSSO::createSecure($this->apiKey, $ssoUserData);

        $loginUrl = $this->loginUrl ?? $this->defaultLoginUrl();
        $logoutUrl = $this->logoutUrl ?? $this->defaultLogoutUrl();

        if ($loginUrl !== null) {
            $sso->loginURL = $loginUrl;
        }

        if ($logoutUrl !== null) {
            $sso->logoutURL = $logoutUrl;
        }

        return $sso->prepareToSend();
    }

    /**
     * @param array<string, mixed> $userData
     */
    protected function buildSecureSSOUserData(array $userData): SecureSSOUserData
    {
        $ssoUser = SecureSSOUserData::create(
            $userData['id'] ?? '',
            $userData['email'] ?? '',
            $userData['username'] ?? '',
            $userData['avatar'] ?? null,
        );

        if (isset($userData['display_name'])) {
            $ssoUser->displayName = $userData['display_name'];
        }

        if (isset($userData['website_url'])) {
            $ssoUser->websiteUrl = $userData['website_url'];
        }

        if (isset($userData['is_admin'])) {
            $ssoUser->isAdmin = (bool) $userData['is_admin'];
        }

        if (isset($userData['is_moderator'])) {
            $ssoUser->isModerator = (bool) $userData['is_moderator'];
        }

        if (isset($userData['group_ids'])) {
            $ssoUser->groupIds = (array) $userData['group_ids'];
        }

        return $ssoUser;
    }

    /**
     * @param array<string, mixed> $userData
     * @return array<string, mixed>
     */
    protected function buildSimplePayload(array $userData): array
    {
        $simple = [
            'username' => $userData['username'] ?? '',
        ];

        if (isset($userData['email'])) {
            $simple['email'] = $userData['email'];
        }

        if (isset($userData['avatar'])) {
            $simple['avatar'] = $userData['avatar'];
        }

        if (isset($userData['website_url'])) {
            $simple['websiteUrl'] = $userData['website_url'];
        }

        return $simple;
    }

    protected function defaultLoginUrl(): ?string
    {
        try {
            return route('login');
        } catch (\Throwable) {
            return null;
        }
    }

    protected function defaultLogoutUrl(): ?string
    {
        try {
            return route('logout');
        } catch (\Throwable) {
            return null;
        }
    }
}
