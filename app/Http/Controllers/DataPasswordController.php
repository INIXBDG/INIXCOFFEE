<?php

namespace App\Http\Controllers;

use App\Models\DataPassword;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Exports\ArrayExport;
use App\Models\DataPasswordLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\DataPasswordPemulihan;
use Throwable;

class DataPasswordController extends Controller
{
    private const KATEGORI = ['Server', 'Email', 'Aplikasi', 'Perangkat', 'Lainnya'];
    private const IDLE_MENIT = 5;

    private const PROGRAMMER = 'Programmer';

    private const MIN_RETENSI_HARI = 30;

    private const STATUS_LUPA = [
        'gagal'         => 'Gagal',
        'terverifikasi' => 'Terverifikasi (belum dipakai)',
        'selesai'       => 'Selesai (password diganti)',
        'kedaluwarsa'   => 'Kedaluwarsa',
    ];

    private const LUPA_MAKS_GAGAL   = 5;
    private const LUPA_BLOKIR_MENIT = 15;
    private const LUPA_TOKEN_MENIT  = 10;

    private const HAK_AKSES = ['hrd', 'direksi', 'gm', 'direktur'];

    private const AKSI = [
        'master_dibuat' => 'Password master dibuat',
        'buka_berhasil' => 'Buka vault (berhasil)',
        'buka_gagal' => 'Buka vault (gagal)',
        'kunci' => 'Kunci vault',
        'kunci_otomatis' => 'Kunci otomatis',
        'lihat' => 'Lihat password',
        'salin' => 'Salin password',
        'tambah' => 'Tambah data',
        'ubah' => 'Ubah data',
        'hapus' => 'Hapus data',
        'ganti_master' => 'Ganti password master',
        'reset_programmer' => 'Reset master oleh Programmer',
        'reset_gagal' => 'Reset master gagal',
        'export_log' => 'Export log',
        'import_log' => 'Import log',
        'bersihkan_log' => 'Bersihkan log',
    ];

    private const KOLOM_LOG = [
        'waktu' => 'Waktu',
        'pengguna' => 'Pengguna',
        'jabatan' => 'Jabatan',
        'aksi' => 'Aksi',
        'data' => 'Data',
        'ip' => 'Alamat IP',
        'perangkat' => 'Perangkat',
        'keterangan' => 'Keterangan',
        'sumber' => 'Sumber',
    ];

    private function isHrd(): bool
    {
        return auth()->check() && in_array(
            mb_strtolower(trim((string) auth()->user()->jabatan)),
            self::HAK_AKSES,
            true
        );
    }

    private function master(): ?DataPassword
    {
        return DataPassword::where('kategori', DataPassword::MASTER_HR_PASS)->first();
    }

    public function index()
    {
        return view('HR.DataPassword.index', [
            'kategori'    => self::KATEGORI,
            'aksiLog'     => self::AKSI,
            'kolomLog'    => self::KOLOM_LOG,
            'statusLupa'  => self::STATUS_LUPA ?? [],
        ]);
    }

    public function getData(Request $request): JsonResponse
    {
        $this->guard();

        $data = DataPassword::query()
            ->when($request->q, function ($q, $term) {
                $q->where(function ($w) use ($term) {
                    $w->where('judul', 'like', "%{$term}%")->orWhere('identifier', 'like', "%{$term}%");
                });
            })
            ->when($request->kategori, fn($q, $v) => $q->where('kategori', $v))
            ->orderByRaw(
                'CASE WHEN kategori IN (' . 
                collect(DataPassword::MASTERS)->map(fn() => '?')->implode(',') . 
                ') THEN 0 ELSE 1 END'
            , DataPassword::MASTERS)
            ->orderBy('judul')
            ->get(['id', 'judul', 'kategori', 'identifier', 'catatan', 'updated_at'])
            ->map(
                fn($d) => [
                    'id' => $d->id,
                    'judul' => $d->judul,
                    'kategori' => $d->kategori,
                    'identifier' => $d->identifier,
                    'catatan' => $d->catatan,
                    'is_master' => $d->isMaster(),
                    'updated_at' => $d->updated_at?->format('d-m-Y H:i'),
                ],
            );

        return response()->json(['data' => $data]);
    }

