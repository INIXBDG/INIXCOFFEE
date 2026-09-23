<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ContactClientExport implements FromCollection, WithHeadings, WithMapping
{
    protected $startDate;
    protected $endDate;
    protected $status;
    protected $salesKey;

    public function __construct($startDate = null, $endDate = null, $status = [], $salesKey = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->status = $status;
        $this->salesKey = $salesKey;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // ===== Query union: pesertas + contacts =====
        $pesertaQuery = DB::table('pesertas as p')
            ->selectRaw('
                p.id AS peserta_id,
                NULL AS contact_id,
                p.nama,
                p.email,
                p.no_hp AS cp,
                p.perusahaan_key,
                NULL AS divisi,
                pr.nama_perusahaan,
                pr.sales_key,
                p.updated_at AS created_at,
                NULL AS contact_status,
                "Peserta Regist" AS status_text
            ')
            ->join('perusahaans as pr', 'p.perusahaan_key', '=', 'pr.id');

        if (!empty($this->salesKey)) {
            $pesertaQuery->where('pr.sales_key', $this->salesKey);
        }

        $contactQuery = DB::table('contacts as c')
            ->selectRaw('
                NULL AS peserta_id,
                c.id AS contact_id,
                c.nama,
                c.email,
                c.cp,
                c.id_perusahaan AS perusahaan_key,
                c.divisi,
                pr.nama_perusahaan,
                pr.sales_key,
                c.updated_at AS created_at,
                c.status AS contact_status,
                CASE
                    WHEN c.status = "1" THEN "Contact Baru"
                    WHEN c.status = "0" THEN "Contact"
                    ELSE "Unknown"
                END AS status_text
            ')
            ->join('perusahaans as pr', 'c.id_perusahaan', '=', 'pr.id');

        if (!empty($this->salesKey)) {
            $contactQuery->where('pr.sales_key', $this->salesKey);
        }

        $pesertaSql = $pesertaQuery->toSql();
        $pesertaBindings = $pesertaQuery->getBindings();

        $contactSql = $contactQuery->toSql();
        $contactBindings = $contactQuery->getBindings();

        $unionSql = "({$pesertaSql}) UNION ALL ({$contactSql})";
        $unionBindings = array_merge($pesertaBindings, $contactBindings);

        $masterQuery = DB::table(DB::raw("({$unionSql}) as master"))
            ->setBindings($unionBindings);

        if (!empty($this->status)) {
            $masterQuery->whereIn('status_text', (array) $this->status);
        }

        if (!empty($this->startDate)) {
            $masterQuery->whereDate('created_at', '>=', $this->startDate);
        }
        if (!empty($this->endDate)) {
            $masterQuery->whereDate('created_at', '<=', $this->endDate);
        }

        return $masterQuery->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return ['No', 'Nama', 'Perusahaan', 'Sales', 'Status', 'Email', 'CP (No)', 'Divisi', 'Tanggal'];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $row->nama,
            $row->nama_perusahaan,
            $row->sales_key,
            $row->status_text,
            $row->email,
            $row->cp,
            $row->divisi,
            $row->created_at,
        ];
    }
}