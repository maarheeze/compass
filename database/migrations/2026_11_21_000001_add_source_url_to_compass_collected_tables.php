<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['compass_repositories', 'compass_issues', 'compass_pull_requests'] as $collectedTable) {
            Schema::table($collectedTable, static function (Blueprint $table): void {
                $table->renameColumn('url', 'source_url');
            });

            Schema::table($collectedTable, static function (Blueprint $table): void {
                $table->string('source_url')
                    ->nullable()
                    ->change();
            });
        }

        Schema::table('compass_sites', static function (Blueprint $table): void {
            $table->string('source_url')->nullable();
        });
    }
};
