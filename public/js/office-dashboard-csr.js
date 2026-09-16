function runWhenVisible(selector, callback) {
    const el = document.querySelector(selector);
    if (!el) return;
    
    const observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting) {
            callback();
            observer.disconnect();
        }
    }, { threshold: 0.1 });
    observer.observe(el);
}

document.addEventListener('DOMContentLoaded', function() {

    // Helper formatter
    const formatRupiah = (angka) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(angka).replace('Rp', 'Rp. ');
    };

    // 0. Kehadiran Chart & Tidak Hadir
    runWhenVisible('#kehadiranChart', () => {
        fetch('/office/api/dashboard/kehadiran')
            .then(res => res.json())
            .then(data => {
                // Update average
                const avgEl = document.getElementById('csr-kehadiran-avg');
                const totalEl = document.getElementById('csr-kehadiran-total');
                if (avgEl && totalEl) {
                    const totalData = data.kehadiranChart.data.reduce((a, b) => a + b, 0);
                    const avg = data.kehadiranChart.data.length ? (totalData / data.kehadiranChart.data.length).toFixed(1) : 0;
                    avgEl.textContent = avg;
                    totalEl.textContent = data.total_karyawan;
                }

                // Update Chart (if Chart instance exists)
                // We search for the global chart instance. In dashboard.blade.php it was created on canvas.
                const canvas = document.getElementById('kehadiranChart');
                if (canvas) {
                    const chartInstance = Chart.getChart(canvas);
                    if (chartInstance) {
                        chartInstance.data.labels = data.kehadiranChart.labels;
                        chartInstance.data.datasets[0].data = data.kehadiranChart.data;
                        chartInstance.update();
                    }
                }

                // Update Tidak Hadir
                const thContainer = document.getElementById('csr-tidak-hadir-container');
                if (thContainer) {
                    if (data.tidakHadirList.length === 0) {
                        thContainer.outerHTML = `
                        <div class="text-center py-5">
                            <i class="bx bx-check-circle text-success" style="font-size: 4rem; opacity: 0.8;"></i>
                            <p class="text-success fw-bold mt-3 mb-0" style="font-size: 1.1rem;">Semua karyawan hadir hari ini!</p>
                            <small class="text-muted d-block mt-2">Kehadiran 100% 👏</small>
                        </div>`;
                    } else {
                        let html = '<div class="list-group list-group-flush">';
                        data.tidakHadirList.forEach(item => {
                            const initial = item.nama.substring(0, 1).toUpperCase();
                            html += `
                            <div class="list-group-item bg-transparent px-0 py-3 border-bottom">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avatar avatar-sm bg-opacity-15 rounded-circle">
                                            <span class="text-danger fw-bold small">${initial}</span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1 fw-medium text-dark">${item.nama}</h6>
                                        <small class="text-muted d-block">${item.divisi}</small>
                                        <span class="badge bg-warning text-dark mt-1">Tidak Hadir</span>
                                    </div>
                                </div>
                            </div>`;
                        });
                        html += `</div><div class="mt-3 text-center"><small class="text-danger fw-medium">Total: ${data.tidakHadirList.length} karyawan</small></div>`;
                        thContainer.outerHTML = html; // replace the spinner
                    }
                }
            })
            .catch(err => console.error(err));
    });

    // 1. Tagihan Perusahaan
    runWhenVisible('#csr-tagihan-container', () => {
        fetch('/office/api/dashboard/tagihan')
            .then(res => res.json())
            .then(data => {
                const container = document.getElementById('csr-tagihan-container');
                const loading = document.getElementById('csr-tagihan-loading');
                if (!container) return;
                if (loading) loading.remove();

                if (data.trackingTagihanPerusahaans.length === 0) {
                    container.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">Belum ada data tagihan.</td></tr>';
                    return;
                }

                let html = '';
                data.trackingTagihanPerusahaans.forEach(tagihan => {
                    let checkboxHTML = '';
                    if (tagihan.status === 'selesai') {
                        checkboxHTML = '<input class="custom-check" type="checkbox" checked disabled>';
                    } else if (tagihan.status === 'telat') {
                        checkboxHTML = '<input class="custom-fail" type="checkbox" checked disabled>';
                    } else {
                        checkboxHTML = `<input class="check-blue" data-id="${tagihan.id}" type="checkbox" id="edit-tagihan">`;
                    }

                    // Status config
                    const statusConfig = {
                        'pending': { color: 'warning', icon: 'bx-time-five' },
                        'proses': { color: 'primary', icon: 'bx-loader-circle' },
                        'selesai': { color: 'success', icon: 'bx-check-circle' },
                        'telat': { color: 'danger', icon: 'bx-info-circle' }
                    };
                    const config = statusConfig[tagihan.status] || { color: 'secondary', icon: 'bx-info-circle' };

                    let tanggalHTML = '';
                    if (tagihan.tanggal_perkiraan_mulai === tagihan.tanggal_perkiraan_selesai || !tagihan.tanggal_perkiraan_selesai) {
                        const date = new Date(tagihan.tanggal_perkiraan_mulai);
                        tanggalHTML = date.toLocaleDateString('id-ID', { day: '2-digit', month: 'long' });
                    } else {
                        const date = new Date(tagihan.tanggal_perkiraan_selesai);
                        tanggalHTML = date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' });
                    }

                    const kegiatan = tagihan.tagihan_perusahaan?.kegiatan || tagihan.kegiatan || '-';
                    const nominal = tagihan.nominal ? formatRupiah(tagihan.nominal) : '-';

                    html += `
                    <tr class="border-bottom">
                        <td class="text-center ps-4">${checkboxHTML}</td>
                        <td><div class="small">${tanggalHTML}</div></td>
                        <td><div class="text-truncate" style="max-width: 150px;">${kegiatan}</div></td>
                        <td><span>${nominal}</span></td>
                        <td><div class="text-truncate" style="max-width: 300px;">${tagihan.tracking || '-'}</div></td>
                        <td class="text-center pe-4">
                            <span class="badge bg-${config.color}-subtle text-${config.color} px-3 text-capitalize">
                                <i class="bx ${config.icon} me-1"></i> ${tagihan.status}
                            </span>
                        </td>
                        <td class="text-center pe-4 position-relative">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">Aksi</button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><button class="dropdown-item" data-id="${tagihan.id}" data-bs-toggle="modal" id="edit-tagihan" data-bs-target="#modalEditTagihan">Edit</button></li>
                                    <li><a class="dropdown-item" href="/tagihan-perusahaan/detail/${tagihan.id}">Detail</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    `;
                });
                container.innerHTML = html;
            })
            .catch(err => console.error(err));
    });

    // 2. Administrasi
    runWhenVisible('#csr-admin-container', () => {
        fetch('/office/api/dashboard/administrasi')
            .then(res => res.json())
            .then(data => {
                const container = document.getElementById('csr-admin-container');
                const loading = document.getElementById('csr-admin-loading');
                if (!container) return;
                if (loading) loading.remove();

                if (data.administrasis.length === 0) {
                    container.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">Belum ada data administrasi.</td></tr>';
                    return;
                }

                let html = '';
                data.administrasis.forEach(admin => {
                    let checkboxHTML = '';
                    if (admin.status === 'selesai') {
                        checkboxHTML = '<input class="custom-check" type="checkbox" checked disabled>';
                    } else if (admin.status === 'telat') {
                        checkboxHTML = '<input class="custom-fail" type="checkbox" checked disabled>';
                    } else {
                        checkboxHTML = `<input class="check-blue" data-id="${admin.id}" type="checkbox" id="edit-admin">`;
                    }

                    const statusConfig = {
                        'pending': { color: 'warning', icon: 'bx-time-five' },
                        'proses': { color: 'primary', icon: 'bx-loader-circle' },
                        'selesai': { color: 'success', icon: 'bx-check-circle' },
                        'telat': { color: 'danger', icon: 'bx-info-circle' }
                    };
                    const config = statusConfig[admin.status] || { color: 'secondary', icon: 'bx-info-circle' };

                    const dateline = admin.dateline ? new Date(admin.dateline).toLocaleDateString('id-ID', { day: '2-digit', month: 'long' }) : '-';

                    html += `
                    <tr class="border-bottom">
                        <td class="text-center ps-4">${checkboxHTML}</td>
                        <td><div class="small">${dateline}</div></td>
                        <td><div class="text-truncate" style="max-width: 200px;">${admin.kegiatan || '-'}</div></td>
                        <td><div class="text-truncate" style="max-width: 300px;">${admin.tracking || '-'}</div></td>
                        <td class="text-center pe-4">
                            <span class="badge bg-${config.color}-subtle text-${config.color} px-3 text-capitalize">
                                <i class="bx ${config.icon} me-1"></i> ${admin.status}
                            </span>
                        </td>
                        <td class="text-center pe-4 position-relative">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">Aksi</button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><button class="dropdown-item" data-id="${admin.id}" data-bs-toggle="modal" id="edit-admin" data-bs-target="#modalEditAdmin">Edit</button></li>
                                    <li><a class="dropdown-item" href="/administrasi-karyawan/detail/${admin.id}">Detail</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    `;
                });
                container.innerHTML = html;
            })
            .catch(err => console.error(err));
    });

    // 3. Ticket
    runWhenVisible('#csr-ticket-container', () => {
        fetch('/office/api/dashboard/ticket')
            .then(res => res.json())
            .then(data => {
                const container = document.getElementById('csr-ticket-container');
                const loading = document.getElementById('csr-ticket-loading');
                if (!container) return;
                if (loading) loading.remove();

                if (data.ticket.length === 0) {
                    container.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">Belum ada data ticket terbaru.</td></tr>';
                    return;
                }

                let html = '';
                data.ticket.forEach(item => {
                    const statusConfig = {
                        'Request': { color: 'warning', icon: 'bx-time-five' },
                        'On Progress': { color: 'primary', icon: 'bx-loader-circle' },
                        'Selesai': { color: 'success', icon: 'bx-check-circle' }
                    };
                    const config = statusConfig[item.status] || { color: 'secondary', icon: 'bx-info-circle' };

                    const date = item.created_at ? new Date(item.created_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }) : '-';

                    html += `
                    <tr class="border-bottom">
                        <td class="ps-4">
                            <div class="fw-medium text-dark">${item.subject || '-'}</div>
                            <div class="small text-muted">${item.nama || '-'}</div>
                        </td>
                        <td><div class="small">${date}</div></td>
                        <td class="text-center pe-4">
                            <span class="badge bg-${config.color}-subtle text-${config.color} px-3 text-capitalize">
                                <i class="bx ${config.icon} me-1"></i> ${item.status}
                            </span>
                        </td>
                        <td class="text-center pe-4">
                            <a href="/ITSM/${item.id}" class="btn btn-sm btn-outline-primary">Detail</a>
                        </td>
                    </tr>
                    `;
                });
                container.innerHTML = html;
            })
            .catch(err => console.error(err));
    });

    // 4. Karyawan & Divisi Stats
    runWhenVisible('#total_karyawan_ui', () => {
        fetch('/office/api/dashboard/karyawan')
            .then(res => res.json())
            .then(data => {
                const totalUi = document.getElementById('total_karyawan_ui');
                if (totalUi) totalUi.textContent = data.total_karyawan;

                const container = document.getElementById('divisi_stats_container');
                const modalsContainer = document.getElementById('csr-modals-container');
                
                if (container && data.divisiStats) {
                    let html = '';
                    let modalsHtml = '';
                    
                    data.divisiStats.forEach((divisi, index) => {
                        html += `
                        <div class="col-xl-3 col-md-6">
                            <div class="card border-0 shadow-sm h-100 hover-card rounded-3 overflow-hidden glass-force"
                                data-bs-toggle="modal" data-bs-target="#modalDivisi${index}" role="button"
                                tabindex="0">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="flex-shrink-0">
                                            <div class="avatar avatar-md bg-${divisi.color} bg-opacity-15 rounded-pill">
                                                <i class="${divisi.icon}" style="font-size: 1.5rem;color:white"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="text-muted mb-1 small text-uppercase tracking-wider">${divisi.nama}</h6>
                                            <h3 class="mb-0 fw-bold text-dark">${divisi.total}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        `;

                        // Build Modal HTML
                        let tbodyHtml = '';
                        if (divisi.data && divisi.data.length > 0) {
                            divisi.data.forEach((karyawan, kIndex) => {
                                let fotoUrl = karyawan.foto ? `/storage/foto_karyawan/${karyawan.foto}` : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(karyawan.nama_karyawan) + '&background=random';
                                tbodyHtml += `
                                <tr>
                                    <td class="text-center">${kIndex + 1}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="${fotoUrl}" alt="Foto" class="rounded-circle me-3 border" style="width: 40px; height: 40px; object-fit: cover;">
                                            <div>
                                                <h6 class="mb-0 fw-semibold text-dark">${karyawan.nama_karyawan}</h6>
                                                <small class="text-muted">${karyawan.email_kantor || '-'}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-light text-dark border">${karyawan.jabatan || '-'}</span></td>
                                    <td class="text-center">
                                        <a href="/Karyawan/detail/${karyawan.id}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                            <i class="bx bx-user me-1"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                                `;
                            });
                        } else {
                            tbodyHtml = `
                            <tr>
                                <td colspan="4">
                                    <div class="text-center py-5 text-muted">
                                        <i class="bx bx-user-x mb-3" style="font-size: 4rem; opacity: 0.5;"></i>
                                        <p class="mb-0 fw-medium">Belum ada data karyawan di divisi ini</p>
                                    </div>
                                </td>
                            </tr>
                            `;
                        }

                        modalsHtml += `
                        <div class="modal fade" id="modalDivisi${index}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-xl modal-dialog-centered">
                                <div class="modal-content border-0 shadow-lg">
                                    <div class="modal-header bg-light border-bottom-0 pb-4 pt-4 px-4">
                                        <div>
                                            <h5 class="modal-title fw-bold text-dark mb-1 d-flex align-items-center">
                                                <i class="${divisi.icon} text-${divisi.color} me-2 fs-4"></i>
                                                Divisi ${divisi.nama}
                                            </h5>
                                            <p class="text-muted small mb-0">Total ${divisi.total} Karyawan Aktif</p>
                                        </div>
                                        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-0">
                                        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                            <table class="table table-hover align-middle mb-0">
                                                <thead class="table-light sticky-top">
                                                    <tr>
                                                        <th class="text-center border-0" width="5%">No</th>
                                                        <th class="border-0" width="40%">Karyawan</th>
                                                        <th class="border-0" width="35%">Jabatan</th>
                                                        <th class="text-center border-0" width="20%">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    ${tbodyHtml}
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        `;
                    });

                    container.innerHTML = html;
                    if (modalsContainer) {
                        modalsContainer.innerHTML = modalsHtml;
                    }
                }
            })
            .catch(err => console.error(err));
    });
});
