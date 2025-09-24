<!DOCTYPE html>
<html>
<head>
    <title>Detail Tiket</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Detail Tiket #{{ $ticket->id }}</h1>
        <p><strong>Nomor Pengirim:</strong> {{ $ticket->no_user }}</p>
        <p><strong>Deskripsi:</strong> {{ $ticket->deskripsi }}</p>
        <p><strong>Status:</strong> {{ $ticket->status }}</p>
        <p><strong>Catatan Resolusi:</strong> {{ $ticket->alasan ?? 'Tidak ada' }}</p>

        @if ($ticket->status == 'Di Proses')
            <form action="{{ route('tickets.finish', $ticket) }}" method="POST" class="mt-4">
                @csrf
                <label for="alasan" class="block">Catatan Resolusi:</label>
                <textarea name="alasan" class="w-full p-2 border rounded"></textarea>
                <button type="submit" class="bg-blue-500 text-white p-2 rounded mt-2">Selesai</button>
            </form>
            <form action="{{ route('tickets.block', $ticket) }}" method="POST" class="mt-4">
                @csrf
                <label for="alasan" class="block">Alasan Terkendala:</label>
                <textarea name="alasan" class="w-full p-2 border rounded"></textarea>
                <button type="submit" class="bg-red-500 text-white p-2 rounded mt-2">Tandai Terkendala</button>
            </form>
        @endif
    </div>
</body>
</html>