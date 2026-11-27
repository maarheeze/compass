<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compass_site_scheduled_tasks', static function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('site_report_id')
                ->constrained('compass_site_reports')
                ->cascadeOnDelete();

            $table->string('command');
            $table->string('expression');

            $table->timestamps();
        });
    }
};
