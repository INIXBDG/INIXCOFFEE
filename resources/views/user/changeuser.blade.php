<!-- resources/views/user-dropdown.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>User Dropdown</title>
    <link href="{{ asset('assets/vendor/css/select2.min.css') }}" rel="stylesheet" />
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/select2/select2.min.js') }}"></script>
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
