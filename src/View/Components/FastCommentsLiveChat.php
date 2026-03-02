<?php

namespace FastComments\Laravel\View\Components;

use FastComments\Laravel\FastCommentsManager;

class FastCommentsLiveChat extends BaseComponent
{
    protected function containerPrefix(): string
    {
        return 'fc-lc-';
    }

    protected function constructorName(): string
    {
        return 'FastCommentsLiveChat';
    }

    protected function scriptPath(): string
    {
        return '/js/embed-live-chat.min.js';
    }

    public function __construct(
        FastCommentsManager $manager,
        ?string $urlId = null,
        ?string $url = null,
        ?bool $readonly = null,
        ?string $locale = null,
        ?bool $hasDarkBackground = null,
        ?string $defaultSortDirection = null,
    ) {
        $this->initWidget($manager, [
            'urlId' => $urlId,
            'url' => $url,
            'readonly' => $readonly,
            'locale' => $locale,
            'hasDarkBackground' => $hasDarkBackground,
            'defaultSortDirection' => $defaultSortDirection,
        ]);

        $this->injectSso($manager);
    }
}
