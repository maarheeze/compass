<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compass_alerts', static function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('assignee_id')
                ->nullable()
                ->constrained('compass_users')
                ->nullOnDelete();
            $table->uuidMorphs('subject');

            $table->string('type');
            $table->string('condition');
            $table->timestamp('resolved_at')->nullable();
            $table->string('status');
            $table->timestamp('done_at')->nullable();
            $table->boolean('is_muted');

            $table->timestamps();
        });
    }
};
