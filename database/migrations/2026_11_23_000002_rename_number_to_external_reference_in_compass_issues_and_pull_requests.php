<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['compass_issues', 'compass_pull_requests'] as $issuesTable) {
            Schema::table($issuesTable, static function (Blueprint $table): void {
                $table->renameColumn('number', 'external_reference');
            });

            Schema::table($issuesTable, static function (Blueprint $table): void {
                $table->string('external_reference')->change();
            });
        }
    }
};