    public function secret(Request $request, int $id): JsonResponse
    {
        $this->guard();

        $item = DataPassword::findOrFail($id);
        $secret = $item->revealSecret();

        if ($secret === null) {
            return response()->json(
                [
                    'message' => 'Password ini belum bisa dibaca. Buka ulang vault, atau ganti password-nya lewat Edit.',
                ],
                422,
            );
        }

        $this->catat($request->input('aksi') === 'salin' ? 'salin' : 'lihat', $item);

        return response()->json(['secret' => $secret]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $this->guard();

        $item = DataPassword::findOrFail($id);

        if ($item->isMaster()) {
            $data = $request->validate(
                [
                    'password' => ['required', 'string', 'min:8', 'max:50'],
                ],
                ['password.min' => 'Password minimal 8 karakter.'],
            );

            $item->update(['password' => Crypt::encryptString($data['password'])]);

            return response()->json(['message' => 'Password master berhasil diganti.']);
        }

        $data = $request->validate($this->rules(false));

        $attributes = [
            'judul' => $data['judul'],
            'kategori' => $data['kategori'],
            'identifier' => $data['identifier'] ?? null,
            'catatan' => $data['catatan'] ?? null,
        ];

        if (!empty($data['password'])) {
            $attributes['password'] = Crypt::encryptString($data['password']);
        }

        $item->update($attributes);

        $this->catat($item->isMaster() ? 'ganti_master' : 'ubah', $item);

        return response()->json(['message' => 'Data berhasil diperbarui.']);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->guard();

        $item = DataPassword::findOrFail($id);

        if ($item->isMaster()) {
            return response()->json(['message' => 'Password master tidak dapat dihapus dari sini.'], 422);
        }

        $this->catat('hapus', $item);

        $item->delete();

        return response()->json(['message' => 'Data berhasil dihapus.']);
    }

    public function store(Request $request): JsonResponse
    {
        $this->guard();

        $data = $request->validate($this->rules(true));

        $item = DataPassword::create([
            'judul' => $data['judul'],
            'kategori' => $data['kategori'],
            'identifier' => $data['identifier'] ?? null,
            'password' => Crypt::encryptString($data['password']),
            'catatan' => $data['catatan'] ?? null,
        ]);

        $this->catat('tambah', $item);

        return response()->json(['message' => 'Password berhasil disimpan.'], 201);
    }

    private function rules(bool $passwordWajib): array
    {
        return [
            'judul' => ['required', 'string', 'max:255'],
            'kategori' => ['required', Rule::in(self::KATEGORI)],
            'identifier' => ['nullable', 'string', 'max:255'],
            'password' => [$passwordWajib ? 'required' : 'nullable', 'string', 'max:50'],
            'catatan' => ['nullable', 'string', 'max:2000'],
        ];
    }

    private function guard(): void
    {
        abort_unless($this->isHrd(), 403, 'Anda bukan HRD.');
        abort_unless($this->master(), 409, 'Password vault belum dibuat.');

        $hr = app(\App\Http\Controllers\HR\HRController::class);
        abort_unless($hr->unlocked(), 423, 'Terkunci.');
    }

    public function resetProgrammer(Request $request)
    {
        abort_unless($this->isProgrammer(), 403);

        $master = $this->master();
        abort_unless($master, 409, 'Password master belum dibuat oleh HRD.');

        $data = $request->validate(
            [
                'password' => ['required', 'string', 'min:8', 'max:50', 'confirmed'],
                'account_password' => ['required', 'string'],
            ],
            [
                'password.confirmed' => 'Konfirmasi password tidak sama.',
                'password.min' => 'Password minimal 8 karakter.',
                'password.max' => 'Password maksimal 50 karakter.',
            ],
        );

        if (!Hash::check($data['account_password'], auth()->user()->getAuthPassword())) {
            $this->catat('reset_gagal', $master, 'Password akun Programmer salah');

            return back()->withErrors(['account_password' => 'Password akun Anda salah.']);
        }

        $master->update(['password' => Crypt::encryptString($data['password'])]);
        $this->catat('reset_programmer', $master);

        return redirect()->route('HR.data-password.index')->with('success', 'Password master berhasil direset. Sampaikan password baru kepada HRD.');
    }

    public function logData(Request $request): JsonResponse
    {
        $this->guard();

        $logs = $this->logQuery($request)->paginate(20);

        return response()->json($logs->through(fn($l) => $this->barisLog($l)));
    }

    public function logExport(Request $request)
    {
        $this->guard();

        $request->validate(
            [
                'format' => ['required', 'in:pdf,xlsx'],
                'columns' => ['required', 'array', 'min:1'],
                'columns.*' => ['in:' . implode(',', array_keys(self::KOLOM_LOG))],
            ],
            ['columns.required' => 'Pilih minimal 1 kolom untuk diexport.'],
        );

        $cols = array_values(array_intersect(array_keys(self::KOLOM_LOG), $request->input('columns')));

        $rows = $this->logQuery($request)
            ->limit(10000)
            ->get()
            ->values()
            ->map(function ($l, $n) use ($cols) {
                $b = $this->barisLog($l);

                return array_merge([$n + 1], array_map(fn($c) => $b[$c], $cols));
            })
            ->all();

        $headings = array_merge(['No'], array_map(fn($c) => self::KOLOM_LOG[$c], $cols));
        $file = 'log-data-password-' . now()->format('Ymd-His');

        $this->catat('export_log', null, 'Format ' . strtoupper($request->format) . ', ' . count($rows) . ' baris');

        if ($request->format === 'xlsx') {
            return Excel::download(new ArrayExport($headings, $rows), $file . '.xlsx');
        }

        $judul = 'Log Aktivitas Data Password';

        return Pdf::loadView('HR.InstrukturOl.export-pdf', compact('judul', 'headings', 'rows'))
            ->setPaper('a4', 'landscape')
            ->download($file . '.pdf');
    }

    public function logImport(Request $request): JsonResponse
    {
        $this->guard();

        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:5120']]);

        $sheet = Excel::toArray(new \stdClass(), $request->file('file'))[0] ?? [];
        $head = array_map(fn($h) => mb_strtolower(trim((string) $h)), array_shift($sheet) ?? []);

        $map = [];
        foreach (self::KOLOM_LOG as $key => $label) {
            $i = array_search(mb_strtolower($label), $head, true);
            if ($i !== false) {
                $map[$key] = $i;
            }
        }

        if (!isset($map['waktu'], $map['aksi'])) {
            return response()->json(['message' => 'Format tidak sesuai. Kolom "Waktu" dan "Aksi" wajib ada (gunakan file hasil export).'], 422);
        }

        if (count($sheet) > 5000) {
            return response()->json(['message' => 'Maksimal 5000 baris per import.'], 422);
        }

        $labelToKey = array_flip(self::AKSI);
        $masuk = 0;
        $lewat = 0;

        foreach ($sheet as $r) {
            $cell = fn($k) => isset($map[$k]) ? trim((string) ($r[$map[$k]] ?? '')) : '';
            $waktu = $this->parseWaktu($r[$map['waktu']] ?? null);
            $aksi = $cell('aksi');

            if (!$waktu || $aksi === '') {
                $lewat++;
                continue;
            }

            $aksi = $labelToKey[$aksi] ?? mb_substr($aksi, 0, 60);
            $nama = $cell('pengguna') ?: '-';
            $ip = $cell('ip') ?: null;

            $ada = DataPasswordLog::where('waktu', $waktu)->where('aksi', $aksi)->where('user_nama', $nama)->where('ip_address', $ip)->exists();

            if ($ada) {
                $lewat++;
                continue;
            }

            DataPasswordLog::create([
                'user_nama' => $nama,
                'user_jabatan' => $cell('jabatan') ?: null,
                'aksi' => $aksi,
                'data_judul' => $cell('data') !== '' && $cell('data') !== '-' ? $cell('data') : null,
                'ip_address' => $ip,
                'device' => $cell('perangkat') ?: null,
                'keterangan' => mb_substr($cell('keterangan'), 0, 500) ?: null,
                'sumber' => 'import',
                'waktu' => $waktu,
            ]);
            $masuk++;
        }

        $this->catat('import_log', null, "Diimpor {$masuk} baris, dilewati {$lewat}");

        return response()->json(['message' => "{$masuk} log diimpor, {$lewat} baris dilewati (kosong, tidak valid, atau sudah ada)."]);
    }

    private function logQuery(Request $request)
    {
        return DataPasswordLog::query()
            ->when($request->q, function ($q, $t) {
                $q->where(function ($w) use ($t) {
                    $w->where('user_nama', 'like', "%{$t}%")
                        ->orWhere('ip_address', 'like', "%{$t}%")
                        ->orWhere('device', 'like', "%{$t}%")
                        ->orWhere('data_judul', 'like', "%{$t}%");
                });
            })
            ->when($request->aksi, fn($q, $v) => $q->where('aksi', $v))
            ->when($request->dari, fn($q, $v) => $q->whereDate('waktu', '>=', $v))
            ->when($request->sampai, fn($q, $v) => $q->whereDate('waktu', '<=', $v))
            ->orderByDesc('waktu')
            ->orderByDesc('id');
    }

    private function barisLog(DataPasswordLog $l): array
    {
        return [
            'aksi_key' => $l->aksi,
            'waktu' => $l->waktu?->format('d-m-Y H:i:s'),
            'pengguna' => $l->user_nama ?: '-',
            'jabatan' => $l->user_jabatan ?: '-',
            'aksi' => self::AKSI[$l->aksi] ?? $l->aksi,
            'data' => $l->data_judul ?: '-',
            'ip' => $l->ip_address ?: '-',
            'perangkat' => $l->device ?: '-',
            'keterangan' => $l->keterangan ?: '-',
            'sumber' => $l->sumber === 'import' ? 'Import' : 'Sistem',
        ];
    }

    private function parseWaktu($v): ?Carbon
    {
        if ($v === null || $v === '') {
            return null;
        }

        try {
            if (is_numeric($v)) {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($v));
            }

            return Carbon::createFromFormat('d-m-Y H:i:s', trim((string) $v)) ?: null;
        } catch (Throwable $e) {
            try {
                return Carbon::parse((string) $v);
            } catch (Throwable $e2) {
                return null;
            }
        }
    }

