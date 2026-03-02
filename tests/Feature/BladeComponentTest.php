<?php

namespace FastComments\Laravel\Tests\Feature;

use FastComments\Laravel\Tests\TestCase;
use Illuminate\Support\Facades\Auth;

class BladeComponentTest extends TestCase
{
    public function test_fastcomments_component_renders_container_div(): void
    {
        $html = $this->blade('<x-fastcomments />');

        $html->assertSee('<div id="fc-', false);
        $html->assertSee('</div>', false);
    }

    public function test_fastcomments_component_loads_correct_script(): void
    {
        $html = $this->blade('<x-fastcomments />');

        // @js() escapes forward slashes in rendered output
        $html->assertSee('cdn.fastcomments.com', false);
        $html->assertSee('embed-v2.min.js', false);
        $html->assertSee('FastCommentsUI', false);
    }

    public function test_fastcomments_component_includes_tenant_id(): void
    {
        $html = $this->blade('<x-fastcomments />');

        $html->assertSee('test-tenant-id', false);
    }

    public function test_fastcomments_component_with_url_id(): void
    {
        $html = $this->blade('<x-fastcomments url-id="my-page" />');

        $html->assertSee('my-page', false);
    }

    public function test_fastcomments_live_chat_component_renders(): void
    {
        $html = $this->blade('<x-fastcomments-live-chat />');

        $html->assertSee('cdn.fastcomments.com', false);
        $html->assertSee('embed-live-chat.min.js', false);
        $html->assertSee('FastCommentsLiveChat', false);
    }

    public function test_fastcomments_comment_count_component_renders(): void
    {
        $html = $this->blade('<x-fastcomments-comment-count />');

        $html->assertSee('cdn.fastcomments.com', false);
        $html->assertSee('widget-comment-count.min.js', false);
        $html->assertSee('FastCommentsCommentCount', false);
    }

    public function test_fastcomments_component_includes_sso_when_enabled(): void
    {
        $this->app['config']->set('fastcomments.sso.enabled', true);
        $this->app['config']->set('fastcomments.sso.mode', 'simple');
        $this->app['config']->set('fastcomments.sso.login_url', 'https://example.com/login');
        $this->app['config']->set('fastcomments.sso.logout_url', 'https://example.com/logout');

        // Re-bind SSO manager with new config
        $this->refreshApplication();
        $this->defineEnvironment($this->app);
        $this->app['config']->set('fastcomments.sso.enabled', true);
        $this->app['config']->set('fastcomments.sso.mode', 'simple');
        $this->app['config']->set('fastcomments.sso.login_url', 'https://example.com/login');
        $this->app['config']->set('fastcomments.sso.logout_url', 'https://example.com/logout');

        Auth::shouldReceive('user')->andReturn(null);

        $html = $this->blade('<x-fastcomments />');

        // @js() escapes slashes, so check for key parts
        $html->assertSee('example.com', false);
        $html->assertSee('loginURL', false);
        $html->assertSee('logoutURL', false);
    }

    public function test_eu_region_uses_eu_cdn(): void
    {
        $this->app['config']->set('fastcomments.region', 'eu');
        $this->refreshApplication();
        $this->defineEnvironment($this->app);
        $this->app['config']->set('fastcomments.region', 'eu');

        $html = $this->blade('<x-fastcomments />');

        $html->assertSee('cdn-eu.fastcomments.com', false);
        $html->assertSee('embed-v2.min.js', false);
    }
}
