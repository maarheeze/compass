<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compass_pull_requests', static function (Blueprint $table): void {
            $table->id();

            $table->foreignId('repository_id')
                ->constrained('compass_repositories')
                ->cascadeOnDelete();

            $table->string('github_id')->unique();
            $table->unsignedInteger('number');
            $table->string('title');
            $table->string('url');
            $table->string('author_login')->nullable();
            $table->boolean('is_draft');
            $table->timestamp('opened_at');
            $table->string('collection_run_id');
            $table->timestamp('collected_at');

            $table->timestamps();
        });
    }
};
