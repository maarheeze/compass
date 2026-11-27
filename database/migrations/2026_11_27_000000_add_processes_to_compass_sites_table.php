<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compass_sites', static function (Blueprint $table): void {
            $table->string('queue_worker_state')->nullable();
            $table->timestamp('processes_collected_at')->nullable();
        });
    }
};
