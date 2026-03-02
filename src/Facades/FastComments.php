<?php

namespace FastComments\Laravel\Facades;

use FastComments\Client\Api\DefaultApi;
use FastComments\Client\Api\PublicApi;
use FastComments\Laravel\SSO\SSOManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static DefaultApi admin()
 * @method static PublicApi publicApi()
 * @method static SSOManager sso()
 * @method static string tenantId()
 * @method static ?string region()
 * @method static string cdnHost()
 * @method static string apiHost()
 * @method static array widgetConfig(array $overrides = [])
 *
 * @see \FastComments\Laravel\FastCommentsManager
 */
class FastComments extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'fastcomments';
    }
}
