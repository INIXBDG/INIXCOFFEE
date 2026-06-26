<nav class="layout-navbar navbar navbar-expand-xl align-items-center" id="layout-navbar">
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="iconify bx bx-menu bx-sm" data-icon="bx:menu"></i>
        </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">

        <div class="navbar-nav align-items-center">
            <div class="nav-item d-flex align-items-center">
                <i class="iconify bx bx-search fs-4 lh-0 text-muted me-2" data-icon="bx:search"></i>
                <input type="text" class="form-control border-0 shadow-none bg-transparent ps-1"
                    placeholder="Search..." aria-label="Search...">
            </div>
        </div>

        <ul class="navbar-nav flex-row align-items-center ms-auto">

            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        <img src="{{ auth()->user()->foto ?? asset('assets/img/avatars/1.png') }}" alt="User Avatar"
                            class="w-px-40 h-auto rounded-circle" id="userAvatarDropdown">
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end mt-2 py-2">
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2" href="#">
                            <div class="avatar avatar-xs">
                                <img src="{{ auth()->user()->foto ?? asset('assets/img/avatars/1.png') }}"
                                    alt="User Avatar" class="rounded-circle">
                            </div>
                            <div class="d-flex flex-column">
                                <span class="fw-semibold small"
                                    id="userFullName">{{ auth()->user()->nama_lengkap ?? auth()->user()->username }}</span>
                                <span class="small text-muted"
                                    id="userRole">{{ auth()->user()->jabatan ?? 'Staff' }}</span>
                            </div>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider my-1"></div>
                    </li>
                    <li>
                        <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#exampleModal">
                            <i class="iconify bx bx-user me-2" data-icon="bx:user"></i>
                            <span>My Profile</span>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider my-1"></div>
                    </li>
                    <li>
                        <a class="dropdown-item text-danger fw-medium" href="#" id="logoutButton">
                            <i class="iconify bx bx-log-out me-2" data-icon="bx:log-out"></i>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </li>

        </ul>
    </div>
</nav>
<div class="modal fade" id="exampleModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow">

            <div class="modal-header border-0">
                <h5 class="modal-title fw-semibold">
                    <i class="bi bi-person-circle me-2"></i>
                    Profil Akun
                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <!-- Header Profile -->
                <div class="text-center mb-4">

                    <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center"
                        style="width:60px;height:60px;">
                        <img src="{{ auth()->user()->foto ?? asset('assets/img/avatars/1.png') }}" alt="User Avatar"
                            class="w-px-1 h-auto rounded-circle">
                    </div>

                    <h4 class="mt-3 mb-1">
                        {{ auth()->user()->karyawan->nama_lengkap ?? auth()->user()->username }}
                    </h4>
                </div>

                <div class="border rounded-3 p-3">

                    <div class="row mb-3">
                        <div class="col-4 text-muted">
                            Username
                        </div>
                        <div class="col-8 fw-semibold">
                            {{ auth()->user()->username }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-4 text-muted">
                            Email
                        </div>
                        <div class="col-8 fw-semibold">
                            {{ auth()->user()->karyawan->email }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-4 text-muted">
                            Jabatan
                        </div>
                        <div class="col-8 fw-semibold">
                            {{ auth()->user()->karyawan->jabatan ?? '-' }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-4 text-muted">
                            Divisi
                        </div>
                        <div class="col-8 fw-semibold">
                            {{ auth()->user()->karyawan->divisi ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0">
                <button class="btn btn-primary w-30" data-bs-dismiss="modal">
                    Tutup
                </button>
            </div>

        </div>
    </div>
</div>