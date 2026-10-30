<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compass_dependabot_ymls', static function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('repository_id')
                ->unique()
                ->constrained('compass_repositories')
                ->cascadeOnDelete();

            $table->string('oid');
            $table->json('package_ecosystems');

            $table->timestamps();
        });
    }
};
