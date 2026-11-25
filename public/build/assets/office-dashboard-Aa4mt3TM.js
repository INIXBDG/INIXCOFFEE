function r(m,t){const i=document.querySelector(m);if(!i)return;const n=new IntersectionObserver(d=>{d[0].isIntersecting&&(t(),n.disconnect())},{threshold:.1});n.observe(i)}document.addEventListener("DOMContentLoaded",function(){const m=t=>new Intl.NumberFormat("id-ID",{style:"currency",currency:"IDR",minimumFractionDigits:0}).format(t).replace("Rp","Rp. ");r("#kehadiranChart",()=>{fetch("/office/api/dashboard/kehadiran").then(t=>t.json()).then(t=>{const i=document.getElementById("csr-kehadiran-avg"),n=document.getElementById("csr-kehadiran-total");if(i&&n){const a=t.kehadiranChart.data.reduce((o,c)=>o+c,0),s=t.kehadiranChart.data.length?(a/t.kehadiranChart.data.length).toFixed(1):0;i.textContent=s,n.textContent=t.total_karyawan}const d=document.getElementById("kehadiranChart");if(d){const a=Chart.getChart(d);a&&(a.data.labels=t.kehadiranChart.labels,a.data.datasets[0].data=t.kehadiranChart.data,a.update())}const e=document.getElementById("csr-tidak-hadir-container");if(e)if(t.tidakHadirList.length===0)e.outerHTML=`
                        <div class="text-center py-5">
                            <i class="bx bx-check-circle text-success" style="font-size: 4rem; opacity: 0.8;"></i>
                            <p class="text-success fw-bold mt-3 mb-0" style="font-size: 1.1rem;">Semua karyawan hadir hari ini!</p>
                            <small class="text-muted d-block mt-2">Kehadiran 100% 👏</small>
                        </div>`;else{let a='<div class="list-group list-group-flush">';t.tidakHadirList.forEach(s=>{const o=s.nama.substring(0,1).toUpperCase();a+=`
                            <div class="list-group-item bg-transparent px-0 py-3 border-bottom">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avatar avatar-sm bg-opacity-15 rounded-circle">
                                            <span class="text-danger fw-bold small">${o}</span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1 fw-medium text-dark">${s.nama}</h6>
                                        <small class="text-muted d-block">${s.divisi}</small>
                                        <span class="badge bg-warning text-dark mt-1">Tidak Hadir</span>
                                    </div>
                                </div>
                            </div>`}),a+=`</div><div class="mt-3 text-center"><small class="text-danger fw-medium">Total: ${t.tidakHadirList.length} karyawan</small></div>`,e.outerHTML=a}}).catch(t=>console.error(t))}),r("#csr-tagihan-container",()=>{fetch("/office/api/dashboard/tagihan").then(t=>t.json()).then(t=>{const i=document.getElementById("csr-tagihan-container"),n=document.getElementById("csr-tagihan-loading");if(!i)return;if(n&&n.remove(),t.trackingTagihanPerusahaans.length===0){i.innerHTML='<tr><td colspan="5" class="text-center text-muted py-4">Belum ada data tagihan.</td></tr>';return}let d="";t.trackingTagihanPerusahaans.forEach(e=>{let a="";e.status==="selesai"?a='<input class="custom-check" type="checkbox" checked disabled>':e.status==="telat"?a='<input class="custom-fail" type="checkbox" checked disabled>':a=`<input class="check-blue" data-id="${e.id}" type="checkbox" id="edit-tagihan">`;const o={pending:{color:"warning",icon:"bx-time-five"},proses:{color:"primary",icon:"bx-loader-circle"},selesai:{color:"success",icon:"bx-check-circle"},telat:{color:"danger",icon:"bx-info-circle"}}[e.status]||{color:"secondary",icon:"bx-info-circle"};let c="";e.tanggal_perkiraan_mulai===e.tanggal_perkiraan_selesai||!e.tanggal_perkiraan_selesai?c=new Date(e.tanggal_perkiraan_mulai).toLocaleDateString("id-ID",{day:"2-digit",month:"long"}):c=new Date(e.tanggal_perkiraan_selesai).toLocaleDateString("id-ID",{day:"2-digit",month:"short"});const l=e.tagihan_perusahaan?.kegiatan||e.kegiatan||"-",b=e.nominal?m(e.nominal):"-";d+=`
                    <tr class="border-bottom">
                        <td class="text-center ps-4">${a}</td>
                        <td><div class="small">${c}</div></td>
                        <td><div class="text-truncate" style="max-width: 150px;">${l}</div></td>
                        <td><span>${b}</span></td>
                        <td><div class="text-truncate" style="max-width: 300px;">${e.tracking||"-"}</div></td>
                        <td class="text-center pe-4">
                            <span class="badge bg-${o.color}-subtle text-${o.color} px-3 text-capitalize">
                                <i class="bx ${o.icon} me-1"></i> ${e.status}
                            </span>
                        </td>
                        <td class="text-center pe-4 position-relative">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">Aksi</button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><button class="dropdown-item" data-id="${e.id}" data-bs-toggle="modal" id="edit-tagihan" data-bs-target="#modalEditTagihan">Edit</button></li>
                                    <li><a class="dropdown-item" href="/tagihan-perusahaan/detail/${e.id}">Detail</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    `}),i.innerHTML=d}).catch(t=>console.error(t))}),r("#csr-admin-container",()=>{fetch("/office/api/dashboard/administrasi").then(t=>t.json()).then(t=>{const i=document.getElementById("csr-admin-container"),n=document.getElementById("csr-admin-loading");if(!i)return;if(n&&n.remove(),t.administrasis.length===0){i.innerHTML='<tr><td colspan="5" class="text-center text-muted py-4">Belum ada data administrasi.</td></tr>';return}let d="";t.administrasis.forEach(e=>{let a="";e.status==="selesai"?a='<input class="custom-check" type="checkbox" checked disabled>':e.status==="telat"?a='<input class="custom-fail" type="checkbox" checked disabled>':a=`<input class="check-blue" data-id="${e.id}" type="checkbox" id="edit-admin">`;const o={pending:{color:"warning",icon:"bx-time-five"},proses:{color:"primary",icon:"bx-loader-circle"},selesai:{color:"success",icon:"bx-check-circle"},telat:{color:"danger",icon:"bx-info-circle"}}[e.status]||{color:"secondary",icon:"bx-info-circle"},c=e.dateline?new Date(e.dateline).toLocaleDateString("id-ID",{day:"2-digit",month:"long"}):"-";d+=`
                    <tr class="border-bottom">
                        <td class="text-center ps-4">${a}</td>
                        <td><div class="small">${c}</div></td>
                        <td><div class="text-truncate" style="max-width: 200px;">${e.kegiatan||"-"}</div></td>
                        <td><div class="text-truncate" style="max-width: 300px;">${e.tracking||"-"}</div></td>
                        <td class="text-center pe-4">
                            <span class="badge bg-${o.color}-subtle text-${o.color} px-3 text-capitalize">
                                <i class="bx ${o.icon} me-1"></i> ${e.status}
                            </span>
                        </td>
                        <td class="text-center pe-4 position-relative">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">Aksi</button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><button class="dropdown-item" data-id="${e.id}" data-bs-toggle="modal" id="edit-admin" data-bs-target="#modalEditAdmin">Edit</button></li>
                                    <li><a class="dropdown-item" href="/administrasi-karyawan/detail/${e.id}">Detail</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    `}),i.innerHTML=d}).catch(t=>console.error(t))}),r("#csr-ticket-container",()=>{fetch("/office/api/dashboard/ticket").then(t=>t.json()).then(t=>{const i=document.getElementById("csr-ticket-container"),n=document.getElementById("csr-ticket-loading");if(!i)return;if(n&&n.remove(),t.ticket.length===0){i.innerHTML='<tr><td colspan="5" class="text-center text-muted py-4">Belum ada data ticket terbaru.</td></tr>';return}let d="";t.ticket.forEach(e=>{const s={Request:{color:"warning",icon:"bx-time-five"},"On Progress":{color:"primary",icon:"bx-loader-circle"},Selesai:{color:"success",icon:"bx-check-circle"}}[e.status]||{color:"secondary",icon:"bx-info-circle"},o=e.created_at?new Date(e.created_at).toLocaleDateString("id-ID",{day:"2-digit",month:"long",year:"numeric"}):"-";d+=`
                    <tr class="border-bottom">
                        <td class="ps-4">
                            <div class="fw-medium text-dark">${e.subject||"-"}</div>
                            <div class="small text-muted">${e.nama||"-"}</div>
                        </td>
                        <td><div class="small">${o}</div></td>
                        <td class="text-center pe-4">
                            <span class="badge bg-${s.color}-subtle text-${s.color} px-3 text-capitalize">
                                <i class="bx ${s.icon} me-1"></i> ${e.status}
                            </span>
                        </td>
                        <td class="text-center pe-4">
                            <a href="/ITSM/${e.id}" class="btn btn-sm btn-outline-primary">Detail</a>
                        </td>
                    </tr>
                    `}),i.innerHTML=d}).catch(t=>console.error(t))}),r("#total_karyawan_ui",()=>{fetch("/office/api/dashboard/karyawan").then(t=>t.json()).then(t=>{const i=document.getElementById("total_karyawan_ui");i&&(i.textContent=t.total_karyawan);const n=document.getElementById("divisi_stats_container"),d=document.getElementById("csr-modals-container");if(n&&t.divisiStats){let e="",a="";t.divisiStats.forEach((s,o)=>{e+=`
                        <div class="col-xl-3 col-md-6">
                            <div class="card border-0 shadow-sm h-100 hover-card rounded-3 overflow-hidden glass-force"
                                data-bs-toggle="modal" data-bs-target="#modalDivisi${o}" role="button"
                                tabindex="0">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="flex-shrink-0">
                                            <div class="avatar avatar-md bg-${s.color} bg-opacity-15 rounded-pill">
                                                <i class="${s.icon}" style="font-size: 1.5rem;color:white"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="text-muted mb-1 small text-uppercase tracking-wider">${s.nama}</h6>
                                            <h3 class="mb-0 fw-bold text-dark">${s.total}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        `;let c="";s.data&&s.data.length>0?s.data.forEach((l,b)=>{let h=l.foto?`/storage/foto_karyawan/${l.foto}`:"https://ui-avatars.com/api/?name="+encodeURIComponent(l.nama_karyawan)+"&background=random";c+=`
                                <tr>
                                    <td class="text-center">${b+1}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="${h}" alt="Foto" class="rounded-circle me-3 border" style="width: 40px; height: 40px; object-fit: cover;">
                                            <div>
                                                <h6 class="mb-0 fw-semibold text-dark">${l.nama_karyawan}</h6>
                                                <small class="text-muted">${l.email_kantor||"-"}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-light text-dark border">${l.jabatan||"-"}</span></td>
                                    <td class="text-center">
                                        <a href="/Karyawan/detail/${l.id}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                            <i class="bx bx-user me-1"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                                `}):c=`
                            <tr>
                                <td colspan="4">
                                    <div class="text-center py-5 text-muted">
                                        <i class="bx bx-user-x mb-3" style="font-size: 4rem; opacity: 0.5;"></i>
                                        <p class="mb-0 fw-medium">Belum ada data karyawan di divisi ini</p>
                                    </div>
                                </td>
                            </tr>
                            `,a+=`
                        <div class="modal fade" id="modalDivisi${o}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-xl modal-dialog-centered">
                                <div class="modal-content border-0 shadow-lg">
                                    <div class="modal-header bg-light border-bottom-0 pb-4 pt-4 px-4">
                                        <div>
                                            <h5 class="modal-title fw-bold text-dark mb-1 d-flex align-items-center">
                                                <i class="${s.icon} text-${s.color} me-2 fs-4"></i>
                                                Divisi ${s.nama}
                                            </h5>
                                            <p class="text-muted small mb-0">Total ${s.total} Karyawan Aktif</p>
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
                                                    ${c}
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        `}),n.innerHTML=e,d&&(d.innerHTML=a)}}).catch(t=>console.error(t))})});Object.assign(window,{runWhenVisible:r});
