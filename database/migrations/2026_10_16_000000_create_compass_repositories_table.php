<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compass_repositories', static function (Blueprint $table): void {
            $table->id();

            $table->string('name')->unique();
            $table->string('default_branch');
            $table->string('last_commit_sha');
            $table->string('visibility');
            $table->string('collection_run_id');
            $table->boolean('is_active');
            $table->timestamp('collected_at');

            $table->timestamps();
        });
    }
};
