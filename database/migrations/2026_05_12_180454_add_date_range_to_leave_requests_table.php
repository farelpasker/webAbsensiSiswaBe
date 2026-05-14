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
        if (!Schema::hasColumn('leave_requests', 'start_date') && !Schema::hasColumn('leave_requests', 'end_date')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                $table->date('start_date')->nullable()->after('proof');
                $table->date('end_date')->nullable()->after('start_date');
                $table->dropColumn('date');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'end_date']);
            $table->date('date')->after('proof')->nullable();
        });
    }
};
