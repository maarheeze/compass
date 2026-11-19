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
            $table->uuid('id')->primary();

            $table->foreignUuid('pull_request_id')
                ->index()
                ->constrained('compass_pull_requests')
                ->cascadeOnDelete();

            $table->string('login');
            $table->string('avatar_url');

            $table->timestamps();
        });
    }
};
