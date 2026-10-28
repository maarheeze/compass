<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compass_alert_history_entries', static function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('alert_id')
                ->constrained('compass_alerts')
                ->cascadeOnDelete();
            $table->foreignUuid('user_id')
                ->nullable()
                ->constrained('compass_users')
                ->nullOnDelete();

            $table->string('type');
            $table->timestamp('occurred_at');

            $table->timestamps();
        });
    }
};
