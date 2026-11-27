<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compass_site_reports', static function (Blueprint $table): void {
            $table->timestamp('scheduler_last_ran_at')->nullable();
            $table->unsignedInteger('scheduler_grace_minutes')->nullable();
            $table->string('queue_driver')->nullable();
        });
    }
};
