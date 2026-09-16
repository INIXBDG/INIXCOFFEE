                                        @forelse($administrasis as $administrasi)
                                            <tr class="border-bottom ">
                                                @if ($administrasi->status === 'selesai')
                                                    <td class="text-center ps-4"><input class="custom-check"
                                                            type="checkbox" checked disabled></td>
                                                @elseif ($administrasi->status === 'terlambat')
                                                    <td class="text-center ps-4"><input class="custom-fail"
                                                            type="checkbox" checked disabled></td>
                                                @else
                                                    <td class="text-center ps-4"><input
                                                            class="check-blue edit-administrasi"
                                                            data-id="{{ $administrasi->id }}" type="checkbox"></td>
                                                @endif
                                                <td>
                                                    {{ $administrasi->nama_administrasi }}
                                                </td>
                                                <td>
                                                    {{ \Carbon\Carbon::parse($administrasi->dateline)->format('l, d F Y') }}
                                                </td>
                                                <td>
                                                    {{ $administrasi->tanggal_selesai ? \Carbon\Carbon::parse($administrasi->tanggal_selesai)->format('l, d F Y') : '-' }}
                                                </td>
                                                <td class="text-center pe-4">
                                                    @php
                                                        $statusConfig = [
                                                            'pending' => [
                                                                'color' => 'warning',
                                                                'icon' => 'bx-time-five',
                                                            ],
                                                            'proses' => [
                                                                'color' => 'primary',
                                                                'icon' => 'bx-loader-circle',
                                                            ],
                                                            'selesai' => [
                                                                'color' => 'success',
                                                                'icon' => 'bx-check-circle',
                                                            ],
                                                            'terlambat' => [
                                                                'color' => 'danger',
                                                                'icon' => 'bx-info-circle',
                                                            ],
                                                        ];
                                                        $config = $statusConfig[$administrasi->status] ?? [
                                                            'color' => 'secondary',
                                                            'icon' => 'bx-info-circle',
                                                        ];
                                                    @endphp
                                                    <span
                                                        class="badge bg-{{ $config['color'] }}-subtle text-{{ $config['color'] }} px-3 py-2 text-capitalize">
                                                        <i class="bx {{ $config['icon'] }} me-1"></i>
                                                        {{ $administrasi->status }}
                                                    </span>
                                                </td>
                                                <td class="text-center pe-4">
                                                    @php
                                                        if ($administrasi->tanggal_selesai) {
                                                            $diff = \Carbon\Carbon::parse(
                                                                $administrasi->dateline,
                                                            )->diffInDays(
                                                                \Carbon\Carbon::parse($administrasi->tanggal_selesai),
                                                                false,
                                                            );

                                                            if ($diff <= 0 || $administrasi->status === 'selesai') {
                                                                $progress = 100;
                                                                $color = 'success';
                                                            } elseif ($diff <= 3) {
                                                                $progress = 80;
                                                                $color = 'warning';
                                                            } elseif ($diff <= 7) {
                                                                $progress = 60;
                                                                $color = 'warning';
                                                            } else {
                                                                $progress = 0;
                                                                $color = 'danger';
                                                            }
                                                        } else {
                                                            $progress = 0;
                                                            $color = 'danger';
                                                        }
                                                    @endphp

                                                    <span
                                                        class="badge bg-{{ $color }}-subtle text-{{ $color }}">
                                                        {{ $progress }}%
                                                    </span>
                                                </td>
                                                <td class="text-center pe-4 position-relative">
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-outline-primary dropdown-toggle"
                                                            type="button" data-bs-toggle="dropdown"
                                                            data-bs-boundary="viewport" aria-expanded="false">
                                                            Aksi
                                                        </button>

                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            <li>
                                                                <button class="dropdown-item edit-administrasi"
                                                                    data-id="{{ $administrasi->id }}"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#modalEditAdministrasi">
                                                                    Edit
                                                                </button>
                                                            </li>

                                                            <li>
                                                                @if ($administrasi->bukti_transfer)
                                                                    <a class="dropdown-item"
                                                                        href="{{ asset('storage/' . $administrasi->bukti_transfer) }}"
                                                                        target="_blank">
                                                                        Lihat Bukti Transfer
                                                                    </a>
                                                                @endif
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item"
                                                                    href="{{ route('administrasi.karyawan.edit', $administrasi->id) }}">
                                                                    Detail
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <form
                                                                    action="{{ route('administrasi.karyawan.destroy', $administrasi->id) }}"
                                                                    method="POST"
                                                                    onsubmit="return confirm('Yakin ingin menghapus administrasi ini?')">
                                                                    @csrf
                                                                    @method('DELETE')

                                                                    <button type="submit"
                                                                        class="dropdown-item text-danger">
                                                                        <i class="bx bx-trash me-2"></i> Hapus
                                                                    </button>
                                                                </form>
                                                            </li>

                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center py-5">
                                                    <div class="d-flex flex-column align-items-center">
                                                        <i class="bx bx-message-square-x text-muted"
                                                            style="font-size: 3rem;"></i>
                                                        <p class="text-muted mt-3 mb-0">Tidak ada administrasi untuk
                                                            ditampilkan
                                                        </p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
