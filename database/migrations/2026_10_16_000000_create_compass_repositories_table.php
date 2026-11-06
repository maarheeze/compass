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
            $table->uuid('id')->primary();

            $table->string('source');
            $table->string('external_id');
            $table->string('name');
            $table->string('full_name');
            $table->string('url');
            $table->string('default_branch');
            $table->string('last_commit_sha');
            $table->timestamp('last_commit_committed_at');
            $table->string('visibility');
            $table->string('composer_json_version')->nullable();
            $table->string('composer_lock_version')->nullable();
            $table->string('package_json_version')->nullable();
            $table->string('dependabot_yml_version')->nullable();
            $table->string('run_id');
            $table->timestamp('collected_at');
            $table->timestamp('missing_at')->nullable();
            $table->timestamp('missing_ignored_at')->nullable();

            $table->timestamps();

            $table->unique(['source', 'external_id']);
        });
    }
};
