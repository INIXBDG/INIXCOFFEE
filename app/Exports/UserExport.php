<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UserExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return User::with('karyawan')
            ->where('status_akun', '1')
            ->where('jabatan', '!=', 'Outsource')
            ->get()
            ->map(function ($user) {
                return [
                    'nama_lengkap' => $user->karyawan?->nama_lengkap,
                    'email'        => $user->karyawan?->email,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Nama Lengkap',
            'Email',
        ];
    }
}