    private function catat(string $aksi, ?DataPassword $item = null, ?string $ket = null): void
    {
        try {
            $u = auth()->user();
            $ua = (string) request()->userAgent();

            DataPasswordLog::create([
                'user_id' => $u?->id,
                'user_nama' => $u->nama_lengkap ?? ($u->name ?? ($u->email ?? ($u ? 'ID ' . $u->id : '-'))),
                'user_jabatan' => $u->jabatan ?? null,
                'aksi' => $aksi,
                'data_id' => $item?->id,
                'data_judul' => $item?->judul,
                'ip_address' => request()->ip(),
                'device' => $this->device($ua),
                'user_agent' => mb_substr($ua, 0, 1000),
                'keterangan' => $ket,
                'sumber' => 'sistem',
                'waktu' => now(),
            ]);
        } catch (Throwable $e) {
            report($e); // gagal mencatat tidak boleh menggagalkan aksi utama
        }
    }

    public function lupaVerifikasi(Request $request): JsonResponse
    {
        abort_unless($this->isHrd(), 403);

        $master = $this->master();
        abort_unless($master, 409, 'Password vault belum dibuat.');

        $user = auth()->user();

        $gagal = DataPasswordPemulihan::where('user_id', $user->id)
            ->where('status', DataPasswordPemulihan::GAGAL)
            ->where('created_at', '>=', now()->subMinutes(self::LUPA_BLOKIR_MENIT))
            ->count();

        if ($gagal >= self::LUPA_MAKS_GAGAL) {
            return response()->json(['message' => 'Terlalu banyak percobaan gagal. Coba lagi dalam ' . self::LUPA_BLOKIR_MENIT . ' menit.'], 429);
        }

        $adaData = $this->dataBiasa()->exists();

        $input = $request->validate(
            [
                'account_password' => ['required', 'string'],
                'username' => ['required', 'string', 'max:255'],
                'judul_data' => [$adaData ? 'required' : 'nullable', 'string', 'max:255'],
                'alasan' => ['required', 'string', 'min:10', 'max:1000'],
                'konfirmasi' => ['accepted'],
            ],
            [
                'account_password.required' => 'Pertanyaan 1 wajib diisi.',
                'username.required' => 'Pertanyaan 2 wajib diisi.',
                'judul_data.required' => 'Pertanyaan 3 wajib diisi.',
                'alasan.required' => 'Alasan wajib diisi.',
                'alasan.min' => 'Alasan minimal 10 karakter.',
                'konfirmasi.accepted' => 'Anda harus menyatakan bahwa ini benar-benar Anda.',
            ],
        );

        $hasil = [
            'password_akun' => Hash::check($input['account_password'], $user->getAuthPassword()),
            'username' => $this->normal($input['username']) === $this->normal((string) $user->username),
            'judul_data' => !$adaData
                || $this->dataBiasa()->pluck('judul')->contains(
                    fn($j) => $this->normal((string) $j) === $this->normal($input['judul_data'] ?? '')
                ),
        ];
        $lolos = !in_array(false, $hasil, true);

        DataPasswordPemulihan::where('user_id', $user->id)
            ->where('status', DataPasswordPemulihan::TERVERIFIKASI)
            ->update(['status' => DataPasswordPemulihan::KEDALUWARSA]);

        $rec = DataPasswordPemulihan::create([
            'user_id' => $user->id,
            'user_nama' => $this->namaUser() ?: '-',
            'user_jabatan' => $user->jabatan ?? null,
            'status' => $lolos ? DataPasswordPemulihan::TERVERIFIKASI : DataPasswordPemulihan::GAGAL,
            'jawaban_nama' => $input['username'],
            'jawaban_judul' => $input['judul_data'] ?? null,
            'alasan' => $input['alasan'],
            'konfirmasi' => true,
            'username' => $this->normal($input['username']) === $this->normal((string) $user->username),
            'hasil' => $hasil,
            'ip_address' => $request->ip(),
            'device' => $this->device((string) $request->userAgent()),
            'diverifikasi_at' => $lolos ? now() : null,
        ]);

        if (!$lolos) {
            $this->catat('lupa_gagal', $master, "Verifikasi #{$rec->id} tidak lulus");

            $sisa = max(self::LUPA_MAKS_GAGAL - ($gagal + 1), 0);

            return response()->json(['message' => "Jawaban tidak sesuai. Sisa percobaan: {$sisa}."], 422);
        }

        session(['dp_lupa' => ['id' => $rec->id, 'until' => now()->addMinutes(self::LUPA_TOKEN_MENIT)->timestamp]]);
        $this->catat('lupa_verifikasi', $master, "Verifikasi #{$rec->id} lulus");

        return response()->json(['message' => 'Identitas terverifikasi. Silakan buat password baru.']);
    }

