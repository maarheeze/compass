<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compass_repositories', static function (Blueprint $table): void {
            $table->string('package_json_oid')->nullable();
            $table->string('dependabot_yml_oid')->nullable();
        });
    }
};
