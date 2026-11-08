<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Maarheeze\Compass\Alerts\AlertTypes;

/**
 * @implements Scope<Model>
 */
readonly class RegisteredAlertTypes implements Scope
{
    public function __construct(
        private AlertTypes $alertTypes,
    ) {
    }

    /**
     * @param Builder<Model> $builder
     */
    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereIn($model->qualifyColumn('alert_type_identifier'), $this->alertTypes->identifiers());
    }
}
