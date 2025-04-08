<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'Create RKM',
            'Edit RKM',
            'Delete RKM',
            'View RKM',
            'Akses Development',
            'View Materi',
            'Create Materi',
            'Edit Materi',
            'Delete Materi',
            'View Feedback',
            'Create Feedback',
            'View Souvenir',
            'Create Souvenir',
            'Edit Souvenir',
            'View Peserta',
            'Create Peserta',
            'Edit Peserta',
            'View Registrasi',
            'Create Registrasi',
            'View Perusahaan',
            'Create Perusahaan',
            'Edit Perusahaan',
            'Delete Perusahaan',
            'View Jabatan',
            'Create Jabatan',
            'Delete Jabatan',
            'View DataKaryawan',
            'Create DataKaryawan',
            'Edit DataKaryawan',
            'Delete DataKaryawan',
            'Approval Cuti',
            'Approval SPJ',
            'Approval Pengajuanbarang',
            'Fitur Menu Manajemen',
            'Fitur Menu RKM',
            'Fitur Menu Finance',
            'View AnalisisRKM',
            'View CC',
            'Create CC',
            'Edit CC',
            'Delete CC',
            'View Tunjangan',
            'Create Tunjangan',
            'Fitur Menu Education',
            'Fitur Menu Peserta',
            'View Exam',
            'View TunjanganEducation',
            'View RekapInstruktur',
            'View Outstanding',
            'Create Outstanding',
            'Edit Outstanding',
            'Delete Outstanding',
            'Create TunjanganEducation',
            'Approval TunjanganEducation',
            'Create Exam',
            'Edit Exam',
            'Delete Exam',
            'Assign RKM Instruktur',
            'Assign RKM Kelas',
            'RegistrasiForm RKM',
            'Absensi RKM',
            'Souvenir RKM',
            'Edit Materi Status (Education Manager)',
            'Detail Feedback Per Bulan',
            'Edit Registrasi',
            'Delete Registrasi',
            'View RegistExam',
            'View ListExam',
            'UploadInvoice RegistExam',
            'DetailPeserta RegistExam',
            'UploadHasil RegistExam',
            'CC RegistExam',
            'Hitung TunjanganEducation',
            'View RekapAbsensi',
            'Create RekapAbsensi'
         ];
         
         foreach ($permissions as $permission) {
              Permission::create(['name' => $permission]);
         }
    }
}
