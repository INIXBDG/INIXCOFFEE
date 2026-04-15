<?php

namespace App\Exports;

use App\Models\ChecklistKeperluan;
use App\Models\RKM;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ChecklistRkmExport implements FromArray, ShouldAutoSize
{
    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    private function formatStatus($value)
    {
        return $value ? 'Tersedia' : 'Tidak Tersedia';
    }

    private function formatSelesai($value)
    {
        return $value ? '✔' : '✖';
    }

    public function array(): array
    {
        $rkm = RKM::with(['materi', 'perusahaan'])->findOrFail($this->id);

        $data = [];

        $checklists = ChecklistKeperluan::where('id_rkm', $rkm->id)
            ->with('subChecklistKeperluans')
            ->whereNotNull('tanggal_keperluan')
            ->orderBy('tanggal_keperluan', 'asc')
            ->get()
            ->keyBy('tanggal_keperluan');

        if ($checklists->isEmpty()) {
            return [['DATA CHECKLIST TIDAK ADA']];
        }

        // =========================
        // HEADER INFORMASI RKM
        // =========================
        $data[] = ['Materi', optional($rkm->materi)->nama_materi];
        $data[] = ['Tanggal Training', $rkm->tanggal_awal . ' - ' . $rkm->tanggal_akhir];
        $data[] = ['Perusahaan', $rkm->perusahaan->nama_perusahaan];
        $data[] = ['-------------------------------------------------'];
        $data[] = [''];

        // =========================
        // HEADER TANGGAL (KESAMPING)
        // =========================
        $headerTanggal = ['Tanggal Keperluan'];

        foreach ($checklists as $tanggal => $item) {
            $headerTanggal[] = $tanggal;
        }

        $data[] = $headerTanggal;
        $data[] = [''];

        // helper ambil value per tanggal
        $getValue = function ($callback) use ($checklists) {
            $row = []; 

            foreach ($checklists as $tanggal => $item) {
                $sub = $item->subChecklistKeperluans;
                $row[] = $callback($item, $sub);
            }

            return $row;
        };

        // =========================
        // DATA
        // =========================

        // Materi
        $data[] = array_merge(['Materi'], $getValue(fn($i) => $this->formatSelesai($i->materi)));
        $data[] = array_merge(['Modul'], $getValue(fn($i, $s) => $this->formatStatus($s->materi_module)));
        $data[] = array_merge(['Elearning'], $getValue(fn($i, $s) => $this->formatStatus($s->materi_elearning)));
        $data[] = [''];

        if ($rkm->metode_kelas === 'Offline') {
            $data[] = array_merge(['Kelas'], $getValue(fn($i) => $this->formatSelesai($i->kelas)));
            $data[] = [''];
        }

        // Coffee Break
        $data[] = array_merge(['Coffee Break'], $getValue(fn($i) => $this->formatSelesai($i->cb)));
        $data[] = array_merge(['Instruktur'], $getValue(fn($i, $s) => $this->formatStatus($s->cb_instruktur)));

        if ($rkm->metode_kelas === 'Offline') {
            $data[] = array_merge(['Peserta'], $getValue(fn($i, $s) => $this->formatStatus($s->cb_peserta)));
        }

        $data[] = [''];

        // Makan Siang
        $data[] = array_merge(['Makan Siang'], $getValue(fn($i) => $this->formatSelesai($i->maksi)));
        $data[] = array_merge(['Instruktur'], $getValue(fn($i, $s) => $this->formatStatus($s->maksi_instruktur)));

        if ($rkm->metode_kelas === 'Offline') {
            $data[] = array_merge(['Peserta'], $getValue(fn($i, $s) => $this->formatStatus($s->maksi_peserta)));
        }

        $data[] = [''];

        // Keperluan Kelas
        if ($rkm->metode_kelas === 'Offline') {
            $data[] = array_merge(['Keperluan Kelas'], $getValue(fn($i) => $this->formatSelesai($i->keperluan_kelas)));
            $data[] = array_merge(['AC'], $getValue(fn($i, $s) => $this->formatStatus($s->kelas_ac)));
            $data[] = array_merge(['Jam'], $getValue(fn($i, $s) => $this->formatStatus($s->kelas_jam)));
            $data[] = array_merge(['Buku'], $getValue(fn($i, $s) => $this->formatStatus($s->kelas_buku)));
            $data[] = array_merge(['Pulpen'], $getValue(fn($i, $s) => $this->formatStatus($s->kelas_pulpen)));
            $data[] = array_merge(['Permen'], $getValue(fn($i, $s) => $this->formatStatus($s->kelas_permen)));
            $data[] = array_merge(['Camilan'], $getValue(fn($i, $s) => $this->formatStatus($s->kelas_camilan)));
            $data[] = array_merge(['Minuman'], $getValue(fn($i, $s) => $this->formatStatus($s->kelas_minuman)));
            $data[] = array_merge(['Lampu'], $getValue(fn($i, $s) => $this->formatStatus($s->kelas_lampu)));
            $data[] = array_merge(['Kondisi & Kebersihan'], $getValue(fn($i, $s) => $this->formatStatus($s->kelas_kondisi_kebersihan)));
            $data[] = [''];
        }

        // =========================
        // PROGRESS PER HARI
        // =========================
        $progressRow = ['Progress'];

        foreach ($checklists as $tanggal => $item) {
            $sub = $item->subChecklistKeperluans;

            $progress = 0;

            if ($rkm->metode_kelas === 'Offline') {

                $materi = ($sub->materi_module ? 1 : 0) + ($sub->materi_elearning ? 1 : 0);
                $progress += ($materi / 2) * 20;

                if ($item->kelas) $progress += 20;

                $cb = ($sub->cb_instruktur ? 1 : 0) + ($sub->cb_peserta ? 1 : 0);
                $progress += ($cb / 2) * 20;

                $maksi = ($sub->maksi_instruktur ? 1 : 0) + ($sub->maksi_peserta ? 1 : 0);
                $progress += ($maksi / 2) * 20;

                $kelas =
                    ($sub->kelas_ac ? 1 : 0) +
                    ($sub->kelas_jam ? 1 : 0) +
                    ($sub->kelas_buku ? 1 : 0) +
                    ($sub->kelas_pulpen ? 1 : 0) +
                    ($sub->kelas_permen ? 1 : 0) +
                    ($sub->kelas_camilan ? 1 : 0) +
                    ($sub->kelas_minuman ? 1 : 0) +
                    ($sub->kelas_lampu ? 1 : 0) +
                    ($sub->kelas_kondisi_kebersihan ? 1 : 0);

                $progress += ($kelas / 9) * 20;

                $progress = round($progress);

            } else {
                $totalKategori = 3;
                $kategoriSelesai = 0;

                $materiChecked =
                    ($sub->materi_module ? 1 : 0) +
                    ($sub->materi_elearning ? 1 : 0);

                $kategoriSelesai += $materiChecked / 2;
                $kategoriSelesai += ($sub->cb_instruktur ? 1 : 0);
                $kategoriSelesai += ($sub->maksi_instruktur ? 1 : 0);

                $progress = round(($kategoriSelesai / $totalKategori) * 100);
            }

            $progressRow[] = $progress . '%';
        }

        $data[] = $progressRow;

        return $data;
    }
}