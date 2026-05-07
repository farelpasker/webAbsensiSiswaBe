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
        if(!Schema::hasColumn('attendances', 'latitude')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->decimal('latitude', 10, 7)->nullable()->after('time_out');
            });
        }

        if(!Schema::hasColumn('attendances', 'longitude')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};
