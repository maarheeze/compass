<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compass_site_reports', static function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('site_id')
                ->constrained('compass_sites')
                ->cascadeOnDelete();

            $table->string('source');
            $table->string('status');
            $table->boolean('app_debug')->nullable();
            $table->string('app_env')->nullable();
            $table->string('app_name')->nullable();
            $table->string('key')->nullable();
            $table->string('path')->nullable();
            $table->timestamp('collected_at');

            $table->timestamps();

            $table->unique(['site_id', 'source']);
        });
    }
};
