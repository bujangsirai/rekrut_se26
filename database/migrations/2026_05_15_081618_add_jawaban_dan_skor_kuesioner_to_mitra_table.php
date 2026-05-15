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
        Schema::table('mitra', function (Blueprint $table) {
            $table->json('jawaban_kuesioner')->nullable()->after('status_kelulusan');
            $table->integer('skor_kuesioner')->nullable()->after('jawaban_kuesioner');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mitra', function (Blueprint $table) {
            $table->dropColumn(['jawaban_kuesioner', 'skor_kuesioner']);
        });
    }
};
