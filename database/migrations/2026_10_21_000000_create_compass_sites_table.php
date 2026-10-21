<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compass_sites', static function (Blueprint $table): void {
            $table->id();

            $table->string('forge_id')->unique();
            $table->string('name');
            $table->string('url');
            $table->string('status');
            $table->string('php_version')->nullable();
            $table->string('repository_url')->nullable();
            $table->string('repository_branch')->nullable();
            $table->string('server_name');
            $table->string('server_slug');
            $table->string('server_ip_address')->nullable();
            $table->string('deployment_status')->nullable();
            $table->string('deployed_commit_hash')->nullable();
            $table->text('deployed_commit_message')->nullable();
            $table->timestamp('deployment_started_at')->nullable();
            $table->timestamp('deployment_ended_at')->nullable();
            $table->string('collection_run_id');
            $table->boolean('is_active');
            $table->timestamp('collected_at');

            $table->timestamps();
        });
    }
};