    public function lupaRiwayat(Request $request): JsonResponse
    {
        $this->guard();

        $rows = DataPasswordPemulihan::query()
            ->when($request->q, function ($q, $t) {
                $q->where(function ($w) use ($t) {
                    $w->where('user_nama', 'like', "%{$t}%")
                        ->orWhere('ip_address', 'like', "%{$t}%")
                        ->orWhere('device', 'like', "%{$t}%")
                        ->orWhere('alasan', 'like', "%{$t}%");
                });
            })
            ->when($request->status, fn($q, $v) => $q->where('status', $v))
            ->when($request->dari, fn($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($request->sampai, fn($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(20);

        return response()->json($rows->through(fn($r) => [
            'waktu' => $r->created_at?->format('d-m-Y H:i:s'),
            'pengguna' => $r->user_nama ?: '-',
            'jabatan' => $r->user_jabatan ?: '-',
            'status_key' => $r->status,
            'status' => self::STATUS_LUPA[$r->status] ?? $r->status,
            'hasil' => $r->hasil ?? [],
            'alasan' => $r->alasan ?: '-',
            'jawaban_username' => $r->jawaban_nama ?: '-',
            'jawaban_judul' => $r->jawaban_judul ?: '-',
            'ip' => $r->ip_address ?: '-',
            'perangkat' => $r->device ?: '-',
            'diverifikasi' => $r->diverifikasi_at?->format('d-m-Y H:i:s') ?: '-',
            'selesai' => $r->selesai_at?->format('d-m-Y H:i:s') ?: '-',
        ]));
    }

    public function lupaReset(Request $request): JsonResponse
    {
        abort_unless($this->isHrd(), 403);

        $master = $this->master();
        abort_unless($master, 409, 'Password vault belum dibuat.');

        $data = $request->validate(
            ['password' => ['required', 'string', 'min:8', 'max:50', 'confirmed']],
            [
                'password.confirmed' => 'Konfirmasi password tidak sama.',
                'password.min' => 'Password minimal 8 karakter.',
                'password.max' => 'Password maksimal 50 karakter.',
            ],
        );

        $s = session('dp_lupa');
        $rec =
            $s && now()->timestamp < ($s['until'] ?? 0)
                ? DataPasswordPemulihan::where('id', $s['id'])
                    ->where('user_id', auth()->id())
                    ->where('status', DataPasswordPemulihan::TERVERIFIKASI)
                    ->first()
                : null;

        if (!$rec) {
            session()->forget('dp_lupa');

            return response()->json(['message' => 'Verifikasi berakhir atau tidak valid. Ulangi verifikasi.', 'ulang' => true], 422);
        }

        $master->update(['password' => Crypt::encryptString($data['password'])]);
        $rec->update(['status' => DataPasswordPemulihan::SELESAI, 'selesai_at' => now()]);
        session()->forget('dp_lupa');

        $this->catat('lupa_reset', $master, "Verifikasi #{$rec->id}");
        $this->touchSession();

        return response()->json(['message' => 'Password master berhasil dibuat ulang.']);
    }

    private function dataBiasa()
    {
        return DataPassword::query()->whereNotIn('kategori', DataPassword::MASTERS);
    }

    private function namaUser(): string
    {
        $u = auth()->user();

        return (string) ($u->nama_lengkap ?? ($u->name ?? ($u->email ?? '')));
    }

    private function normal(string $s): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/', ' ', $s)));
    }

    private function device(string $ua): string
    {
        if ($ua === '') {
            return '-';
        }

        $os = match (true) {
            str_contains($ua, 'Windows') => 'Windows',
            str_contains($ua, 'Android') => 'Android',
            preg_match('/iPhone|iPad|iPod/', $ua) === 1 => 'iOS',
            str_contains($ua, 'Mac OS') => 'macOS',
            str_contains($ua, 'Linux') => 'Linux',
            default => 'OS tidak dikenal',
        };

        $browser = 'Browser tidak dikenal';
        foreach (['Edg' => 'Edge', 'OPR' => 'Opera', 'Firefox' => 'Firefox', 'Chrome' => 'Chrome', 'Safari' => 'Safari'] as $k => $n) {
            if (preg_match('/' . $k . '\/(\d+)/', $ua, $m)) {
                $browser = $n . ' ' . $m[1];
                break;
            }
        }

        $tipe = str_contains($ua, 'Mobile') ? 'Mobile' : 'Desktop';

        return "{$browser} · {$os} ({$tipe})";
    }

    private function isProgrammer(): bool
    {
        return auth()->check() && auth()->user()->jabatan === self::PROGRAMMER;
    }

    public function logCleanup(Request $request): JsonResponse
    {
        $this->guard();

        $data = $request->validate(
            [
                'periode' => ['required', 'in:30,90,180,365,730,custom'],
                'sebelum' => ['required_if:periode,custom', 'nullable', 'date'],
            ],
            [
                'sebelum.required_if' => 'Tanggal wajib diisi.',
            ],
        );

        $cutoff = $data['periode'] === 'custom' ? Carbon::parse($data['sebelum'])->startOfDay() : now()->subDays((int) $data['periode'])->startOfDay();

        if ($cutoff->gt(now()->subDays(self::MIN_RETENSI_HARI)->startOfDay())) {
            return response()->json(
                [
                    'message' => 'Log yang lebih baru dari ' . self::MIN_RETENSI_HARI . ' hari tidak dapat dihapus.',
                ],
                422,
            );
        }

        $query = DataPasswordLog::where('waktu', '<', $cutoff)->where('aksi', '!=', 'bersihkan_log');

        if ($request->boolean('preview')) {
            return response()->json([
                'jumlah' => $query->count(),
                'sebelum' => $cutoff->format('d-m-Y'),
            ]);
        }

        $master = $this->master();

        if (!$master || !$master->verifySecret((string) $request->input('password'))) {
            $this->catat('buka_gagal', null, 'Password salah saat bersihkan log');

            return response()->json(['message' => 'Password master salah.'], 422);
        }

        $jumlah = $query->delete();

        $this->catat('bersihkan_log', null, "Menghapus {$jumlah} log sebelum {$cutoff->format('d-m-Y')}");

        return response()->json(['message' => "{$jumlah} log sebelum {$cutoff->format('d-m-Y')} berhasil dihapus."]);
    }
}
