<?php

namespace App\Http\Controllers;

use App\Models\SurveyKepuasan;
use App\Models\Tickets;
use Illuminate\Http\Request;

class SurveyKepuasanController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isItsm = ($user->karyawan && $user->karyawan->divisi === 'IT Service Management') 
            || $user->can('View ITSM Only') 
            || $user->jabatan === 'HRD';

        $ticket_id = $request->query('ticket_id');

        // Skenario 1: Akses tanpa parameter ticket_id
        if (!$ticket_id) {
            $namaUser = optional($user->karyawan)->nama_lengkap;
            $pendingTickets = collect();

            if ($namaUser) {
                // HANYA ambil tiket yang berstatus 'Selesai' dan BELUM disurvei
                $pendingTickets = Tickets::where('nama_karyawan', $namaUser)
                    ->where('status', 'Selesai')
                    ->where('is_surveyed', false)
                    ->latest()
                    ->get();
            }

            // Aturan 1: Jika hanya ada 1 survey yang belum diisi -> langsung masuk ke form survey
            if ($pendingTickets->count() === 1) {
                $ticket = $pendingTickets->first();
                $latestSurvey = null;
                return view('surveykepuasan.create', compact('ticket', 'latestSurvey'));
            }

            // Aturan 2: Jika ada lebih dari 1 survey yang belum diisi -> tampilkan daftar pemilihan tiket (yang sudah diisi otomatis hilang dari UI)
            if ($pendingTickets->count() > 1) {
                $userTickets = $pendingTickets;
                return view('surveykepuasan.select', compact('userTickets', 'isItsm'));
            }

            // Aturan 3: Jika user TIDAK punya form untuk survey (semua sudah diisi atau belum ada) -> tampilkan halaman Gambar 2
            $latestSurvey = null;
            if ($namaUser) {
                $latestSurvey = SurveyKepuasan::where('id_user', $user->id)
                    ->orWhereHas('ticket', function ($q) use ($namaUser) {
                        $q->where('nama_karyawan', $namaUser);
                    })
                    ->latest()
                    ->first();
            }

            return view('surveykepuasan.completed', compact('latestSurvey', 'isItsm'));
        }

        // Skenario 2: Akses dengan ticket_id spesifik
        $ticket = Tickets::where('ticket_id', $ticket_id)->first();
        if (!$ticket) {
            return redirect()->route('surveykepuasan.index')->with('error', 'Tiket ' . $ticket_id . ' tidak ditemukan.');
        }

        // Cek status tiket
        if ($ticket->status !== 'Selesai') {
            return redirect()->route('surveykepuasan.index')->with('warning', 'Survey hanya dapat diisi untuk tiket yang sudah berstatus Selesai.');
        }

        // Validasi kepemilikan tiket: Tim ITSM atau user lain TIDAK BOLEH mengisi survey dari user lain!
        $currentUserNama = optional($user->karyawan)->nama_lengkap;
        $isTicketOwner = ($currentUserNama && strtolower(trim($ticket->nama_karyawan)) === strtolower(trim($currentUserNama)));

        if (!$isTicketOwner) {
            if ($isItsm) {
                return redirect()->route('surveyKepuasan.indexTable')->with('warning', 'Tim ITSM tidak boleh mengisi survey milik user lain. Anda hanya dapat melihat data hasil survey pada tabel rekap.');
            }
            return redirect()->route('surveykepuasan.index')->with('error', 'Anda tidak berhak mengakses survey untuk tiket milik pengguna lain.');
        }

        // Jika tiket ini sudah pernah disurvei -> tampilkan halaman Terima Kasih (Gambar 2)
        if ($ticket->is_surveyed) {
            $latestSurvey = SurveyKepuasan::where('ticket_id', $ticket->ticket_id)->latest()->first();
            return view('surveykepuasan.completed', compact('latestSurvey', 'isItsm'));
        }

        $latestSurvey = null;
        return view('surveykepuasan.create', compact('ticket', 'latestSurvey'));
    }

    public function store(Request $request)
    {
        $id_user = auth()->user()->id;

        $request->validate([
            'ticket_id' => 'required|exists:tickets,ticket_id',
            'q1' => 'required|integer|min:1|max:4',
            'q2' => 'required|string',
            'q3' => 'nullable|string',
            'q4' => 'required|integer|min:1|max:4',
            'q5' => 'nullable|string',
        ]);

        // Cek tiket apakah berstatus Selesai
        $ticket = Tickets::where('ticket_id', $request->ticket_id)->first();
        if ($ticket && $ticket->status !== 'Selesai') {
            return redirect()->route('tickets.index')->with('error', 'Survey hanya bisa diisi jika tiket telah Selesai.');
        }

        // Validasi kepemilikan tiket saat submit survey
        $currentUserNama = optional(auth()->user()->karyawan)->nama_lengkap;
        $isTicketOwner = ($ticket && $currentUserNama && strtolower(trim($ticket->nama_karyawan)) === strtolower(trim($currentUserNama)));
        if (!$isTicketOwner) {
            return redirect()->route('tickets.index')->with('error', 'Anda tidak diperbolehkan mengisi survey untuk tiket milik pengguna lain.');
        }

        // Cek apakah tiket sudah pernah disurvei
        $alreadySurveyed = SurveyKepuasan::where('ticket_id', $request->ticket_id)->exists();
        if ($alreadySurveyed) {
            return redirect()->route('surveykepuasan.index')->with('warning', 'Survey untuk tiket ini sudah pernah diisi.');
        }

        $surveyKepuasan = new SurveyKepuasan();
        $surveyKepuasan->id_user = $id_user;
        $surveyKepuasan->ticket_id = $request->ticket_id;
        $surveyKepuasan->q1 = $request->q1;
        $surveyKepuasan->q2 = $request->q2;
        $surveyKepuasan->q3 = $request->q3;
        $surveyKepuasan->q4 = $request->q4;
        $surveyKepuasan->q5 = $request->q5;
        $surveyKepuasan->save();

        // Pembaruan status tiket menjadi sudah disurvei
        Tickets::where('ticket_id', $request->ticket_id)->update([
            'is_surveyed' => true
        ]);

        return redirect()->route('surveykepuasan.index')->with('success', 'Survey berhasil dikirim! Terima kasih atas partisipasi Anda.');
    }

    public function indexTable()
    {
        $user = auth()->user();
        $isItsm = ($user->karyawan && $user->karyawan->divisi === 'IT Service Management') 
            || $user->can('View ITSM Only') 
            || $user->jabatan === 'HRD';

        if (!$isItsm) {
            return redirect()->route('tickets.index')->with('warning', 'Akses terbatas untuk tim IT Service Management.');
        }

        $data = SurveyKepuasan::with(['user.karyawan', 'ticket', 'karyawan'])->latest()->get();
        return view('surveykepuasan.index', compact('data'));
    }

    public function destroy($id)
    {
        $data = SurveyKepuasan::find($id);

        if (!$data) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $ticketId = $data->ticket_id;
        $data->delete();

        // Kembalikan is_surveyed tiket ke false jika tidak ada survey lain
        if ($ticketId) {
            $hasOtherSurvey = SurveyKepuasan::where('ticket_id', $ticketId)->exists();
            if (!$hasOtherSurvey) {
                Tickets::where('ticket_id', $ticketId)->update(['is_surveyed' => false]);
            }
        }

        return response()->json(['message' => 'Data berhasil dihapus'], 200);
    }
}