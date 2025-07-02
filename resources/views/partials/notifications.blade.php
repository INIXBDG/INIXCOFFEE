@foreach (auth()->user()->unreadNotifications as $notification)
    @if ($notification->data['message']['tipe'] == 'Izin 3 Jam')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['message']['status'] }}
                    {{ $notification->data['message']['nama_lengkap'] }}</strong> untuk izin
                {{ $notification->data['message']['durasi'] }} jam, Mulai Jam
                {{ \Carbon\Carbon::parse($notification->data['message']['jam_mulai'])->format('H:i') }} s/d Jam
                {{ \Carbon\Carbon::parse($notification->data['message']['jam_selesai'])->format('H:i') }}</p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            {{-- <p><strong>Status:</strong> {{ $notification->data['status'] }}</p> --}}
            <div class="d-flex">
                <a href="{{ $notification->data['path'] }}" class="btn btn-primary btn-sm" style="margin-right:8px;">Lihat
                    Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai
                        Dibaca</button>
                </form>
            </div>
        </div>
    @endif
    @if ($notification->data['message']['tipe'] == 'no_record')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['message']['status'] }}</strong> Atas
                Pengajuan Klaim Absen Tidak Terekam Oleh
                <strong>{{ $notification->data['message']['nama_lengkap'] }}</strong> Untuk Tanggal
                {{ \Carbon\Carbon::parse($notification->data['message']['tanggal'])->format('d-M-Y') }} Dengan Alasan
                {{ $notification->data['message']['kronologi'] }}
            </p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            {{-- <p><strong>Status:</strong> {{ $notification->data['status'] }}</p> --}}
            <div class="d-flex">
                <a href="{{ $notification->data['path'] }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai
                        Dibaca</button>
                </form>
            </div>
        </div>
    @endif
    @if ($notification->data['message']['tipe'] == 'scheme_work')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['message']['status'] }}</strong> Atas
                Pengajuan Klaim Terlambat Karena Perubahan Skema Kerja Oleh
                <strong>{{ $notification->data['message']['nama_lengkap'] }}</strong> Untuk Tanggal
                {{ \Carbon\Carbon::parse($notification->data['message']['tanggal'])->format('d-M-Y') }} Dengan Alasan
                {{ $notification->data['message']['kronologi'] }}
            </p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            {{-- <p><strong>Status:</strong> {{ $notification->data['status'] }}</p> --}}
            <div class="d-flex">
                <a href="{{ $notification->data['path'] }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai
                        Dibaca</button>
                </form>
            </div>
        </div>
    @endif
    @if ($notification->data['message']['tipe'] == 'cancel_leave')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['message']['status'] }}</strong> Atas
                Pengajuan Klaim Pembatalan Cuti Tpe {{ $notification->data['message']['jenis'] }} Oleh
                <strong>{{ $notification->data['message']['nama_lengkap'] }}</strong> Pada Tanggal
                {{ \Carbon\Carbon::parse($notification->data['message']['tanggal_awal'])->format('d-M-Y') }} s/d
                {{ \Carbon\Carbon::parse($notification->data['message']['tanggal_akhir'])->format('d-M-Y') }} Dengan
                Alasan {{ $notification->data['message']['kronologi'] }}
            </p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            {{-- <p><strong>Status:</strong> {{ $notification->data['status'] }}</p> --}}
            <div class="d-flex">
                <a href="{{ $notification->data['path'] }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai
                        Dibaca</button>
                </form>
            </div>
        </div>
    @endif
    @if ($notification->data['message']['tipe'] == 'komentar')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['user'] }}</strong> telah menambahkan
                {{ $notification->data['message']['tipe'] }} "{{ $notification->data['message']['content'] }}" di
                {{ $notification->data['message']['materi_key'] }}</p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            {{-- <p><strong>Status:</strong> {{ $notification->data['status'] }}</p> --}}
            <div class="d-flex">
                <a href="{{ $notification->data['path'] }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Komentar</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai
                        Dibaca</button>
                </form>
            </div>
        </div>
    @endif
    @if ($notification->data['message']['tipe'] == 'RKM Baru')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['user'] }}</strong> telah menambahkan
                {{ $notification->data['message']['tipe'] }} dengan judul
                "{{ $notification->data['message']['nama_materi'] }}" dengan peserta dari
                {{ $notification->data['message']['nama_perusahaan'] }}</p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            {{-- <p><strong>Status:</strong> {{ $notification->data['status'] }}</p> --}}
            <div class="d-flex">
                <a href="{{ $notification->data['path'] }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat RKM</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai
                        Dibaca</button>
                </form>
            </div>
        </div>
    @endif
    @if ($notification->data['message']['tipe'] == 'RKM Update')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['user'] }}</strong> telah mengubah
                {{ $notification->data['message']['tipe'] }} dengan judul
                "{{ $notification->data['message']['nama_materi'] }}" dengan peserta dari
                {{ $notification->data['message']['nama_perusahaan'] }}</p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            {{-- <p><strong>Status:</strong> {{ $notification->data['status'] }}</p> --}}
            <div class="d-flex">
                <a href="{{ $notification->data['path'] }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat RKM</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai
                        Dibaca</button>
                </form>
            </div>
        </div>
    @endif
    @if ($notification->data['message']['tipe'] == 'Assign Kelas')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['user'] }}</strong> telah menambahkan
                anda sebagai {{ $notification->data['message']['role'] }} di kelas
                "{{ $notification->data['message']['nama_materi'] }}" dengan peserta dari
                {{ $notification->data['message']['nama_perusahaan'] }}</p>
            <p>Di assign pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            <div class="d-flex">
                <a href="{{ $notification->data['path'] }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat RKM</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai
                        Dibaca</button>
                </form>
            </div>
        </div>
    @endif
    @if ($notification->data['message']['tipe'] == 'Mengajukan Exam')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['user'] }}</strong> telah
                {{ $notification->data['message']['tipe'] }} dengan judul
                "{{ $notification->data['message']['nama_materi'] }}" dengan peserta dari
                {{ $notification->data['message']['nama_perusahaan'] }}</p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            {{-- <p><strong>Status:</strong> {{ $notification->data['status'] }}</p> --}}
            <div class="d-flex">
                <a href="{{ $notification->data['path'] }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Exam</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai
                        Dibaca</button>
                </form>
            </div>
        </div>
    @endif
    @if ($notification->data['message']['tipe'] == 'Menyetujui Pengajuan Exam')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['user'] }}</strong> telah
                {{ $notification->data['message']['tipe'] }} yang diajukan pada tanggal
                {{ \Carbon\Carbon::parse($notification->data['message']['tanggal_pengajuan'])->format('d M Y') }}
                dengan judul "{{ $notification->data['message']['materi'] }}" dengan peserta dari
                {{ $notification->data['message']['perusahaan'] }}</p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            {{-- <p><strong>Status:</strong> {{ $notification->data['status'] }}</p> --}}
            <div class="d-flex">
                <a href="{{ $notification->data['path'] }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Exam</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai
                        Dibaca</button>
                </form>
            </div>
        </div>
    @endif
    @if ($notification->data['message']['tipe'] == 'Mengajukan Cuti')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['user'] }}</strong> telah
                {{ $notification->data['message']['tipe'] }}
                <strong>{{ $notification->data['message']['jenis_cuti'] }}</strong> dengan durasi
                {{ $notification->data['message']['durasi'] }} hari Pada Tanggal
                {{ \Carbon\Carbon::parse($notification->data['message']['tanggal_awal'])->format('d M Y') }} s/d
                {{ \Carbon\Carbon::parse($notification->data['message']['tanggal_akhir'])->format('d M Y') }}
            </p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            {{-- <p><strong>Status:</strong> {{ $notification->data['status'] }}</p> --}}
            <div class="d-flex">
                <a href="{{ $notification->data['path'] }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai
                        Dibaca</button>
                </form>
            </div>
        </div>
    @endif
    @if ($notification->data['message']['tipe'] == 'Meminta anda untuk menggantikan posisi nya dikarenakan')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['user'] }}</strong> telah
                {{ $notification->data['message']['tipe'] }}
                <strong>{{ $notification->data['message']['jenis_cuti'] }}</strong> dengan durasi
                {{ $notification->data['message']['durasi'] }} hari Pada Tanggal
                {{ \Carbon\Carbon::parse($notification->data['message']['tanggal_awal'])->format('d M Y') }} s/d
                {{ \Carbon\Carbon::parse($notification->data['message']['tanggal_akhir'])->format('d M Y') }}
            </p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            {{-- <p><strong>Status:</strong> {{ $notification->data['status'] }}</p> --}}
            <div class="d-flex">
                <a href="{{ $notification->data['path'] }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai
                        Dibaca</button>
                </form>
            </div>
        </div>
    @endif
    @if ($notification->data['message']['tipe'] == 'Menyetujui' || $notification->data['message']['tipe'] == 'Menolak')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['user'] }}</strong> telah
                {{ $notification->data['message']['tipe'] }} {{ $notification->data['message']['jenis_cuti'] }}
                {{ $notification->data['message']['nama_lengkap'] }} dengan durasi
                {{ $notification->data['message']['durasi'] }} hari Pada Tanggal
                {{ \Carbon\Carbon::parse($notification->data['message']['tanggal_awal'])->format('d M Y') }} s/d
                {{ \Carbon\Carbon::parse($notification->data['message']['tanggal_akhir'])->format('d M Y') }}</p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            {{-- <p><strong>Status:</strong> {{ $notification->data['status'] }}</p> --}}
            <div class="d-flex">
                <a href="{{ $notification->data['path'] }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai
                        Dibaca</button>
                </form>
            </div>
        </div>
    @endif
    @if ($notification->data['message']['tipe'] == 'Mengajukan Surat Perjalanan')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['user'] }}</strong> telah
                {{ $notification->data['message']['tipe'] }}
                <strong>{{ $notification->data['message']['alasan'] }}</strong> dengan durasi
                {{ $notification->data['message']['durasi'] }} hari Pada Tanggal
                {{ \Carbon\Carbon::parse($notification->data['message']['tanggal_berangkat'])->format('d M Y') }} s/d
                {{ \Carbon\Carbon::parse($notification->data['message']['tanggal_pulang'])->format('d M Y') }}
            </p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            {{-- <p><strong>Status:</strong> {{ $notification->data['status'] }}</p> --}}
            <div class="d-flex">
                <a href="{{ $notification->data['path'] }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai
                        Dibaca</button>
                </form>
            </div>
        </div>
    @endif
    @if (
        $notification->data['message']['tipe'] == 'Menyetujui SPJ' ||
            $notification->data['message']['tipe'] == 'Menolak SPJ')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['user'] }}</strong> telah
                {{ $notification->data['message']['tipe'] }} {{ $notification->data['message']['nama_lengkap'] }}
                dengan durasi {{ $notification->data['message']['durasi'] }} hari Pada Tanggal
                {{ \Carbon\Carbon::parse($notification->data['message']['tanggal_berangkat'])->format('d M Y') }} s/d
                {{ \Carbon\Carbon::parse($notification->data['message']['tanggal_pulang'])->format('d M Y') }}</p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            {{-- <p><strong>Status:</strong> {{ $notification->data['status'] }}</p> --}}
            <div class="d-flex">
                <a href="{{ $notification->data['path'] }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai
                        Dibaca</button>
                </form>
            </div>
        </div>
    @endif
    @if (
        $notification->data['message']['tipe'] == 'Outstanding' &&
            \Carbon\Carbon::parse($notification->data['message']['due_date'])->gte(\Carbon\Carbon::now()))
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">Perusahaan
                    {{ $notification->data['message']['nama_perusahaan'] }}</strong> dengan materi
                <strong>{{ $notification->data['message']['nama_materi'] }}</strong> belum menyelesaikan pembayaran
                sebesar <strong>{{ formatRupiah($notification->data['message']['net_sales']) }} SEGERA
                    DITAGIH</strong>. Batas waktu Penagihan Pada Tanggal
                <strong>{{ \Carbon\Carbon::parse($notification->data['message']['due_date'])->format('d M Y') }}</strong>
            </p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            <div class="d-flex">
                <a href="{{ $notification->data['path'] }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai
                        Dibaca</button>
                </form>
            </div>
        </div>
    @endif
    @if (
        $notification->data['message']['tipe'] == 'Bayar Exam' &&
            \Carbon\Carbon::parse($notification->data['message']['tanggal_pengajuan'])->addWeeks(2)->gte(\Carbon\Carbon::now()))
        <div class="notification mb-3">
            <p>Exam dengan materi<strong style="text-transform: capitalize;">
                    {{ $notification->data['message']['materi'] }}</strong> dan perusahaan
                <strong>{{ $notification->data['message']['perusahaan'] }}
                    {{ $notification->data['message']['pax'] }}</strong> pax Segera lakukan Checkout Pembayaran 2
                minggu dari tanggal pengajuan
                <strong>{{ \Carbon\Carbon::parse($notification->data['message']['tanggal_pengajuan'])->format('d M Y') }}</strong>
                dengan harga <strong>{{ $notification->data['message']['mata_uang'] }}
                    {{ $notification->data['message']['harga_dollar'] }}/pax</strong> atau
                <strong>{{ formatRupiah($notification->data['message']['harga_rupiah']) }}/pax</strong>
            </p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            <div class="d-flex">
                <a href="{{ $notification->data['path'] }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Sudah Melakukan
                        Checkout</button>
                </form>
            </div>
        </div>
    @endif
    @if ($notification->data['message']['tipe'] == 'Bayar CC')
        <div class="notification mb-3">
            <p>CC dengan atas nama <strong style="text-transform: capitalize;">
                    {{ $notification->data['message']['cc'] }}</strong> telah melakukan pembayaran untuk exam
                <strong>{{ $notification->data['message']['materi'] }}</strong> dari perusahaan
                <strong>{{ $notification->data['message']['perusahaan'] }}
                    {{ $notification->data['message']['pax'] }}</strong> pax Segera lakukan Pembayaran CC 2 minggu
                dari tanggal pengajuan
                <strong>{{ \Carbon\Carbon::parse($notification->data['message']['tanggal_pengajuan'])->format('d M Y') }}</strong>
                dengan harga <strong>{{ $notification->data['message']['mata_uang'] }}
                    {{ $notification->data['message']['harga_dollar'] }}</strong> atau
                <strong>{{ formatRupiah($notification->data['message']['harga_rupiah']) }}</strong>
            </p>
            {{-- <p><strong style="text-transform: capitalize;">Perusahaan {{ $notification->data['message']['nama_perusahaan'] }}</strong> dengan materi <strong>{{ $notification->data['message']['nama_materi'] }}</strong>  belum menyelesaikan pembayaran sebesar {{($notification->data['message']['net_sales'])}} <strong>SEGERA DITAGIH</strong>. Batas waktu Penagihan Pada Tanggal {{ \Carbon\Carbon::parse($notification->data['message']['due_date'])->format('d M Y') }}</p> --}}
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            {{-- <p><strong>Status:</strong> {{ $notification->data['status'] }}</p> --}}
            <div class="d-flex">
                <a href="{{ $notification->data['path'] }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Sudah Melakukan
                        Pembayaran</button>
                </form>
            </div>
        </div>
    @endif
    @if ($notification->data['message']['tipe'] == 'Mengajukan Permintaan Barang')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['user'] }}</strong> telah
                {{ $notification->data['message']['tipe'] }}
                <strong>{{ $notification->data['message']['tipe_barang'] }}</strong> Pada Tanggal
                {{ \Carbon\Carbon::parse($notification->data['message']['tanggal_pengajuan'])->format('d M Y') }}
            </p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            {{-- <p><strong>Status:</strong> {{ $notification->data['status'] }}</p> --}}
            <div class="d-flex">
                <a href="{{ $notification->data['path'] }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai
                        Dibaca</button>
                </form>
            </div>
        </div>
    @endif
    @if ($notification->data['message']['tipe'] == 'Menyetujui Pengajuan Barang')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['user'] }}</strong> telah
                {{ $notification->data['message']['tipe'] }} {{ $notification->data['message']['nama_lengkap'] }}
                dengan Status "{{ $notification->data['message']['status'] }}" Pada Tanggal
                {{ \Carbon\Carbon::parse($notification->data['message']['tanggal'])->format('d M Y') }}</p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            {{-- <p><strong>Status:</strong> {{ $notification->data['status'] }}</p> --}}
            <div class="d-flex">
                <a href="{{ $notification->data['path'] }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai
                        Dibaca</button>
                </form>
            </div>
        </div>
    @endif
    @if ($notification->data['message']['tipe'] == 'Menolak Pengajuan Barang')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['user'] }}</strong> telah
                {{ $notification->data['message']['tipe'] }} {{ $notification->data['message']['nama_lengkap'] }}
                dengan Alasan "{{ $notification->data['message']['status'] }}" Pada Tanggal
                {{ \Carbon\Carbon::parse($notification->data['message']['tanggal'])->format('d M Y') }}</p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            {{-- <p><strong>Status:</strong> {{ $notification->data['status'] }}</p> --}}
            <div class="d-flex">
                <a href="{{ $notification->data['path'] }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai
                        Dibaca</button>
                </form>
            </div>
        </div>
    @endif
    @if ($notification->data['message']['tipe'] == 'Segera Upload Bukti Pembelian/Invoice')
        <div class="notification mb-3">
            <p><strong>{{ $notification->data['message']['tipe'] }}
                    {{ $notification->data['message']['nama_lengkap'] }} dalam waktu 3 Hari dimulai tanggal
                    {{ \Carbon\Carbon::parse($notification->data['message']['tanggal'])->format('d M Y') }} sampai
                    dengan
                    {{ \Carbon\Carbon::parse($notification->data['message']['tanggal'])->addDays(3)->format('d M Y') }}</strong>
            </p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            {{-- <p><strong>Status:</strong> {{ $notification->data['status'] }}</p> --}}
            <div class="d-flex">
                <a href="{{ $notification->data['path'] }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai
                        Dibaca</button>
                </form>
            </div>
        </div>
    @endif
    @if ($notification->data['message']['tipe'] == 'Memerintahkan anda untuk Lembur')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['user'] }}</strong> telah
                {{ $notification->data['message']['tipe'] }} Pada <strong>Hari
                    {{ $notification->data['message']['waktu_lembur'] }}</strong> Tanggal
                {{ \Carbon\Carbon::parse($notification->data['message']['tanggal_lembur'])->format('d M Y') }} dengan
                tugas "{{ $notification->data['message']['uraian_tugas'] }}"</p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            {{-- <p><strong>Status:</strong> {{ $notification->data['status'] }}</p> --}}
            <div class="d-flex">
                <a href="{{ $notification->data['path'] }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai
                        Dibaca</button>
                </form>
            </div>
        </div>
    @endif
    @if ($notification->data['message']['tipe'] == 'Mengisi Jam dan Detail Tugas Lembur')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['user'] }}</strong> telah
                {{ $notification->data['message']['tipe'] }} Pada <strong>Hari
                    {{ $notification->data['message']['waktu_lembur'] }}</strong> Tanggal
                {{ \Carbon\Carbon::parse($notification->data['message']['tanggal_lembur'])->format('d M Y') }} dengan
                tugas "{{ $notification->data['message']['uraian_tugas'] }}"</p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            {{-- <p><strong>Status:</strong> {{ $notification->data['status'] }}</p> --}}
            <div class="d-flex">
                <a href="{{ $notification->data['path'] }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai
                        Dibaca</button>
                </form>
            </div>
        </div>
    @endif
    @if ($notification->data['message']['tipe'] == 'Telah Menyetujui Perintah Lembur')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['user'] }}</strong> telah
                {{ $notification->data['message']['tipe'] }} {{ $notification->data['message']['id_karyawan'] }}
                Pada <strong>Hari {{ $notification->data['message']['waktu_lembur'] }}</strong> Tanggal
                {{ \Carbon\Carbon::parse($notification->data['message']['tanggal_lembur'])->format('d M Y') }} Selamat
                Berlembur Ria!</p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            {{-- <p><strong>Status:</strong> {{ $notification->data['status'] }}</p> --}}
            <div class="d-flex">
                <a href="{{ $notification->data['path'] }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai
                        Dibaca</button>
                </form>
            </div>
        </div>
    @endif
    @if ($notification->data['message']['tipe'] == 'Menolak Hitungan Lembur')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['user'] }}</strong> telah
                {{ $notification->data['message']['tipe'] }} {{ $notification->data['message']['nama_karyawan'] }}
                dengan alasan {{ $notification->data['message']['alasan'] }}</p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            {{-- <p><strong>Status:</strong> {{ $notification->data['status'] }}</p> --}}
            <div class="d-flex">
                <a href="{{ $notification->data['path'] }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai
                        Dibaca</button>
                </form>
            </div>
        </div>
    @endif
    @if ($notification->data['message']['tipe'] == 'Persetujuan Payment Advanced')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['user'] }}</strong> telah
                {{ $notification->data['message']['status'] }} {{ $notification->data['message']['tipe'] }}
                {{ $notification->data['message']['nama_karyawan'] }} dengan alasan
                {{ $notification->data['message']['alasan'] }}</p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            {{-- <p><strong>Status:</strong> {{ $notification->data['status'] }}</p> --}}
            <div class="d-flex">
                <a href="{{ $notification->data['path'] }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai
                        Dibaca</button>
                </form>
            </div>
        </div>
    @endif
    @if (isset($notification->data['message']['tipe']) &&
            $notification->data['message']['tipe'] == 'Pesan Contact Us Website INIXINDO')
        <div class="notification mb-3 p-3 border rounded shadow-sm" style="background-color: #f8f9fa;">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="mb-1 text-primary" style="font-weight: 600;">
                        {{ $notification->data['message']['tipe'] ?? 'Notifikasi Kontak' }}
                    </h6>
                    <p class="mb-1">
                        <strong>Dari:</strong> {{ $notification->data['message']['name'] ?? 'Unknown' }}
                        ({{ $notification->data['message']['instansi'] ?? 'Tidak diketahui' }})
                    </p>
                    <p class="mb-1" style="word-break: break-word; white-space: normal;">
                        <strong>Pesan:</strong> {{ $notification->data['message']['pesan'] ?? 'Tidak ada pesan' }}
                    </p>
                    <p class="text-muted mb-0" style="font-size: 0.9rem;">
                        Pada {{ $notification->created_at->format('d M Y H:i:s') }}
                    </p>
                </div>
                <div class="d-flex">
                    <a href="https://inixindobdg.co.id/login" class="btn btn-primary btn-sm me-2"
                        style="font-size: 0.85rem;">Lihat Selengkapnya</a>
                    <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                        class="d-inline">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn btn-danger btn-sm" style="font-size: 0.85rem;">Tandai
                            sebagai Dibaca</button>
                    </form>
                </div>
            </div>
        </div>
    @endif
    <hr>
@endforeach
