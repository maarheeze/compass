<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compass_runs', static function (Blueprint $table): void {
            $table->nullableUuidMorphs('subject');

            $table->index('collector');
            $table->index('task');
        });
    }
};
