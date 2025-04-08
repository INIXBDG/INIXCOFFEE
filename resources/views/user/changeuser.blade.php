<!-- resources/views/user-dropdown.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>User Dropdown</title>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>
<body>
    <h1>Pilih Pengguna</h1>
    <select id="user-select" class="form-control">
        <option value="">Pilih Pengguna</option>
        @foreach ($users as $user)
            <option value="{{ $user->id }}" data-jabatan="{{ $user->jabatan }}">{{ $user->username }}</option>
        @endforeach
    </select>

    <script>
        $(document).ready(function() {
            $('#user-select').on('change', function() {
                var selectedUserId = $(this).val();
                var selectedUserJabatan = $('#user-select option:selected').data('jabatan');
                if (selectedUserId) {
                    $.ajax({
                        url: '{{ route('change.user') }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            user_id: selectedUserId
                        },
                        success: function(response) {
                            if (response.status === 'success') {
                                window.location.href = response.redirect;
                            } else {
                                alert(response.message);
                            }
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>
