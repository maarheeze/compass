<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compass_projects', static function (Blueprint $table): void {
            $table->id();

            $table->string('repository')->unique();
            $table->string('default_branch');
            $table->string('commit_sha');
            $table->timestamp('committed_at');
            $table->timestamp('collected_at');

            $table->timestamps();
        });
    }
};
