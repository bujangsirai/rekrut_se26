<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterKecamatanDesaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('master_kecamatan_desa')->upsert([
            ['id' => 1, 'kode_kec' => '9999999', 'nama_kec' => 'LUAR SUMBAWA BARAT', 'kode_desa' => '9999999999', 'nama_desa' => 'LUAR SUMBAWA BARAT'],
            ['id' => 2, 'kode_kec' => '5207010', 'nama_kec' => 'SEKONGKANG', 'kode_desa' => '5207010001', 'nama_desa' => 'SEKONGKANG BAWAH'],
            ['id' => 3, 'kode_kec' => '5207010', 'nama_kec' => 'SEKONGKANG', 'kode_desa' => '5207010002', 'nama_desa' => 'SEKONGKANG ATAS'],
            ['id' => 4, 'kode_kec' => '5207010', 'nama_kec' => 'SEKONGKANG', 'kode_desa' => '5207010003', 'nama_desa' => 'TONGO'],
            ['id' => 5, 'kode_kec' => '5207010', 'nama_kec' => 'SEKONGKANG', 'kode_desa' => '5207010004', 'nama_desa' => 'AI KANGKUNG'],
            ['id' => 6, 'kode_kec' => '5207010', 'nama_kec' => 'SEKONGKANG', 'kode_desa' => '5207010005', 'nama_desa' => 'TATAR'],
            ['id' => 7, 'kode_kec' => '5207010', 'nama_kec' => 'SEKONGKANG', 'kode_desa' => '5207010006', 'nama_desa' => 'TALONANG BARU'],
            ['id' => 8, 'kode_kec' => '5207010', 'nama_kec' => 'SEKONGKANG', 'kode_desa' => '5207010007', 'nama_desa' => 'KEMUNING'],

            ['id' => 10, 'kode_kec' => '5207020', 'nama_kec' => 'JEREWEH', 'kode_desa' => '5207020002', 'nama_desa' => 'BELO'],
            ['id' => 11, 'kode_kec' => '5207020', 'nama_kec' => 'JEREWEH', 'kode_desa' => '5207020004', 'nama_desa' => 'GOA'],
            ['id' => 12, 'kode_kec' => '5207020', 'nama_kec' => 'JEREWEH', 'kode_desa' => '5207020005', 'nama_desa' => 'BERU'],
            ['id' => 13, 'kode_kec' => '5207020', 'nama_kec' => 'JEREWEH', 'kode_desa' => '5207020006', 'nama_desa' => 'DASAN ANYAR'],

            ['id' => 14, 'kode_kec' => '5207021', 'nama_kec' => 'MALUK', 'kode_desa' => '5207021001', 'nama_desa' => 'MALUK'],
            ['id' => 15, 'kode_kec' => '5207021', 'nama_kec' => 'MALUK', 'kode_desa' => '5207021002', 'nama_desa' => 'BENETE'],
            ['id' => 16, 'kode_kec' => '5207021', 'nama_kec' => 'MALUK', 'kode_desa' => '5207021003', 'nama_desa' => 'BUKIT DAMAI'],
            ['id' => 17, 'kode_kec' => '5207021', 'nama_kec' => 'MALUK', 'kode_desa' => '5207021004', 'nama_desa' => 'MANTUN'],
            ['id' => 18, 'kode_kec' => '5207021', 'nama_kec' => 'MALUK', 'kode_desa' => '5207021005', 'nama_desa' => 'PASIR PUTIH'],

            ['id' => 19, 'kode_kec' => '5207030', 'nama_kec' => 'TALIWANG', 'kode_desa' => '5207030003', 'nama_desa' => 'LALAR LIANG'],
            ['id' => 20, 'kode_kec' => '5207030', 'nama_kec' => 'TALIWANG', 'kode_desa' => '5207030004', 'nama_desa' => 'LABUAN LALAR'],
            ['id' => 21, 'kode_kec' => '5207030', 'nama_kec' => 'TALIWANG', 'kode_desa' => '5207030005', 'nama_desa' => 'KUANG'],
            ['id' => 22, 'kode_kec' => '5207030', 'nama_kec' => 'TALIWANG', 'kode_desa' => '5207030006', 'nama_desa' => 'LABUAN KERTASARI'],
            ['id' => 23, 'kode_kec' => '5207030', 'nama_kec' => 'TALIWANG', 'kode_desa' => '5207030007', 'nama_desa' => 'BUGIS'],
            ['id' => 24, 'kode_kec' => '5207030', 'nama_kec' => 'TALIWANG', 'kode_desa' => '5207030008', 'nama_desa' => 'DALAM'],
            ['id' => 25, 'kode_kec' => '5207030', 'nama_kec' => 'TALIWANG', 'kode_desa' => '5207030009', 'nama_desa' => 'MENALA'],
            ['id' => 26, 'kode_kec' => '5207030', 'nama_kec' => 'TALIWANG', 'kode_desa' => '5207030010', 'nama_desa' => 'SAMPIR'],
            ['id' => 27, 'kode_kec' => '5207030', 'nama_kec' => 'TALIWANG', 'kode_desa' => '5207030011', 'nama_desa' => 'SELOTO'],
            ['id' => 28, 'kode_kec' => '5207030', 'nama_kec' => 'TALIWANG', 'kode_desa' => '5207030012', 'nama_desa' => 'TAMEKAN'],
            ['id' => 29, 'kode_kec' => '5207030', 'nama_kec' => 'TALIWANG', 'kode_desa' => '5207030013', 'nama_desa' => 'BANJAR'],
            ['id' => 30, 'kode_kec' => '5207030', 'nama_kec' => 'TALIWANG', 'kode_desa' => '5207030014', 'nama_desa' => 'BATU PUTIH'],
            ['id' => 31, 'kode_kec' => '5207030', 'nama_kec' => 'TALIWANG', 'kode_desa' => '5207030015', 'nama_desa' => 'TELAGA BERTONG'],
            ['id' => 32, 'kode_kec' => '5207030', 'nama_kec' => 'TALIWANG', 'kode_desa' => '5207030016', 'nama_desa' => 'SERMONG'],
            ['id' => 33, 'kode_kec' => '5207030', 'nama_kec' => 'TALIWANG', 'kode_desa' => '5207030017', 'nama_desa' => 'ARAB KENANGAN'],

            ['id' => 34, 'kode_kec' => '5207031', 'nama_kec' => 'BRANG ENE', 'kode_desa' => '5207031001', 'nama_desa' => 'KALIMANTONG'],
            ['id' => 35, 'kode_kec' => '5207031', 'nama_kec' => 'BRANG ENE', 'kode_desa' => '5207031002', 'nama_desa' => 'MURA'],
            ['id' => 36, 'kode_kec' => '5207031', 'nama_kec' => 'BRANG ENE', 'kode_desa' => '5207031003', 'nama_desa' => 'LAMPOK'],
            ['id' => 37, 'kode_kec' => '5207031', 'nama_kec' => 'BRANG ENE', 'kode_desa' => '5207031004', 'nama_desa' => 'MANEMENG'],
            ['id' => 38, 'kode_kec' => '5207031', 'nama_kec' => 'BRANG ENE', 'kode_desa' => '5207031005', 'nama_desa' => 'MUJAHIDDIN'],
            ['id' => 39, 'kode_kec' => '5207031', 'nama_kec' => 'BRANG ENE', 'kode_desa' => '5207031006', 'nama_desa' => 'MATAIYANG'],

            ['id' => 40, 'kode_kec' => '5207040', 'nama_kec' => 'BRANG REA', 'kode_desa' => '5207040001', 'nama_desa' => 'SAPUGARA BREE'],
            ['id' => 41, 'kode_kec' => '5207040', 'nama_kec' => 'BRANG REA', 'kode_desa' => '5207040002', 'nama_desa' => 'DESA BERU'],
            ['id' => 42, 'kode_kec' => '5207040', 'nama_kec' => 'BRANG REA', 'kode_desa' => '5207040003', 'nama_desa' => 'TEPAS'],
            ['id' => 43, 'kode_kec' => '5207040', 'nama_kec' => 'BRANG REA', 'kode_desa' => '5207040004', 'nama_desa' => 'BANGKAT MONTEH'],
            ['id' => 44, 'kode_kec' => '5207040', 'nama_kec' => 'BRANG REA', 'kode_desa' => '5207040005', 'nama_desa' => 'SEMINAR SALIT'],
            ['id' => 45, 'kode_kec' => '5207040', 'nama_kec' => 'BRANG REA', 'kode_desa' => '5207040006', 'nama_desa' => 'TEPAS SEPAKAT'],
            ['id' => 46, 'kode_kec' => '5207040', 'nama_kec' => 'BRANG REA', 'kode_desa' => '5207040007', 'nama_desa' => 'MOTENG'],
            ['id' => 47, 'kode_kec' => '5207040', 'nama_kec' => 'BRANG REA', 'kode_desa' => '5207040008', 'nama_desa' => 'LAMUNTET'],
            ['id' => 48, 'kode_kec' => '5207040', 'nama_kec' => 'BRANG REA', 'kode_desa' => '5207040009', 'nama_desa' => 'RARAK RONGIS'],

            ['id' => 49, 'kode_kec' => '5207050', 'nama_kec' => 'SETELUK', 'kode_desa' => '5207050002', 'nama_desa' => 'KELANIR'],
            ['id' => 50, 'kode_kec' => '5207050', 'nama_kec' => 'SETELUK', 'kode_desa' => '5207050003', 'nama_desa' => 'MERARAN'],
            ['id' => 51, 'kode_kec' => '5207050', 'nama_kec' => 'SETELUK', 'kode_desa' => '5207050004', 'nama_desa' => 'AIR SUNING'],
            ['id' => 52, 'kode_kec' => '5207050', 'nama_kec' => 'SETELUK', 'kode_desa' => '5207050005', 'nama_desa' => 'REMPE'],
            ['id' => 53, 'kode_kec' => '5207050', 'nama_kec' => 'SETELUK', 'kode_desa' => '5207050006', 'nama_desa' => 'TAPIR'],
            ['id' => 54, 'kode_kec' => '5207050', 'nama_kec' => 'SETELUK', 'kode_desa' => '5207050007', 'nama_desa' => 'SETELUK ATAS'],
            ['id' => 55, 'kode_kec' => '5207050', 'nama_kec' => 'SETELUK', 'kode_desa' => '5207050008', 'nama_desa' => 'SETELUK TENGAH'],
            ['id' => 56, 'kode_kec' => '5207050', 'nama_kec' => 'SETELUK', 'kode_desa' => '5207050009', 'nama_desa' => 'LAMUSUNG'],
            ['id' => 57, 'kode_kec' => '5207050', 'nama_kec' => 'SETELUK', 'kode_desa' => '5207050010', 'nama_desa' => 'LOKA'],
            ['id' => 58, 'kode_kec' => '5207050', 'nama_kec' => 'SETELUK', 'kode_desa' => '5207050011', 'nama_desa' => 'SERAN'],

            ['id' => 59, 'kode_kec' => '5207051', 'nama_kec' => 'POTO TANO', 'kode_desa' => '5207051001', 'nama_desa' => 'SENAYAN'],
            ['id' => 60, 'kode_kec' => '5207051', 'nama_kec' => 'POTO TANO', 'kode_desa' => '5207051002', 'nama_desa' => 'MANTAR'],
            ['id' => 61, 'kode_kec' => '5207051', 'nama_kec' => 'POTO TANO', 'kode_desa' => '5207051003', 'nama_desa' => 'KIANTAR'],
            ['id' => 62, 'kode_kec' => '5207051', 'nama_kec' => 'POTO TANO', 'kode_desa' => '5207051004', 'nama_desa' => 'POTO TANO'],
            ['id' => 63, 'kode_kec' => '5207051', 'nama_kec' => 'POTO TANO', 'kode_desa' => '5207051005', 'nama_desa' => 'UPT TAMBAK SARI'],
            ['id' => 64, 'kode_kec' => '5207051', 'nama_kec' => 'POTO TANO', 'kode_desa' => '5207051006', 'nama_desa' => 'TUA NANGA'],
            ['id' => 65, 'kode_kec' => '5207051', 'nama_kec' => 'POTO TANO', 'kode_desa' => '5207051007', 'nama_desa' => 'TEBO'],
            ['id' => 66, 'kode_kec' => '5207051', 'nama_kec' => 'POTO TANO', 'kode_desa' => '5207051008', 'nama_desa' => 'KOKAR LIAN'],
        ], ['kode_kec', 'kode_desa'], ['nama_kec', 'nama_desa']);
    }
}
