<nav class="layout-navbar navbar navbar-expand-xl align-items-center" id="layout-navbar">
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="iconify bx bx-menu bx-sm" data-icon="bx:menu"></i>
        </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">

        <div class="navbar-nav align-items-center navbar-search">
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
                        <img src="{{ auth()->user()?->foto ?? asset('assets/img/avatars/1.png') }}" alt="User Avatar"
                            class="w-px-40 h-auto rounded-circle" id="userAvatarDropdown">
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end mt-2 py-2">
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2" href="#">
                            <div class="avatar avatar-xs">
                                <img src="{{ auth()->user()?->foto ?? asset('assets/img/avatars/1.png') }}"
                                    alt="User Avatar" class="rounded-circle">
                            </div>
                            <div class="d-flex flex-column">
                                <span class="fw-semibold small"
                                    id="userFullName">{{ auth()->user()?->nama_lengkap ?? auth()->user()?->username ?? 'Guest' }}</span>
                                <span class="small text-muted"
                                    id="userRole">{{ auth()->user()?->jabatan ?? 'Staff' }}</span>
                            </div>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider my-1"></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#">
                            <i class="iconify bx bx-user me-2" data-icon="bx:user"></i>
                            <span>My Profile</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#">
                            <i class="iconify bx bx-cog me-2" data-icon="bx:cog"></i>
                            <span>Settings</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#">
                            <i class="iconify bx bx-list-ul me-2" data-icon="bx:list-ul"></i>
                            <span>Activity Log</span>
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