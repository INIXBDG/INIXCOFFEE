<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;

class DataPassword extends Model
{
    public const MASTER = 'Password Fitur Data Password';
    public const MASTER_LEGALITAS = 'Password Fitur Legalitas';
    public const MASTER_KARYAWAN = 'master_karyawan';

    protected $table = 'data_passwords';

    protected $fillable = ['judul', 'kategori', 'identifier', 'password', 'catatan'];

    protected $hidden = ['password'];

    public const MASTERS = [self::MASTER, self::MASTER_LEGALITAS]; 

    public function isMaster(): bool
    {
        return in_array($this->kategori, self::MASTERS, true);
    }

    public function verifySecret(string $plain): bool
    {
        if (str_starts_with($this->password, '$2y$')) {
            if (!Hash::check($plain, $this->password)) {
                return false;
            }

            $this->update(['password' => Crypt::encryptString($plain)]);

            return true;
        }

        try {
            return hash_equals(Crypt::decryptString($this->password), $plain);
        } catch (DecryptException $e) {
            return false;
        }
    }

    public function revealSecret(): ?string
    {
        try {
            return Crypt::decryptString($this->password);
        } catch (DecryptException $e) {
            return null;
        }
    }
}