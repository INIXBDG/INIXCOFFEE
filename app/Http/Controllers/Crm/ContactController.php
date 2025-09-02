<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Aktivitas;
use App\Models\Contact;
use App\Models\Materi;
use App\Models\Peluang;
use App\Models\Perusahaan;
use App\Models\RKM;
use App\Models\User;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{

    // public function index()
    // {
    //     $user = Auth::user();
    //     $allowedJabatan = ['Adm Sales', 'HRD', 'Finance & Accounting', 'GM', 'SPV Sales'];

    //     if ($user->jabatan === 'Sales') {
    //         $idSales = $user->id_sales;
    //         $data = Perusahaan::where('sales_key', $idSales)->get();
    //         $perusahaan = Perusahaan::where('sales_key', $user->id_sales)->get();
    //     } elseif (in_array($user->jabatan, $allowedJabatan)) {
    //         $data = Perusahaan::all();
    //         $perusahaan = Perusahaan::all();
    //     } else {
    //         abort(403, 'Anda tidak memiliki akses ke halaman ini.');
    //     }

    //     $kelasTerakhir = [];
    //     $aktivitasTerakhir = [];

    //     foreach ($data as $item) {
    //         $kelasTerakhir[$item->id] = RKM::where('perusahaan_key', $item->id)
    //             ->latest()
    //             ->with('materi')
    //             ->first();

    //         $aktivitasTerakhir[$item->id] = Aktivitas::where('id_contact', $item->id)
    //             ->latest()
    //             ->first();
    //     }

    //     return view('crm.contact.index', compact('data', 'perusahaan', 'kelasTerakhir', 'aktivitasTerakhir'));
    // }

    public function index()
    {
        return view('crm.contact.index');
    }

    public function getPerusahaan(Request $request)
    {
        $user = Auth::user();
        $allowedJabatan = ['Adm Sales', 'HRD', 'Finance & Accounting', 'GM', 'SPV Sales'];

        if ($user->jabatan === 'Sales') {
            $idSales = $user->id_sales;
            $query = Perusahaan::where('sales_key', $idSales);
        } elseif (in_array($user->jabatan, $allowedJabatan)) {
            $query = Perusahaan::query();
        } else {
            return response()->json(['error' => 'Anda tidak memiliki akses ke data ini.'], 403);
        }

        // Ambil parameter filter dari request
        $nama = $request->input('nama_perusahaan');
        $lokasi = $request->input('lokasi');
        $status = $request->input('status');
        $sales = $request->input('sales_key');

        if ($nama) {
            $query->where('nama_perusahaan', 'like', '%' . $nama . '%');
        }
        if ($lokasi) {
            $query->where('lokasi', $lokasi);
        }
        if ($status) {
            $query->where('status', $status);
        }
        if ($sales) {
            $query->where('sales_key', $sales);
        }

        $data = $query->get();

        $kelasTerakhir = [];
        $aktivitasTerakhir = [];

        foreach ($data as $item) {
            $kelasTerakhir[$item->id] = RKM::where('perusahaan_key', $item->id)
                ->latest()
                ->with('materi')
                ->first();

            $contactIds = $item->contacts->pluck('id');

            $aktivitasTerakhir[$item->id] = Aktivitas::whereIn('id_contact',$contactIds)
                ->latest()
                ->first();
        }

        $response = $data->map(function ($contact) use ($kelasTerakhir, $aktivitasTerakhir) {
            return [
                'id' => $contact->id,
                'nama_perusahaan' => $contact->nama_perusahaan,
                'npwp' => $contact->npwp,
                'alamat' => $contact->alamat,
                'kategori_perusahaan' => $contact->kategori_perusahaan,
                'lokasi' => $contact->lokasi ?? '-',
                'cp' => $contact->cp ?? '-',
                'no_telp' => $contact->no_telp ?? '-',
                'email' => $contact->email ?? '-',
                'status' => $contact->status ?? '-',
                'sales_key' => $contact->sales_key,
                'kelas_terakhir' => isset($kelasTerakhir[$contact->id]) ? $kelasTerakhir[$contact->id]->materi->nama_materi ?? '-' : 'Belum ada kelas',
                'kelas_terakhir_date' => isset($kelasTerakhir[$contact->id]) ? $kelasTerakhir[$contact->id]->created_at->translatedFormat('d F Y') : null,
                'aktivitas_terakhir_date' => isset($aktivitasTerakhir[$contact->id]) ? $aktivitasTerakhir[$contact->id]->created_at->format('d-m-Y') : 'Belum ada aktivitas',
            ];
        });

        return response()->json($response);
    }

    public function detail($id)
    {
        $data = Perusahaan::with(['contacts', 'peserta'])->where('id', $id)->firstOrFail();

        $items = [];

        // Tambahkan semua contacts
        foreach ($data->contacts as $contact) {
            $items[] = [
                'id' => $contact->id,
                'nama' => $contact->nama,
                'type' => 'contact',
                'label' => "[Contact] " . $contact->nama . " (" . ($contact->email ?? 'Tidak ada email') . ")"
            ];
        }

        // Tambahkan semua peserta
        foreach ($data->peserta as $peserta) {
            $items[] = [
                'id' => $peserta->id,
                'nama' => $peserta->nama,
                'type' => 'peserta',
                'label' => "[Peserta] " . $peserta->nama. " (" . ($peserta->email ?? 'Tidak ada email') . ")"
            ];
        }

        // Urutkan berdasarkan nama
        usort($items, function ($a, $b) {
            return strcasecmp($a['label'], $b['label']);
        });

        // Ambil data lainnya (jika diperlukan)
        $aktivitass = Aktivitas::with(['contact', 'peserta'])
            ->where(function ($query) use ($data) {
                $query->whereIn('id_contact', $data->contacts->pluck('id'))
                    ->orWhereIn('id_peserta', $data->peserta->pluck('id'));
            })
            ->orderByDesc('created_at')
            ->get();

        $aktivitas = Aktivitas::with(['contact', 'peserta'])
            ->where(function ($query) use ($data) {
                $query->whereIn('id_contact', $data->contacts->pluck('id'))
                    ->orWhereIn('id_peserta', $data->peserta->pluck('id'));
            })
            ->whereNull('id_peluang')
            ->orderByDesc('created_at')
            ->get();


        $peluang = Peluang::where('id_contact', $data->id)
            ->with('materiRelation')
            ->get();

        $materi = Materi::all();


        return view('crm.contact.detail', compact('data', 'items', 'aktivitas','aktivitass', 'peluang', 'materi'));
    }

    public function store(Request $request)
    {
        // Validasi input sesuai field perusahaan
        $validated = $request->validate([
            'nama_perusahaan'      => 'required|string|max:255',
            'kategori_perusahaan'  => 'nullable|string|max:255',
            'lokasi'               => 'nullable|string|max:255',
            // 'sales_key'            => 'nullable|string|max:255',
            'status'               => 'nullable|string|max:255',
            'npwp'                 => 'nullable|string|max:255',
            'alamat'               => 'nullable|string|max:1000',
            'cp'                   => 'nullable|string|max:20',
            'no_telp'              => 'nullable|string|max:20',
            'email'                => 'nullable|email|max:255',
            'foto_npwp'            => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:5120',
        ]);

        // Handle upload foto_npwp, jika ada
        if ($request->hasFile('foto_npwp')) {
            $file = $request->file('foto_npwp');
            $extension = $file->getClientOriginalExtension();
            $filename = $validated['nama_perusahaan'] . '_npwp.' . $extension;
            $file->storeAs('public/npwp', $filename);
            $validated['foto_npwp'] = $filename;
        }

        // Menambahkan id_sales dari input manual atau default dari user login
        $id_sales = $request->input('id_sales', auth()->user()->id_sales ?? null);

        $validated['sales_key'] = $id_sales;

        // Simpan data perusahaan
        $perusahaan = Perusahaan::create($validated + ['sales_key' => $id_sales]);

        return back()->with([
            'message' => 'Data perusahaan berhasil disimpan.',
            'data' => $perusahaan,
        ]);
    }

    public function delete($id)
    {
        $contact = Contact::where('id', $id)->first();
        $contact->delete();

        return back()->with([
            'message' => 'Kontak berhasil dihapus.',
        ]);
    }

    public function update($id, Request $request)
    {
        // Validasi input request
        $validated = $request->validate([
            'nama_perusahaan' => 'required|string|max:255',
            'kategori_perusahaan' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'lokasi' => 'nullable|string|max:255',
            'status' => 'required|string|max:50',
            'npwp' => 'nullable|string|max:50',
            'alamat' => 'nullable|string|max:500',
            'no_telp' => 'nullable|string|max:20',
            'cp' => 'nullable|string|max:100',
            'foto_npwp' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $contact = Perusahaan::findOrFail($id);

        // Update atribut dari data yang sudah tervalidasi
        $contact->nama_perusahaan = $validated['nama_perusahaan'];
        $contact->kategori_perusahaan = $validated['kategori_perusahaan'];
        $contact->email = $validated['email'];
        $contact->lokasi = $validated['lokasi'] ?? $contact->lokasi;
        $contact->status = $validated['status'];
        $contact->npwp = $validated['npwp'] ?? $contact->npwp;
        $contact->alamat = $validated['alamat'] ?? $contact->alamat;
        $contact->no_telp = $validated['no_telp'] ?? $contact->no_telp;
        $contact->cp = $validated['cp'] ?? $contact->cp;

        // Upload file jika ada unggahan baru
        if ($request->hasFile('foto_npwp')) {
            $file = $request->file('foto_npwp');
            $extension = $file->getClientOriginalExtension();
            $filename = $validated['nama_perusahaan'] . '_npwp.' . $extension;
            $file->storeAs('public/npwp', $filename);

            // Simpan path file ke kolom foto_npwp
            $contact->foto_npwp = 'npwp/' . $filename;
        }

        // Simpan perubahan ke database
        $contact->save();

        return back()->with([
            'message' => 'Kontak berhasil diperbarui.',
        ]);
    }
}
