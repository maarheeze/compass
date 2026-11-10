<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View;

use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Component;

readonly class ComponentHtml
{
    public function html(Component $component): ?string
    {
        if (!$component->shouldRender()) {
            return null;
        }

        return BladeCompiler::renderComponent($component);
    }
}
