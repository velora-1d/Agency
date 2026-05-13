<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_finance_split_items', function (Blueprint $table): void {
            $table->string('component_type', 30)->nullable()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('project_finance_split_items', function (Blueprint $table): void {
            $table->dropColumn('component_type');
        });
    }
};
