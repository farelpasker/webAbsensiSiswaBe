<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $modelKey = $columnNames['model_morph_key'] ?? 'model_id';

        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($modelKey) {
            $table->dropIndex(['model_id', 'model_type']);
        });

        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($modelKey) {
            $table->dropIndex(['model_id', 'model_type']);
        });

        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($modelKey) {
            $table->string($modelKey)->change();
        });

        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($modelKey) {
            $table->string($modelKey)->change();
        });

        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($modelKey) {
            $table->index([$modelKey, 'model_type']);
        });

        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($modelKey) {
            $table->index([$modelKey, 'model_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $modelKey = $columnNames['model_morph_key'] ?? 'model_id';

        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($modelKey) {
            $table->dropIndex(['model_id', 'model_type']);
        });

        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($modelKey) {
            $table->dropIndex(['model_id', 'model_type']);
        });

        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($modelKey) {
            $table->unsignedBigInteger($modelKey)->change();
        });

        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($modelKey) {
            $table->unsignedBigInteger($modelKey)->change();
        });

        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($modelKey) {
            $table->index([$modelKey, 'model_type']);
        });

        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($modelKey) {
            $table->index([$modelKey, 'model_type']);
        });
    }
};
