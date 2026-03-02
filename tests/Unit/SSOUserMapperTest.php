<?php

namespace FastComments\Laravel\Tests\Unit;

use FastComments\Laravel\SSO\Contracts\MapsToFastCommentsUser;
use FastComments\Laravel\SSO\SSOUserMapper;
use FastComments\Laravel\Tests\TestCase;
use Illuminate\Contracts\Auth\Authenticatable;

class SSOUserMapperTest extends TestCase
{
    public function test_maps_basic_fields(): void
    {
        $mapper = new SSOUserMapper(
            userMap: ['id' => 'id', 'email' => 'email', 'username' => 'name'],
        );

        $user = $this->makeUser(['id' => 42, 'email' => 'test@example.com', 'name' => 'Test User']);
        $result = $mapper->map($user);

        $this->assertSame('42', $result['id']);
        $this->assertSame('test@example.com', $result['email']);
        $this->assertSame('Test User', $result['username']);
    }

    public function test_casts_id_to_string(): void
    {
        $mapper = new SSOUserMapper(
            userMap: ['id' => 'id', 'email' => 'email', 'username' => 'name'],
        );

        $user = $this->makeUser(['id' => 123, 'email' => 'a@b.com', 'name' => 'Tester']);
        $result = $mapper->map($user);

        $this->assertSame('123', $result['id']);
    }

    public function test_skips_null_mapped_fields(): void
    {
        $mapper = new SSOUserMapper(
            userMap: ['id' => 'id', 'email' => 'email', 'username' => 'name', 'avatar' => null],
        );

        $user = $this->makeUser(['id' => 1, 'email' => 'a@b.com', 'name' => 'Tester']);
        $result = $mapper->map($user);

        $this->assertArrayNotHasKey('avatar', $result);
    }

    public function test_supports_callable_mapping(): void
    {
        $mapper = new SSOUserMapper(
            userMap: [
                'id' => 'id',
                'email' => 'email',
                'username' => fn ($u) => strtoupper($u->name),
            ],
        );

        $user = $this->makeUser(['id' => 1, 'email' => 'a@b.com', 'name' => 'test']);
        $result = $mapper->map($user);

        $this->assertSame('TEST', $result['username']);
    }

    public function test_supports_dot_notation(): void
    {
        $mapper = new SSOUserMapper(
            userMap: ['id' => 'id', 'email' => 'email', 'username' => 'profile.display_name'],
        );

        $user = $this->makeUser([
            'id' => 1,
            'email' => 'a@b.com',
            'profile' => (object) ['display_name' => 'Nested Name'],
        ]);
        $result = $mapper->map($user);

        $this->assertSame('Nested Name', $result['username']);
    }

    public function test_applies_is_admin_callable(): void
    {
        $mapper = new SSOUserMapper(
            userMap: ['id' => 'id', 'email' => 'email', 'username' => 'name'],
            isAdmin: fn ($u) => $u->is_admin,
        );

        $user = $this->makeUser(['id' => 1, 'email' => 'a@b.com', 'name' => 'Admin', 'is_admin' => true]);
        $result = $mapper->map($user);

        $this->assertTrue($result['is_admin']);
    }

    public function test_applies_is_moderator_callable(): void
    {
        $mapper = new SSOUserMapper(
            userMap: ['id' => 'id', 'email' => 'email', 'username' => 'name'],
            isModerator: fn ($u) => $u->is_moderator,
        );

        $user = $this->makeUser(['id' => 1, 'email' => 'a@b.com', 'name' => 'Mod', 'is_moderator' => true]);
        $result = $mapper->map($user);

        $this->assertTrue($result['is_moderator']);
    }

    public function test_applies_group_ids_callable(): void
    {
        $mapper = new SSOUserMapper(
            userMap: ['id' => 'id', 'email' => 'email', 'username' => 'name'],
            groupIds: fn ($u) => $u->group_ids,
        );

        $user = $this->makeUser(['id' => 1, 'email' => 'a@b.com', 'name' => 'User', 'group_ids' => ['g1', 'g2']]);
        $result = $mapper->map($user);

        $this->assertSame(['g1', 'g2'], $result['group_ids']);
    }

    public function test_interface_takes_precedence(): void
    {
        $mapper = new SSOUserMapper(
            userMap: ['id' => 'id', 'email' => 'email', 'username' => 'name'],
        );

        $user = new class implements Authenticatable, MapsToFastCommentsUser {
            public function toFastCommentsUserData(): array
            {
                return [
                    'id' => '999',
                    'email' => 'interface@example.com',
                    'username' => 'InterfaceUser',
                ];
            }

            public function getAuthIdentifierName(): string { return 'id'; }
            public function getAuthIdentifier(): mixed { return 1; }
            public function getAuthPasswordName(): string { return 'password'; }
            public function getAuthPassword(): string { return ''; }
            public function getRememberToken(): ?string { return null; }
            public function setRememberToken($value): void {}
            public function getRememberTokenName(): string { return ''; }
        };

        $result = $mapper->map($user);

        $this->assertSame('999', $result['id']);
        $this->assertSame('interface@example.com', $result['email']);
        $this->assertSame('InterfaceUser', $result['username']);
    }

    public function test_removes_null_values_from_result(): void
    {
        $mapper = new SSOUserMapper(
            userMap: ['id' => 'id', 'email' => 'email', 'username' => 'name', 'display_name' => 'missing_attr'],
        );

        $user = $this->makeUser(['id' => 1, 'email' => 'a@b.com', 'name' => 'User']);
        $result = $mapper->map($user);

        $this->assertArrayNotHasKey('display_name', $result);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    protected function makeUser(array $attributes): Authenticatable
    {
        return new class($attributes) implements Authenticatable {
            /** @param array<string, mixed> $attrs */
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
