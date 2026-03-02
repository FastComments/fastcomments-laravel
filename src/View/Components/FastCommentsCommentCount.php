<?php

namespace FastComments\Laravel\View\Components;

use FastComments\Laravel\FastCommentsManager;

class FastCommentsCommentCount extends BaseComponent
{
    protected function containerPrefix(): string
    {
        return 'fc-cc-';
    }

    protected function constructorName(): string
    {
        return 'FastCommentsCommentCount';
    }

    protected function scriptPath(): string
    {
        return '/js/widget-comment-count.min.js';
    }

    public function __construct(
        FastCommentsManager $manager,
        ?string $urlId = null,
        ?bool $numberOnly = null,
        ?bool $isLive = null,
    ) {
        $this->initWidget($manager, [
            'urlId' => $urlId,
            'numberOnly' => $numberOnly,
            'isLive' => $isLive,
        ]);
    }
}
