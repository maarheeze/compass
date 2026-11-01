<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compass_run_failures', static function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('run_id')
                ->constrained('compass_runs')
                ->cascadeOnDelete();

            $table->text('message');

            $table->timestamps();
        });
    }
};
