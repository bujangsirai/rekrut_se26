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
        Schema::create('mitra', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lengkap');
            $table->string('email');
            $table->string('url_ktp')->nullable();
            $table->string('kode_akses', 64)->unique();
            $table->timestamp('kode_akses_kedaluwarsa_pada')->nullable();
            $table->timestamp('terakhir_diakses_pada')->nullable();
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan']);
            $table->string('kode_kec', 7);
            $table->string('kode_desa', 10);
            $table->text('alamat_lengkap');
            $table->date('tanggal_lahir');
            $table->string('tempat_lahir');
            $table->enum('status_perkawinan', ['Belum Kawin', 'Kawin', 'Cerai Hidup', 'Cerai Mati']);
            $table->enum('pendidikan_terakhir', ['SLTP/Kebawah', 'SLTA', 'DI/DII/DII', 'DIV/S1/S2/S3']);
            $table->string('pekerjaan', 120);
            $table->string('nomor_whatsapp', 30);
            $table->longText('riwayat_kegiatan_bps')->nullable();
            $table->enum('status_sobat', ['Sudah', 'Belum'])->default('Belum');
            $table->enum('status_wawancara', ['Belum Wawancara', 'Sudah Wawancara'])->default('Belum Wawancara');
            $table->enum('status_kelulusan', ['Lulus', 'Belum Lulus'])->default('Belum Lulus');
            $table->timestamps();

            $table->index(['kode_kec', 'kode_desa']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mitra');
    }
};
