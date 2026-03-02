<?php

namespace FastComments\Laravel\SSO;

use FastComments\Laravel\SSO\Contracts\MapsToFastCommentsUser;
use Illuminate\Contracts\Auth\Authenticatable;

class SSOUserMapper
{
    /**
     * @param array<string, mixed> $userMap Config user_map array
     * @param callable|null $isAdmin
     * @param callable|null $isModerator
     * @param callable|null $groupIds
     */
    public function __construct(
        protected array $userMap,
        protected mixed $isAdmin = null,
        protected mixed $isModerator = null,
        protected mixed $groupIds = null,
    ) {
    }

    /**
     * Map an Authenticatable user to FastComments SSO user data.
     *
     * @return array<string, mixed>
     */
    public function map(Authenticatable $user): array
    {
        if ($user instanceof MapsToFastCommentsUser) {
            return $this->normalizeData($user->toFastCommentsUserData());
        }

        return $this->mapFromConfig($user);
    }

    /**
     * @return array<string, mixed>
     */
    protected function mapFromConfig(Authenticatable $user): array
    {
        $data = [];

        foreach ($this->userMap as $fcField => $source) {
            if ($source === null) {
                continue;
            }

            if (is_callable($source)) {
                $data[$fcField] = $source($user);
            } else {
                $data[$fcField] = data_get($user, $source);
            }
        }

        // Apply role/group callables
        if ($this->isAdmin !== null && is_callable($this->isAdmin)) {
            $data['is_admin'] = ($this->isAdmin)($user);
        }

        if ($this->isModerator !== null && is_callable($this->isModerator)) {
            $data['is_moderator'] = ($this->isModerator)($user);
        }

        if ($this->groupIds !== null && is_callable($this->groupIds)) {
            $data['group_ids'] = ($this->groupIds)($user);
        }

        return $this->normalizeData($data);
    }

    /**
     * Ensure id is cast to string and remove null values.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function normalizeData(array $data): array
    {
        if (isset($data['id'])) {
            $data['id'] = (string) $data['id'];
        }

        return array_filter($data, fn ($value) => $value !== null);
    }
}
