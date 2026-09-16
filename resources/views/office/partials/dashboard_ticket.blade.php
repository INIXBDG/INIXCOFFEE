                                            @forelse($ticket as $item)
                                                <tr class="border-bottom">
                                                    <td class="ps-4">
                                                        <div class="small">
                                                            {{ \Carbon\Carbon::parse($item->timestamp)->format('d M Y, H:i') }}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="text-truncate" style="max-width: 150px;"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ $item->nama_karyawan }}">
                                                                {{ $item->nama_karyawan }}
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-secondary-subtle text-secondary">
                                                            {{ $item->divisi }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="text-truncate" style="max-width: 120px;"
                                                            data-bs-toggle="tooltip" title="{{ $item->kategori }}">
                                                            <i class="bx bx-category text-muted me-1"></i>
                                                            {{ $item->kategori }}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="text-truncate" style="max-width: 200px;"
                                                            data-bs-toggle="tooltip" title="{{ $item->keperluan }}">
                                                            {{ $item->keperluan }}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="text-truncate" style="max-width: 250px;"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ $item->detail_kendala }}">
                                                            {{ $item->detail_kendala }}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="text-truncate" style="max-width: 100px;"
                                                                data-bs-toggle="tooltip" title="{{ $item->pic }}">
                                                                {{ $item->pic ?? '-' }}
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-center pe-4">
                                                        @php
                                                            $statusConfig = [
                                                                'Menunggu' => [
                                                                    'color' => 'warning',
                                                                    'icon' => 'bx-time-five',
                                                                ],
                                                                'Di Proses' => [
                                                                    'color' => 'primary',
                                                                    'icon' => 'bx-loader-circle',
                                                                ],
                                                                'Selesai' => [
                                                                    'color' => 'success',
                                                                    'icon' => 'bx-check-circle',
                                                                ],
                                                                'Terkendala' => [
                                                                    'color' => 'danger',
                                                                    'icon' => 'bx-error-circle',
                                                                ],
                                                            ];
                                                            $config = $statusConfig[$item->status] ?? [
                                                                'color' => 'secondary',
                                                                'icon' => 'bx-info-circle',
                                                            ];
                                                        @endphp
                                                        <span
                                                            class="badge bg-{{ $config['color'] }}-subtle text-{{ $config['color'] }} px-3 py-2">
                                                            <i class="bx {{ $config['icon'] }} me-1"></i>
                                                            {{ $item->status }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center py-5">
                                                        <div class="d-flex flex-column align-items-center">
                                                            <i class="bx bx-message-square-x text-muted"
                                                                style="font-size: 3rem;"></i>
                                                            <p class="text-muted mt-3 mb-0">Tidak ada ticket untuk
                                                                ditampilkan
                                                            </p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
