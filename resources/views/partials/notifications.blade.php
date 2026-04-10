@foreach (auth()->user()->unreadNotifications as $notification)
    @php
        // Menggunakan null coalescing untuk mencegah "Undefined array key"
        $tipePesan = $notification->data['message']['tipe'] ?? '';
    @endphp

    @if ($tipePesan == 'Izin 3 Jam')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['message']['status'] ?? '-' }}
                    {{ $notification->data['message']['nama_lengkap'] ?? '-' }}</strong> untuk izin
                {{ $notification->data['message']['durasi'] ?? '-' }} jam, Mulai Jam
                {{ \Carbon\Carbon::parse($notification->data['message']['jam_mulai'] ?? now())->format('H:i') }} s/d Jam
                {{ \Carbon\Carbon::parse($notification->data['message']['jam_selesai'] ?? now())->format('H:i') }}</p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            <div class="d-flex">
                <a href="{{ $notification->data['path'] ?? '#' }}" class="btn btn-primary btn-sm" style="margin-right:8px;">Lihat Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai Dibaca</button>
                </form>
            </div>
        </div>
    @endif

    @if ($tipePesan == 'survey_reminder')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['message']['judul'] ?? '-' }}</strong>
                <br>
                {{ $notification->data['message']['deskripsi'] ?? '-' }}
            </p>
            <div class="d-flex">
                <a href="{{ $notification->data['path'] ?? '#' }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai Dibaca</button>
                </form>
            </div>
        </div>
    @endif

    @if ($tipePesan == 'Update Catering')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">Update Pengajuan Catering</strong>
                <br>
                @php
                    $pesan = $notification->data['message']['pesan'] ?? '';
                    $lines = explode("\n", $pesan);
                    $header = array_shift($lines);
                @endphp
                {{ $header }}
                @if (!empty($lines))
                    <ul class="mt-2 mb-0" style="padding-left: 20px; margin-bottom: 0;">
                        @foreach ($lines as $line)
                            @if (trim($line))
                                <li>{{ trim(str_replace('• ', '', $line)) }}</li>
                            @endif
                        @endforeach
                    </ul>
                @endif
            </p>
            <br>
            <div class="d-flex">
                <a href="{{ $notification->data['path'] ?? '#' }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai Dibaca</button>
                </form>
            </div>
        </div>
    @endif

    @if ($tipePesan == 'Pengajuan catering')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">Pengajuan Catering</strong>
                <br>
                <strong>{{ $notification->data['message']['nama_lengkap'] ?? '-' }}</strong> telah mengajukan catering pada
                tanggal {{ $notification->data['message']['tanggal_pengajuan'] ?? '-' }}
            </p>
            <div class="d-flex">
                <a href="{{ $notification->data['path'] ?? '#' }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai Dibaca</button>
                </form>
            </div>
        </div>
    @endif

    @if ($tipePesan == 'no_record')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['message']['status'] ?? '-' }}</strong> Atas
                Pengajuan Klaim Absen Tidak Terekam Oleh
                <strong>{{ $notification->data['message']['nama_lengkap'] ?? '-' }}</strong> Untuk Tanggal
                {{ \Carbon\Carbon::parse($notification->data['message']['tanggal'] ?? now())->format('d-M-Y') }} Dengan Alasan
                {{ $notification->data['message']['kronologi'] ?? '-' }}
            </p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            <div class="d-flex">
                <a href="{{ $notification->data['path'] ?? '#' }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai Dibaca</button>
                </form>
            </div>
        </div>
    @endif

    @if ($tipePesan == 'scheme_work')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['message']['status'] ?? '-' }}</strong> Atas
                Pengajuan Klaim Terlambat Karena Perubahan Skema Kerja Oleh
                <strong>{{ $notification->data['message']['nama_lengkap'] ?? '-' }}</strong> Untuk Tanggal
                {{ \Carbon\Carbon::parse($notification->data['message']['tanggal'] ?? now())->format('d-M-Y') }} Dengan Alasan
                {{ $notification->data['message']['kronologi'] ?? '-' }}
            </p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            <div class="d-flex">
                <a href="{{ $notification->data['path'] ?? '#' }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai Dibaca</button>
                </form>
            </div>
        </div>
    @endif

    @if ($tipePesan == 'cancel_leave')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['message']['status'] ?? '-' }}</strong> Atas
                Pengajuan Klaim Pembatalan Cuti Tpe {{ $notification->data['message']['jenis'] ?? '-' }} Oleh
                <strong>{{ $notification->data['message']['nama_lengkap'] ?? '-' }}</strong> Pada Tanggal
                {{ \Carbon\Carbon::parse($notification->data['message']['tanggal_awal'] ?? now())->format('d-M-Y') }} s/d
                {{ \Carbon\Carbon::parse($notification->data['message']['tanggal_akhir'] ?? now())->format('d-M-Y') }} Dengan
                Alasan {{ $notification->data['message']['kronologi'] ?? '-' }}
            </p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            <div class="d-flex">
                <a href="{{ $notification->data['path'] ?? '#' }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai Dibaca</button>
                </form>
            </div>
        </div>
    @endif

    @if ($tipePesan == 'komentar')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['user'] ?? '-' }}</strong> telah menambahkan
                {{ $notification->data['message']['tipe'] ?? '-' }} "{{ $notification->data['message']['content'] ?? '-' }}" di
                {{ $notification->data['message']['materi_key'] ?? '-' }}</p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            <div class="d-flex">
                <a href="{{ $notification->data['path'] ?? '#' }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Komentar</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai Dibaca</button>
                </form>
            </div>
        </div>
    @endif

    @if ($tipePesan == 'Penilaian 360')
        <div class="notification mb-3">
            <p>Mohon untuk <strong style="text-transform: capitalize;">{{ $notification->data['message']['content'] ?? '-' }}</strong>. terima kasih</p>
            <p>Dibuat Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            <div class="d-flex">
                <a href="{{ $notification->data['path'] ?? '#' }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Nilai Sekarang</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai Dibaca</button>
                </form>
            </div>
        </div>
    @endif

    @if ($tipePesan == 'RKM Baru')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['user'] ?? '-' }}</strong> telah
                menambahkan
                {{ $notification->data['message']['tipe'] ?? '-' }} dengan judul
                "{{ $notification->data['message']['nama_materi'] ?? '-' }}" dengan peserta dari
                {{ $notification->data['message']['nama_perusahaan'] ?? '-' }}</p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            <div class="d-flex">
                <a href="{{ $notification->data['path'] ?? '#' }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat RKM</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai Dibaca</button>
                </form>
            </div>
        </div>
    @endif

    @if ($tipePesan == 'RKM Update')
        <div class="notification mb-3">
            <p>
                <strong style="text-transform: capitalize;">{{ $notification->data['user'] ?? '-' }}</strong>
                telah mengubah RKM dengan judul
                "{{ $notification->data['message']['nama_materi'] ?? '-' }}" dengan peserta dari
                {{ $notification->data['message']['nama_perusahaan'] ?? '-' }}
            </p>
            @if (!empty($notification->data['message']['detail']))
                <p><strong>Perubahan:</strong> {{ $notification->data['message']['detail'] }}</p>
            @endif
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            <div class="d-flex">
                <a href="{{ $notification->data['path'] ?? '#' }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat RKM</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai Dibaca</button>
                </form>
            </div>
        </div>
    @endif

    @if ($tipePesan == 'Assign Kelas')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['user'] ?? '-' }}</strong> telah menambahkan anda sebagai {{ $notification->data['message']['role'] ?? '-' }} di kelas "{{ $notification->data['message']['nama_materi'] ?? '-' }}" dengan peserta dari {{ $notification->data['message']['nama_perusahaan'] ?? '-' }}</p>
            <p>Di assign pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            <div class="d-flex">
                <a href="{{ $notification->data['path'] ?? '#' }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat RKM</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai Dibaca</button>
                </form>
            </div>
        </div>
    @endif

    @if ($tipePesan == 'Mengajukan Exam')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['user'] ?? '-' }}</strong> telah
                {{ $notification->data['message']['tipe'] ?? '-' }} dengan judul
                "{{ $notification->data['message']['nama_materi'] ?? '-' }}" dengan peserta dari
                {{ $notification->data['message']['nama_perusahaan'] ?? '-' }}</p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            <div class="d-flex">
                <a href="{{ $notification->data['path'] ?? '#' }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Exam</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai Dibaca</button>
                </form>
            </div>
        </div>
    @endif

    @if ($tipePesan == 'Menyetujui Pengajuan Exam')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['user'] ?? '-' }}</strong> telah
                {{ $notification->data['message']['tipe'] ?? '-' }} yang diajukan pada tanggal
                {{ \Carbon\Carbon::parse($notification->data['message']['tanggal_pengajuan'] ?? now())->format('d M Y') }}
                dengan judul "{{ $notification->data['message']['materi'] ?? '-' }}" dengan peserta dari
                {{ $notification->data['message']['perusahaan'] ?? '-' }}</p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            <div class="d-flex">
                <a href="{{ $notification->data['path'] ?? '#' }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Exam</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai Dibaca</button>
                </form>
            </div>
        </div>
    @endif

    @if ($tipePesan == 'Mengajukan Cuti')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['user'] ?? '-' }}</strong> telah
                {{ $notification->data['message']['tipe'] ?? '-' }}
                <strong>{{ $notification->data['message']['jenis_cuti'] ?? '-' }}</strong> dengan durasi
                {{ $notification->data['message']['durasi'] ?? '-' }} hari Pada Tanggal
                {{ \Carbon\Carbon::parse($notification->data['message']['tanggal_awal'] ?? now())->format('d M Y') }} s/d
                {{ \Carbon\Carbon::parse($notification->data['message']['tanggal_akhir'] ?? now())->format('d M Y') }}
            </p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            <div class="d-flex">
                <a href="{{ $notification->data['path'] ?? '#' }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai Dibaca</button>
                </form>
            </div>
        </div>
    @endif

    @if ($tipePesan == 'Meminta anda untuk menggantikan posisi nya dikarenakan')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['user'] ?? '-' }}</strong> telah
                {{ $notification->data['message']['tipe'] ?? '-' }}
                <strong>{{ $notification->data['message']['jenis_cuti'] ?? '-' }}</strong> dengan durasi
                {{ $notification->data['message']['durasi'] ?? '-' }} hari Pada Tanggal
                {{ \Carbon\Carbon::parse($notification->data['message']['tanggal_awal'] ?? now())->format('d M Y') }} s/d
                {{ \Carbon\Carbon::parse($notification->data['message']['tanggal_akhir'] ?? now())->format('d M Y') }}
            </p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            <div class="d-flex">
                <a href="{{ $notification->data['path'] ?? '#' }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai Dibaca</button>
                </form>
            </div>
        </div>
    @endif

    @if (in_array($tipePesan, ['Menyetujui', 'Menolak', 'Menyetujui Cuti', 'Menolak Cuti']))
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['user'] ?? '-' }}</strong> telah
                {{ $notification->data['message']['tipe'] ?? '-' }} {{ $notification->data['message']['jenis_cuti'] ?? '-' }}
                {{ $notification->data['message']['nama_lengkap'] ?? '-' }} dengan durasi
                {{ $notification->data['message']['durasi'] ?? '-' }} hari Pada Tanggal
                {{ \Carbon\Carbon::parse($notification->data['message']['tanggal_awal'] ?? now())->format('d M Y') }} s/d
                {{ \Carbon\Carbon::parse($notification->data['message']['tanggal_akhir'] ?? now())->format('d M Y') }}</p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            <div class="d-flex">
                <a href="{{ $notification->data['path'] ?? '#' }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai Dibaca</button>
                </form>
            </div>
        </div>
    @endif

    @if ($tipePesan == 'Mengajukan Surat Perjalanan')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['user'] ?? '-' }}</strong> telah
                {{ $notification->data['message']['tipe'] ?? '-' }}
                <strong>{{ $notification->data['message']['alasan'] ?? '-' }}</strong> dengan durasi
                {{ $notification->data['message']['durasi'] ?? '-' }} hari Pada Tanggal
                {{ \Carbon\Carbon::parse($notification->data['message']['tanggal_berangkat'] ?? now())->format('d M Y') }} s/d
                {{ \Carbon\Carbon::parse($notification->data['message']['tanggal_pulang'] ?? now())->format('d M Y') }}
            </p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            <div class="d-flex">
                <a href="{{ $notification->data['path'] ?? '#' }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai Dibaca</button>
                </form>
            </div>
        </div>
    @endif

    @if (in_array($tipePesan, ['Menyetujui SPJ', 'Menolak SPJ']))
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['user'] ?? '-' }}</strong> telah
                {{ $notification->data['message']['tipe'] ?? '-' }} {{ $notification->data['message']['nama_lengkap'] ?? '-' }}
                dengan durasi {{ $notification->data['message']['durasi'] ?? '-' }} hari Pada Tanggal
                {{ \Carbon\Carbon::parse($notification->data['message']['tanggal_berangkat'] ?? now())->format('d M Y') }} s/d
                {{ \Carbon\Carbon::parse($notification->data['message']['tanggal_pulang'] ?? now())->format('d M Y') }}</p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            <div class="d-flex">
                <a href="{{ $notification->data['path'] ?? '#' }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai Dibaca</button>
                </form>
            </div>
        </div>
    @endif

    @if ($tipePesan == 'Outstanding' && \Carbon\Carbon::parse($notification->data['message']['due_date'] ?? now())->gte(\Carbon\Carbon::now()))
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">Perusahaan
                    {{ $notification->data['message']['nama_perusahaan'] ?? '-' }}</strong> dengan materi
                <strong>{{ $notification->data['message']['nama_materi'] ?? '-' }}</strong> belum menyelesaikan pembayaran
                sebesar <strong>{{ formatRupiah($notification->data['message']['net_sales'] ?? 0) }} SEGERA
                    DITAGIH</strong>. Batas waktu Penagihan Pada Tanggal
                <strong>{{ \Carbon\Carbon::parse($notification->data['message']['due_date'] ?? now())->format('d M Y') }}</strong>
            </p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            <div class="d-flex">
                <a href="{{ $notification->data['path'] ?? '#' }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai Dibaca</button>
                </form>
            </div>
        </div>
    @endif

    @if ($tipePesan == 'Bayar Exam' && \Carbon\Carbon::parse($notification->data['message']['tanggal_pengajuan'] ?? now())->addWeeks(2)->gte(\Carbon\Carbon::now()))
        <div class="notification mb-3">
            <p>Exam dengan materi<strong style="text-transform: capitalize;">
                    {{ $notification->data['message']['materi'] ?? '-' }}</strong> dan perusahaan
                <strong>{{ $notification->data['message']['perusahaan'] ?? '-' }}
                    {{ $notification->data['message']['pax'] ?? '-' }}</strong> pax Segera lakukan Checkout Pembayaran 2
                minggu dari tanggal pengajuan
                <strong>{{ \Carbon\Carbon::parse($notification->data['message']['tanggal_pengajuan'] ?? now())->format('d M Y') }}</strong>
                dengan harga <strong>{{ $notification->data['message']['mata_uang'] ?? '-' }}
                    {{ $notification->data['message']['harga_dollar'] ?? '-' }}/pax</strong> atau
                <strong>{{ formatRupiah($notification->data['message']['harga_rupiah'] ?? 0) }}/pax</strong>
            </p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            <div class="d-flex">
                <a href="{{ $notification->data['path'] ?? '#' }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Sudah Melakukan Checkout</button>
                </form>
            </div>
        </div>
    @endif

    @if ($tipePesan == 'Bayar CC')
        <div class="notification mb-3">
            <p>CC dengan atas nama <strong style="text-transform: capitalize;">
                    {{ $notification->data['message']['cc'] ?? '-' }}</strong> telah melakukan pembayaran untuk exam
                <strong>{{ $notification->data['message']['materi'] ?? '-' }}</strong> dari perusahaan
                <strong>{{ $notification->data['message']['perusahaan'] ?? '-' }}
                    {{ $notification->data['message']['pax'] ?? '-' }}</strong> pax Segera lakukan Pembayaran CC 2 minggu
                dari tanggal pengajuan
                <strong>{{ \Carbon\Carbon::parse($notification->data['message']['tanggal_pengajuan'] ?? now())->format('d M Y') }}</strong>
                dengan harga <strong>{{ $notification->data['message']['mata_uang'] ?? '-' }}
                    {{ $notification->data['message']['harga_dollar'] ?? '-' }}</strong> atau
                <strong>{{ formatRupiah($notification->data['message']['harga_rupiah'] ?? 0) }}</strong>
            </p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            <div class="d-flex">
                <a href="{{ $notification->data['path'] ?? '#' }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Sudah Melakukan Pembayaran</button>
                </form>
            </div>
        </div>
    @endif

    @if ($tipePesan == 'Mengajukan Permintaan Barang')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['user'] ?? '-' }}</strong> telah
                {{ $notification->data['message']['tipe'] ?? '-' }}
                <strong>{{ $notification->data['message']['tipe_barang'] ?? '-' }}</strong> Pada Tanggal
                {{ \Carbon\Carbon::parse($notification->data['message']['tanggal_pengajuan'] ?? now())->format('d M Y') }}
            </p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            <div class="d-flex">
                <a href="{{ $notification->data['path'] ?? '#' }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai Dibaca</button>
                </form>
            </div>
        </div>
    @endif

    @if (in_array($tipePesan, ['Koordinasi Driver', 'Update Koordinasi Driver', 'Mengajukan Koordinasi Driver']))
        <div class="notification mb-4 p-3 border rounded shadow-sm bg-light">
            <h6 class="mb-2 fw-bold text-primary">
                {{ $notification->data['message']['tipe'] ?? '-' }}
            </h6>
            <div class="small text-muted">
                <div><strong>Dari:</strong> {{ $notification->data['user'] ?? '-' }}</div>
                <div><strong>Tipe:</strong>
                    {{ $notification->data['message']['tipe_koordinasi'] ?? 'Koordinasi Driver' }}</div>
                <div><strong>Tanggal:</strong>
                    {{ \Carbon\Carbon::parse($notification->data['message']['tanggal_pembuatan'] ?? $notification->created_at)->format('d/m/Y') }}
                </div>
            </div>
            <div class="mt-3 d-flex gap-2">
                <a href="{{ $notification->data['path'] ?? '#' }}" class="btn btn-sm btn-primary">Lihat Detail</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-sm btn-outline-danger">Tandai Dibaca</button>
                </form>
            </div>
            <small class="text-muted d-block mt-2">
                {{ $notification->created_at->diffForHumans() }}
            </small>
        </div>
    @endif

    @if ($tipePesan == 'Approved Expense Hub ')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['user'] ?? '-' }}</strong> telah
                {{ $notification->data['message']['tipe'] ?? '-' }}
                <strong>{{ $notification->data['message']['tipe_barang'] ?? '-' }}</strong> Pada Tanggal
                {{ \Carbon\Carbon::parse($notification->data['message']['tanggal_pengajuan'] ?? now())->format('d M Y') }}
            </p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            <div class="d-flex">
                <a href="{{ $notification->data['path'] ?? '#' }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai Dibaca</button>
                </form>
            </div>
        </div>
    @endif

    @if ($tipePesan == 'Pengajuan ExpenseHub')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['user'] ?? '-' }}</strong> telah
                Mengajukan <strong>{{ $notification->data['message']['tipe_barang'] ?? '-' }}</strong> Pada Tanggal
                {{ \Carbon\Carbon::parse($notification->data['message']['tanggal_pengajuan'] ?? now())->format('d M Y') }}
            </p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            <div class="d-flex">
                <a href="{{ $notification->data['path'] ?? '#' }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai Dibaca</button>
                </form>
            </div>
        </div>
    @endif

    @if ($tipePesan == 'Menolak Pengajuan Barang')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['user'] ?? '-' }}</strong> telah
                {{ $notification->data['message']['tipe'] ?? '-' }} {{ $notification->data['message']['nama_lengkap'] ?? '-' }}
                dengan Alasan "{{ $notification->data['message']['status'] ?? '-' }}" Pada Tanggal
                {{ \Carbon\Carbon::parse($notification->data['message']['tanggal'] ?? now())->format('d M Y') }}</p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            <div class="d-flex">
                <a href="{{ $notification->data['path'] ?? '#' }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai Dibaca</button>
                </form>
            </div>
        </div>
    @endif

    @if ($tipePesan == 'Segera Upload Bukti Pembelian/Invoice')
        <div class="notification mb-3">
            <p><strong>{{ $notification->data['message']['tipe'] ?? '-' }}
                    {{ $notification->data['message']['nama_lengkap'] ?? '-' }} dalam waktu 3 Hari dimulai tanggal
                    {{ \Carbon\Carbon::parse($notification->data['message']['tanggal'] ?? now())->format('d M Y') }} sampai
                    dengan
                    {{ \Carbon\Carbon::parse($notification->data['message']['tanggal'] ?? now())->addDays(3)->format('d M Y') }}</strong>
            </p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            <div class="d-flex">
                <a href="{{ $notification->data['path'] ?? '#' }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai Dibaca</button>
                </form>
            </div>
        </div>
    @endif

    @if ($tipePesan == 'Memerintahkan anda untuk Lembur')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['user'] ?? '-' }}</strong> telah
                {{ $notification->data['message']['tipe'] ?? '-' }} Pada <strong>Hari
                    {{ $notification->data['message']['waktu_lembur'] ?? '-' }}</strong> Tanggal
                {{ \Carbon\Carbon::parse($notification->data['message']['tanggal_lembur'] ?? now())->format('d M Y') }} dengan
                tugas "{{ $notification->data['message']['uraian_tugas'] ?? '-' }}"</p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            <div class="d-flex">
                <a href="{{ $notification->data['path'] ?? '#' }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai Dibaca</button>
                </form>
            </div>
        </div>
    @endif

    @if ($tipePesan == 'Mengisi Jam dan Detail Tugas Lembur')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['user'] ?? '-' }}</strong> telah
                {{ $notification->data['message']['tipe'] ?? '-' }} Pada <strong>Hari
                    {{ $notification->data['message']['waktu_lembur'] ?? '-' }}</strong> Tanggal
                {{ \Carbon\Carbon::parse($notification->data['message']['tanggal_lembur'] ?? now())->format('d M Y') }} dengan
                tugas "{{ $notification->data['message']['uraian_tugas'] ?? '-' }}"</p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            <div class="d-flex">
                <a href="{{ $notification->data['path'] ?? '#' }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai Dibaca</button>
                </form>
            </div>
        </div>
    @endif

    @if ($tipePesan == 'Telah Menyetujui Perintah Lembur')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['user'] ?? '-' }}</strong> telah
                {{ $notification->data['message']['tipe'] ?? '-' }} {{ $notification->data['message']['id_karyawan'] ?? '-' }}
                Pada <strong>Hari {{ $notification->data['message']['waktu_lembur'] ?? '-' }}</strong> Tanggal
                {{ \Carbon\Carbon::parse($notification->data['message']['tanggal_lembur'] ?? now())->format('d M Y') }} Selamat
                Berlembur Ria!</p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            <div class="d-flex">
                <a href="{{ $notification->data['path'] ?? '#' }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai Dibaca</button>
                </form>
            </div>
        </div>
    @endif

    @if ($tipePesan == 'Menolak Hitungan Lembur')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['user'] ?? '-' }}</strong> telah
                {{ $notification->data['message']['tipe'] ?? '-' }} {{ $notification->data['message']['nama_karyawan'] ?? '-' }}
                dengan alasan {{ $notification->data['message']['alasan'] ?? '-' }}</p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            <div class="d-flex">
                <a href="{{ $notification->data['path'] ?? '#' }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai Dibaca</button>
                </form>
            </div>
        </div>
    @endif

    @if ($tipePesan == 'Persetujuan Payment Advanced')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['user'] ?? '-' }}</strong> telah
                {{ $notification->data['message']['status'] ?? '-' }} {{ $notification->data['message']['tipe'] ?? '-' }}
                {{ $notification->data['message']['nama_karyawan'] ?? '-' }} dengan alasan
                {{ $notification->data['message']['alasan'] ?? '-' }}</p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            <div class="d-flex">
                <a href="{{ $notification->data['path'] ?? '#' }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai Dibaca</button>
                </form>
            </div>
        </div>
    @endif

    @if ($tipePesan == 'Pesan Contact Us Website INIXINDO')
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
                        <button type="submit" class="btn btn-danger btn-sm" style="font-size: 0.85rem;">Tandai sebagai Dibaca</button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if ($tipePesan == 'Pembayaran_from_web')
        <div class="notification mb-3">
            <p>
                Pembayaran berhasil dilakukan oleh <strong>{{ $notification->data['message']['nama'] ?? '-' }}</strong>
                @if (!empty($notification->data['message']['instansi']))
                    dari instansi <strong>{{ $notification->data['message']['instansi'] }}</strong>
                @endif
                dengan email <strong>{{ $notification->data['message']['email'] ?? '-' }}</strong>.
            </p>
            <p>
                Total pembayaran sebesar
                <strong>Rp{{ number_format($notification->data['message']['total_harga'] ?? 0, 0, ',', '.') }}</strong>.
            </p>
            <p>Pada tanggal {{ $notification->created_at->format('d M Y H:i:s') }}</p>

            <strong style="text-transform: capitalize;">
                Status:
                {{ is_array($notification->data['message']['status'] ?? null) ? json_encode($notification->data['message']['status']) : ($notification->data['message']['status'] ?? '-') }}
            </strong>
            <br>

            @if (
                !empty($notification->data['message']['cartItems']) &&
                    is_array($notification->data['message']['cartItems']) &&
                    count($notification->data['message']['cartItems']) > 0)
                <p>Items dalam keranjang:</p>
                <ul>
                    @foreach ($notification->data['message']['cartItems'] as $item)
                        <li>{{ $item['name'] ?? 'Item' }} - Jumlah: {{ $item['quantity'] ?? '-' }}</li>
                    @endforeach
                </ul>
            @else
                <p><em>Tidak ada item dalam keranjang.</em></p>
            @endif

            <div class="d-flex mt-3">
                <a href="https://inixindobdg.co.id/admin" class="btn btn-primary btn-sm" style="margin-right:8px;">
                    Lihat Selengkapnya
                </a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">
                        Tandai sebagai Dibaca
                    </button>
                </form>
            </div>
        </div>
    @endif

    @if ($tipePesan == 'Request Penawaran')
        <div class="notification mb-3">
            <p>
                Ada Request Penawaran dari peserta <strong>{{ $notification->data['message']['nama'] ?? '-' }}</strong>
                @if (!empty($notification->data['message']['instansi']))
                    dari instansi <strong>{{ $notification->data['message']['instansi'] }}</strong>
                @endif
                dengan email <strong>{{ $notification->data['message']['email'] ?? '-' }}</strong>.
            </p>
            <p>
                untuk materi <strong>{{ $notification->data['message']['nama_materi'] ?? '-' }}</strong>.
            </p>
            <p>Pada tanggal {{ $notification->created_at->format('d M Y H:i:s') }}</p>

            <br>
            <div class="d-flex">
                <a href="https://inixindobdg.co.id/admin" class="btn btn-primary btn-sm" style="margin-right:8px;">
                    Lihat Selengkapnya
                </a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">
                        Tandai sebagai Dibaca
                    </button>
                </form>
            </div>
        </div>
    @endif

    @if ($tipePesan == 'Ticketing Baru')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['user'] ?? '-' }}</strong> divisi
                {{ $notification->data['message']['divisi'] ?? '-' }} telah membuat
                {{ $notification->data['message']['tipe'] ?? '-' }} dengan keperluan
                {{ $notification->data['message']['keperluan'] ?? '-' }} di kategori
                {{ $notification->data['message']['kategori'] ?? '-' }}</p>
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            <div class="d-flex">
                <a href="{{ $notification->data['path'] ?? '#' }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai Dibaca</button>
                </form>
            </div>
        </div>
    @endif

    @if ($tipePesan == 'Perubahan Payment Advance')
        <div class="notification mb-3 p-3 border rounded bg-light">
            <p>
                <strong style="text-transform: capitalize;">
                    {{ $notification->data['message']['karyawan'] ?? '-' }}
                </strong>
                telah melakukan <strong>perubahan</strong> pada Payment Advance
                untuk kelas <strong>{{ $notification->data['message']['rkm'] ?? '-' }}</strong>,
                yang berjalan di <strong>{{ $notification->data['message']['waktu'] ?? '-' }}</strong>.<br>
                Data ini milik <strong>{{ $notification->data['message']['milik'] ?? '-' }}</strong>.
            </p>

                @if (!empty($notification->data['message']['perubahan']))
                    <div class="mt-2">
                        <strong>Detail perubahan:</strong>
                        <ul class="mt-1 mb-0">
                            @php
                                $perubahanData = $notification->data['message']['perubahan'];
                                if (is_string($perubahanData)) {
                                    $perubahanData = json_decode($perubahanData, true) ?? [];
                                }
                            @endphp

                            @if(is_array($perubahanData))
                                @foreach ($perubahanData as $field => $values)
                                    <li>
                                        <strong>{{ ucfirst(str_replace('_', ' ', $field)) }}</strong>:

                                        <span class="text-danger">
                                            @if(isset($values['before']) && is_numeric($values['before']))
                                                {{ number_format((float)$values['before'], 0, ',', '.') }}
                                            @else
                                                {{ $values['before'] ?? '-' }}
                                            @endif
                                        </span>

                                        →

                                        <span class="text-success">
                                            @if(isset($values['after']) && is_numeric($values['after']))
                                                {{ number_format((float)$values['after'], 0, ',', '.') }}
                                            @else
                                                {{ $values['after'] ?? '-' }}
                                            @endif
                                        </span>
                                    </li>
                                @endforeach
                            @endif
                        </ul>
                    </div>
                @endif

            <small class="text-muted">
                Pada
                {{ \Carbon\Carbon::parse($notification->data['message']['waktu_perubahan'] ?? now())->locale('id')->translatedFormat('d F Y H:i') }}
            </small>

            <div class="mt-2 d-flex gap-2">
                <a href="{{ $notification->data['path'] ?? '#' }}" class="btn btn-primary btn-sm me-2">
                    Lihat Detail
                </a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        Tandai sebagai Dibaca
                    </button>
                </form>
            </div>
        </div>
    @endif

    @if ($tipePesan == 'Pembayaran Outstanding Selesai')
        <div class="alert alert-success d-flex justify-content-between align-items-start shadow-sm p-3 mb-3 border-start border-4 border-success">
            <div>
                <h6 class="fw-bold mb-2 text-success">
                    <i class="bi bi-check-circle-fill me-2"></i>Pembayaran Outstanding Selesai
                </h6>
                <p class="mb-1">
                    <strong>{{ $notification->data['message']['perusahaan'] ?? '-' }}</strong> telah menyelesaikan pembayaran untuk
                    <strong>{{ $notification->data['message']['materi'] ?? '-' }}</strong>
                    <span class="text-primary">({{ $notification->data['message']['periode'] ?? '-' }})</span>.
                </p>
                <p class="mb-2 small text-muted">
                    No. Invoice: <strong>{{ $notification->data['message']['no_invoice'] ?? '-' }}</strong> |
                    Tanggal Bayar:
                    <strong>
                        {{ !empty($notification->data['message']['tgl_bayar'])
                            ? \Carbon\Carbon::parse($notification->data['message']['tgl_bayar'])->locale('id')->translatedFormat('d F Y')
                            : '-' }}
                    </strong>
                </p>
                <small class="text-muted">
                    Dikirim: {{ \Carbon\Carbon::parse($notification->created_at)->locale('id')->translatedFormat('d F Y H:i') }} WIB
                </small>

                <div class="mt-2">
                    <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-check2"></i> Tandai Dibaca
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if (in_array($tipePesan, ['Mengajukan Permintaan Souvenir', 'Pengajuan Souvenir Disetujui', 'Pengajuan Souvenir Ditolak', 'Pengajuan Souvenir Diperbarui']))
        <div class="notification mb-3 p-3 border rounded bg-light">
            <p>
                <strong style="text-transform: capitalize;">
                    {{ $notification->data['user'] ?? '-' }}
                </strong>

                @if ($tipePesan == 'Mengajukan Permintaan Souvenir')
                    telah <strong>membuat pengajuan baru</strong> untuk permintaan
                @elseif ($tipePesan == 'Pengajuan Souvenir Disetujui')
                    telah <strong>menyetujui</strong> pengajuan
                @elseif ($tipePesan == 'Pengajuan Souvenir Ditolak')
                    telah <strong>menolak</strong> pengajuan
                @elseif ($tipePesan == 'Pengajuan Souvenir Diperbarui')
                    telah <strong>memperbarui status</strong> pengajuan
                @endif

                @if (isset($notification->data['message']['tipe_barang']))
                    <strong>{{ $notification->data['message']['tipe_barang'] }}</strong>.
                @elseif (isset($notification->data['message']['nama_lengkap']))
                    milik <strong>{{ $notification->data['message']['nama_lengkap'] }}</strong>.
                @endif
            </p>

            <p class="mb-2 text-muted">
                @if (isset($notification->data['message']['status']))
                    <i class="bi bi-info-circle"></i> {{ $notification->data['message']['status'] }}<br>
                @endif

                @php
                    $tgl = $notification->data['message']['tanggal'] ?? ($notification->data['message']['tanggal_pengajuan'] ?? null);
                @endphp

                @if ($tgl)
                    <small><i class="bi bi-clock"></i>
                        {{ \Carbon\Carbon::parse($tgl)->translatedFormat('d F Y H:i') }}</small>
                @endif
            </p>

            <div class="d-flex gap-2">
                <a href="{{ $notification->data['path'] ?? '#' }}" class="btn btn-primary btn-sm">
                    <img src="{{ asset('icon/eye.svg') }}" width="16px" style="filter: invert(1);"> Lihat Detail
                </a>

                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-outline-secondary btn-sm">
                        Tandai Dibaca
                    </button>
                </form>
            </div>
        </div>
    @endif

    @if ($tipePesan == 'Laporan Distribusi Souvenir')
        <div class="notification mb-3 p-3 border rounded bg-light">
            <div class="d-flex justify-content-between align-items-start">
                <div style="width: 100%;">
                    <p class="mb-1">
                        <strong class="text-capitalize text-primary">
                            {{ $notification->data['user'] ?? '-' }}
                        </strong>
                        telah mendistribusikan
                        <strong>{{ $notification->data['message']['tipe_barang'] ?? 'Souvenir' }}</strong>.
                    </p>

                    <div class="mt-2 mb-2">
                        <small class="text-muted d-block text-uppercase"
                            style="font-size: 0.7rem; font-weight: bold;">
                            Diberikan Kepada:
                        </small>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-person-badge text-secondary me-2"></i>
                            <div>
                                <span class="text-dark fw-bold">
                                    {{ $notification->data['message']['penerima_nama'] ?? 'Tanpa Nama' }}
                                </span>
                                <span class="text-muted ms-1" style="font-size: 0.85rem;">
                                    ({{ $notification->data['message']['penerima_jabatan'] ?? '-' }})
                                </span>
                            </div>
                        </div>
                    </div>

                    @if (!empty($notification->data['message']['detail_barang']) && is_array($notification->data['message']['detail_barang']))
                        <div class="p-2 bg-white border rounded mb-2">
                            <small class="text-muted d-block text-uppercase mb-1"
                                style="font-size: 0.7rem; font-weight: bold;">
                                Detail Item:
                            </small>
                            <ul class="list-unstyled mb-0" style="font-size: 0.9rem;">
                                @foreach ($notification->data['message']['detail_barang'] as $item)
                                    <li class="d-flex justify-content-between align-items-center border-bottom pb-1 mb-1 last:border-0">
                                        <span class="text-secondary">
                                            <i class="bi bi-box-seam me-1"></i>
                                            {{ $item['nama_souvenir'] ?? '-' }}
                                        </span>
                                        <span class="badge bg-success rounded-pill">
                                            {{ $item['qty'] ?? 0 }} pcs
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (!empty($notification->data['message']['nama_rkm']))
                        <div class="mt-2 p-2 bg-white border rounded">
                            <small class="text-muted d-block text-uppercase"
                                style="font-size: 0.7rem; font-weight: bold;">
                                Kegiatan / Materi:
                            </small>
                            <span class="text-dark fw-bold">
                                {{ $notification->data['message']['nama_rkm'] }}
                            </span>

                            @if (!empty($notification->data['message']['rkm_start']) && !empty($notification->data['message']['rkm_end']))
                                <div class="mt-1 text-secondary" style="font-size: 0.85rem;">
                                    <i class="bi bi-calendar-range me-1"></i>
                                    {{ \Carbon\Carbon::parse($notification->data['message']['rkm_start'])->format('d M') }}
                                    s/d
                                    {{ \Carbon\Carbon::parse($notification->data['message']['rkm_end'])->format('d M Y') }}
                                </div>
                            @endif
                        </div>
                    @endif

                    <small class="text-muted mt-2 d-block">
                        <i class="bi bi-clock"></i> Diinput pada:
                        {{ \Carbon\Carbon::parse($notification->data['message']['tanggal_pengajuan'] ?? now())->translatedFormat('d F Y') }}
                    </small>
                </div>
            </div>

            <div class="d-flex gap-2 mt-3">
                <a href="{{ $notification->data['path'] ?? '#' }}" class="btn btn-sm btn-primary">
                    <img src="{{ asset('icon/eye.svg') }}" width="14px" style="filter: invert(1);" class="me-1"> Lihat Data
                </a>

                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                        Tandai Dibaca
                    </button>
                </form>
            </div>
        </div>
    @endif

    @if ($tipePesan == 'Penukaran Souvenir')
        <div class="notification mb-3 p-3 border rounded bg-light">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="mb-1">
                        <strong class="text-capitalize text-primary">
                            {{ $notification->data['user'] ?? '-' }}
                        </strong>
                        telah melakukan
                        <strong>Penukaran Souvenir</strong>.
                    </p>

                    <div class="mt-2 p-2 bg-white border rounded">
                        <small class="text-muted d-block text-uppercase"
                            style="font-size: 0.7rem; font-weight: bold;">
                            Pemilik / Peserta:
                        </small>
                        <div class="mb-2">
                            <i class="bi bi-person-circle text-secondary me-1"></i>
                            <span class="text-dark fw-bold">
                                {{ $notification->data['message']['nama_peserta'] ?? 'Peserta' }}
                            </span>
                        </div>

                        <div class="d-flex align-items-center p-2 bg-light rounded border border-light">
                            <div class="text-danger" style="font-size: 0.85rem;" title="Dikembalikan">
                                <i class="bi bi-x-circle me-1"></i>
                                <span class="text-decoration-line-through text-secondary">
                                    {{ $notification->data['message']['souvenir_lama'] ?? '-' }}
                                </span>
                            </div>

                            <div class="mx-2 text-muted">
                                <i class="bi bi-arrow-right"></i>
                            </div>

                            <div class="text-success fw-bold" style="font-size: 0.85rem;" title="Diterima">
                                <i class="bi bi-check-circle me-1"></i>
                                {{ $notification->data['message']['souvenir_baru'] ?? '-' }}
                            </div>
                        </div>
                    </div>

                    <small class="text-muted mt-2 d-block">
                        <i class="bi bi-clock"></i> Diproses pada:
                        {{ \Carbon\Carbon::parse($notification->data['message']['tanggal_tukar'] ?? now())->translatedFormat('d F Y H:i') }} WIB
                    </small>
                </div>
            </div>

            <div class="d-flex gap-2 mt-3">
                <a href="{{ $notification->data['path'] ?? '#' }}" class="btn btn-sm btn-primary">
                    <img src="{{ asset('icon/eye.svg') }}" width="14px" style="filter: invert(1);" class="me-1"> Lihat Riwayat
                </a>

                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                        Tandai Dibaca
                    </button>
                </form>
            </div>
        </div>
    @endif

    @if ($tipePesan == 'Preorder Modul')
        <div class="notification mb-3 p-3 border rounded bg-light">
            <div class="d-flex justify-content-between align-items-start">
                <div class="w-100">
                    <p class="mb-1">
                        <strong class="text-capitalize text-primary">
                            {{ $notification->data['message']['pembuat'] ?? 'User' }}
                        </strong>
                        telah membuat sebuah preorder modul baru.
                    </p>

                    <div class="mt-2 p-3 bg-white border rounded">
                        <div class="row">
                            <div class="col-md-6 mb-2 mb-md-0">
                                <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.7rem;">
                                    Nomor Modul
                                </small>
                                <span class="text-dark fw-bold font-monospace">
                                    <i class="bi bi-hash text-secondary me-1"></i>
                                    {{ $notification->data['message']['noModul'] ?? '-' }}
                                </span>
                            </div>

                            <div class="col-md-6">
                                <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.7rem;">
                                    Tipe
                                </small>
                                <span class="badge bg-info text-dark">
                                    {{ $notification->data['message']['type'] ?? '-' }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-3 pt-2 border-top">
                            <p class="mb-0 text-muted fst-italic" style="font-size: 0.85rem;">
                                "Silahkan di cek beberapa saat nanti."
                            </p>
                        </div>
                    </div>

                    <small class="text-muted mt-2 d-block">
                        <i class="bi bi-clock"></i> Dibuat pada:
                        {{ $notification->created_at->translatedFormat('d F Y H:i') }} WIB
                    </small>
                </div>
            </div>

            <div class="d-flex gap-2 mt-3">
                <a href="{{ $notification->data['path'] ?? '#' }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-eye me-1"></i> Lihat Detail
                </a>

                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                        Tandai Dibaca
                    </button>
                </form>
            </div>
        </div>
    @endif

    @if ($tipePesan == 'Persetujuan Preorder')
        <div class="notification mb-3 p-3 border rounded bg-light">
            <div class="w-100">
                <p class="mb-2 text-success fw-bold">
                    Pengajuan anda telah disetujui
                </p>

                <div class="mb-2">
                    <small class="text-muted d-block">Nomor Modul</small>
                    <span class="fw-bold font-monospace">
                        {{ $notification->data['message']['noModul'] ?? '-' }}
                    </span>
                </div>

                <div class="mb-2">
                    <small class="text-muted d-block">Tipe Modul</small>
                    <span class="badge bg-info text-dark">
                        {{ $notification->data['message']['type'] ?? '-' }}
                    </span>
                </div>

                <small class="text-muted d-block">
                    <i class="bi bi-clock"></i> Dibuat pada:
                    {{ $notification->created_at->translatedFormat('d F Y H:i') }} WIB
                </small>

                <div class="d-flex gap-2 mt-3">
                    <a href="{{ $notification->data['path'] ?? '#' }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-eye me-1"></i> Detail
                    </a>

                    <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                            Tandai Dibaca
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if ($tipePesan == 'Outstanding Lunas dengan data PA')
        <div class="notification mb-3 p-3 border rounded bg-light">
            <div class="w-100">
                <p class="mb-2 text-success fw-bold">
                    Pembayaran Outstanding Telah Diselesaikan
                </p>

                <div class="mb-2">
                    <small class="text-muted d-block">Perusahaan</small>
                    <span class="fw-bold">
                        {{ $notification->data['message']['perusahaan'] ?? '-' }}
                    </span>
                </div>

                <div class="mb-2">
                    <small class="text-muted d-block">Materi</small>
                    <span>
                        {{ $notification->data['message']['materi'] ?? '-' }}
                    </span>
                </div>

                <div class="mb-2">
                    <small class="text-muted d-block">Periode Kelas</small>
                    <span class="badge bg-info text-dark">
                        {{ $notification->data['message']['periode'] ?? '-' }}
                    </span>
                </div>

                <div class="alert alert-info py-2 px-3 mb-3" style="font-size: 0.85rem;">
                    <i class="bi bi-info-circle me-1"></i>
                    Data <strong>Payment Advance (PA)</strong> tersedia.
                </div>

                <small class="text-muted d-block">
                    <i class="bi bi-clock"></i> Dibuat pada:
                    {{ $notification->created_at->translatedFormat('d F Y H:i') }} WIB
                </small>

                <div class="d-flex gap-2 mt-3">
                    <a href="{{ $notification->data['path'] ?? '#' }}" target="_blank" class="btn btn-sm btn-primary">
                        <i class="bi bi-eye me-1"></i>Detail
                    </a>

                    <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                            Tandai Dibaca
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if (in_array($tipePesan, ['Mengajukan Pengembangan Diri', 'Pengembangan Diri Disetujui', 'Pengembangan Diri Ditolak']))
        @php
            $msg = $notification->data['message'];
            $kategori = $msg['tipe_kategori'] ?? 'Umum';
            $namaItem = $msg['nama_item'] ?? '-';
            $namaSertifTambahan = $msg['nama_sertifikasi_tambahan'] ?? null;
            $harga = $msg['harga'] ?? 0;
            $tglUjian = $msg['tanggal_ujian'] ?? null;
            $tglBerlakuDari = $msg['berlaku_dari'] ?? null;
            $tglBerlakuSampai = $msg['berlaku_sampai'] ?? null;
            $tglMulai = $msg['tanggal_mulai'] ?? null;
            $tglSelesai = $msg['tanggal_selesai'] ?? null;
            $tglPelatihanLama = $msg['tanggal_pelatihan'] ?? null;
            $tglPengajuan = $msg['tanggal_pengajuan'] ?? $msg['tanggal'] ?? null;
        @endphp

        <div class="notification mb-3 p-3 border rounded bg-light">
            <p>
                <strong style="text-transform: capitalize;">
                    {{ $notification->data['user'] ?? 'User' }}
                </strong>

                @if ($msg['tipe'] == 'Mengajukan Pengembangan Diri')
                    telah <strong>membuat pengajuan baru</strong> untuk
                @elseif ($msg['tipe'] == 'Pengembangan Diri Disetujui')
                    telah <strong>menyetujui</strong>. <br>
                    <span class="text-success fst-italic">
                        "Telah disetujui Education Manager dan Sudah menjadi pengajuan barang dengan kategori Sertifikasi dan Pelatihan"
                    </span> <br>
                    untuk data
                @elseif ($msg['tipe'] == 'Pengembangan Diri Ditolak')
                    telah <strong>menolak</strong> pengajuan
                @endif

                <strong>{{ $kategori }}</strong>: "{{ $namaItem }}"

                @if($namaSertifTambahan)
                     dan <strong>Sertifikasi</strong>: "{{ $namaSertifTambahan }}"
                @endif
                .
            </p>

            <div class="alert alert-light border py-2 px-3 mb-2" style="font-size: 0.9rem;">
                @if($kategori == 'Sertifikasi' && $tglUjian)
                    <div class="row">
                        <div class="col-12 mb-1">
                            <i class="bi bi-calendar-event text-primary"></i>
                            Tgl Ujian: <strong>{{ \Carbon\Carbon::parse($tglUjian)->translatedFormat('d M Y') }}</strong>
                        </div>
                         <div class="col-12 mb-1">
                            <i class="bi bi-clock-history text-warning"></i>
                            Berlaku:
                            {{ $tglBerlakuDari ? \Carbon\Carbon::parse($tglBerlakuDari)->translatedFormat('d M Y') : '-' }}
                            s/d
                            {{ $tglBerlakuSampai ? \Carbon\Carbon::parse($tglBerlakuSampai)->translatedFormat('d M Y') : 'Seumur Hidup' }}
                        </div>
                    </div>
                @elseif($kategori == 'Pelatihan' && ($tglMulai || $tglPelatihanLama))
                    <div class="mb-1">
                        <i class="bi bi-calendar-check text-primary"></i>
                        Pelaksanaan:
                        @if($tglMulai)
                            <strong>{{ \Carbon\Carbon::parse($tglMulai)->translatedFormat('d M Y') }}</strong> s/d <strong>{{ \Carbon\Carbon::parse($tglSelesai)->translatedFormat('d M Y') }}</strong>
                        @else
                            <strong>{{ \Carbon\Carbon::parse($tglPelatihanLama)->translatedFormat('d F Y') }}</strong>
                        @endif
                    </div>
                @endif

                <div class="mt-1 border-top pt-1 text-end">
                    <small class="text-muted">Estimasi Biaya:</small><br>
                    <strong class="text-success">
                        Rp {{ number_format($harga, 0, ',', '.') }}
                    </strong>
                </div>
            </div>

            <p class="mb-2 text-muted">
                @if (isset($msg['status']))
                    <i class="bi bi-info-circle"></i> {{ $msg['status'] }}<br>
                @endif
                @if($tglPengajuan)
                    <small><i class="bi bi-clock"></i> {{ \Carbon\Carbon::parse($tglPengajuan)->translatedFormat('d F Y H:i') }}</small>
                @endif
            </p>

            <div class="d-flex gap-2">
                <a href="{{ $notification->data['path'] ?? '#' }}" class="btn btn-primary btn-sm">
                    <img src="{{ asset('icon/eye.svg') }}" width="16px" style="filter: invert(1);"> Lihat Detail
                </a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-outline-secondary btn-sm">
                        Tandai Dibaca
                    </button>
                </form>
            </div>
        </div>
    @endif

    @if ($tipePesan == 'Notif Kegiatan')
        <div class="notification mb-3 p-3 border rounded bg-light">
            <div class="w-100">
                <p class="mb-2 text-primary fw-bold">Pengajuan Kegiatan Baru Telah Diajukan</p>

                <div class="mb-2">
                    <small class="text-muted d-block">Nama Kegiatan</small>
                    <span class="fw-bold">{{ $notification->data['message']['kegiatan'] ?? '-' }}</span>
                </div>

                <div class="mb-2">
                    <small class="text-muted d-block">Tipe RAB</small>
                    <span class="fw-bold text-uppercase">{{ $notification->data['message']['tipe_kegiatan'] ?? '-' }}</span>
                </div>

                <div class="mb-2">
                    <small class="text-muted d-block">Lama Kegiatan</small>
                    <span>{{ $notification->data['message']['lama_kegiatan'] ?? '-' }}</span>
                </div>

                <div class="mb-2">
                    <small class="text-muted d-block">Waktu Pelaksanaan</small>
                    <span class="badge bg-info text-dark">{{ $notification->data['message']['waktu_kegiatan'] ?? '-' }}</span>
                </div>

                <div class="mb-2">
                    <small class="text-muted d-block">Penanggung Jawab</small>
                    <span class="badge bg-info text-dark">{{ $notification->data['message']['pic'] ?? '-' }}</span>
                </div>

                <div class="alert alert-warning py-2 px-3 mb-3" style="font-size: 0.85rem;">
                    <i class="bi bi-clock me-1"></i>
                    Pengajuan sedang diproses. Silakan cek kembali beberapa saat lagi untuk melihat status terbaru.
                </div>

                <small class="text-muted d-block">
                    <i class="bi bi-clock"></i> Dibuat pada: {{ $notification->created_at->translatedFormat('d F Y H:i') }} WIB
                </small>

                <div class="d-flex gap-2 mt-3">
                    <a href="{{ $notification->data['path'] ?? '#' }}" target="_blank" class="btn btn-sm btn-primary">
                        <i class="bi bi-eye me-1"></i>Detail
                    </a>
                    <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                            Tandai Dibaca
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if ($tipePesan == 'Status Approved')
        <div class="notification mb-3 p-3 border rounded bg-light">
            <div class="w-100">
                <p class="mb-2 text-success fw-bold">Kegiatan Telah Disetujui (Approved)</p>
                <div class="mb-3">
                    <span class="fw-bold">{{ $notification->data['message']['kegiatan'] ?? 'Kegiatan' }}</span>
                    <span class="text-muted"> telah disetujui oleh General Manager.</span>
                </div>
                <div class="alert alert-success py-2 px-3 mb-3" style="font-size: 0.85rem;">
                    <i class="bi bi-check-circle me-1"></i>
                    Status kegiatan telah berubah menjadi <strong>Approved</strong>. Silakan lanjutkan proses selanjutnya dibagian Finance.
                </div>
                <small class="text-muted d-block">
                    <i class="bi bi-clock"></i> Dibuat pada: {{ $notification->created_at->translatedFormat('d F Y H:i') }} WIB
                </small>
                <div class="d-flex gap-2 mt-3">
                    <a href="{{ $notification->data['path'] ?? '#' }}" target="_blank" class="btn btn-sm btn-primary">
                        <i class="bi bi-eye me-1"></i>Detail Kegiatan
                    </a>
                    <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                            Tandai Dibaca
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if ($tipePesan == 'Status Pencairan')
        <div class="notification mb-3 p-3 border rounded bg-light">
            <div class="w-100">
                <p class="mb-2 text-info fw-bold">Dana Kegiatan Telah Dicairkan</p>
                <div class="mb-3">
                    <span class="fw-bold">{{ $notification->data['message']['kegiatan'] ?? 'Kegiatan' }}</span>
                    <span class="text-muted"> telah selesai proses pencairan dana.</span>
                </div>
                <div class="alert alert-info py-2 px-3 mb-3" style="font-size: 0.85rem;">
                    <i class="bi bi-cash-coin me-1"></i>
                    Dana kegiatan telah <strong>dicairkan</strong> oleh Finance. Silakan lanjutkan pelaksanaan kegiatan dan selesaikan.
                </div>
                <small class="text-muted d-block">
                    <i class="bi bi-clock"></i> Dibuat pada: {{ $notification->created_at->translatedFormat('d F Y H:i') }} WIB
                </small>
                <div class="d-flex gap-2 mt-3">
                    <a href="{{ $notification->data['path'] ?? '#' }}" target="_blank" class="btn btn-sm btn-primary">
                        <i class="bi bi-eye me-1"></i>Detail Kegiatan
                    </a>
                    <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                            Tandai Dibaca
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if ($tipePesan == 'Status Menunggu')
        <div class="notification mb-3 p-3 border rounded bg-light">
            <div class="w-100">
                <p class="mb-2 text-warning fw-bold">Kegiatan Dalam Status Menunggu</p>
                <div class="mb-3">
                    <span class="fw-bold">{{ $notification->data['message']['kegiatan'] ?? 'Kegiatan' }}</span>
                    <span class="text-muted"> memerlukan peninjauan lebih lanjut.</span>
                </div>
                <div class="alert alert-warning py-2 px-3 mb-3" style="font-size: 0.85rem;">
                    <i class="bi bi-hourglass-split me-1"></i>
                    Status kegiatan saat ini <strong>Menunggu</strong>. Mohon lakukan review atau tindakan yang diperlukan.
                </div>
                <small class="text-muted d-block">
                    <i class="bi bi-clock"></i> Dibuat pada: {{ $notification->created_at->translatedFormat('d F Y H:i') }} WIB
                </small>
                <div class="d-flex gap-2 mt-3">
                    <a href="{{ $notification->data['path'] ?? '#' }}" target="_blank" class="btn btn-sm btn-primary">
                        <i class="bi bi-eye me-1"></i>Detail Kegiatan
                    </a>
                    <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                            Tandai Dibaca
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if ($tipePesan == 'Laporan Kondisi Kendaraan')
        <div class="notification mb-3 p-3 border rounded bg-light">
            <div class="w-100">
                <p class="mb-2 text-success fw-bold">Laporan Kondisi Kendaraan Diperbarui</p>
                <div class="mb-3">
                    <span class="fw-bold">{{ $notification->data['message']['user'] ?? 'User' }}</span>
                    <span class="text-muted"> telah mengupdate kondisi kendaraan</span>
                    <span class="fw-bold">{{ $notification->data['message']['kendaraan'] ?? '-' }}</span>
                </div>
                <div class="alert alert-success py-2 px-3 mb-3" style="font-size: 0.85rem;">
                    <i class="bi bi-truck me-1"></i>
                    Pemeriksaan dilakukan pada tanggal
                    <strong>{{ \Carbon\Carbon::parse($notification->data['message']['tanggal_pemeriksaan'] ?? now())->translatedFormat('d F Y') }}</strong>
                </div>
                <small class="text-muted d-block">
                    <i class="bi bi-clock"></i> Dibuat pada: {{ $notification->created_at->translatedFormat('d F Y H:i') }} WIB
                </small>
                <div class="d-flex gap-2 mt-3">
                    <a href="{{ $notification->data['path'] ?? '#' }}" target="_blank" class="btn btn-sm btn-primary">
                        <i class="bi bi-eye me-1"></i>Detail Kondisi
                    </a>
                    <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                            Tandai Dibaca
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if ($tipePesan == 'Mengajukan Update Exam')
        <div class="notification mb-3">
            <p><strong style="text-transform: capitalize;">{{ $notification->data['user'] ?? '-' }}</strong> telah
                {{ $notification->data['message']['tipe'] ?? '-' }} </p> {{ $notification->data['message']['nama_exam'] ?? '-' }}
            <p>Pada {{ $notification->created_at->format('d M Y H:i:s') }}</p>
            <div class="d-flex">
                <a href="{{ $notification->data['path'] ?? '#' }}" class="btn btn-primary btn-sm"
                    style="margin-right:8px;">Lihat Selengkapnya</a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger btn-sm" style="margin-left:8px;">Tandai sebagai Dibaca</button>
                </form>
            </div>
        </div>
    @endif

    @if ($tipePesan == 'Ide Inovasi Baru')
        <div class="notification mb-3 p-3 border rounded bg-light">
            <div class="w-100">
                <p class="mb-2 text-primary fw-bold">
                    <i class="bi bi-lightbulb text-warning me-1"></i> Ide Inovasi Baru
                </p>
                <div class="mb-3">
                    <span class="fw-bold" style="text-transform: capitalize;">
                        {{ $notification->data['user'] ?? 'System' }}
                    </span>
                    <span class="text-muted">
                        telah menambahkan ide inovasi baru berjudul
                    </span>
                    <span class="fw-bold text-dark">
                        "{{ $notification->data['message']['judul'] ?? '-' }}"
                    </span>
                </div>
                <small class="text-muted d-block">
                    <i class="bi bi-clock"></i>
                    Dibuat pada: {{ $notification->created_at->format('d M Y H:i:s') }}
                </small>
                <div class="d-flex gap-2 mt-3">
                    <a href="{{ $notification->data['path'] ?? '#' }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-eye me-1"></i>Lihat Data
                    </a>
                    <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                            Tandai Dibaca
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

        @if (in_array($tipePesan, ['Mengajukan Lab', 'Mengajukan Subscription']))
        <div class="notification mb-3 p-3 border rounded bg-light">
            <p class="mb-2">
                <strong class="text-capitalize">{{ $notification->data['user'] ?? '-' }}</strong>
                telah membuat
                <strong>{{ $notification->data['message']['tipe'] ?? '-' }}</strong>

                @if (!empty($notification->data['message']['nama']))
                    dengan nama <strong>{{ $notification->data['message']['nama'] }}</strong>
                @endif

                @if (!empty($notification->data['message']['deskripsi']))
                    ({{ $notification->data['message']['deskripsi'] }})
                @endif
            </p>

            @if (!empty($notification->data['message']['rkm']))
                @php $rkm = $notification->data['message']['rkm']; @endphp
                <div class="ms-3 mb-2">
                    <p class="mb-1">
                        Terkait RKM:
                        <strong>{{ $rkm['nama_materi'] ?? '-' }}</strong>
                        di perusahaan <strong>{{ $rkm['nama_perusahaan'] ?? '-' }}</strong>
                    </p>

                    <p class="mb-0">
                        @if (!empty($rkm['tanggal_awal']))
                            Mulai {{ \Carbon\Carbon::parse($rkm['tanggal_awal'])->translatedFormat('d F Y') }}
                        @endif
                        @if (!empty($rkm['tanggal_akhir']))
                            sampai {{ \Carbon\Carbon::parse($rkm['tanggal_akhir'])->translatedFormat('d F Y') }}
                        @endif
                    </p>
                </div>
            @endif

            <p class="text-muted mb-3">
                Pada {{ $notification->created_at->translatedFormat('d F Y H:i') }}
            </p>

            <div class="d-flex">
                <a href="{{ $notification->data['path'] ?? '#' }}" class="btn btn-sm btn-primary me-2">
                    Lihat Selengkapnya
                </a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        Tandai sebagai Dibaca
                    </button>
                </form>
            </div>
        </div>
    @endif

    @if (in_array($tipePesan, ['Menyetujui Pengajuan Lab/Subscription', 'Menolak Pengajuan Lab/Subscription', 'Update Status Pencairan oleh Finance']))
        <div class="notification mb-3 p-3 border rounded bg-light">
            <p>
                <strong style="text-transform: capitalize;">
                    {{ $notification->data['user'] ?? '-' }}
                </strong>
                @if ($tipePesan == 'Menyetujui Pengajuan Lab/Subscription')
                    telah <strong>menyetujui</strong> pengajuan
                @elseif ($tipePesan == 'Menolak Pengajuan Lab/Subscription')
                    telah <strong>menolak</strong> pengajuan
                @elseif ($tipePesan == 'Update Status Pencairan oleh Finance')
                    telah melakukan <strong>update status pencairan</strong> pada pengajuan
                @endif
                milik <strong>{{ $notification->data['message']['nama_lengkap'] ?? '-' }}</strong>.
            </p>
            <p>
                <em>{{ $notification->data['message']['status'] ?? '-' }}</em><br>
                <small>Pada
                    {{ \Carbon\Carbon::parse($notification->data['message']['tanggal'] ?? now())->translatedFormat('d F Y H:i') }}</small>
            </p>

            <div class="d-flex">
                <a href="{{ $notification->data['path'] ?? '#' }}" class="btn btn-primary btn-sm me-2">
                    Lihat Detail Pengajuan
                </a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        Tandai sebagai Dibaca
                    </button>
                </form>
            </div>
        </div>
    @endif

    @if (in_array($tipePesan, ['Menyetujui Pengajuan Lab/Subscription', 'Menolak Pengajuan Lab/Subscription', 'Update Status Pencairan oleh Finance']))
        <div class="notification mb-3 p-3 border rounded bg-light">
            <p>
                <strong style="text-transform: capitalize;">
                    {{ $notification->data['user'] ?? '-' }}
                </strong>
                @if ($tipePesan == 'Menyetujui Pengajuan Lab/Subscription')
                    telah <strong>menyetujui</strong> pengajuan
                @elseif ($tipePesan == 'Menolak Pengajuan Lab/Subscription')
                    telah <strong>menolak</strong> pengajuan
                @elseif ($tipePesan == 'Update Status Pencairan oleh Finance')
                    telah melakukan <strong>update status pencairan</strong> pada pengajuan
                @endif
                milik <strong>{{ $notification->data['message']['nama_lengkap'] ?? '-' }}</strong>.
            </p>
            <p>
                <em>{{ $notification->data['message']['status'] ?? '-' }}</em><br>
                <small>Pada
                    {{ \Carbon\Carbon::parse($notification->data['message']['tanggal'] ?? now())->translatedFormat('d F Y H:i') }}</small>
            </p>

            <div class="d-flex">
                <a href="{{ $notification->data['path'] ?? '#' }}" class="btn btn-primary btn-sm me-2">
                    Lihat Detail Pengajuan
                </a>
                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        Tandai sebagai Dibaca
                    </button>
                </form>
            </div>
        </div>
    @endif
    <hr>
@endforeach
