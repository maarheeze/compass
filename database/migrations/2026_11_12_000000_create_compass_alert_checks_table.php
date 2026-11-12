<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compass_alert_checks', static function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->uuidMorphs('subject');

            $table->string('alert_type_identifier');
            $table->string('condition');
            $table->timestamp('observed_at');

            $table->timestamps();

            $table->unique(
                ['alert_type_identifier', 'subject_type', 'subject_id'],
                'compass_alert_checks_unique',
            );
        });
    }
};
