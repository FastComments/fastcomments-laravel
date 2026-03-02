<?php

namespace FastComments\Laravel\View\Components;

use FastComments\Laravel\FastCommentsManager;
use Illuminate\View\Component;

abstract class BaseComponent extends Component
{
    public string $containerId;
    public string $scriptSrc;
    public string $constructorName;
    /** @var array<string, mixed> */
    public array $widgetConfig;

    abstract protected function containerPrefix(): string;

    abstract protected function constructorName(): string;

    abstract protected function scriptPath(): string;

    protected function initWidget(FastCommentsManager $manager, array $overrides): void
    {
        $this->containerId = $this->containerPrefix() . bin2hex(random_bytes(8));
        $this->constructorName = $this->constructorName();
        $this->scriptSrc = $manager->cdnHost() . $this->scriptPath();
        $this->widgetConfig = $manager->widgetConfig($this->buildOverrides($overrides));

        if (app()->hasDebugModeEnabled() && empty($this->widgetConfig['tenantId'])) {
            logger()->warning('FastComments: tenant_id is not set. Set FASTCOMMENTS_TENANT_ID in your .env file.');
        }
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    protected function buildOverrides(array $params): array
    {
        return array_filter($params, fn ($v) => $v !== null);
    }

    protected function injectSso(FastCommentsManager $manager): void
    {
        $ssoPayload = $manager->sso()->forWidget();
        if ($ssoPayload !== null) {
            $this->widgetConfig = array_merge($this->widgetConfig, $ssoPayload);
        }
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('fastcomments::components.fastcomments');
    }
}
