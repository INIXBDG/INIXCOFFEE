<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PelamarPsikotest extends Model
{
    protected $table = 'pelamar_psikotests';

    public const KATEGORI = [
        'mbti' => 'MBTI',
        'riasec' => 'RIASEC',
        'logika_numerik' => 'Logika Numerik',
        'analogi_verbal' => 'Analogi Verbal',
    ];

    public const LEVEL_SKOR = [['min' => 80, 'label' => 'Sangat Baik'], ['min' => 60, 'label' => 'Baik'], ['min' => 40, 'label' => 'Cukup'], ['min' => 0, 'label' => 'Rendah']];

    public const MBTI_TIPE = [
        'INTJ' => ['nama' => 'Arsitek', 'ringkas' => 'Strategis, mandiri, dan berpikir jangka panjang.'],
        'INTP' => ['nama' => 'Pemikir', 'ringkas' => 'Analitis, konseptual, gemar menggali teori dan sistem.'],
        'ENTJ' => ['nama' => 'Komandan', 'ringkas' => 'Tegas, terarah, dan natural memimpin menuju target.'],
        'ENTP' => ['nama' => 'Pendebat', 'ringkas' => 'Inovatif, cepat berpikir, suka menantang gagasan.'],
        'INFJ' => ['nama' => 'Advokat', 'ringkas' => 'Berempati, visioner, dan berpegang pada prinsip.'],
        'INFP' => ['nama' => 'Mediator', 'ringkas' => 'Idealis, reflektif, setia pada nilai pribadi.'],
        'ENFJ' => ['nama' => 'Protagonis', 'ringkas' => 'Karismatik, peduli, pandai memotivasi orang lain.'],
        'ENFP' => ['nama' => 'Juru Kampanye', 'ringkas' => 'Antusias, kreatif, mudah membangun hubungan.'],
        'ISTJ' => ['nama' => 'Logistikus', 'ringkas' => 'Teliti, bertanggung jawab, patuh pada prosedur.'],
        'ISFJ' => ['nama' => 'Pembela', 'ringkas' => 'Setia, perhatian, andal menjaga detail.'],
        'ESTJ' => ['nama' => 'Eksekutif', 'ringkas' => 'Terorganisir, tegas, menegakkan aturan dan hasil.'],
        'ESFJ' => ['nama' => 'Konsul', 'ringkas' => 'Ramah, suportif, menjaga keharmonisan tim.'],
        'ISTP' => ['nama' => 'Virtuoso', 'ringkas' => 'Praktis, tenang, ahli menangani masalah teknis.'],
        'ISFP' => ['nama' => 'Petualang', 'ringkas' => 'Fleksibel, peka, bekerja dengan caranya sendiri.'],
        'ESTP' => ['nama' => 'Pengusaha', 'ringkas' => 'Tangkas, berani bertindak, mudah beradaptasi.'],
        'ESFP' => ['nama' => 'Penghibur', 'ringkas' => 'Spontan, energik, menghidupkan suasana.'],
    ];

    public const RIASEC_HURUF = [
        'R' => ['nama' => 'Realistic', 'ringkas' => 'Praktis dan teknis; nyaman dengan alat, mesin, atau pekerjaan lapangan.'],
        'I' => ['nama' => 'Investigative', 'ringkas' => 'Analitis dan ingin tahu; suka meneliti dan memecahkan masalah kompleks.'],
        'A' => ['nama' => 'Artistic', 'ringkas' => 'Kreatif dan ekspresif; menyukai kebebasan dan gagasan orisinal.'],
        'S' => ['nama' => 'Social', 'ringkas' => 'Suka membantu, mengajar, dan bekerja sama dengan orang lain.'],
        'E' => ['nama' => 'Enterprising', 'ringkas' => 'Persuasif dan berorientasi target; suka memimpin dan meyakinkan.'],
        'C' => ['nama' => 'Conventional', 'ringkas' => 'Teliti dan teratur; nyaman dengan data, prosedur, dan aturan jelas.'],
    ];

    public const KETERANGAN = [
        'mbti' => [
            'judul' => 'MBTI (Myers-Briggs Type Indicator)',
            'mengukur' => 'Kecenderungan gaya kepribadian pada empat dimensi: Extraversion–Introversion, Sensing–Intuition, Thinking–Feeling, dan Judging–Perceiving.',
            'cara_baca' => 'Empat huruf menunjukkan preferensi, bukan kemampuan. Tidak ada tipe yang lebih baik atau lebih buruk; yang dilihat adalah kecocokan gaya kerja dengan posisi dan tim.',
            'catatan' => 'Gunakan sebagai bahan diskusi saat interview, bukan sebagai satu-satunya dasar menolak pelamar.',
        ],
        'riasec' => [
            'judul' => 'RIASEC (Holland Code)',
            'mengukur' => 'Minat dan lingkungan kerja yang paling sesuai: Realistic, Investigative, Artistic, Social, Enterprising, Conventional.',
            'cara_baca' => 'Kode berisi 1–3 huruf; huruf pertama adalah minat paling dominan. Skor per huruf (jika diisi) menunjukkan seberapa kuat tiap minat.',
            'catatan' => 'Cocokkan kode dengan karakter pekerjaan pada jabatan yang dilamar.',
        ],
        'logika_numerik' => [
            'judul' => 'Logika Numerik',
            'mengukur' => 'Kemampuan bernalar dengan angka: deret, perhitungan, perbandingan, dan pemecahan soal kuantitatif.',
            'cara_baca' => 'Skor skala 0–100. Interpretasi: 80 ke atas sangat baik, 60–79 baik, 40–59 cukup, di bawah 40 rendah.',
            'catatan' => 'Sesuaikan batas interpretasi dengan norma alat tes yang dipakai dan tuntutan jabatan.',
        ],
        'analogi_verbal' => [
            'judul' => 'Analogi Verbal',
            'mengukur' => 'Kemampuan memahami hubungan antar kata, kosakata, dan penalaran berbasis bahasa.',
            'cara_baca' => 'Skor skala 0–100. Interpretasi: 80 ke atas sangat baik, 60–79 baik, 40–59 cukup, di bawah 40 rendah.',
            'catatan' => 'Penting untuk jabatan yang banyak berkomunikasi, menulis laporan, atau mengajar.',
        ],
    ];

    protected $fillable = ['pelamar_id', 'kategori', 'hasil', 'skor', 'skor_detail', 'tanggal_tes', 'catatan', 'file_path', 'file_nama', 'file_mime', 'diinput_oleh'];

    protected $hidden = ['file_path'];

    protected $casts = [
        'skor' => 'float',
        'skor_detail' => 'array',
        'tanggal_tes' => 'date',
    ];

    public function pelamar()
    {
        return $this->belongsTo(Pelamar::class, 'pelamar_id');
    }

    public function setHasilAttribute($value): void
    {
        $this->attributes['hasil'] = $value === null ? null : strtoupper((string) $value);
    }

    public function getFileUrlAttribute(): ?string
    {
        if (!$this->file_path) {
            return null;
        }

        return url("HR-dashboard/hire/{$this->pelamar_id}/psikotest/{$this->kategori}/file");
    }

    public function getPreviewTipeAttribute(): ?string
    {
        if (!$this->file_path) {
            return null;
        }

        $ext = strtolower(pathinfo((string) ($this->file_nama ?: $this->file_path), PATHINFO_EXTENSION));

        return match (true) {
            $ext === 'pdf' => 'pdf',
            in_array($ext, ['jpg', 'jpeg', 'png'], true) => 'gambar',
            $ext === 'docx' => 'docx',
            $ext === 'doc' => 'doc',
            default => null,
        };
    }

    public static function levelSkor(?float $skor): ?string
    {
        if ($skor === null) {
            return null;
        }

        foreach (self::LEVEL_SKOR as $level) {
            if ($skor >= $level['min']) {
                return $level['label'];
            }
        }

        return self::LEVEL_SKOR[count(self::LEVEL_SKOR) - 1]['label'];
    }

    public static function info(): array
    {
        return [
            'label' => self::KATEGORI,
            'kategori' => self::KETERANGAN,
            'mbti' => self::MBTI_TIPE,
            'riasec' => self::RIASEC_HURUF,
            'level' => self::LEVEL_SKOR,
        ];
    }

    public function ringkas(): array
    {
        return [
            'kategori' => $this->kategori,
            'label' => self::KATEGORI[$this->kategori] ?? $this->kategori,
            'hasil' => $this->hasil,
            'skor' => $this->skor,
            'level' => self::levelSkor($this->skor),
            'skor_detail' => $this->skor_detail,
            'tanggal_tes' => $this->tanggal_tes?->format('Y-m-d'),
            'catatan' => $this->catatan,
            'file_nama' => $this->file_nama,
            'file_url' => $this->file_url,
            'preview_tipe' => $this->preview_tipe,
            'diperbarui' => $this->updated_at?->format('d M Y H:i'),
        ];
    }
}
