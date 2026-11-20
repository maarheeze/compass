<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compass_site_domains', static function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('site_id')
                ->constrained('compass_sites')
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('type');
            $table->string('status');

            $table->timestamps();

            $table->unique(['site_id', 'name']);
        });
    }
};
