<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compass_pull_request_assignees', static function (Blueprint $table): void {
            $table->id();

            $table->foreignId('pull_request_id')
                ->constrained('compass_pull_requests')
                ->cascadeOnDelete();

            $table->string('login')->index();

            $table->timestamps();
        });
    }
};
