<?php

namespace App\Console\Commands;

use App\Models\RKM;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class AutoJobRKMCommands extends Command
{
    protected $signature = 'RKM:auto-job';

    protected $description = 'Auto isi assignment RKM yang kosong dari jadwal sejenis';

    public function handle()
    {
        $senin = today()->startOfWeek()->format('Y-m-d');
        $minggu = today()->endOfWeek()->format('Y-m-d');

        $rkm = RKM::where('tanggal_awal', '<=', $minggu)
            ->where('tanggal_akhir', '>=', $senin)
            ->get();

        if ($rkm->isEmpty()) {
            return;
        }

        $groups = [];
        foreach ($rkm as $item) {
            $groups[$this->assignmentGroupKey($item)][] = $item;
        }

        $totalDiisi = 0;
        foreach ($groups as $group) {
            $totalDiisi += $this->fillMissingAssignments(new Collection($group));
        }

        $this->info("Selesai. {$totalDiisi} kolom berhasil diisi otomatis.");
    }

    protected function fillMissingAssignments(Collection $group): int
    {
        $fields = ['instruktur_key', 'instruktur_key2', 'asisten_key'];
        $assignments = [];

        foreach ($fields as $field) {
            $source = $group->first(fn (RKM $item) => ! $this->isEmptyAssignment($item->{$field}));
            if ($source) {
                $assignments[$field] = $source->{$field};
            }
        }

        $totalDiisi = 0;
        foreach ($group as $item) {
            $perluSimpan = false;

            foreach ($assignments as $field => $value) {
                if ($this->isEmptyAssignment($item->{$field})) {
                    $item->{$field} = $value;
                    $perluSimpan = true;
                    $totalDiisi++;
                }
            }

            if ($perluSimpan) {
                $item->saveQuietly();
            }
        }

        return $totalDiisi;
    }

    protected function assignmentGroupKey(RKM $item): string
    {
        return serialize([
            $item->materi_key,
            substr((string) $item->tanggal_awal, 0, 10),
            $item->metode_kelas,
            $item->ruang,
        ]);
    }

    private function isEmptyAssignment(?string $value): bool
    {
        return $value === null || trim($value) === '' || trim($value) === '-';
    }
}
