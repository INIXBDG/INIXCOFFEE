<?php

namespace App\Http\Controllers;

use App\Exports\rekapExamExport;
use App\Models\approvalexam;
use App\Models\changeexam;
use App\Models\eksam;
use App\Models\karyawan;
use App\Models\listexam;
use App\Models\Materi;
use App\Models\Perusahaan;
use App\Models\RKM;
use App\Models\User;
use App\Notifications\ApprovalExamNotification;
use App\Notifications\BayarExamNotification;
use App\Notifications\PengajuanexamNotification;
use App\Notifications\TicketNotification;
use App\Notifications\updateExamNotification;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Tickets;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class examController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('exam.index');
    }

    public function createOnly()
    {
        $kode_exam = Listexam::all();
        $materi = Materi::all();
        $perusahaan = Perusahaan::select('id', 'nama_perusahaan')->get();
        $rkm = RKM::with('perusahaan', 'materi')->get();

        $currentUser = auth()->user();
        $currentKaryawan = $currentUser->karyawan;
        $isSPVSales = $currentKaryawan && $currentKaryawan->jabatan === 'SPV Sales';

        $salesEmployees = collect();
        if ($isSPVSales) {
            $salesEmployees = Karyawan::where('jabatan', 'like', '%Sales%')
                ->where('divisi', 'like', '%Sales%')
                ->get();
        }

        return view('exam.create_only', compact(
            'kode_exam',
            'materi',
            'perusahaan',
            'rkm',
            'isSPVSales',
            'salesEmployees'
        ));
    }

    public function storeOnly(Request $request)
    {
        $currentUser = auth()->user();
        $currentKaryawan = $currentUser->karyawan;
        $isSPVSales = $currentKaryawan && $currentKaryawan->jabatan === 'SPV Sales';

        $validationRules = [
            'materi' => 'required|integer|exists:materis,id',
            'tanggal' => 'required|date',
            'perusahaan' => 'required|integer|exists:perusahaans,id',
            'pax' => 'required|integer|min:1',
            'harga' => 'required|string',
            'kurs' => 'nullable|string',
            'kurs_dollar' => 'required|string',
            'biaya_admin' => 'required|string',
            'harga_rupiah' => 'required|string',
            'pa' => 'nullable|string',
            'mata_uang' => 'required|string|in:Rupiah,Dollar,Poundsterling,Euro,Franc Swiss',
            'kode_exam' => 'required|string|exists:listexams,kode_exam',
            'diskon' => 'nullable|numeric|min:0|max:100',
            'harga_total_rupiah' => 'required|string',
            'total_final' => 'required|string',
        ];

        if ($isSPVSales) {
            $validationRules['selected_sales'] = 'required|integer|exists:karyawans,id';
        }

        $request->validate($validationRules);

        try {
            $dataMateri = Materi::findOrFail($request->materi);
            $dataPerusahaan = Perusahaan::findOrFail($request->perusahaan);

            $harga = (float) str_replace('.', '', $request->harga);
            $kurs = (float) str_replace('.', '', $request->kurs ?? 0);
            $kursDollar = (float) str_replace('.', '', $request->kurs_dollar);
            $biayaAdmin = (float) str_replace('.', '', $request->biaya_admin);
            $hargaRupiah = (float) str_replace('.', '', $request->harga_rupiah);
            $pa = (float) str_replace('.', '', $request->pa ?? 0);
            $diskonPersen = (float) ($request->diskon ?? 0);
            $pax = (int) $request->pax;
            $totalFinal = (float) str_replace('.', '', $request->total_final);
            $hargaTotalRupiah = (float) str_replace('.', '', $request->harga_total_rupiah);

            $totalHarga = 0;
            switch ($request->mata_uang) {
                case 'Rupiah':
                case 'Dollar':
                    $totalHarga = ($harga + $biayaAdmin) * $kursDollar;
                    break;
                case 'Poundsterling':
                case 'Euro':
                case 'Franc Swiss':
                    $totalHarga = ($harga * $kurs) + ($biayaAdmin * $kursDollar);
                    break;
            }

            if (abs($totalHarga * $pax - $hargaTotalRupiah) > 0.01) {
                return redirect()->back()
                    ->withErrors(['harga_total_rupiah' => 'Total harga dalam Rupiah tidak sesuai perhitungan.'])
                    ->withInput();
            }

            if ($isSPVSales) {
                $salesEmployee = Karyawan::where('jabatan', 'like', '%Sales%')
                    ->where('divisi', 'like', '%Sales%')
                    ->find($request->selected_sales);

                if (!$salesEmployee) {
                    return redirect()->back()
                        ->withErrors(['selected_sales' => 'Karyawan sales yang dipilih tidak valid.'])
                        ->withInput();
                }

                $salesKey = $salesEmployee->kode_karyawan;
            } else {
                $salesKey = $currentUser->karyawan->kode_karyawan ?? null;
                if (!$salesKey) {
                    return redirect()->back()
                        ->withErrors(['sales_key' => 'Akun Anda belum memiliki Sales Key. Hubungi admin.'])
                        ->withInput();
                }
            }

            $instrukturKey = $currentUser->id_instruktur ?? null;

            Log::info('Exam Only Debug', [
                'isSPVSales' => $isSPVSales,
                'selected_sales' => $request->selected_sales ?? 'N/A',
                'salesEmployee' => $salesEmployee ?? null,
                'salesKey' => $salesKey,
                'currentUser' => $currentUser->username,
            ]);

            DB::transaction(function () use ($request, $harga, $pa, $biayaAdmin, $kurs, $kursDollar, $totalFinal, $hargaTotalRupiah, $salesKey, $instrukturKey, $dataMateri, $dataPerusahaan) {

                $year = date('Y');
                $namaBulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                $kuartal = ['Q1', 'Q2', 'Q3', 'Q4'];
                $bulanData = array_merge($namaBulan, $kuartal);
                $index = RKM::count() % count($bulanData);
                $bulanValue = $bulanData[$index] . ' ' . $year;

                $rkm = RKM::create([
                    'materi_key' => $request->materi,
                    'tanggal_awal' => $request->tanggal,
                    'tanggal_akhir' => $request->tanggal,
                    'perusahaan_key' => $request->perusahaan,
                    'isi_pax' => $request->pax,
                    'pax' => $request->pax,
                    'metode_kelas' => 'Exam Only',
                    'status' => '3',
                    'harga_jual' => $totalFinal ?? 0,
                    'sales_key' => $salesKey,
                    'bulan' => $bulanValue,
                    'ruang' => 'Exam',
                    'exam' => '1',
                ]);

                if (!$rkm || !$rkm->id) {
                    throw new \Exception('Gagal membuat RKM untuk Exam Only');
                }

                Log::info('RKM Created', [
                    'rkm_id' => $rkm->id,
                    'sales_key' => $rkm->sales_key,
                    'metode_kelas' => $rkm->metode_kelas
                ]);

                $invoice = 'INV-' . $this->generateInvoiceNumber();

                $exam = eksam::create([
                    'tanggal_pengajuan' => now(),
                    'materi' => $dataMateri->nama_materi,
                    'id_rkm' => $rkm->id,
                    'perusahaan' => $dataPerusahaan->nama_perusahaan,
                    'isi_pax' => $request->pax,
                    'pax' => $request->pax,
                    'total_pax' => $request->pax,
                    'harga' => $harga,
                    'kurs' => $kurs,
                    'kurs_dollar' => $kursDollar,
                    'biaya_admin' => $biayaAdmin,
                    'harga_rupiah' => $hargaTotalRupiah,
                    'pa' => $pa,
                    'diskon' => $request->diskon,
                    'total' => $totalFinal,
                    'status' => '3',
                    'invoice' => $invoice,
                    'mata_uang' => $request->mata_uang,
                    'kode_exam' => $request->kode_exam,
                ]);

                approvalexam::create([
                    'id_exam' => $exam->id,
                    'sales' => $salesKey,
                    'spv_sales' => false,
                    'technical_support' => false,
                    'office_manager' => false,
                    'status' => 'Belum Approval SPV Sales',
                ]);
            });

            return redirect()->route('exam.index')->with('success', 'Exam Only berhasil dibuat.');
        } catch (\Exception $e) {
            Log::error('Error storeOnly Exam: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function getExam()
    {
        $existingRKMs = eksam::pluck('id_rkm')->toArray();

        $rkm = RKM::with(['sales', 'materi', 'instruktur', 'perusahaan', 'instruktur2', 'asisten'])
            ->where('exam', '1')
            ->whereNotIn('id', $existingRKMs)
            ->orderBy('tanggal_awal', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'List Registrasi',
            'data' => $rkm,
        ]);
    }

    public function getHistoriExam()
    {
        $rkm = eksam::with([
            'materi',
            'perusahaan',
            'rkm.materi',
            'rkm.perusahaan',
            'approvalexam'
        ])->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'message' => 'List Registrasi',
            'data' => $rkm,
        ]);
    }

    public function updateTanggal(Request $request, $id)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $exam = eksam::findOrFail($id);

        $exam->tanggal_mulai = $request->tanggal_mulai;
        $exam->tanggal_selesai = $request->tanggal_selesai;
        $exam->save();

        return redirect()->back()->with('success', 'Tanggal Mulai dan Selesai Exam berhasil diperbarui.');
    }

    public function uploadInvoice(Request $request, $id)
    {
        $request->validate([
            'file_invoice' => 'required|array',
            'file_invoice.*' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        try {
            $exam = eksam::findOrFail($id);
            $existingFiles = $exam->file_invoice ?? [];

            if ($request->hasFile('file_invoice')) {
                foreach ($request->file('file_invoice') as $file) {
                    $filename = time() . '_' . uniqid() . '_invoice_' . $exam->invoice . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('uploads/invoices'), $filename);
                    $existingFiles[] = $filename;
                }
                $exam->update([
                    'file_invoice' => $existingFiles
                ]);
            }

            return redirect()->back()->with('success', 'Invoice berhasil diunggah.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Gagal mengunggah invoice: ' . $e->getMessage()]);
        }
    }

    public function deleteSpecificInvoice($id, $filename)
    {
        try {
            $exam = eksam::findOrFail($id);
            $existingFiles = $exam->file_invoice ?? [];

            $newFiles = array_filter($existingFiles, function ($file) use ($filename) {
                return $file !== $filename;
            });

            $newFiles = array_values($newFiles);

            if (file_exists(public_path('uploads/invoices/' . $filename))) {
                unlink(public_path('uploads/invoices/' . $filename));
            }

            $exam->update([
                'file_invoice' => empty($newFiles) ? null : $newFiles
            ]);

            return redirect()->back()->with('success', 'File invoice berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Gagal menghapus invoice: ' . $e->getMessage()]);
        }
    }

    public function create($id)
    {
        $rkm = RKM::with('perusahaan', 'materi')->findOrFail($id);
        $kode_exam = listexam::all();
        return view('exam.create', compact('rkm', 'kode_exam'));
    }

    private function generateInvoiceNumber(): string
    {
        $latestExam = eksam::orderBy('created_at', 'desc')->first();
        $currentDate = date('Ymd');
        $invoiceNumber = $currentDate . '-001';

        if ($latestExam) {
            $latestInvoiceNumber = $latestExam->invoice;
            $latestDate = substr($latestInvoiceNumber, 0, 8);

            if ($latestDate == $currentDate) {
                $latestSequence = (int) substr($latestInvoiceNumber, 9);
                $newSequence = str_pad($latestSequence + 1, 3, '0', STR_PAD_LEFT);
                $invoiceNumber = $currentDate . '-' . $newSequence;
            }
        }

        return $invoiceNumber;
    }

    public function store(Request $request)
    {
        $harga_rupiah = preg_replace('/[^\d]/', '', $request->harga_rupiah);
        $total = preg_replace('/[^\d]/', '', $request->total);
        $user = auth()->user()->id_sales;
        $harga = str_replace(',', '.', $request->harga);
        $harga = preg_replace('/[^\d.]/', '', $harga);

        $request->merge([
            'harga' => $harga,
            'total' => $total,
            'harga_rupiah' => $harga_rupiah,
        ]);

        try {
            $rkmSource = RKM::with('materi', 'perusahaan')->where('id', $request->id_rkm)->first();
            if ($request->pax > $rkmSource->pax) {
                return redirect()->back()->with('error', 'Pax tidak boleh lebih dari ' . $rkmSource->pax);
            }
            $data = $request->validate([
                'tanggal_pengajuan' => 'required|date',
                'materi' => 'required|string|max:255',
                'id_rkm' => 'required|string|max:255',
                'perusahaan' => 'required|string|max:255',
                'mata_uang' => 'nullable|string',
                'harga' => 'nullable|string',
                'biaya_admin' => 'nullable|string',
                'harga_rupiah' => 'required|string',
                'kurs' => 'required|string',
                'pax' => 'required|integer',
                'total' => 'required|string',
                'kode_exam' => 'nullable|string',
            ]);

            $invoice = 'INV-' . $this->generateInvoiceNumber();
            $status = 'Belum Approval SPV Sales';

            DB::transaction(function () use ($request, $rkmSource, $invoice, $status) {

                $newRkm = RKM::create([
                    'sales_key' => $rkmSource->sales_key,
                    'materi_key' => $rkmSource->materi_key,
                    'perusahaan_key' => $rkmSource->perusahaan_key,
                    'harga_jual' => $rkmSource->harga_jual,
                    'pax' => $request->pax,
                    'isi_pax' => $request->pax,
                    'tanggal_awal' => $rkmSource->tanggal_awal,
                    'tanggal_akhir' => $rkmSource->tanggal_akhir,
                    'metode_kelas' => $rkmSource->metode_kelas,
                    'event' => $rkmSource->event,
                    'ruang' => $rkmSource->ruang,
                    'instruktur_key' => $rkmSource->instruktur_key,
                    'instruktur_key2' => $rkmSource->instruktur_key2,
                    'asisten_key' => $rkmSource->asisten_key,
                    'status' => $rkmSource->status,
                    'exam' => '1',
                    'authorize' => $rkmSource->authorize,
                    'registrasi_form' => $rkmSource->registrasi_form,
                    'quartal' => $rkmSource->quartal,
                    'bulan' => $rkmSource->bulan,
                    'tahun' => $rkmSource->tahun,
                    'makanan' => $rkmSource->makanan,
                ]);

                $exam = eksam::create([
                    'tanggal_pengajuan' => $request->tanggal_pengajuan,
                    'materi' => $request->materi,
                    'id_rkm' => $newRkm->id,
                    'perusahaan' => $request->perusahaan,
                    'mata_uang' => $request->mata_uang,
                    'harga' => $request->harga,
                    'biaya_admin' => $request->biaya_admin,
                    'harga_rupiah' => $request->harga_rupiah,
                    'kurs' => $request->kurs,
                    'kurs_dollar' => $request->kurs_dollar,
                    'pax' => $request->pax,
                    'total_pax' => $request->pax,
                    'total' => $request->total,
                    'kode_exam' => $request->kode_exam,
                    'status' => $request->status,
                    'invoice' => $invoice
                ]);

                approvalexam::create([
                    'id_exam' => $exam->id,
                    'sales' => $newRkm->sales_key,
                    'spv_sales' => false,
                    'technical_support' => false,
                    'office_manager' => false,
                    'status' => $status,
                ]);
            });

            $data = [
                'nama_materi' => $rkmSource->materi->nama_materi,
                'nama_perusahaan' => $rkmSource->perusahaan->nama_perusahaan,
            ];
            $finance = karyawan::where('jabatan', 'Finance & Accounting')->first();
            $kooroff = karyawan::where('jabatan', 'Koordinator Office')->first();
            $Eduman = karyawan::where('jabatan', 'Education Manager')->first();
            $SPVSales = karyawan::where('jabatan', 'SPV Sales')->first();
            $GM = karyawan::where('jabatan', 'GM')->first();
            $users = array_map(function ($user) {
                return $user === '-' ? null : $user;
            }, [
                $rkmSource->sales_key,
                $Eduman->kode_karyawan,
                $finance->kode_karyawan,
                $kooroff->kode_karyawan,
                $SPVSales->kode_karyawan,
                $GM->kode_karyawan,
                'NF'
            ]);

            $users = User::whereHas('karyawan', function ($query) use ($users) {
                $query->whereIn('kode_karyawan', array_filter($users));
            })->get();

            $path = '/exam/' . eksam::latest()->first()->id;

            foreach ($users as $user) {
                $receiverId = $user->id;
                NotificationFacade::send($user, new PengajuanexamNotification($data, $path, $receiverId));
            }

            return redirect()->route('exam.index')->with(['success' => 'Data Berhasil Disimpan!']);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function show(string $id)
    {
        $rkm = eksam::with('rkm')->findOrFail($id);
        $exam = changeexam::where('id_exam', $id)->get();
        $approvalexam = approvalexam::where('id_exam', $id)->first();
        $biaya_admin = $rkm->biaya_admin * $rkm->kurs_dollar;
        $harga = $rkm->harga * $rkm->kurs;

        Log::info('Exam Show - ID: ' . $id . ', RKM: ' . json_encode($rkm));
        return view('exam.show', compact('rkm', 'exam', 'approvalexam', 'biaya_admin', 'harga'));
    }

    public function edit(string $id)
    {
        $kode_exam = listexam::all();
        $exam = eksam::with('rkm', 'karyawan')->findOrFail($id);
        return view('exam.edit', compact('exam', 'kode_exam'));
    }

    public function update(Request $request, $id)
    {
        $harga_rupiah = preg_replace('/[^\d]/', '', $request->harga_rupiah);
        $request->merge(['harga_rupiah' => $harga_rupiah]);
        $total = preg_replace('/[^\d]/', '', $request->total);
        $request->merge(['total' => $total]);
        $id_karyawan = auth()->user()->karyawan_id;
        $karyawan = karyawan::findOrFail($id_karyawan);
        $kode_karyawan = $karyawan->kode_karyawan;

        try {
            $rkm = RKM::where('id', $request->id_rkm)->first();
            if ($request->pax > $rkm->pax) {
                return redirect()->back()->with('error', 'Pax tidak boleh lebih dari ' . $rkm->pax);
            }

            $data = $request->validate([
                'tanggal_pengajuan' => 'required|date',
                'materi' => 'required|string|max:255',
                'id_rkm' => 'required|string|max:255',
                'perusahaan' => 'required|string|max:255',
                'kode_exam' => 'nullable|string',
                'mata_uang' => 'nullable|string',
                'harga' => 'nullable|numeric',
                'kurs' => 'nullable|numeric',
                'biaya_admin' => 'nullable|numeric',
                'kurs_dollar' => 'nullable|numeric',
                'harga_rupiah' => 'required|string',
                'pax' => 'required|integer',
                'total' => 'required|string',
                'keterangan' => 'nullable|string',
            ]);

            $exam = eksam::findOrFail($id);
            $exam->update([
                'tanggal_pengajuan' => $request->tanggal_pengajuan,
                'materi' => $request->materi,
                'id_rkm' => $request->id_rkm,
                'perusahaan' => $request->perusahaan,
                'kode_exam' => $request->kode_exam,
                'mata_uang' => $request->mata_uang,
                'harga' => $request->harga,
                'biaya_admin' => $request->biaya_admin,
                'harga_rupiah' => $request->harga_rupiah,
                'kurs' => $request->kurs,
                'kurs_dollar' => $request->kurs_dollar,
                'pax' => $request->pax,
                'total_pax' => $request->pax,
                'total' => $request->total,
                'keterangan' => $request->keterangan,
                'status' => $exam->status,
            ]);

            changeexam::create([
                'id_exam' => $exam->id,
                'keterangan' => $request->keterangan,
                'status' => '-',
                'kode_karyawan' => $kode_karyawan,
            ]);

            return redirect()->route('exam.index')->with(['success' => 'Data Berhasil Diperbarui!']);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function destroy($id): RedirectResponse
    {
        $exam = eksam::findOrFail($id);

        $rkm = $exam->rkm;

        approvalexam::where('id_exam', $exam->id)->delete();
        changeexam::where('id_exam', $exam->id)->delete();

        $exam->delete();

        if ($rkm) {
            $rkm->delete();
        }

        return redirect()
            ->route('exam.index')
            ->with(['success' => 'Data Berhasil Dihapus!']);
    }

    public function approvalexam($id)
    {
        $exam = eksam::with('rkm', 'karyawan')->findOrFail($id);
        $kode_exam = listexam::all();

        return view('exam.approval', compact('exam', 'kode_exam'));
    }

    public function sendapprovalexam(Request $request, $id)
    {
        $approval = approvalexam::where('id_exam', $id)->first();

        $id_karyawan = auth()->user()->karyawan_id;
        $karyawan = karyawan::findOrFail($id_karyawan);
        $kode_karyawan = $karyawan->kode_karyawan;
        $jabatan = $karyawan->jabatan;
        if ($jabatan == 'SPV Sales') {
            $status = 'Sudah Diapprove oleh SPV Sales';
            $keterangan = 'Proses';

            $approval->update([
                'id_exam' => $id,
                'spv_sales' => true,
                'technical_support' => false,
                'office_manager' => false,
                'status' => $status,
                'ttd_sales' => $kode_karyawan,
            ]);
            $data = eksam::findOrfail($id);
            $finance = karyawan::where('jabatan', 'Finance & Accounting')->first();
            $kooroff = karyawan::where('jabatan', 'Koordinator Office')->first();
            $GM = karyawan::where('jabatan', 'GM')->first();

            $users = array_map(function ($user) {
                return $user === '-' ? null : $user;
            }, [
                $finance->kode_karyawan,
                $kooroff->kode_karyawan,
                $GM->kode_karyawan,
                $approval->sales
            ]);

            $users = User::whereHas('karyawan', function ($query) use ($users) {
                $query->whereIn('kode_karyawan', array_filter($users));
            })->get();

            $path = '/exam/' . $id;

            foreach ($users as $user) {
                $receiverId = $user->id;
                NotificationFacade::send($user, new ApprovalExamNotification($data, $path, $receiverId));
            }
        }
        if ($jabatan == 'Office Manager' || $jabatan == 'GM' || $jabatan == 'Koordinator Office' || $jabatan == 'Finance & Accounting') {
            $exam = eksam::findOrFail($id);
            if ($exam->kurs != $request->kurs || $exam->kurs_dollar != $request->kurs_dollar) {
                $status = 'Sudah Dikonfirmasi dan Disesuaikan oleh ' . $jabatan;
                $keterangan = 'Proses';

                $exam->update([
                    'harga' => $request->harga,
                    'harga_rupiah' => $request->harga_rupiah,
                    'kurs' => $request->kurs,
                    'kurs_dollar' => $request->kurs_dollar,
                    'total' => $request->total,
                    'status' => $status,
                    'keterangan' => $keterangan,
                    'kode_karyawan' => $kode_karyawan
                ]);
            } else {
                $status = 'Sudah Diapprove oleh ' . $jabatan;
                $keterangan = 'Proses';
            }
            $approval->update([
                'id_exam' => $id,
                'spv_sales' => true,
                'technical_support' => false,
                'office_manager' => true,
                'status' => $status,
                'ttd_off' => $kode_karyawan,
            ]);

            $data = eksam::findOrfail($id);
            $users = array_map(function ($user) {
                return $user === '-' ? null : $user;
            }, [
                'NF',
                $approval->sales
            ]);

            $users = User::whereHas('karyawan', function ($query) use ($users) {
                $query->whereIn('kode_karyawan', array_filter($users));
            })->get();

            $path = '/exam/' . $id;

            foreach ($users as $user) {
                $receiverId = $user->id;
                NotificationFacade::send($user, new ApprovalExamNotification($data, $path, $receiverId));
            }
        }
        if ($jabatan == 'Technical Support') {
            $status = 'Sudah Dikonfirmasi oleh Technical Support';
            $keterangan = 'Selesai';
            $exam = eksam::findOrFail($id);

            if ($exam->harga != $request->harga || $exam->biaya_admin != $request->biaya_admin) {
                $status = 'Sudah Dikonfirmasi dan disesuaikan oleh Technical Support';
                $keterangan = 'Selesai';

                $exam->update([
                    'harga' => $request->harga,
                    'harga_rupiah' => $request->harga_rupiah,
                    'kurs' => $request->kurs,
                    'kurs_dollar' => $request->kurs_dollar,
                    'total' => $request->total,
                    'status' => $status,
                    'keterangan' => $keterangan,
                    'kode_karyawan' => $kode_karyawan
                ]);
            } else {
                $status = 'Sudah Dikonfirmasi oleh Technical Support';
                $keterangan = 'Selesai';
            }

            $approval->update([
                'id_exam' => $id,
                'spv_sales' => true,
                'technical_support' => true,
                'office_manager' => true,
                'status' => $status,
                'ttd_ts' => $kode_karyawan,
            ]);
            $data = eksam::findOrfail($id);
            $users = array_map(function ($user) {
                return $user === '-' ? null : $user;
            }, [
                $approval->sales
            ]);

            $users = User::whereHas('karyawan', function ($query) use ($users) {
                $query->whereIn('kode_karyawan', array_filter($users));
            })->get();

            $path = '/exam/' . $id;

            foreach ($users as $user) {
                $receiverId = $user->id;
                NotificationFacade::send($user, new ApprovalExamNotification($data, $path, $receiverId));
            }

            $finance = karyawan::where('jabatan', 'Finance & Accounting')->first();
            $users = array_map(function ($user) {
                return $user === '-' ? null : $user;
            }, [
                $approval->ttd_ts,
                $finance->kode_karyawan
            ]);

            $users = User::whereHas('karyawan', function ($query) use ($users) {
                $query->whereIn('kode_karyawan', array_filter($users));
            })->get();

            $path = '/exam/' . $id;

            foreach ($users as $user) {
                $receiverId = $user->id;
                NotificationFacade::send($user, new BayarExamNotification($data, $path, $receiverId));
            }
        }

        changeexam::create([
            'id_exam' => $id,
            'keterangan' => $keterangan,
            'status' => $status,
            'kode_karyawan' => $kode_karyawan,
        ]);

        return redirect()->route('exam.show', $id);
    }

    public function invoice($id)
    {
        $data = eksam::with('rkm', 'kodeeksam', 'registexam', 'approvalexam')->findOrFail($id);
        $sales = karyawan::where('kode_karyawan', $data->approvalexam->sales)->first() ?? '-';
        if (!$data->approvalexam->ttd_sales) {
            $spv_sales = karyawan::where('jabatan', 'SPV Sales')->first();
        } else {
            $spv_sales = karyawan::where('kode_karyawan', $data->approvalexam->ttd_sales)->first();
        }
        if (!$data->approvalexam->ttd_off) {
            $office_manager = karyawan::where('jabatan', 'Finance & Accounting')->first();
        } else {
            $office_manager = karyawan::where('kode_karyawan', $data->approvalexam->ttd_off)->first();
        }
        if (!$data->approvalexam->ttd_ts) {
            $technical_support = karyawan::where('jabatan', 'Technical Support')->first();
        } else {
            $technical_support = karyawan::where('kode_karyawan', $data->approvalexam->ttd_ts)->first();
        }
        $biaya_admin = $data->biaya_admin * $data->kurs_dollar;
        $harga = $data->harga * $data->kurs;
        $totalharga = $harga * $data->pax;
        $totalbiayadmin = $biaya_admin * $data->pax;
        return view('exam.invoice', compact('data', 'spv_sales', 'technical_support', 'office_manager', 'sales', 'harga', 'biaya_admin', 'totalharga', 'totalbiayadmin'));

    }

    public function assignRoom($id)
    {
        $exam = eksam::with(['rkm', 'materi', 'perusahaan'])->findOrFail($id);

        if ($exam->status != '3') {
            return redirect()->back()->with('error', 'Hanya Exam Only yang dapat di-assign ruangan.');
        }

        session(['exam_assign_id' => $id]);
        session([
            'exam_assign_data' => [
                'materi' => $exam->materi->nama_materi ?? 'N/A',
                'perusahaan' => $exam->perusahaan->nama_perusahaan ?? 'N/A',
                'pax' => $exam->pax,
                'invoice' => $exam->invoice
            ]
        ]);

        return redirect()->route('managementKelas.index', ['assign_mode' => 'exam', 'exam_id' => $id])
            ->with('info', 'Pilih ruangan dan tanggal untuk exam: ' . ($exam->materi->nama_materi ?? 'N/A'));
    }

    public function processRoomAssignment(Request $request)
    {
        $examId = session('exam_assign_id');

        if (!$examId) {
            return redirect()->route('exam.index')->with('error', 'Session expired. Silakan coba lagi.');
        }

        $request->validate([
            'ruang' => 'required|string',
            'tanggal' => 'required|date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required|after:jam_mulai',
            'kebutuhan' => 'nullable|string',
            'keterangan' => 'nullable|string',
        ]);

        $exam = eksam::with(['rkm', 'materi', 'perusahaan'])->findOrFail($examId);

        if ($exam->status != '3') {
            return redirect()->route('exam.index')->with('error', 'Hanya Exam Only yang dapat di-assign ruangan.');
        }

        try {
            DB::transaction(function () use ($request, $exam) {
                $exam->rkm->update([
                    'ruang' => $request->ruang,
                    'tanggal_awal' => $request->tanggal,
                    'tanggal_akhir' => $request->tanggal,
                    'metode_kelas' => 'Offline'
                ]);

                \App\Models\manajemenRuangan::create([
                    'ruangan' => $request->ruang,
                    'tanggal' => $request->tanggal,
                    'jam_mulai' => $request->jam_mulai,
                    'jam_selesai' => $request->jam_selesai,
                    'kebutuhan' => $request->filled('kebutuhan') ? $request->kebutuhan :
                        'Exam - ' . ($exam->materi->nama_materi ?? 'Unknown'),
                    'keterangan' => $request->filled('keterangan') ? $request->keterangan :
                        'Exam untuk ' . ($exam->perusahaan->nama_perusahaan ?? 'Unknown') .
                        ' (Pax: ' . $exam->pax . ', Invoice: ' . $exam->invoice . ')',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            });

            session()->forget(['exam_assign_id', 'exam_assign_data']);

            return redirect()->route('exam.index')->with('success', 'Ruangan berhasil di-assign untuk exam.');

        } catch (\Exception $e) {
            Log::error('Exam room assignment failed: ' . $e->getMessage());
            return redirect()->route('exam.index')->with('error', 'Gagal assign ruangan: ' . $e->getMessage());
        }
    }

    public function removeRoomAssignment($id)
    {
        try {
            $exam = eksam::with('rkm')->findOrFail($id);

            DB::transaction(function () use ($exam) {
                \App\Models\manajemenRuangan::where('ruangan', $exam->rkm->ruang)
                    ->where('kebutuhan', 'LIKE', 'Exam - %')
                    ->delete();

                $exam->rkm->update([
                    'ruang' => null,
                    'metode_kelas' => 'Exam Only'
                ]);
            });

            return redirect()->back()->with('success', 'Assignment ruangan berhasil dihapus.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus assignment: ' . $e->getMessage());
        }
    }

    public function rekapExam()
    {
        return view('exam.rekapexam');
    }

    public function getRekapExam($year, $month)
    {
        $rkm = eksam::with(['rkm', 'registexam', 'registexam.peserta', 'registexam.creditcard', 'registexam.hasilexam'])
            ->whereMonth('tanggal_pengajuan', $month)
            ->whereYear('tanggal_pengajuan', $year)
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json([
            'success' => true,
            'message' => 'Rekap Exam di ' . $month . '-' . $year,
            'data' => $rkm,
        ]);
    }

    public function rekapExamExportExcel($year, $month)
    {
        $rkm = eksam::with(['rkm', 'registexam', 'registexam.peserta', 'registexam.creditcard', 'registexam.hasilexam'])
            ->whereMonth('tanggal_pengajuan', $month)
            ->whereYear('tanggal_pengajuan', $year)
            ->orderBy('created_at', 'desc')
            ->get();
        $data = $rkm->flatMap(function ($item) {
            return $item->registexam->map(function ($reg) use ($item) {
                return [
                    'Invoice' => $item->invoice,
                    'Tanggal Pengajuan' => $item->tanggal_pengajuan,
                    'Nama Materi' => $item->materi,
                    'Nama Perusahaan' => $item->perusahaan,
                    'Kode Exam' => $item->kode_exam,
                    'Pax' => $item->pax,
                    'Nama Peserta' => $reg->peserta->nama ?? 'Belum Daftar',
                    'Tanggal Exam' => $reg->tanggal_exam,
                    'Waktu Exam' => $reg->pukul,
                    'Grade Exam' => $reg->hasilexam->Hasil ?? 'Tidak Ada',
                    'Hasil Exam' => $reg->hasilexam->keterangan ?? 'Tidak Ada',
                    'Kartu Kredit' => $reg->creditcard->nama_pemilik ?? 'Belum Daftar',
                    'Mata Uang' => $item->mata_uang,
                    'Harga' => $item->harga,
                    'Kurs Harga' => $item->kurs,
                    'Biaya Admin' => $item->biaya_admin,
                    'Kurs Biaya Admin' => $item->kurs_dollar,
                    'Harga dalam Rupiah' => $item->harga_rupiah,
                    'Total Harga dalam Rupiah' => $item->total,

                ];
            });
        });

        return Excel::download(new rekapExamExport($data), 'Rekap Exam ' . $year . '-' . $month . '.xlsx');
    }

    public function hargaExam()
    {
        $exams = listexam::all();
        return view('exam.hargaExam', compact('exams'));
    }

    public function detailHargaExam($id)
    {
        $exam = listexam::findOrFail($id);

        return view('exam.detailHarga', compact('exam'));
    }

    public function pengajuanUpdateExam(Request $request, $id)
    {
        // 1. Ambil data exam spesifik
        $exam = listexam::findOrFail($id);

        // 2. Buat instansi tiket baru
        $ticket = Tickets::create([
            'nama_karyawan'  => Auth::user()->karyawan->nama_lengkap ?? 'Sistem',
            'divisi'         => Auth::user()->karyawan->divisi ?? '-',
            'kategori'       => 'Exam',
            'keperluan'      => 'Technical Support',
            'detail_kendala' => 'Permintaan update harga terbaru dari ' . $exam->nama_exam,
            'timestamp'      => now()
        ]);

        // 3. Hasilkan ID Tiket terstruktur (Identik dengan metode store)
        $todayCount = Tickets::whereDate('created_at', today())->count();
        $char = chr(96 + $todayCount);
        $ticketId = 'NIX' . now()->format('ymd') . $char;
        $ticket->ticket_id = $ticketId;
        $ticket->save();

        // 4. Ekstraksi entitas User divisi IT Service Management untuk TicketNotification
        $itsm = karyawan::where('divisi', 'IT Service Management')->get();
        $kodeKaryawanList = $itsm->pluck('kode_karyawan')->toArray();

        // Filter nilai '-' menjadi null
        $usersITSM = array_map(function ($user) {
            return $user === '-' ? null : $user;
        }, $kodeKaryawanList);

        // Ambil data User terkait kode_karyawan yang valid
        $users = User::whereHas('karyawan', function ($query) use ($usersITSM) {
            $query->whereIn('kode_karyawan', array_filter($usersITSM));
        })->get();

        $pathTicket = '/tickets';
        $statusTicket = "Ticketing Baru";

        // 5. Transmisi Notifikasi 1: Ticketing (ke IT Service Management)
        foreach ($users as $user) {
            $receiverId = $user->id;
            NotificationFacade::send($user, new TicketNotification($ticket, $pathTicket, $statusTicket, $receiverId));
        }

        // 6. Transmisi Notifikasi 2: Update Exam (ke Technical Support)
        $technical_support = User::where('jabatan', 'Technical Support')->get();
        $pathExam = "/listexams/{$id}/edit";
        $dataExam = [
            'nama_exam' => $exam->nama_exam,
        ];

        foreach ($technical_support as $user) {
            $receiverId = $user->id;
            NotificationFacade::send($user, new updateExamNotification($dataExam, $pathExam, $receiverId));
        }

        // 7. Transmisi muatan data (payload) ke endpoint Webhook eksternal
        try {
            Http::withHeaders([
                'Accept' => 'application/json',
                'X-Webhook-Secret' => 'RAHASIA_KITA'
            ])->post('https://inixindobdg.co.id/api/new-ticket-notification', [
                'ticket_id'      => $ticket->ticket_id,
                'nama_karyawan'  => $ticket->nama_karyawan,
                'divisi'         => $ticket->divisi,
                'kategori'       => $ticket->kategori,
                'keperluan'      => $ticket->keperluan,
                'detail_kendala' => $ticket->detail_kendala,
            ]);

        } catch (\Exception $e) {
            Log::error("Gagal mengirim webhook: " . $e->getMessage());
        }

        return redirect()->back()->with(['success' => 'Pengajuan berhasil dibuat, alur tiket dan notifikasi ganda telah dijalankan.']);
    }
}
