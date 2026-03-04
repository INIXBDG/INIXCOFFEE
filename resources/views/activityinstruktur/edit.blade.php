@extends('layouts.app')

@section('content')
{{-- Memuat Font Awesome untuk Ikon --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
{{-- Memuat Tailwind CSS (jika belum ada di layout) --}}
<script src="https://cdn.tailwindcss.com"></script>

<div class="container mx-auto py-8 px-4 max-w-2xl"> {{-- Batasi lebar kontainer --}}
    {{-- Header --}}
    <div class="mb-6">
        {{-- Ganti Ikon dan Judul --}}
        <h4 class="text-2xl font-bold text-gray-800 mb-2">
            <i class="fas fa-pencil-alt mr-2 text-yellow-500"></i> Edit Aktivitas Harian
        </h4>
        <a href="{{ route('daily-activities.index') }}" class="text-blue-500 hover:text-blue-700 text-sm">
            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar Aktivitas
        </a>
    </div>

    {{-- Form Edit Aktivitas --}}
    {{-- Ganti Action ke route update --}}
    <form action="{{ route('daily-activities.update', $dailyActivity->id) }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-lg shadow-md space-y-4">
        @csrf
        @method('PUT') {{-- Atau PATCH --}}

        {{-- 1. Pilih Task --}}
        <div>
            <label for="id_task" class="block text-gray-700 text-sm font-bold mb-2">Task Terkait</label>

            <select name="id_task" id="id_task"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">— Tidak Ada Task —</option>

                @foreach ($tasks as $task)
                    <option value="{{ $task->id }}"
                        {{ $dailyActivity->id_task == $task->id ? 'selected' : '' }}>
                        {{ $task->title }}
                    </option>
                @endforeach
            </select>

            @error('id_task')
                <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
            @enderror
        </div>


        {{-- 2. Aktivitas Utama --}}
        <div>
            <label for="activity" class="block text-gray-700 text-sm font-bold mb-2">Aktivitas yang Dilakukan <span class="text-red-500">*</span></label>
            {{-- Ganti old() agar mencakup data $dailyActivity --}}
            <textarea name="activity" id="activity" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 @error('activity') border-red-500 @enderror" placeholder="Jelaskan secara singkat apa yang Anda kerjakan..." required>{{ old('activity', $dailyActivity->activity) }}</textarea>
            @error('activity') <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p> @enderror
        </div>

        {{-- 3. Deskripsi Tambahan (Opsional) --}}
        <div>
            <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Deskripsi Tambahan (Opsional)</label>
             {{-- Ganti old() agar mencakup data $dailyActivity --}}
            <textarea name="description" id="description" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 @error('description') border-red-500 @enderror" placeholder="Berikan detail lebih lanjut jika diperlukan...">{{ old('description', $dailyActivity->description) }}</textarea>
            @error('description') <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p> @enderror
        </div>

        {{-- 4. Unggah Dokumen (Opsional) --}}
        <div>
            <label for="doc" class="block text-gray-700 text-sm font-bold mb-2">Ubah Dokumen (Opsional)</label>
            {{-- Tampilkan dokumen saat ini jika ada --}}
            @if($dailyActivity->doc)
            <div class="mb-2 text-sm">
                Dokumen saat ini:
                <a href="{{ Storage::url($dailyActivity->doc) }}" target="_blank" class="text-blue-500 hover:text-blue-700 ms-2 inline-flex items-center"> {{-- Tambah class --}}
                    <i class="fas fa-file-alt mr-1"></i> Gambar
                </a>
                {{-- Opsi untuk menghapus dokumen --}}
                <div class="mt-1"> {{-- Ganti form-check dengan div biasa --}}
                  <input class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mr-1" type="checkbox" name="remove_doc" value="1" id="remove_doc"> {{-- Style checkbox Tailwind --}}
                  <label class="text-sm text-red-600" for="remove_doc"> {{-- Ganti class label --}}
                    Hapus dokumen saat ini
                  </label>
                </div>
            </div>
            @endif
            <input type="file" name="doc" id="doc" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 @error('doc') border-red-500 @enderror">
            <p class="text-xs text-gray-500 mt-1">Biarkan kosong jika tidak ingin mengubah. Maks 2MB.</p>
            @error('doc') <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p> @enderror
        </div>

        {{-- 5. Tanggal Aktivitas --}}
        <div>
            <label for="activity_date" class="block text-gray-700 text-sm font-bold mb-2">Tanggal Aktivitas <span class="text-red-500">*</span></label>
            {{-- Ganti old() agar mencakup data $dailyActivity --}}
            <input type="date" name="activity_date" id="activity_date" value="{{ old('activity_date', optional($dailyActivity->activity_date)->format('Y-m-d')) }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 @error('activity_date') border-red-500 @enderror" required>
            @error('activity_date') <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p> @enderror
        </div>

        {{-- Tombol Submit --}}
        <div class="flex justify-end pt-4 border-t">
             {{-- Ganti Teks Tombol --}}
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition duration-150">
                <i class="fas fa-save mr-1"></i> Simpan Perubahan
            </button>
        </div>

    </form>
</div>
@endsection
