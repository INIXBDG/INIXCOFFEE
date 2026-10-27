@extends('layout_HR.app')

@section('content_HR')
    <div class="container-fluid px-4 py-4">
        <div class="card shadow-sm mx-auto" style="max-width:480px">
            <div class="card-body p-4">
                @if ($mode === 'denied')
                    <div class="text-center">
                        <div style="font-size:3rem;line-height:1">&#128274;</div>
                        <h5 class="mt-2">Akses Ditolak</h5>
                    </div>
                    <div class="alert alert-warning mt-3 mb-3">
                        Anda bukan HRD, mohon menghubungi pihak terkait atau Tim ITSM untuk menangani kondisi ini.
                    </div>
                    <a href="{{ route('HR.index') }}" class="btn btn-outline-secondary w-100">Kembali</a>
                @elseif ($mode === 'programmer')
                    <div class="text-center">
                        <i class="iconify" data-icon="mdi:lock-reset" data-width="48" data-height="48"></i>
                        <h5 class="mt-2 mb-1">Reset Password Master</h5>
                        <p class="text-muted small">
                            Khusus Programmer, dipakai bila HRD lupa password master Data Password.
                            Anda tidak dapat melihat isi data password.
                        </p>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('HR.data-password.reset-programmer') }}" autocomplete="off"
                        onsubmit="return confirm('Reset password master Data Password? Aksi ini tercatat di log.')">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Password master baru <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" minlength="8" maxlength="50"
                                autocomplete="new-password" required>
                            <div class="form-text">8 sampai 50 karakter.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ulangi password baru <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control"
                                autocomplete="new-password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password akun Anda (verifikasi) <span
                                    class="text-danger">*</span></label>
                            <input type="password" name="account_password" class="form-control"
                                autocomplete="current-password" required>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('HR.index') }}" class="btn btn-light flex-fill">Kembali</a>
                            <button type="submit" class="btn btn-danger flex-fill">Reset Password Master</button>
                        </div>
                    </form>
                @else
                    <div class="text-center">
                        <div style="font-size:3rem;line-height:1">&#128273;</div>
                        <h5 class="mt-2 mb-1">Buat Password Data Password</h5>
                        <p class="text-muted small">
                            Password ini dipakai setiap kali membuka halaman Data Password. Simpan baik-baik,
                            password ini tidak bisa dilihat kembali.
                        </p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('HR.data-password.setup.store') }}" autocomplete="off">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Password baru <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" minlength="8"
                                autocomplete="new-password" required autofocus>
                            <div class="form-text">Minimal 8 karakter.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ulangi password <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control"
                                autocomplete="new-password" required>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('HR.index') }}" class="btn btn-light flex-fill">Kembali</a>
                            <button type="submit" class="btn btn-primary flex-fill">Buat Password</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection
