<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

class DokumenParser
{
    private const BULAN = [
        'januari' => 1,
        'februari' => 2,
        'maret' => 3,
        'april' => 4,
        'mei' => 5,
        'juni' => 6,
        'juli' => 7,
        'agustus' => 8,
        'september' => 9,
        'oktober' => 10,
        'november' => 11,
        'desember' => 12,
        'january' => 1,
        'february' => 2,
        'march' => 3,
        'may' => 5,
        'june' => 6,
        'july' => 7,
        'august' => 8,
        'october' => 10,
        'december' => 12,
        'jan' => 1,
        'feb' => 2,
        'mar' => 3,
        'apr' => 4,
        'jun' => 6,
        'jul' => 7,
        'agu' => 8,
        'aug' => 8,
        'sep' => 9,
        'okt' => 10,
        'oct' => 10,
        'nov' => 11,
        'des' => 12,
        'dec' => 12,
    ];

    private const KEAHLIAN = ['PHP', 'Laravel', 'CodeIgniter', 'JavaScript', 'TypeScript', 'React', 'Vue', 'Angular', 'Node.js', 'Python', 'Java', 'Kotlin', 'Flutter', 'C#', 'C++', 'SQL', 'MySQL', 'PostgreSQL', 'MongoDB', 'Docker', 'Git', 'Linux', 'HTML', 'CSS', 'Tailwind', 'Bootstrap', 'jQuery', 'REST API', 'WordPress', 'Figma', 'Photoshop', 'Illustrator', 'Canva', 'Video Editing', 'Excel', 'Microsoft Office', 'PowerPoint', 'Power BI', 'Tableau', 'SEO', 'Digital Marketing', 'Social Media', 'Copywriting', 'Akuntansi', 'Pajak', 'Customer Service', 'Negosiasi', 'Komunikasi', 'Public Speaking', 'Networking', 'Mikrotik', 'Cisco', 'Cyber Security', 'Project Management', 'Scrum', 'Agile', 'Leadership', 'Administrasi', 'Recruitment', 'AWS', 'Azure', 'JIRA', 'Unity'];

    private const JUDUL_AKHIR = '/^(pendidikan|education|keahlian|skills?|kemampuan|keterampilan|sertifikasi|certifications?|bahasa|languages?|referensi|references|organisasi|projects?|proyek|data pribadi|personal|pengalaman|experience|work experience|riwayat pekerjaan|employment history|profil|profile|summary|tentang saya)\b/i';

    public function ekstrakTeks(UploadedFile $file): string
    {
        $ext = strtolower($file->getClientOriginalExtension());

        if ($ext === 'pdf') {
            return $this->dariPdf($file->getRealPath());
        }

        if ($ext === 'docx') {
            return $this->dariDocx($file->getRealPath());
        }

        return '';
    }

    public function parse(string $teks): array
    {
        $teks = str_replace(["\r\n", "\r", "\xC2\xA0"], ["\n", "\n", ' '], $teks);
        $teks = preg_replace('/[ \t]+/u', ' ', $teks);
        $baris = array_values(array_filter(array_map('trim', explode("\n", $teks)), fn($l) => $l !== ''));

        $lahir = $this->lahir($teks);

        $hasil = [
            'nama_lengkap' => $this->nama($baris, $teks),
            'email' => $this->email($teks),
            'no_telepon' => $this->telepon($teks),
            'nik_ktp' => $this->nik($teks),
            'tempat_lahir' => $lahir['tempat_lahir'] ?? null,
            'tanggal_lahir' => $lahir['tanggal_lahir'] ?? null,
            'jenis_kelamin' => $this->jenisKelamin($teks),
            'status_pernikahan' => $this->statusPernikahan($teks),
            'agama' => $this->agama($teks),
            'alamat' => $this->ambil('/^\s*Alamat\s*[:;]\s*(.+)$/im', $teks, 400),
            'domisili' => $this->ambil('/(?:Domisili|Lokasi|Location)\s*[:;]\s*([^\n]+)/i', $teks, 100),
            'linkedin_url' => $this->linkedin($teks),
            'pendidikan_terakhir' => $this->pendidikan($teks),
            'institusi' => $this->institusi($teks),
            'jurusan' => $this->jurusan($teks),
            'ipk' => $this->ipk($teks),
            'pengalaman_tahun' => $this->pengalaman($baris, $teks),
            'keahlian' => $this->keahlian($baris, $teks),
        ];

        return array_filter($hasil, fn($v) => $v !== null && $v !== '');
    }

    private function dariPdf(string $path): string
    {
        try {
            $parser = new \Smalot\PdfParser\Parser();
            return trim($parser->parseFile($path)->getText());
        } catch (\Throwable $e) {
            return '';
        }
    }

    private function dariDocx(string $path): string
    {
        $zip = new \ZipArchive();

        if ($zip->open($path) !== true) {
            return '';
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if (!$xml) {
            return '';
        }

        $xml = preg_replace('/<\/w:p>/', "\n", $xml);
        $xml = preg_replace('/<w:tab\/>/', "\t", $xml);
        $xml = preg_replace('/<w:br[^>]*\/>/', "\n", $xml);

        return trim(html_entity_decode(strip_tags($xml), ENT_QUOTES | ENT_XML1, 'UTF-8'));
    }

    private function kapital(string $s): string
    {
        return mb_convert_case(mb_strtolower(trim(preg_replace('/\s+/', ' ', $s))), MB_CASE_TITLE, 'UTF-8');
    }

    private function ambil(string $pola, string $teks, int $max): ?string
    {
        if (preg_match($pola, $teks, $m)) {
            $nilai = trim($m[1]);
            return $nilai === '' ? null : mb_substr($nilai, 0, $max);
        }

        return null;
    }

    private function nama(array $baris, string $teks): ?string
    {
        if (preg_match('/^\s*Nama(?:\s*Lengkap)?\s*[:;]\s*(.+)$/im', $teks, $m)) {
            $nama = trim(preg_replace('/[^\p{L}\s\.\',]/u', '', $m[1]));
            if ($nama !== '') {
                return mb_substr($this->kapital($nama), 0, 100);
            }
        }

        $abaikan = '/curriculum|vitae|resume|^cv$|riwayat hidup|daftar riwayat|profil|profile|email|telp|phone|alamat|address|linkedin|www\.|http|@|\d/i';

        foreach (array_slice($baris, 0, 8) as $l) {
            if (preg_match($abaikan, $l)) {
                continue;
            }

            $jumlahKata = count(preg_split('/\s+/', $l));

            if ($jumlahKata >= 2 && $jumlahKata <= 5 && preg_match('/^[\p{L}\s\.\',]+$/u', $l)) {
                return mb_substr($this->kapital($l), 0, 100);
            }
        }

        return null;
    }

    private function email(string $teks): ?string
    {
        if (preg_match('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', $teks, $m)) {
            return strtolower($m[0]);
        }

        return null;
    }

    private function telepon(string $teks): ?string
    {
        if (preg_match('/(?<!\d)(?:\+?62|0)[\s\-\.]?8\d{1,2}[\s\-\.]?\d{3,4}[\s\-\.]?\d{2,5}(?!\d)/', $teks, $m)) {
            return mb_substr(trim($m[0]), 0, 20);
        }

        return null;
    }

    private function nik(string $teks): ?string
    {
        if (preg_match('/(?<!\d)\d{16}(?!\d)/', $teks, $m)) {
            return $m[0];
        }

        return null;
    }

    private function linkedin(string $teks): ?string
    {
        if (preg_match('/(?:https?:\/\/)?(?:[a-z]{2,3}\.)?linkedin\.com\/[^\s]+/i', $teks, $m)) {
            $url = rtrim($m[0], '.,;)');
            return str_starts_with($url, 'http') ? $url : 'https://' . $url;
        }

        return null;
    }

    private function susun(int $y, int $m, int $d): ?string
    {
        if ($y < 1940 || $y > (int) date('Y') || !checkdate($m, $d, $y)) {
            return null;
        }

        return sprintf('%04d-%02d-%02d', $y, $m, $d);
    }

    private function tanggal(string $s): ?string
    {
        if (preg_match('/(\d{4})-(\d{2})-(\d{2})/', $s, $m)) {
            return $this->susun((int) $m[1], (int) $m[2], (int) $m[3]);
        }

        if (preg_match('/(\d{1,2})[\-\/\.\s](\d{1,2})[\-\/\.\s](\d{4})/', $s, $m)) {
            return $this->susun((int) $m[3], (int) $m[2], (int) $m[1]);
        }

        if (preg_match('/(\d{1,2})\s+([A-Za-z]+)\s+(\d{4})/', $s, $m)) {
            $bulan = self::BULAN[strtolower($m[2])] ?? null;
            if ($bulan) {
                return $this->susun((int) $m[3], $bulan, (int) $m[1]);
            }
        }

        return null;
    }

    private function lahir(string $teks): array
    {
        $tempat = null;
        $tanggal = null;

        if (preg_match('/(?:Tempat\s*\/?\s*Tgl\.?\s*Lahir|Tempat\s*,?\s*Tanggal\s*Lahir|TTL)\s*[:;]?\s*([^\n]+)/iu', $teks, $m)) {
            $nilai = trim($m[1]);
            $tanggal = $this->tanggal($nilai);

            if (preg_match('/^([\p{L}\s\.]+?)\s*[,\-]\s*\d/u', $nilai, $t)) {
                $tempat = $this->kapital($t[1]);
            }
        }

        if (!$tanggal && preg_match('/(?:Tanggal\s*Lahir|Tgl\.?\s*Lahir|Date\s*of\s*Birth|Birth\s*date|Born)\s*[:;]?\s*([^\n]+)/iu', $teks, $m)) {
            $tanggal = $this->tanggal($m[1]);
        }

        if (!$tempat && preg_match('/Tempat\s*Lahir\s*[:;]\s*([\p{L}\s\.]+)/iu', $teks, $m)) {
            $tempat = $this->kapital($m[1]);
        }

        return array_filter([
            'tempat_lahir' => $tempat ? mb_substr($tempat, 0, 100) : null,
            'tanggal_lahir' => $tanggal,
        ]);
    }

    private function jenisKelamin(string $teks): ?string
    {
        if (preg_match('/(?:Jenis\s*Kelamin|Gender|Sex)\s*[:;]?\s*([A-Za-z\-]+)/i', $teks, $m)) {
            $nilai = strtolower($m[1]);

            if (preg_match('/^(perempuan|wanita|female|p$)/', $nilai)) {
                return 'P';
            }

            if (preg_match('/^(laki|pria|male|l$)/', $nilai)) {
                return 'L';
            }
        }

        return null;
    }

    private function statusPernikahan(string $teks): ?string
    {
        if (!preg_match('/(?:Status\s*Perkawinan|Status\s*Pernikahan|Marital\s*Status)\s*[:;]?\s*([^\n]+)/i', $teks, $m)) {
            return null;
        }

        $nilai = strtolower($m[1]);

        if (preg_match('/belum|single|lajang/', $nilai)) {
            return 'Belum Kawin';
        }

        if (str_contains($nilai, 'cerai hidup')) {
            return 'Cerai Hidup';
        }

        if (str_contains($nilai, 'cerai mati')) {
            return 'Cerai Mati';
        }

        if (preg_match('/kawin|menikah|married/', $nilai)) {
            return 'Kawin';
        }

        return null;
    }

    private function agama(string $teks): ?string
    {
        if (preg_match('/Agama\s*[:;]?\s*(Islam|Kristen|Katolik|Katholik|Hindu|Budha|Buddha|Konghucu)/i', $teks, $m)) {
            $nilai = ucfirst(strtolower($m[1]));

            return match ($nilai) {
                'Katholik' => 'Katolik',
                'Budha' => 'Buddha',
                default => $nilai,
            };
        }

        return null;
    }

    private function pendidikan(string $teks): ?string
    {
        $peta = [
            'S3' => '/\b(S3|S-3|Doktor|Doctoral|PhD|Ph\.D)\b/i',
            'S2' => '/\b(S2|S-2|Magister|Master)\b/i',
            'D4' => '/\b(D4|D-4|Sarjana\s*Terapan)\b/i',
            'S1' => '/\b(S1|S-1|Sarjana|Bachelor|Strata\s*1)\b/i',
            'D3' => '/\b(D3|D-3|Diploma)\b/i',
            'SMA/SMK' => '/\b(SMA|SMK|SMU|Senior\s*High|Madrasah\s*Aliyah)\b/i',
        ];

        foreach ($peta as $jenjang => $pola) {
            if (preg_match($pola, $teks)) {
                return $jenjang;
            }
        }

        return null;
    }

    private function institusi(string $teks): ?string
    {
        if (preg_match('/\b((?:Universitas|Institut|Politeknik|Sekolah\s+Tinggi|Akademi|University|Institute|Polytechnic|STMIK|STIKOM|STIE)\s+[^\n,\-–|\(\d]{3,80})/iu', $teks, $m)) {
            return mb_substr(trim($m[1]), 0, 150);
        }

        return null;
    }

    private function jurusan(string $teks): ?string
    {
        $nilai = null;

        if (preg_match('/(?:Jurusan|Program\s*Studi|Prodi|Major)\s*[:\-]?\s*([^\n,\(\|]{3,60})/iu', $teks, $m)) {
            $nilai = $m[1];
        } elseif (preg_match('/\b(?:S1|S2|D3|D4|Bachelor(?:\s+of)?|Sarjana)\s+([A-Za-z ]{4,50})/u', $teks, $m)) {
            $nilai = $m[1];
        }

        if (!$nilai) {
            return null;
        }

        $nilai = preg_split('/\s+(?:Universitas|Institut|Politeknik|University|Institute|IPK|GPA)\b/i', $nilai)[0];
        $nilai = trim($nilai);

        return $nilai === '' ? null : mb_substr($this->kapital($nilai), 0, 100);
    }

    private function ipk(string $teks): ?string
    {
        if (preg_match('/(?:IPK|GPA|CGPA|IP\s*Kumulatif)\s*[:\-]?\s*([0-4](?:[\.,]\d{1,2})?)/iu', $teks, $m)) {
            $angka = (float) str_replace(',', '.', $m[1]);

            if ($angka > 0 && $angka <= 4) {
                return number_format($angka, 2, '.', '');
            }
        }

        return null;
    }

    private function bagian(array $baris, string $judul): array
    {
        $hasil = [];
        $mulai = null;

        foreach ($baris as $i => $l) {
            if (mb_strlen($l) <= 60 && preg_match($judul, $l)) {
                $mulai = $i;
                break;
            }
        }

        if ($mulai === null) {
            return [];
        }

        $hasil[] = $baris[$mulai];

        foreach (array_slice($baris, $mulai + 1, 80) as $l) {
            if (mb_strlen($l) <= 40 && preg_match(self::JUDUL_AKHIR, $l) && !preg_match($judul, $l)) {
                break;
            }

            $hasil[] = $l;
        }

        return $hasil;
    }

    private function pengalaman(array $baris, string $teks): ?int
    {
        if (preg_match('/(\d{1,2})\s*\+?\s*(?:tahun|thn|years?|yrs?)\s*(?:of\s*)?(?:pengalaman|experience)/i', $teks, $m)) {
            return min(50, (int) $m[1]);
        }

        if (preg_match('/(?:pengalaman|experience)[^\n\d]{0,30}(\d{1,2})\s*\+?\s*(?:tahun|thn|years?|yrs?)/i', $teks, $m)) {
            return min(50, (int) $m[1]);
        }

        $bagian = $this->bagian($baris, '/^(pengalaman kerja|pengalaman|riwayat pekerjaan|work experience|professional experience|experience|employment history)\b/i');

        if (!$bagian) {
            return null;
        }

        $total = 0;
        $ditemukan = false;
        $tahunIni = (int) date('Y');

        foreach ($bagian as $l) {
            if (preg_match_all('/((?:19|20)\d{2})\s*(?:-|–|—|to|sampai|s\/d)\s*((?:19|20)\d{2}|sekarang|saat ini|present|now|current)/iu', $l, $mm, PREG_SET_ORDER)) {
                foreach ($mm as $r) {
                    $awal = (int) $r[1];
                    $akhir = ctype_digit($r[2]) ? (int) $r[2] : $tahunIni;

                    if ($akhir >= $awal && $awal >= 1970) {
                        $total += $akhir - $awal;
                        $ditemukan = true;
                    }
                }
            }
        }

        return $ditemukan ? min(50, $total) : null;
    }

    private function keahlian(array $baris, string $teks): ?string
    {
        $daftar = [];
        $bagian = $this->bagian($baris, '/^(keahlian|skills?|kemampuan|technical skills?|hard skills?|soft skills?|keterampilan)\b/i');

        foreach ($bagian as $l) {
            $l = preg_replace('/^[^:]{0,30}:/u', '', $l);

            foreach (preg_split('/[,;|•·●▪◦\-–]|\s{2,}/u', $l) as $item) {
                $item = trim($item, " \t.\"'()");

                if ($item === '' || mb_strlen($item) < 2 || mb_strlen($item) > 40) {
                    continue;
                }

                if (str_word_count($item) > 4 || ctype_digit($item)) {
                    continue;
                }

                if (preg_match('/^(keahlian|skills?|kemampuan|keterampilan|technical skills?|hard skills?|soft skills?)$/i', $item)) {
                    continue;
                }

                $daftar[mb_strtolower($item)] = $item;
            }
        }

        foreach (self::KEAHLIAN as $skill) {
            if (preg_match('/(?<![A-Za-z0-9+#])' . preg_quote($skill, '/') . '(?![A-Za-z0-9+#])/i', $teks)) {
                $daftar[mb_strtolower($skill)] = $skill;
            }
        }

        $daftar = array_slice(array_values($daftar), 0, 25);

        return $daftar ? implode(', ', $daftar) : null;
    }
}
