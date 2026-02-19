@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
<script src="https://cdn.tailwindcss.com"></script>

<div class="container mx-auto py-8 px-4 max-w-2xl">

    <div class="mb-6">
        <h4 class="text-2xl font-bold text-gray-800 mb-2">
            <i class="fas fa-plus-circle mr-2 text-blue-500"></i> Tambah Aktivitas Harian Baru
        </h4>
        <a href="{{ route('daily-activities.index') }}" class="text-blue-500 hover:text-blue-700 text-sm">
            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar Aktivitas
        </a>
    </div>

    <form action="{{ route('daily-activities.store') }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-lg shadow-md space-y-4">
        @csrf

        <div>
            <label for="id_task" class="block text-gray-700 text-sm font-bold mb-2">Pilih Task Terkait <span class="text-red-500">*</span></label>
            <select name="id_task" id="id_task" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 @error('id_task') border-red-500 @enderror">
                <option value="">-- Pilih Task --</option>
                @foreach ($tasks as $task)
                    <option value="{{ $task->id }}" {{ old('id_task') == $task->id ? 'selected' : '' }}>
                        {{ $task->title }}
                    </option>
                @endforeach
            </select>
            @error('id_task')
                <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="activity" class="block text-gray-700 text-sm font-bold mb-2">Aktivitas yang Dilakukan <span class="text-red-500">*</span></label>
            <textarea name="activity" id="activity" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 @error('activity') border-red-500 @enderror" placeholder="Jelaskan secara singkat apa yang Anda kerjakan..." required>{{ old('activity') }}</textarea>
            @error('activity')
                <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Deskripsi Tambahan (Opsional)</label>
            <textarea name="description" id="description" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 @error('description') border-red-500 @enderror" placeholder="Berikan detail lebih lanjut jika diperlukan...">{{ old('description') }}</textarea>
            @error('description')
                <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="doc" class="block text-gray-700 text-sm font-bold mb-2">Unggah Dokumen (Opsional)</label>
            <input type="file" name="doc" id="doc" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 @error('doc') border-red-500 @enderror">
            <p class="text-xs text-gray-500 mt-1">Maksimal 2MB. Format: pdf, doc(x), xls(x), jpg, png.</p>
            @error('doc')
                <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="start_date" class="block text-gray-700 text-sm font-bold mb-2">Tanggal Mulai Aktivitas <span class="text-red-500">*</span></label>
            <input type="date" name="start_date" id="start_date" value="{{ old('start_date', now()->format('Y-m-d')) }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 @error('start_date') border-red-500 @enderror" required>
            @error('start_date')
                <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex justify-end pt-4 border-t">
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition duration-150">
                <i class="fas fa-save mr-1"></i> Simpan Aktivitas
            </button>
        </div>

    </form>
</div>
@endsection
