<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compass_site_endpoint_checks', static function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('site_id')
                ->constrained('compass_sites')
                ->cascadeOnDelete();

            $table->string('path');
            $table->string('url');
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->boolean('is_successful');

            $table->timestamps();

            $table->index(['site_id', 'path']);
        });
    }
};
