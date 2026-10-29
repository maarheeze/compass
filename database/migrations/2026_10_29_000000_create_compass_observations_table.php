<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compass_observations', static function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('alert_id')
                ->nullable()
                ->constrained('compass_alerts')
                ->cascadeOnDelete();
            $table->uuidMorphs('subject');

            $table->string('alert_type');
            $table->string('condition');
            $table->timestamp('observed_at');

            $table->timestamps();
        });
    }
};
