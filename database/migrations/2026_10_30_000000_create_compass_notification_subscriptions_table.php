<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compass_notification_subscriptions', static function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('user_id')
                ->constrained('compass_users')
                ->cascadeOnDelete();

            $table->string('alert_type');
            $table->string('channel');

            $table->timestamps();

            $table->unique(['user_id', 'alert_type', 'channel'], 'compass_notification_subscriptions_unique');
        });
    }
};
