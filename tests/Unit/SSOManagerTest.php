<?php

namespace FastComments\Laravel\Tests\Unit;

use FastComments\Laravel\SSO\SSOManager;
use FastComments\Laravel\SSO\SSOUserMapper;
use FastComments\Laravel\Tests\TestCase;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

class SSOManagerTest extends TestCase
{
    public function test_for_widget_returns_null_when_disabled(): void
    {
        $manager = $this->makeSSOManager(enabled: false);
        $this->assertNull($manager->forWidget());
    }

    public function test_is_enabled_returns_config_value(): void
    {
        $enabled = $this->makeSSOManager(enabled: true);
        $disabled = $this->makeSSOManager(enabled: false);

        $this->assertTrue($enabled->isEnabled());
        $this->assertFalse($disabled->isEnabled());
    }

    public function test_for_widget_returns_login_logout_urls_when_no_user(): void
    {
        $manager = $this->makeSSOManager(
            enabled: true,
            loginUrl: 'https://example.com/login',
            logoutUrl: 'https://example.com/logout',
        );

        Auth::shouldReceive('user')->andReturn(null);

        $result = $manager->forWidget();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('sso', $result);
        $this->assertIsArray($result['sso']);
        $this->assertSame('https://example.com/login', $result['sso']['loginURL']);
        $this->assertSame('https://example.com/logout', $result['sso']['logoutURL']);
        $this->assertArrayNotHasKey('userDataJSONBase64', $result['sso']);
    }

    public function test_for_widget_secure_mode_with_authenticated_user(): void
    {
        $manager = $this->makeSSOManager(
            enabled: true,
            mode: 'secure',
            loginUrl: 'https://example.com/login',
            logoutUrl: 'https://example.com/logout',
        );

        $user = $this->makeUser(['id' => 5, 'email' => 'test@example.com', 'name' => 'Tester']);

        $result = $manager->forWidget($user);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('sso', $result);
        $this->assertIsArray($result['sso']);

        // The sso value should be an array (not a JSON string) with the secure payload fields
        $sso = $result['sso'];
        $this->assertArrayHasKey('userDataJSONBase64', $sso);
        $this->assertArrayHasKey('verificationHash', $sso);
        $this->assertArrayHasKey('timestamp', $sso);
        $this->assertSame('https://example.com/login', $sso['loginURL']);
        $this->assertSame('https://example.com/logout', $sso['logoutURL']);

        // Decode the user data to verify
        $userData = json_decode(base64_decode($sso['userDataJSONBase64']), true);
        $this->assertSame('5', $userData['id']);
        $this->assertSame('test@example.com', $userData['email']);
        $this->assertSame('Tester', $userData['username']);
    }

    public function test_for_widget_simple_mode_with_authenticated_user(): void
    {
        $manager = $this->makeSSOManager(
            enabled: true,
            mode: 'simple',
            loginUrl: 'https://example.com/login',
            logoutUrl: 'https://example.com/logout',
        );

        $user = $this->makeUser(['id' => 5, 'email' => 'test@example.com', 'name' => 'Tester']);

        $result = $manager->forWidget($user);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('simpleSSO', $result);
        $this->assertSame('Tester', $result['simpleSSO']['username']);
        $this->assertSame('test@example.com', $result['simpleSSO']['email']);
    }

    public function test_for_widget_auto_reads_auth_user(): void
    {
        $manager = $this->makeSSOManager(
            enabled: true,
            mode: 'secure',
            loginUrl: 'https://example.com/login',
            logoutUrl: 'https://example.com/logout',
        );

        $user = $this->makeUser(['id' => 7, 'email' => 'auto@example.com', 'name' => 'AutoUser']);
        Auth::shouldReceive('user')->andReturn($user);

        $result = $manager->forWidget();

        $this->assertArrayHasKey('sso', $result);
    }

    public function test_token_for_returns_secure_token_string(): void
    {
        $manager = $this->makeSSOManager(
            enabled: true,
            mode: 'secure',
        );

        $user = $this->makeUser(['id' => 10, 'email' => 'token@example.com', 'name' => 'TokenUser']);
        $token = $manager->tokenFor($user);

        $this->assertIsString($token);

        $decoded = json_decode($token, true);
        $this->assertArrayHasKey('userDataJSONBase64', $decoded);
        $this->assertArrayHasKey('verificationHash', $decoded);
    }

    protected function makeSSOManager(
        bool $enabled = false,
        string $mode = 'secure',
        ?string $loginUrl = null,
        ?string $logoutUrl = null,
    ): SSOManager {
        $mapper = new SSOUserMapper(
            userMap: ['id' => 'id', 'email' => 'email', 'username' => 'name'],
        );

        return new SSOManager(
            mapper: $mapper,
            apiKey: 'test-api-key',
            enabled: $enabled,
            mode: $mode,
            loginUrl: $loginUrl,
            logoutUrl: $logoutUrl,
        );
    }

    protected function makeUser(array $attributes): Authenticatable
    {
        return new class($attributes) implements Authenticatable {
            public function __construct(protected array $attrs)
            {
                foreach ($attrs as $key => $value) {
                    $this->{$key} = $value;
                }
            }

            public function __get(string $name): mixed
            {
                return $this->attrs[$name] ?? null;
            }

            public function __isset(string $name): bool
            {
                return isset($this->attrs[$name]);
            }

            public function getAuthIdentifierName(): string { return 'id'; }
            public function getAuthIdentifier(): mixed { return $this->attrs['id'] ?? null; }
            public function getAuthPasswordName(): string { return 'password'; }
            public function getAuthPassword(): string { return ''; }
            public function getRememberToken(): ?string { return null; }
            public function setRememberToken($value): void {}
            public function getRememberTokenName(): string { return ''; }
        };
    }
}
