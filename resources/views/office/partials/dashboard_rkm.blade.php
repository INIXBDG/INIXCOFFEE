                                            @forelse ($rkms as $detail_rkm)
                                                @php
                                                    $checklists = $detail_rkm->checklists ?? [];
                                                    $rowspan = count($checklists) > 0 ? count($checklists) : 1;
                                                @endphp
                                                @if (count($checklists) > 0)
                                                    @foreach ($checklists as $tanggal => $item)
                                                        <tr class="border-bottom">

                                                            @if ($loop->first)
                                                                <td class="ps-4" rowspan="{{ $rowspan }}">
                                                                    {{ $loop->parent->iteration }}
                                                                </td>

                                                                <td rowspan="{{ $rowspan }}">
                                                                    {{ $detail_rkm->materi?->nama_materi ?? '-' }}
                                                                </td>

                                                                <td rowspan="{{ $rowspan }}">
                                                                    <span class="text-success fw-semibold">
                                                                        Rp
                                                                        {{ number_format($detail_rkm->harga_jual, 0, ',', '.') }}
                                                                    </span>
                                                                </td>

                                                                <td rowspan="{{ $rowspan }}">
                                                                    @if ($detail_rkm->tanggal_awal == $detail_rkm->tanggal_akhir)
                                                                        {{ \Carbon\Carbon::parse($detail_rkm->tanggal_awal)->translatedFormat('d M Y') }}
                                                                    @else
                                                                        {{ \Carbon\Carbon::parse($detail_rkm->tanggal_awal)->translatedFormat('d M Y') }}
                                                                        -
                                                                        {{ \Carbon\Carbon::parse($detail_rkm->tanggal_akhir)->translatedFormat('d M Y') }}
                                                                    @endif
                                                                </td>

                                                                <td rowspan="{{ $rowspan }}">
                                                                    @foreach ($detail_rkm->perusahaan as $perusahaan)
                                                                        {{ $perusahaan->nama_perusahaan }},
                                                                    @endforeach
                                                                </td>

                                                                <td rowspan="{{ $rowspan }}">
                                                                    {{ $detail_rkm->sales_all }}</td>
                                                                <td rowspan="{{ $rowspan }}">
                                                                    {{ implode(', ', array_filter([$detail_rkm->instruktur_key, $detail_rkm->instruktur_key2, $detail_rkm->asisten_key])) }}
                                                                </td>
                                                                <td rowspan="{{ $rowspan }}">
                                                                    {{ $detail_rkm->ruang ?? 'Belum Ditentukan' }}
                                                                </td>
                                                                <td rowspan="{{ $rowspan }}">
                                                                    <span
                                                                        class="badge bg-info-subtle text-info px-3 py-2">
                                                                        {{ number_format($detail_rkm->total_pax, 0, ',', '.') }}
                                                                    </span>
                                                                </td>

                                                                <td rowspan="{{ $rowspan }}">
                                                                    @if ($detail_rkm->exam == '1')
                                                                        <span
                                                                            class="badge bg-success-subtle text-success px-3 py-2">
                                                                            Ya
                                                                        </span>
                                                                    @else
                                                                        <span
                                                                            class="badge bg-secondary-subtle text-secondary px-3 py-2">
                                                                            Tidak
                                                                        </span>
                                                                    @endif
                                                                </td>

                                                                <td rowspan="{{ $rowspan }}">
                                                                    @php
                                                                        $makananList = $detail_rkm->makanan
                                                                            ? explode(', ', $detail_rkm->makanan)
                                                                            : [];
                                                                        $makananValue =
                                                                            count($makananList) > 0
                                                                                ? $makananList[0]
                                                                                : 'Tidak Ada';
                                                                    @endphp

                                                                    @if ($makananValue == '0' || $makananValue == 'Tidak Ada')
                                                                        Tidak Ada
                                                                    @elseif ($makananValue == '1' || $makananValue == 'Nasi Box')
                                                                        Nasi Box
                                                                    @elseif ($makananValue == '2' || $makananValue == 'Prasmanan')
                                                                        Prasmanan
                                                                    @else
                                                                        Belum Ditentukan
                                                                    @endif
                                                                </td>
                                                            @endif

                                                            <td class="text-center">
                                                                {{ \Carbon\Carbon::parse($tanggal)->format('d M') }}
                                                            </td>

                                                            <td class="text-center">
                                                                <input type="checkbox" class="custom-check"
                                                                    {{ $item->materi ? 'checked' : '' }} disabled>
                                                            </td>

                                                            <td class="text-center">
                                                                @if ($detail_rkm->metode_kelas === 'Offline')
                                                                    <input type="checkbox" class="custom-check"
                                                                        {{ $item->kelas ? 'checked' : '' }} disabled>
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>

                                                            <td class="text-center">
                                                                <input type="checkbox" class="custom-check"
                                                                    {{ $item->cb ? 'checked' : '' }} disabled>
                                                            </td>

                                                            <td class="text-center">
                                                                <input type="checkbox" class="custom-check"
                                                                    {{ $item->maksi ? 'checked' : '' }} disabled>
                                                            </td>

                                                            <td class="text-center">
                                                                @if ($detail_rkm->metode_kelas === 'Offline')
                                                                    <input type="checkbox" class="custom-check"
                                                                        {{ $item->keperluan_kelas ? 'checked' : '' }}
                                                                        disabled>
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>

                                                            <td class="text-center">
                                                                {{ $item->progress ?? 0 }}%
                                                            </td>
                                                            @if ($loop->first)
                                                                <td rowspan="{{ $rowspan }}"
                                                                    class="text-center align-middle">
                                                                    <a href="{{ route('export.pdf.checklist', $detail_rkm->id) }}"
                                                                        id="exportPdfRkm"
                                                                        class="btn btn-outline-danger btn-sm mb-1">
                                                                        PDF
                                                                    </a>
                                                                    <a href="{{ route('export.excel.checklist', $detail_rkm->id) }}"
                                                                        id="exportExcelRkm"
                                                                        class="btn btn-outline-success btn-sm">
                                                                        Excel
                                                                    </a>
                                                                </td>
                                                            @endif
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr class="border-bottom">
                                                        <td class="ps-4">{{ $loop->iteration }}</td>

                                                        <td>
                                                            {{ $detail_rkm->materi?->nama_materi ?? '-' }}
                                                        </td>

                                                        <td>
                                                            <span class="text-success fw-semibold">
                                                                Rp
                                                                {{ number_format($detail_rkm->harga_jual, 0, ',', '.') }}
                                                            </span>
                                                        </td>

                                                        <td>
                                                            @if ($detail_rkm->tanggal_awal == $detail_rkm->tanggal_akhir)
                                                                {{ \Carbon\Carbon::parse($detail_rkm->tanggal_awal)->translatedFormat('d M Y') }}
                                                            @else
                                                                {{ \Carbon\Carbon::parse($detail_rkm->tanggal_awal)->translatedFormat('d M Y') }}
                                                                -
                                                                {{ \Carbon\Carbon::parse($detail_rkm->tanggal_akhir)->translatedFormat('d M Y') }}
                                                            @endif
                                                        </td>

                                                        <td>
                                                            @foreach ($detail_rkm->perusahaan as $perusahaan)
                                                                {{ $perusahaan->nama_perusahaan }},
                                                            @endforeach
                                                        </td>

                                                        <td>{{ $detail_rkm->sales_all }}</td>
                                                        <td> {{ implode(', ', array_filter([$detail_rkm->instruktur_key, $detail_rkm->instruktur_key2, $detail_rkm->asisten_key])) }}
                                                        </td>
                                                        <td>{{ $detail_rkm->ruang ?? 'Belum Ditentukan' }}</td>
                                                        <td>
                                                            <span class="badge bg-info-subtle text-info px-3 py-2">
                                                                {{ number_format($detail_rkm->total_pax, 0, ',', '.') }}
                                                            </span>
                                                        </td>

                                                        <td>
                                                            @if ($detail_rkm->exam == '1')
                                                                <span
                                                                    class="badge bg-success-subtle text-success px-3 py-2">
                                                                    Ya
                                                                </span>
                                                            @else
                                                                <span
                                                                    class="badge bg-secondary-subtle text-secondary px-3 py-2">
                                                                    Tidak
                                                                </span>
                                                            @endif
                                                        </td>

                                                        <td>
                                                            @php
                                                                $makananList = $detail_rkm->makanan
                                                                    ? explode(', ', $detail_rkm->makanan)
                                                                    : [];
                                                                $makananValue =
                                                                    count($makananList) > 0
                                                                        ? $makananList[0]
                                                                        : 'Tidak Ada';
                                                            @endphp

                                                            @if ($makananValue == '0' || $makananValue == 'Tidak Ada')
                                                                Tidak Ada
                                                            @elseif ($makananValue == '1' || $makananValue == 'Nasi Box')
                                                                Nasi Box
                                                            @elseif ($makananValue == '2' || $makananValue == 'Prasmanan')
                                                                Prasmanan
                                                            @else
                                                                Belum Ditentukan
                                                            @endif
                                                        </td>

                                                        {{-- Kolom checklist kosong --}}
                                                        <td colspan="8" class="text-center text-muted">
                                                            Tidak ada checklist
                                                        </td>
                                                    </tr>
                                                @endif

                                            @empty
                                                <tr>
                                                    <td colspan="12" class="text-center py-5">
                                                        Tidak ada data
                                                    </td>
                                                </tr>
                                            @endforelse
