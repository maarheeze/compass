<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compass_composer_json_packages', static function (Blueprint $table): void {
            $table->id();

            $table->foreignId('composer_json_id')
                ->constrained('compass_composer_jsons')
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('constraint');
            $table->string('section');

            $table->timestamps();
        });
    }
};
