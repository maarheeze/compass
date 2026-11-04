<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compass_site_endpoints', static function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('site_report_id')
                ->constrained('compass_site_reports')
                ->cascadeOnDelete();

            $table->string('path');

            $table->timestamps();

            $table->unique(['site_report_id', 'path']);
        });
    }
};
