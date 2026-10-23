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

            $table->string('github_id')->unique();
            $table->string('name');
            $table->string('url');
            $table->string('default_branch');
            $table->string('last_commit_sha');
            $table->string('visibility');
            $table->string('composer_json_oid')->nullable();
            $table->string('composer_lock_oid')->nullable();
            $table->string('collection_run_id');
            $table->timestamp('collected_at');
            $table->timestamp('missing_at')->nullable();
            $table->timestamp('missing_ignored_at')->nullable();

            $table->timestamps();
        });
    }
};
