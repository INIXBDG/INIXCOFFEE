                                        @forelse($trackingTagihanPerusahaans as $tagihan)
                                            <tr class="border-bottom ">
                                                @if ($tagihan->status === 'selesai')
                                                    <td class="text-center ps-4"><input class="custom-check"
                                                            type="checkbox" checked disabled></td>
                                                @elseif ($tagihan->status === 'telat')
                                                    <td class="text-center ps-4"><input class="custom-fail"
                                                            type="checkbox" checked disabled></td>
                                                @else
                                                    <td class="text-center ps-4"><input class="check-blue"
                                                            data-id="{{ $tagihan->id }}" type="checkbox"
                                                            id="edit-tagihan"></td>
                                                @endif
                                                <td>
                                                    @if (
                                                        $tagihan->tanggal_perkiraan_mulai === $tagihan->tanggal_perkiraan_selesai ||
                                                            $tagihan->tanggal_perkiraan_selesai === null)
                                                        <div class="small">
                                                            {{ \Carbon\Carbon::parse($tagihan->tanggal_perkiraan_mulai)->format('d F') }}
                                                        </div>
                                                    @else
                                                        <div class="small">
                                                            {{ \Carbon\Carbon::parse($tagihan->tanggal_perkiraan_selesai)->format('d M') }}
                                                        </div>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="text-truncate" style="max-width: 150px;">
                                                            {{ $tagihan->tagihanPerusahaan?->kegiatan ?? $tagihan->kegiatan }}
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="">
                                                        {{ $tagihan->nominal ? 'Rp. ' . number_format($tagihan->nominal, 0, ',', '.') : '-' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="text-truncate" style="max-width: 300px;">
                                                        {{ $tagihan->tracking ?? '-' }}
                                                    </div>
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
                                                            'telat' => [
                                                                'color' => 'danger',
                                                                'icon' => 'bx-info-circle',
                                                            ],
                                                        ];
                                                        $config = $statusConfig[$tagihan->status] ?? [
                                                            'color' => 'secondary',
                                                            'icon' => 'bx-info-circle',
                                                        ];
                                                    @endphp
                                                    <span
                                                        class="badge bg-{{ $config['color'] }}-subtle text-{{ $config['color'] }} px-3 py-text-capitalize2 ">
                                                        <i class="bx {{ $config['icon'] }} me-1"></i>
                                                        {{ $tagihan->status }}
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
                                                                <button class="dropdown-item"
                                                                    data-id="{{ $tagihan->id }}" data-bs-toggle="modal"
                                                                    id="edit-tagihan" data-bs-target="#modalEditTagihan">
                                                                    Edit
                                                                </button>
                                                            </li>

                                                            <li>
                                                                <button
                                                                    class="dropdown-item text-success btn-ajukan-tagihan"
                                                                    data-id="{{ $tagihan->id }}">
                                                                    Ajukan Tagihan
                                                                </button>

                                                                <form id="form-ajukan-{{ $tagihan->id }}" method="POST"
                                                                    style="display:none;">
                                                                    @csrf
                                                                    @php
                                                                        $user = auth()->user();
                                                                        $karyawan = $user->karyawan;
                                                                    @endphp
                                                                    <input type="hidden" name="id_tagihan"
                                                                        value="{{ $tagihan->id }}">
                                                                    <input name="id_karyawan"
                                                                        value="{{ $karyawan->id }}">
                                                                    <input id="nama_karyawan" type="text"
                                                                        name="nama_karyawan"
                                                                        value="{{ $karyawan->nama_lengkap }}">
                                                                    <input id="divisi" type="text" name="divisi"
                                                                        value="{{ $karyawan->divisi }}">
                                                                    <input type="text" name="tipe"
                                                                        value="Tagihan Perusahaan">
                                                                    <input type="text" name="barang[nama_barang][]"
                                                                        value="{{ $tagihan->kegiatan ?? $tagihan->tagihanPerusahaan?->kegiatan }}">
                                                                    <input type="number" name="barang[qty][]"
                                                                        value="1">
                                                                    <input type="text" name="barang[harga_barang][]"
                                                                        value="{{ $tagihan->nominal ?? null }}">
                                                                    <input type="text" name="barang[keterangan][]"
                                                                        value="{{ $tagihan->keterangan ?? null }}">
                                                                </form>
                                                            </li>

                                                            <li>
                                                                <a class="dropdown-item"
                                                                    href="{{ route('detailTagihanPerusahaan', $tagihan->id) }}">
                                                                    Detail
                                                                </a>
                                                            </li>

                                                            <li>
                                                                <form
                                                                    action="{{ route('hapusTagihanPerusahaan', $tagihan->id) }}"
                                                                    method="POST"
                                                                    onsubmit="return confirm('Yakin ingin menghapus?')">
                                                                    @csrf
                                                                    <button type="submit"
                                                                        class="dropdown-item text-danger">
                                                                        Hapus
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
                                                        <p class="text-muted mt-3 mb-0">Tidak ada tagihan untuk
                                                            ditampilkan
                                                        </p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
