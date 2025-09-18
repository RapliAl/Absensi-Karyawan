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
        Schema::table('rekap_bulanans', function (Blueprint $table) {
            $table->string('file_export')->nullable()->after('total_hari_kerja');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rekap_bulanans', function (Blueprint $table) {
            $table->dropColumn('file_export');
        });
    }
};
