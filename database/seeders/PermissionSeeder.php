<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Daftar permission yang akan di-seed.
     * Urutan mengikuti id ascending pada data sumber.
     */
    protected array $permissions = [
        'Dashboard CRM',
        'View CRM Laporan Penjualan 1',
        'View CRM Laporan Penjualan 2',
        'View CRM History Status',
        'View Target Aktivitas Sales',
        'View MOM',
        'View Todo Administrasi',
        'View CRM Checklist RKM',
        'View CRM Dashboard Pribadi',
        'View CRM Perpindahan DB',
        'PA Peluang',
        'Store Peluang',
        'Update Peluang',
        'Delete Peluang',
        'UpdateTahap Peluang',
        'Restore Peluang',
        'Upload Peluang Form Registrasi',
        'Store Contact CRM',
        'Update Contact CRM',
        'Delete Contact CRM',
        'View Aktivitas Sales',
        'Store Aktivitas Sales',
        'Update Aktivitas Sales',
        'Delete Aktivitas Sales',
        'View Contact CRM',
        'View PIC CRM',
        'Store PIC CRM',
        'Update PIC CRM',
        'Delete PIC CRM',
        'Store MOM',
        'Update MOM',
        'Delete MOM',
        'Store Target Aktivitas Sales',
        'Update Target Aktivitas Sales',
        'Delete Target Aktivitas Sales',
        'Store Todo Administrasi',
        'Update Todo Administrasi',
        'Delete Todo Administrasi',
        'View Inixcert',
        'Store Inixcert',
        'Delete Inixcert',
        'View Catering',
        'Store Catering',
        'Update Catering',
        'Delete Catering',
        'View PO Modul',
        'Store PO Modul',
        'Update PO Modul',
        'Delete PO Modul',
        'View Tagihan Perusahaan',
        'Store Tagihan Perusahaan',
        'Update Tagihan Perusahaan',
        'Delete Tagihan Perusahaan',
        'View RAB Kegiatan',
        'Store RAB Kegiatan',
        'Update RAB Kegiatan',
        'Delete RAB Kegiatan',
        'View Administrasi Karyawan',
        'Store Administrasi Karyawan',
        'Update Administrasi Karyawan',
        'Delete Administrasi Karyawan',
        'View Alias',
        'Update Alias',
        'View Peluang',
        'View RekapExam',
        'View PoSertifa',
        'Store PoSertifa',
        'Update PoSertifa',
        'Delete PoSertifa',
        'View PengajuanSouvenir',
        'Store PengajuanSouvenir',
        'Update PengajuanSouvenir',
        'Delete PengajuanSouvenir',
        'View Vendor Office',
        'Store Vendor Office',
        'Delete Vendor Office',
        'View PenambahanSouvenir',
        'Store PenambahanSouvenir',
        'Update PenambahanSouvenir',
        'View PenukaranSouvenir',
        'Store PenukaranSouvenir',
        'View DashboardSouvenir',
        'View DaftarTugas OB',
        'Store DaftarTugas OB',
        'Aktifkan DaftarTugas OB',
        'Update DaftarTugas OB Kategori',
        'Delete DaftarTugas OB Kategori',
        'View StockOpname',
        'Store StockOpname',
        'Update StockOpname',
        'Delete StockOpname',
        'CleanLog StockOpname',
        'SyncBaseline StockOpname',
        'StoreKeluar StockOpname',
        'InlineUpdate StockOpname',
        'View KondisiTools',
        'Store KondisiTools',
        'Update KondisiTools',
        'Delete KondisiTools',
        'View KoordinasiOfficeBoy',
        'Store KoordinasiOfficeBoy',
        'Update KoordinasiOfficeBoy',
        'Delete KoordinasiOfficeBoy',
        'View PickupDriver',
        'Store PickupDriver',
        'Update PickupDriver',
        'Delete PickupDriver',
        'View BiayaTransportasi',
        'Store BiayaTransportasi',
        'Update BiayaTransportasi',
        'Delete BiayaTransportasi',
        'View KondisiKendaraan',
        'Store KondisiKendaraan',
        'Update KondisiKendaraan',
        'Delete KondisiKendaraan',
        'View PerbaikanKendaraan',
        'Store PerbaikanKendaraan',
        'Update PerbaikanKendaraan',
        'Delete PerbaikanKendaraan',
        'View LaporanAnalisis Accounting',
        'Store LaporanAnalisis Accounting',
        'Update LaporanAnalisis Accounting',
        'Delete LaporanAnalisis Accounting',
        'View PicPenagihan',
        'Store PicPenagihan',
        'Update PicPenagihan',
        'Delete PicPenagihan',
        'View ApprovalPendapatan',
        'Update ApprovalPendapatan',
        'View SOP Perusahaan',
        'Store SOP Perusahaan',
        'Update SOP Perusahaan',
        'Delete SOP Perusahaan',
    ];

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guard = 'web';

        foreach ($this->permissions as $name) {
            Permission::firstOrCreate(
                [
                    'name' => $name,
                    'guard_name' => $guard,
                ]
            );
        }

        $this->command->info(count($this->permissions) . ' permissions berhasil di-seed.');
    }
}