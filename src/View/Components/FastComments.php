<?php

namespace FastComments\Laravel\View\Components;

use FastComments\Laravel\FastCommentsManager;

class FastComments extends BaseComponent
{
    protected function containerPrefix(): string
    {
        return 'fc-';
    }

    protected function constructorName(): string
    {
        return 'FastCommentsUI';
    }

    protected function scriptPath(): string
    {
        return '/js/embed-v2.min.js';
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
