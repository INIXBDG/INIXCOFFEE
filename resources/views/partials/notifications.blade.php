@foreach (auth()->user()->unreadNotifications as $notification)
    <div class="card mb-3 border-0 bg-light rounded-3">
        <div class="card-body">
            @php
                $message = $notification->data['message'];
                $tipe = $message['tipe'] ?? '-';
                $status = $message['status'] ?? '-';
                $user = $notification->data['user'] ?? '-';
                $path = $notification->data['path'] ?? '#';
            @endphp

            <div class="d-flex align-items-start">
                <div class="me-3">
                    <i class="bi bi-info-circle-fill text-info" style="font-size: 1.5rem;"></i>
                </div>

                <div class="flex-grow-1">
                    <p class="mb-1 fw-semibold text-dark text-capitalize">
                        @if($tipe === 'komentar')
                            {{ $user }} menambahkan komentar "{{ $message['content'] ?? '' }}" di {{ $message['materi_key'] ?? '' }}
                        @elseif($tipe === 'RKM Baru' || $tipe === 'RKM Update')
                            {{ $user }} {{ strtolower($tipe) }} dengan judul "{{ $message['nama_materi'] ?? '' }}" dari {{ $message['nama_perusahaan'] ?? '' }}
                        @elseif($tipe === 'Izin 3 Jam')
                            {{ $status }} {{ $message['nama_lengkap'] ?? '' }} untuk izin {{ $message['durasi'] ?? '' }} jam dari {{ \Carbon\Carbon::parse($message['jam_mulai'])->format('H:i') }} s/d {{ \Carbon\Carbon::parse($message['jam_selesai'])->format('H:i') }}
                        @elseif($tipe === 'no_record')
                            {{ $status }} atas klaim tidak terekam oleh {{ $message['nama_lengkap'] ?? '' }} tanggal {{ \Carbon\Carbon::parse($message['tanggal'])->format('d M Y') }} alasan: {{ $message['kronologi'] ?? '' }}
                        @elseif($tipe === 'Assign Kelas')
                            {{ $user }} menambahkan anda sebagai {{ $message['role'] ?? '' }} di kelas "{{ $message['nama_materi'] ?? '' }}"
                        @elseif($tipe === 'Mengajukan Exam')
                            {{ $user }} {{ strtolower($tipe) }} "{{ $message['nama_materi'] ?? '' }}" dari {{ $message['nama_perusahaan'] ?? '' }}
                        @elseif($tipe === 'Menyetujui Pengajuan Exam')
                            {{ $user }} {{ strtolower($tipe) }} pengajuan tanggal {{ \Carbon\Carbon::parse($message['tanggal_pengajuan'])->format('d M Y') }} materi "{{ $message['materi'] ?? '' }}"
                        @elseif($tipe === 'Mengajukan Cuti')
                            {{ $user }} {{ strtolower($tipe) }} {{ $message['jenis_cuti'] ?? '' }} dari tanggal {{ \Carbon\Carbon::parse($message['tanggal_awal'])->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($message['tanggal_akhir'])->format('d M Y') }}
                        @elseif($tipe === 'Pembayaran_from_web')
                            Pembayaran oleh {{ $message['nama'] ?? '' }} dari {{ $message['instansi'] ?? '' }} sebesar Rp{{ number_format($message['total_harga'], 0, ',', '.') }}
                        @elseif($tipe === 'Pesan Contact Us Website INIXINDO')
                            Pesan dari {{ $message['name'] ?? '' }} - {{ $message['instansi'] ?? '' }}: {{ $message['pesan'] ?? '' }}
                        @else
                            {{ $user ?? 'Notifikasi' }} - {{ $tipe }}
                        @endif
                    </p>
                    <small class="text-muted fst-italic d-block mb-3">{{ $notification->created_at->format('d M Y H:i:s') }}</small>
                    
                    <div class="d-flex gap-2">
                        <a href="{{ $path }}" class="btn btn-sm btn-outline-primary px-3">Lihat</a>
                        <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-sm btn-outline-danger px-3">Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endforeach