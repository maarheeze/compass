<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

use Illuminate\Database\Eloquent\Model;
use Maarheeze\Compass\Alerts\AlertSubject;

interface SubjectJob extends LockedJob
{
    public function skipsSubject(): bool;

    public function subject(): AlertSubject&Model;
}
