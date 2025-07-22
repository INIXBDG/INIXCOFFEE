<!DOCTYPE html>
<html>
<head>
    <title>Daftar Tiket</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Daftar Tiket</h1>
        @if (session('success'))
            <div class="bg-green-100 text-green-700 p-4 mb-4 rounded">
                {{ session('success') }}
            </div>
        @endif
        <table class="w-full bg-white shadow-md rounded">
            <thead>
                <tr class="bg-gray-200">
                    <th class="p-2">ID</th>
                    <th class="p-2">Nomor Pengirim</th>
                    <th class="p-2">Deskripsi</th>
                    <th class="p-2">Status</th>
                    <th class="p-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tickets as $ticket)
                    <tr>
                        <td class="p-2">{{ $ticket->id }}</td>
                        <td class="p-2">{{ $ticket->no_user }}</td>
                        <td class="p-2">{{ $ticket->deskripsi }}</td>
                        <td class="p-2">{{ $ticket->status }}</td>
                        <td class="p-2">
                            <a href="{{ route('tickets.show', $ticket) }}" class="text-blue-500">Detail</a>
                            @if ($ticket->status == 'Menunggu')
                                <form action="{{ route('tickets.accept', $ticket) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-green-500">Terima</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>