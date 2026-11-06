<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Models\HasAvatar;

class Avatar extends Component
{
    public function __construct(
        public readonly HasAvatar $person,
        public readonly ?string $url,
        private readonly ViewFactory $view,
    ) {
    }

    public function render(): View
    {
        return $this->view->make('compass::components.avatar');
    }
}
