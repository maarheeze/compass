<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compass_runs', static function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->string('type');
            $table->string('batch_id');
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('failed_job_count')->nullable();

            $table->timestamps();
        });
    }
};
