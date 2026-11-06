<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models;

interface HasAvatar
{
    public function avatarName(): string;

    public function avatarUrl(): string;
}
