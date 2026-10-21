<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compass_composer_lock_packages', static function (Blueprint $table): void {
            $table->id();

            $table->foreignId('composer_lock_id')
                ->constrained('compass_composer_locks')
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('version');
            $table->string('section');

            $table->timestamps();
        });
    }
};
