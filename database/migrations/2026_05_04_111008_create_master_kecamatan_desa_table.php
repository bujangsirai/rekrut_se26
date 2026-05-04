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
        Schema::create('master_kecamatan_desa', function (Blueprint $table) {
            $table->id();
            $table->string('kode_kec', 7);
            $table->string('nama_kec', 50);
            $table->string('kode_desa', 10);
            $table->string('nama_desa', 100);

            $table->index('kode_kec');
            $table->index('kode_desa');
            $table->unique(['kode_kec', 'kode_desa']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_kecamatan_desa');
    }
};
