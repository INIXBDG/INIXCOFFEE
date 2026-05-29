<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cost;

class CostSeeder extends Seeder
{
    public function run(): void
    {
        $costs = [
            // Data Variable Cost
            ['name' => 'DISCOUNT Penjualan', 'type' => 'Biaya-Biaya Training', 'status' => 'variable'],
            ['name' => 'PAYMENT ADVANCED Penjualan', 'type' => 'Biaya-Biaya Training', 'status' => 'variable'],
            ['name' => 'Biaya Keperluan discount cabang', 'type' => 'Biaya-Biaya Training', 'status' => 'variable'],
            ['name' => 'Biaya Akomodasi  Instruktur (Kontrak: INIX)', 'type' => 'Biaya-Biaya Training', 'status' => 'variable'],
            ['name' => 'Biaya Transportasi  Instruktur (Kontrak: INIX)', 'type' => 'Biaya-Biaya Training', 'status' => 'variable'],
            ['name' => 'Biaya Fee Instruktur (Kontrak: INIX)', 'type' => 'Biaya-Biaya Training', 'status' => 'variable'],
            ['name' => 'Biaya Fee Instruktur (u/ pembuatan modul)', 'type' => 'Biaya-Biaya Training', 'status' => 'variable'],
            ['name' => 'Biaya Fee Instruktur (Kontrak:OUTSOURCE)', 'type' => 'Biaya-Biaya Training', 'status' => 'variable'],
            ['name' => 'Biaya Sewa Router & Switch', 'type' => 'Biaya-Biaya Training', 'status' => 'variable'],
            ['name' => 'Biaya Sewa Komputer , TV & LCD', 'type' => 'Biaya-Biaya Training', 'status' => 'variable'],
            ['name' => 'Biaya Konsumsi Training (Lunch katering)', 'type' => 'Biaya-Biaya Training', 'status' => 'variable'],
            ['name' => 'Biaya Konsumsi Training (Coffee Break harian)', 'type' => 'Biaya-Biaya Training', 'status' => 'variable'],
            ['name' => 'Biaya Konsumsi Training (Lunch hari terakhir)', 'type' => 'Biaya-Biaya Training', 'status' => 'variable'],
            ['name' => 'Biaya Transportasi (Konsumsi/Lunch hr terakhir, Oleh2 peserta)', 'type' => 'Biaya-Biaya Training', 'status' => 'variable'],
            ['name' => 'Biaya keperluan kelas (ATK)', 'type' => 'Biaya-Biaya Training', 'status' => 'variable'],
            ['name' => 'Biaya Pembuatan SOUVENIR', 'type' => 'Biaya-Biaya Training', 'status' => 'variable'],
            ['name' => 'Biaya Pembelian Modul Authorized', 'type' => 'Biaya-Biaya Training', 'status' => 'variable'],
            ['name' => 'Biaya Photo Copy Modul', 'type' => 'Biaya-Biaya Training', 'status' => 'variable'],
            ['name' => 'Biaya Modul Reguler Ke Holding', 'type' => 'Biaya-Biaya Training', 'status' => 'variable'],
            ['name' => 'Biaya Sewa Room Meeting ', 'type' => 'Biaya-Biaya Training', 'status' => 'variable'],
            ['name' => 'Biaya Keperluan pembayaran PPN', 'type' => 'Biaya-Biaya Training', 'status' => 'variable'],
            ['name' => 'Biaya Keperluan pembayaran PPH', 'type' => 'Biaya-Biaya Training', 'status' => 'variable'],
            ['name' => 'Biaya Ujian Sertifikasi (Vue)/Pembayaran Exam Peserta', 'type' => 'Biaya-Biaya Training', 'status' => 'variable'],
            ['name' => 'Biaya Ujian Sertifikasi (Vue)/Pembayaran Exam Instruktur', 'type' => 'Biaya-Biaya Training', 'status' => 'variable'],
            ['name' => 'Biaya Proyek', 'type' => 'Biaya-Biaya Training', 'status' => 'variable'],
            
            ['name' => 'Biaya Tunjangan Komisi Sales', 'type' => 'Biaya Tunjangan Prestasi', 'status' => 'variable'],
            ['name' => 'Biaya Tunjangan Komisi Instruktur', 'type' => 'Biaya Tunjangan Prestasi', 'status' => 'variable'],
            ['name' => 'Biaya Bonus Tahunan u/Sales', 'type' => 'Biaya Tunjangan Prestasi', 'status' => 'variable'],
            ['name' => 'Biaya FEE Proyek', 'type' => 'Biaya Tunjangan Prestasi', 'status' => 'variable'],
           
            
            // Data Fixed Cost
            ['name' => 'Inventaris Cicilan Kendaraan', 'type' => 'Biaya Inventaris', 'status' => 'fixed'],
            ['name' => 'Inventaris Office', 'type' => 'Biaya Inventaris', 'status' => 'fixed'],
            ['name' => 'Inventaris Kelas', 'type' => 'Biaya Inventaris', 'status' => 'fixed'],
            ['name' => 'Inventaris Education', 'type' => 'Biaya Inventaris', 'status' => 'fixed'],
            ['name' => 'Inventaris Sales/Tim Digital', 'type' => 'Biaya Inventaris', 'status' => 'fixed'],
            ['name' => 'Inventaris ITSM', 'type' => 'Biaya Inventaris', 'status' => 'fixed'],

            ['name' => 'Biaya Gaji karyawan', 'type' => 'Biaya Tunjangan Karyawan', 'status' => 'fixed'],
            ['name' => 'Biaya THR karyawan', 'type' => 'Biaya Tunjangan Karyawan', 'status' => 'fixed'],
            ['name' => 'Biaya Tunjangan Telekomunikasi Sales (Voucher)', 'type' => 'Biaya Tunjangan Karyawan', 'status' => 'fixed'],
            ['name' => 'Biaya Tunjangan Telekomunikasi Office (Voucher)', 'type' => 'Biaya Tunjangan Karyawan', 'status' => 'fixed'],
            ['name' => 'Biaya Tunjangan Uang Makan', 'type' => 'Biaya Tunjangan Karyawan', 'status' => 'fixed'],
            ['name' => 'Biaya Tunjangan Transportasi Staff', 'type' => 'Biaya Tunjangan Karyawan', 'status' => 'fixed'],
            ['name' => 'Biaya Tunjangan Transportasi Direktur', 'type' => 'Biaya Tunjangan Karyawan', 'status' => 'fixed'],
            ['name' => 'Biaya Tunjangan Absensi/Premi Hadir', 'type' => 'Biaya Tunjangan Karyawan', 'status' => 'fixed'],
            ['name' => 'Biaya Tunjangan Kesehatan & Ketenagakerjaan', 'type' => 'Biaya Tunjangan Karyawan', 'status' => 'fixed'],
            ['name' => 'Biaya Tunjangan Lembur', 'type' => 'Biaya Tunjangan Karyawan', 'status' => 'fixed'],
            ['name' => 'Biaya Fee konsultan pajak', 'type' => 'Biaya Tunjangan Karyawan', 'status' => 'fixed'],
            ['name' => 'Biaya Entertainment Outbond Staff', 'type' => 'Biaya Tunjangan Karyawan', 'status' => 'fixed'],
            ['name' => 'Biaya Tunjangan Akomodasi (Kostan)', 'type' => 'Biaya Tunjangan Karyawan', 'status' => 'fixed'],

            ['name' => 'Biaya Akomodasi (Hotel) Direksi', 'type' => 'Biaya Tugas Luar Kota (SPJ) : DIREKSI', 'status' => 'fixed'],
            ['name' => 'Biaya Transportasi Direksi', 'type' => 'Biaya Tugas Luar Kota (SPJ) : DIREKSI', 'status' => 'fixed'],
            ['name' => 'Biaya Bensin Direksi', 'type' => 'Biaya Tugas Luar Kota (SPJ) : DIREKSI', 'status' => 'fixed'],

            ['name' => 'Biaya Akomodasi (Hotel) Sales', 'type' => 'Biaya Tugas Luar Kota (SPJ) : SALES', 'status' => 'fixed'],
            ['name' => 'Biaya Transportasi Sales', 'type' => 'Biaya Tugas Luar Kota (SPJ) : SALES', 'status' => 'fixed'],
            ['name' => 'Biaya Bensin Sales', 'type' => 'Biaya Tugas Luar Kota (SPJ) : SALES', 'status' => 'fixed'],
            ['name' => 'Biaya Tugas Luar Kota (SPJ) Mobile/Visit : SALES', 'type' => 'Biaya Tugas Luar Kota (SPJ) : SALES', 'status' => 'fixed'],

            ['name' => 'Biaya Akomodasi (Hotel) Office', 'type' => 'Biaya Tugas Luar Kota (SPJ) : OFFICE', 'status' => 'fixed'],
            ['name' => 'Biaya Transportasi Office', 'type' => 'Biaya Tugas Luar Kota (SPJ) : OFFICE', 'status' => 'fixed'],
            ['name' => 'Biaya Bensin Office', 'type' => 'Biaya Tugas Luar Kota (SPJ) : OFFICE', 'status' => 'fixed'],

            ['name' => 'Biaya Akomodasi (Hotel) Education', 'type' => 'Biaya Tugas Luar Kota (SPJ) : EDUCATION', 'status' => 'fixed'],
            ['name' => 'Biaya Transportasi Education', 'type' => 'Biaya Tugas Luar Kota (SPJ) : EDUCATION', 'status' => 'fixed'],
            ['name' => 'Biaya Bensin Education', 'type' => 'Biaya Tugas Luar Kota (SPJ) : EDUCATION', 'status' => 'fixed'],


            ['name' => 'Biaya Sewa Gedung ', 'type' => 'Biaya Operasional', 'status' => 'fixed'],
            ['name' => 'Biaya Keperluan Kantor/ ATK', 'type' => 'Biaya Operasional', 'status' => 'fixed'],
            ['name' => 'Biaya Keperluan ADM (Materai,Prangko,dll)', 'type' => 'Biaya Operasional', 'status' => 'fixed'],
            ['name' => 'Biaya Keperluan R/T & Dapur', 'type' => 'Biaya Operasional', 'status' => 'fixed'],
            ['name' => 'Biaya Cetak & FotoCopy Office', 'type' => 'Biaya Operasional', 'status' => 'fixed'],
            ['name' => 'Biaya Cetak & FotoCopy Sales & Marketing', 'type' => 'Biaya Operasional', 'status' => 'fixed'],
            ['name' => 'Biaya Transportasi Sales & Marketing', 'type' => 'Biaya Operasional', 'status' => 'fixed'],
            ['name' => 'Biaya Transportasi Office', 'type' => 'Biaya Operasional', 'status' => 'fixed'],
            ['name' => 'Biaya Listrik Gardu 1 ( 535590761626 )', 'type' => 'Biaya Operasional', 'status' => 'fixed'],
            ['name' => 'Biaya Listrik Gardu 2 ( 535590289170 )', 'type' => 'Biaya Operasional', 'status' => 'fixed'],
            ['name' => 'Biaya Listrik Gardu 3 / Pulsa Listrik', 'type' => 'Biaya Operasional', 'status' => 'fixed'],
            ['name' => 'Biaya Telekomunikasi Tlp: 203.2831', 'type' => 'Biaya Operasional', 'status' => 'fixed'],
            ['name' => 'Biaya Telekomunikasi Tlp: 820.62578', 'type' => 'Biaya Operasional', 'status' => 'fixed'],
            ['name' => 'Biaya Telekomunikasi Internet (BIZNET)', 'type' => 'Biaya Operasional', 'status' => 'fixed'],
            ['name' => 'Biaya Telekomunikasi Internet (MELSA)', 'type' => 'Biaya Operasional', 'status' => 'fixed'],
            ['name' => 'Biaya Telekomunikasi Internet (MyRepublic)', 'type' => 'Biaya Operasional', 'status' => 'fixed'],
            ['name' => 'Biaya Insentif Karyawan (jaga kantor)', 'type' => 'Biaya Operasional', 'status' => 'fixed'],
            ['name' => 'Biaya Telekomunikasi Office', 'type' => 'Biaya Operasional', 'status' => 'fixed'],
            ['name' => 'Biaya Promosi (Iklan LoKer)', 'type' => 'Biaya Operasional', 'status' => 'fixed'],
            ['name' => 'Biaya Entertainment Umum&Marketing', 'type' => 'Biaya Operasional', 'status' => 'fixed'],
            ['name' => 'Biaya Pengiriman/Pos (Telemarketing Sales)', 'type' => 'Biaya Operasional', 'status' => 'fixed'],
            ['name' => 'Biaya Pajak Bumi & Bangunan (PBB)', 'type' => 'Biaya Operasional', 'status' => 'fixed'],
            ['name' => 'Biaya Sumbangan', 'type' => 'Biaya Operasional', 'status' => 'fixed'],
            ['name' => 'Biaya Sewa Gedung Cipaganti', 'type' => 'Biaya Operasional', 'status' => 'fixed'],
            ['name' => 'Biaya Renovasi Kantor Cipaganti', 'type' => 'Biaya Operasional', 'status' => 'fixed'],
            ['name' => 'Biaya Langganan Koran/Majalah', 'type' => 'Biaya Operasional', 'status' => 'fixed'],
            ['name' => 'Biaya Keamanan RW 09 Cipaganti', 'type' => 'Biaya Operasional', 'status' => 'fixed'],
            ['name' => 'Biaya Kebersihan Pemkot Cipaganti', 'type' => 'Biaya Operasional', 'status' => 'fixed'],
            ['name' => 'Biaya Pinjaman Karyawan (Piutang)', 'type' => 'Biaya Operasional', 'status' => 'fixed'],
            ['name' => 'Biaya Keperluan Workshop/Seminar', 'type' => 'Biaya Operasional', 'status' => 'fixed'],
            ['name' => 'Biaya Keperluan Tim Digital (Sales/Marketing)', 'type' => 'Biaya Operasional', 'status' => 'fixed'],
            ['name' => 'Biaya Keperluan Sales Gathering', 'type' => 'Biaya Operasional', 'status' => 'fixed'],
            ['name' => 'Biaya Service Monitor , AC  dll', 'type' => 'Biaya Operasional', 'status' => 'fixed'],
            ['name' => 'Biaya Service dan Perawatan Kendaraan', 'type' => 'Biaya Operasional', 'status' => 'fixed'],
            ['name' => 'Biaya Keperluan Pajak Kendaraan Perusahaan', 'type' => 'Biaya Operasional', 'status' => 'fixed'],
            ['name' => 'Biaya Kegiatan olahraga dLL', 'type' => 'Biaya Operasional', 'status' => 'fixed'],
            ['name' => 'Biaya Seragam', 'type' => 'Biaya Operasional', 'status' => 'fixed'],
            ['name' => 'Biaya Sewa kursi', 'type' => 'Biaya Operasional', 'status' => 'fixed'],
            ['name' => 'Biaya Potong Rumput', 'type' => 'Biaya Operasional', 'status' => 'fixed'],
            ['name' => 'Biaya Administrasi BCA', 'type' => 'Biaya Operasional', 'status' => 'fixed'],
            ['name' => 'Biaya Administrasi Bank', 'type' => 'Biaya Operasional', 'status' => 'fixed'],
            ['name' => 'Biaya pajak Tabungan BCA', 'type' => 'Biaya Operasional', 'status' => 'fixed'],
            ['name' => 'Pajak Bunga Bank', 'type' => 'Biaya Operasional', 'status' => 'fixed'],
            ['name' => 'Biaya Operasional kendaraan (bensin)', 'type' => 'Biaya Operasional', 'status' => 'fixed'],
            ['name' => 'Biaya Perbaikan Kantor Cipaganti ', 'type' => 'Biaya Operasional', 'status' => 'fixed'],
            ['name' => 'Biaya Transportasi Peserta (Gopay/Bluebird/Grab)', 'type' => 'Biaya Operasional', 'status' => 'fixed'],
            ['name' => 'Biaya Bensin dan Toll /Mobile Sales', 'type' => 'Biaya Operasional', 'status' => 'fixed'],

            // ['name' => 'Biaya', 'type' => 'Biaya', 'status' => 'fixed'],
           
        ];

        Cost::insert($costs);
    }
}