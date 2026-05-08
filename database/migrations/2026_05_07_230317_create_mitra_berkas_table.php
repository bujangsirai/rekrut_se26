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
        Schema::create('mitra_berkas', function (Blueprint $table) {
            $table->id();
            $table->char('nik', 16);
            $table->string('jenis_berkas');
            $table->string('file_path');
            $table->timestamps();

            $table->foreign('nik')->references('nik')->on('mitra')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mitra_berkas');
    }
};
