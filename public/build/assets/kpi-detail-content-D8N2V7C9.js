(function(T){const f=i=>(Number(i)||0).toFixed(1),R=i=>(Number(i)||0).toFixed(2),j=i=>String(i??"").replace(/[&<>"']/g,a=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"})[a]),M=["Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"],D=i=>{if(!i)return"-";const a=String(i).split("-");return a.length<2?i:M[parseInt(a[1],10)-1]||i},O={personal:{manualRoutes:["dorong inovasi pelayanan","inisiatif efisiensi keuangan","mengurangi manual work dan error","pengeluaran biaya karyawan"],rupiahRoutes:["Pemasukan Kotor","meningkatkan revenue perusahaan"],trafikRoutes:["target penjualan tahunan","Pemasukan Kotor"],updateTargetUrl:()=>T.KPI_CONFIG.updateTargetPerSalesRoute},index:{manualRoutes:["dorong inovasi pelayanan","inisiatif efisiensi keuangan","mengurangi manual work dan error"],rupiahRoutes:["pemasukan kotor","meningkatkan revenue perusahaan","target penjualan tahunan","target penjualan project tahunan","pendapatan penjualan project"],trafikRoutes:["target penjualan tahunan","pemasukan kotor"],updateTargetUrl:()=>T.KPI_CONFIG.updateTargetPerSales}},Y=i=>new Intl.NumberFormat("id-ID",{style:"currency",currency:"IDR",minimumFractionDigits:0}).format(i||0),Z=(i=0)=>"Rp "+Number(i||0).toLocaleString("id-ID"),G=i=>i==="index"?Z:Y,V=(i,a)=>i==="index"?`${T.KPI_CONFIG.storageBase}/${a||""}`:`/storage/${a||""}`,W=i=>{if(!i)return"-";const a=new Date(i);return isNaN(a)?"-":a.toLocaleDateString("id-ID",{day:"2-digit",month:"short"})},Q="peserta puas dengan pelayanan dan fasilitas training",J=i=>String(i||"").toLowerCase().trim()===Q;function aa(i){const a=Array.isArray(i.karyawan)?i.karyawan.filter(r=>r):i.karyawan?[i.karyawan]:[];let s=1;const t=a.map(r=>`<div class="d-flex align-items-center py-2 participant-item"><div class="avatar me-3">${s++}</div><div class="flex-grow-1"><div class="fw-semibold text-dark small">${r.nama_lengkap||"-"}</div><div class="text-muted small">${r.jabatan||"-"}</div></div></div>`).join("")||'<div class="text-muted small">Tidak ada karyawan</div>';return{list:a,html:t}}function z(i,a){var m;const s=M,t=((m=i.previous_quarter)==null?void 0:m.data)||[],r=o=>a==="index"?`download="${o?"true":"false"}"`:o?"download":"",n=a==="index"?"menu-icon ":"";return`<div class="mt-4"><div class="row g-4">${t.map((o,c)=>`<div class="col-12 col-md-6 col-lg-4"><div class="card border-0 shadow-sm h-100 quarter-card"><div class="card-body d-flex flex-column p-4"><div class="d-flex justify-content-between align-items-start mb-3"><div><h6 class="fw-semibold text-muted mb-1">Periode</h6><h5 class="fw-bold mb-0">${s[(o.month||1)-1]??"-"}</h5></div><span class="badge rounded-pill bg-${o.color||"secondary"} bg-opacity-10 text-${o.color||"secondary"} px-3 py-2">Laporan</span></div><div class="mb-3"><h3 class="fw-bold text-dark mb-0">Rp ${o.nilai?Number(o.nilai).toLocaleString("id-ID"):"-"}</h3><small class="text-muted">Total Pemasukan</small></div><div class="flex-grow-1"><p class="text-muted small mb-2 description-text" id="desc-${c}">${o.description??"-"}</p>${o.description&&o.description.length>100?`<button class="btn btn-sm btn-link p-0 text-primary btn-toggle-desc" data-target="desc-${c}">Lihat Selengkapnya</button>`:""}</div><div class="d-flex justify-content-end align-items-center mt-4"><a href="${V(a,o.file_paths)}" class="btn btn-sm btn-dark d-flex align-items-center gap-2" ${r(o.file_paths)}><i class="${n}fa-solid fa-download"></i> Download</a></div></div></div></div>`).join("")}</div></div><style>.quarter-card { border-radius: 16px; transition: all 0.25s ease; background: #ffffff; }.quarter-card:hover { transform: translateY(-6px) scale(1.01); box-shadow: 0 15px 35px rgba(0,0,0,0.08); }.quarter-card h3 { letter-spacing: 0.5px; }.quarter-card .badge { font-size: 12px; font-weight: 500; }.description-text { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }.description-text.expanded { -webkit-line-clamp: unset; overflow: visible; }.quarter-card .btn { border-radius: 8px; font-size: 13px; }</style>`}function ta(i){var s,t,r,n;const a=i||{};return`<div class="row g-4 mt-1"><div class="col-lg-4"><div class="card shadow-sm border-0 rounded-4 h-100"><div class="card-body"><h6 class="fw-semibold text-secondary mb-3">RINGKASAN</h6><div class="d-flex justify-content-between mb-2"><span class="text-muted">Total Feedback</span><span class="fw-bold">${a.total_feedback??0}</span></div><div class="d-flex justify-content-between mb-2"><span class="text-muted">Total Sesi</span><span class="fw-bold">${a.total_sessions??0}</span></div><div class="d-flex justify-content-between"><span class="text-muted">Prediksi</span><span class="fw-bold text-primary">${a.prediction??0}</span></div></div></div></div><div class="col-lg-4"><div class="card shadow-sm border-0 rounded-4 h-100"><div class="card-body"><h6 class="fw-semibold text-secondary mb-3">PERFORMA</h6><div class="mb-3"><small class="text-muted d-block">Top Performer</small><span class="fw-bold text-success">${((s=a.top_performer)==null?void 0:s.label)??"-"} (${((t=a.top_performer)==null?void 0:t.value)??0})</span></div><div><small class="text-muted d-block">Lowest Performer</small><span class="fw-bold text-danger">${((r=a.lowest_performer)==null?void 0:r.label)??"-"} (${((n=a.lowest_performer)==null?void 0:n.value)??0})</span></div></div></div></div><div class="col-lg-4"><div class="card shadow-sm border-0 rounded-4 h-100"><div class="card-body"><h6 class="fw-semibold text-secondary mb-3">STATUS</h6><div class="mb-2"><span class="badge bg-${a.trend==="up"?"success":a.trend==="down"?"danger":"secondary"}">Trend: ${a.trend||"-"} (${f(a.trend_value)})</span></div><div class="mb-2"><span class="badge bg-${a.consistency==="stable"?"success":"warning"}">Konsistensi: ${a.consistency||"-"}</span></div><div><span class="badge bg-${a.target_status==="on_track"?"success":a.target_status==="at_risk"?"warning":"danger"}">Target: ${a.target_status||"-"}</span></div></div></div></div><div class="col-12"><div class="card shadow-sm border-0 rounded-4"><div class="card-body"><h6 class="fw-semibold text-secondary mb-3">KATEGORI PENILAIAN</h6><div class="row text-center">${Object.entries(a.category_scores||{}).map(([m,o])=>`<div class="col"><div class="p-2 border rounded-3"><small class="text-muted d-block">${m}</small><h5 class="fw-bold mb-0">${o??0}</h5></div></div>`).join("")}</div></div></div></div><div class="col-12"><div class="alert alert-info rounded-4 shadow-sm mb-0"><i class="fa-solid fa-lightbulb me-2"></i>${a.insight??"-"}</div></div></div>`}function sa(i){const a=i.class_breakdown||{},s=Number(a.internal)||0,t=Number(a.freelance)||0,r=s+t,n=Number(a.target)||0,m=Math.max(0,n-s),o=Math.min(100,Number(i.progress)||0),c=Array.isArray(a.per_bulan)?a.per_bulan:[],v=Array.isArray(a.per_instruktur)?a.per_instruktur:[],e=new Date,d=e.getFullYear()+"-"+String(e.getMonth()+1).padStart(2,"0"),p=c.filter(u=>u.bulan<=d),h=p.length?s/p.length:0,x=[...p].sort((u,y)=>y.internal-u.internal),b=x[0],k=x[x.length-1],C=Math.max(1,...c.map(u=>u.internal+u.freelance)),P=Math.max(1,...v.map(u=>u.jumlah)),_=n/12,g=(u,y,N,K)=>`
            <div class="col-6 col-lg-3">
                <div class="p-3 rounded-3 bg-white border h-100">
                    <div class="text-muted small mb-1">${u}</div>
                    <div class="fs-3 fw-bold text-${K}">${y}</div>
                    <div class="text-muted small">${N}</div>
                </div>
            </div>`,l=c.map(u=>{const y=u.internal+u.freelance,N=u.internal/C*100,K=u.freelance/C*100,E=_>0?u.internal/_*100:0,w=E>=100?"text-success":"text-danger";return`
                <tr>
                    <td class="fw-semibold">${j(D(u.bulan))}</td>
                    <td class="text-center">${u.internal}</td>
                    <td class="text-center">${u.freelance}</td>
                    <td class="text-center fw-semibold">${y}</td>
                    <td style="min-width:180px;">
                        <div class="progress" style="height:10px;">
                            <div class="progress-bar bg-primary" style="width:${N}%"></div>
                            <div class="progress-bar bg-secondary" style="width:${K}%"></div>
                        </div>
                    </td>
                    <td class="text-center fw-semibold ${w}">${f(E)}%</td>
                </tr>`}).join("");return v.map((u,y)=>`
            <tr>
                <td class="text-center">${y+1}</td>
                <td class="fw-semibold">${j(u.nama)}</td>
                <td class="text-center">${u.jumlah}</td>
                <td style="min-width:160px;">
                    <div class="progress" style="height:10px;">
                        <div class="progress-bar bg-success" style="width:${u.jumlah/P*100}%"></div>
                    </div>
                </td>
                <td class="text-center">${f(u.persen)}%</td>
            </tr>`).join(""),`
        <div class="card mt-4 border-0 rounded-4" style="background:#f8fafc;">
            <div class="card-body p-4">
                <h6 class="fw-semibold mb-1"><i class="fa-solid fa-chalkboard-user me-2 text-primary"></i>Kontribusi Kelas yang Dibawakan Instruktur Inixindo</h6>
                <small class="text-muted d-block mb-3">Kelas yang dibawakan orang luar tidak dihitung dalam pencapaian target.</small>

                <div class="row g-3 mb-4">
                    ${g("Seluruh Kelas",r,"Inixindo + orang luar","dark")}
                    ${g("Kelas Instruktur Inixindo",s,"Dihitung untuk target","primary")}
                    ${g("Kelas Orang Luar",t,"Tidak dihitung untuk target","secondary")}
                    ${g("Sisa Menuju Target",m,`Target ${n} kelas`,m===0?"success":"danger")}
                </div>

                <div class="p-3 rounded-3 bg-white border mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="small fw-semibold">Pencapaian target tahun ini</span>
                        <span class="small fw-bold text-primary">${s} dari ${n} kelas (${f(o)}%)</span>
                    </div>
                    <div class="progress" style="height:14px;">
                        <div class="progress-bar bg-primary" style="width:${o}%"></div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    ${g("Rata-rata per Bulan",f(h),`Kelas Inixindo, patokan target ${f(_)} per bulan`,"info")}
                    ${g("Bulan Tertinggi",b?b.internal:"-",b?j(D(b.bulan)):"-","success")}
                    ${g("Bulan Terendah",k?k.internal:"-",k?j(D(k.bulan)):"-","danger")}
                    ${g("Instruktur Terlibat",v.filter(u=>u.jumlah>0).length,`Dari ${v.length} peserta target`,"warning")}
                </div>

                <div class="p-3 rounded-3 bg-white border mb-4">
                    <div class="fw-semibold mb-1">Jumlah Kelas Tiap Bulan</div>
                    <div class="small text-muted mb-3">
                        <span class="badge bg-primary me-1">&nbsp;</span>Instruktur Inixindo
                        <span class="badge bg-secondary ms-3 me-1">&nbsp;</span>Orang luar
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Bulan</th>
                                    <th class="text-center">Kelas Inixindo</th>
                                    <th class="text-center">Kelas Orang Luar</th>
                                    <th class="text-center">Seluruhnya</th>
                                    <th>Perbandingan</th>
                                    <th class="text-center">Pencapaian Bulan Ini</th>
                                </tr>
                            </thead>
                            <tbody>${l||'<tr><td colspan="6" class="text-center text-muted">Belum ada data kelas</td></tr>'}</tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <style>
            .kp-scroll { max-height: 360px; overflow-y: scroll; overflow-x: auto; overscroll-behavior: contain; scrollbar-width: thin; scrollbar-color: #adb5bd #f1f3f5; }
            .kp-scroll::-webkit-scrollbar { width: 10px; height: 10px; }
            .kp-scroll::-webkit-scrollbar-track { background: #f1f3f5; border-radius: 8px; }
            .kp-scroll::-webkit-scrollbar-thumb { background: #adb5bd; border-radius: 8px; }
            .kp-scroll thead th { position: sticky; top: 0; z-index: 1; background: #f8f9fa; }
        </style>`}function ea(i,a,s,t){const r=O[t],n=G(t),m=t==="index"?"menu-icon ":"";if(r.trafikRoutes.includes(s)){const o=a.sales_performance||{};if(!o.data)return"";if(o.type==="individual"){const c=o.data,v=c.status==="achieved"?"badge-success":"badge-warning",e=c.status==="achieved"?"#28a745":"#ffc107",d=Math.min(c.percentage??0,100);return`<div class="card shadow-sm mb-4 mt-2"><div class="card-body"><div class="row"><div class="col-md-6"><p class="mb-2"><strong>Sales:</strong> ${c.nama||"-"}</p><p class="mb-2"><strong>Revenue:</strong> ${n(c.revenue)}</p><p class="mb-3"><strong>Target:</strong> ${n(c.presentase_kemampuan)}</p></div><div class="col-md-6"><div class="mb-2"><div class="d-flex justify-content-between"><span>Progress</span><span>${f(c.percentage)}%</span></div><div class="progress" style="height: 10px;"><div class="progress-bar" role="progressbar" style="width: ${d}%; background-color: ${e};"></div></div></div><div class="text-end"><span class="badge ${v} p-2">${(c.status||"").toUpperCase()}</span></div></div></div></div></div>`}if(o.type==="all"&&Array.isArray(o.data)){let c="";o.data.forEach((e,d)=>{if(!e)return;const p=e.status==="achieved"?"badge-success":"badge-warning",h=e.status==="achieved"?"text-success":"text-warning",x=Number(e.presentase_kemampuan||0).toLocaleString("id-ID",{useGrouping:!1});c+=`<tr id="row-${e.kode_karyawan||d}"><td class="text-center">${d+1}</td><td><strong>${e.nama||"-"}</strong></td><td class="text-end">${n(e.revenue)}</td><td class="text-center"><div class="input-group input-group-sm" style="max-width: 150px; float: right;"><input type="text" class="form-control text-end target-input ${e.id_detailPerson?"":"is-invalid"}" value="${x}" data-id-detail="${e.id_detailPerson||""}" data-kode-karyawan="${e.kode_karyawan||""}" placeholder="Target" ${e.id_detailPerson?"":"disabled"}></div><div class="loading-spinner" style="display: none; float: right; margin-right: 10px;"><span class="spinner-border spinner-border-sm text-primary" role="status"></span></div><div class="update-feedback" style="display: none; float: right; margin-right: 10px; margin-top: 5px;"><i class="${m}fa-solid fa-check-circle text-success"></i></div><div class="clearfix"></div></td><td class="text-center ${h}"><strong>${f(e.percentage)}%</strong></td><td class="text-center"><span class="badge ${p}">${(e.status||"").toUpperCase()}</span></td></tr>`});let v="";return Object.entries(a.triwulan_data||{}).forEach(([e,d])=>{const p=(e||"").toString().replace(/_/g," ");v+=`<div class="col-md-6"><div class="card h-100 shadow-sm border-0"><div class="card-body"><h5 class="card-title">${p}</h5><p class="card-text">Rp ${Number(d||0).toLocaleString("id-ID")}</p></div></div></div>`}),`<div class="row"><div class="col"><div class="card shadow-sm mt-3"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead class="thead-light"><tr><th class="text-center" width="5%">No</th><th>Sales</th><th class="text-end" width="20%">Revenue</th><th class="text-end" width="20%">Target (Editable)</th><th class="text-center" width="15%">Persentase</th><th class="text-center" width="15%">Status</th></tr></thead><tbody>${c||'<tr><td colspan="6" class="text-center">Tidak ada data</td></tr>'}</tbody></table></div></div></div></div><div class="col"><div class="card shadow-sm border-0 mt-3"><div class="card-body"><div class="row g-3">${v||'<p class="text-muted">Tidak ada data triwulan</p>'}</div></div><div class="mb-2 text-center"><hr><p>Data Triwulan diambil dari tahun ${i.detail_jangka||"-"}</p></div></div></div></div>`}return""}return t==="index"&&s==="pemasukan bersih"?z(a,t):t==="index"&&["Kepuasan Pelanggan","kepuasan pelanggan"].includes(s)?ta(a):s==="peningkatan kontribusi pelatihan"?sa(a):""}function ia(i,a,s){const t=G(s),r=i.kontribusi_sales;if(!r||String(a).toLowerCase().trim()!=="pemasukan kotor")return"";const n={on_track:["bg-primary","On Track"],selesai:["bg-success","Selesai"],gagal:["bg-danger","Gagal"]},m=d=>{const[p,h]=n[d]||["bg-secondary","-"];return`<span class="badge ${p}">${h}</span>`},o=d=>d<0?"text-danger":"text-success",c=(r.rows||[]).map((d,p)=>`
            <tr>
                <td class="text-center">${p+1}</td>
                <td class="fw-semibold">${d.nama||"-"}</td>
                <td class="text-end">${t(d.triwulan[1])}</td>
                <td class="text-end">${t(d.triwulan[2])}</td>
                <td class="text-end">${t(d.triwulan[3])}</td>
                <td class="text-end">${t(d.triwulan[4])}</td>
                <td class="text-end fw-bold text-primary">${t(d.total)}</td>
                <td class="text-end">${t(d.target)}</td>
                <td class="text-end fw-semibold ${o(d.selisih)}">${t(d.selisih)}</td>
                <td class="text-center">${f(d.persentase)}%</td>
                <td class="text-center">${m(d.status)}</td>
            </tr>`).join(""),v=r.total,e=v?`
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="2" class="text-center">TOTAL</td>
                    <td class="text-end">${t(v.triwulan[1])}</td>
                    <td class="text-end">${t(v.triwulan[2])}</td>
                    <td class="text-end">${t(v.triwulan[3])}</td>
                    <td class="text-end">${t(v.triwulan[4])}</td>
                    <td class="text-end text-primary">${t(v.total)}</td>
                    <td class="text-end">${t(v.target)}</td>
                    <td class="text-end ${o(v.selisih)}">${t(v.selisih)}</td>
                    <td class="text-center">${f(v.persentase)}%</td>
                    <td class="text-center">${m(v.status)}</td>
                </tr>
            </tfoot>`:"";return`
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body">
                    <h6 class="fw-semibold mb-3"><i class="fa-solid fa-users me-2"></i>Kontribusi Sales ${r.tahun??""}</h6>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle mb-0">
                            <thead class="table-light text-center">
                                <tr>
                                    <th width="4%">No</th><th>Sales</th>
                                    <th>Triwulan I</th><th>Triwulan II</th><th>Triwulan III</th><th>Triwulan IV</th>
                                    <th>Total</th><th>Target</th><th>Selisih</th><th>Persentase</th><th>Status</th>
                                </tr>
                            </thead>
                            <tbody>${c||'<tr><td colspan="11" class="text-center text-muted">Belum ada target penjualan sales untuk tahun ini</td></tr>'}</tbody>
                            ${e}
                        </table>
                    </div>
                </div>
            </div>`}function la(i){var m;const a=((m=i.dataManual)==null?void 0:m.manual_document)||"",s=a?a.split("/").pop():"",t=s?s.split(".").pop().toLowerCase():"",r=a?`/storage/${a}`:"";let n="";return["jpg","jpeg","png"].includes(t)?n=`<div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3"><div class="mb-3" style="max-width: 100%; max-height: 300px;"><img src="${r}" alt="${s}" class="img-fluid rounded shadow-sm" style="max-width: 100%; max-height: 300px; object-fit: contain;"></div><div class="mt-2 text-center"><a href="${r}" download="${s}" class="btn btn-primary btn-sm"><i class="fa-solid fa-download me-1"></i>Download Gambar</a></div><div class="mt-2 small text-muted"><i class="fa-solid fa-file-image me-1"></i>${s}</div></div>`:t==="pdf"?n=`<div class="w-100 h-100 d-flex flex-column"><div class="flex-grow-1 mb-3" style="min-height: 250px;"><iframe src="${r}" class="w-100 h-100" style="border: 1px solid #dee2e6; border-radius: 8px;"></iframe></div><div class="text-center"><a href="${r}" download="${s}" class="btn btn-primary btn-sm"><i class="fa-solid fa-download me-1"></i>Download PDF</a></div><div class="mt-2 small text-muted text-center"><i class="fa-solid fa-file-pdf me-1"></i>${s}</div></div>`:n='<div class="text-center py-5"><div class="mb-3"><i class="fa-solid fa-file text-secondary" style="font-size: 4rem;"></i></div><p class="text-muted mb-3">File tidak dapat ditampilkan</p><p class="text-muted small">Hanya gambar dan PDF yang dapat ditampilkan</p></div>',`<h6 class="fw-semibold mb-3 text-secondary"><i class="fa-solid fa-file me-2"></i>Dokumen Manual</h6><div class="manual-document-container flex-grow-1 d-flex flex-column align-items-center justify-content-center p-3" style="background-color: #f8f9fa; border-radius: 8px;">${n}</div><div class="mt-3 small text-muted text-center"><i class="fa-solid fa-info-circle me-1"></i>Klik tombol download untuk menyimpan file</div>`}function na(i,a,s,t){const r=G(s),n=a.monthly_data||{},m=a.daily_breakdown_per_month||{};if(s==="personal"){const x=Object.entries(n),b=x[x.length-1]||[],k=b[0]?D(b[0]):"-",C=b[1]||0,P=[];Object.values(m).forEach(l=>{Object.entries(l).forEach(([u,y])=>{P.push([u,y])})});const _=P.sort((l,u)=>u[1]-l[1]).slice(0,3),g=t[0]||null;return`<div class="mb-4"><h6 class="fw-semibold text-primary mb-1"><i class="fa-solid fa-wallet me-2"></i>${i.judul}</h6><small class="text-muted">Ringkasan performa</small></div><div class="mb-4 p-3 rounded bg-light"><div class="text-muted small mb-1">Bulan Terakhir</div><div class="fw-semibold">${k}</div><div class="fw-bold fs-6 text-dark">${r(C)}</div></div><div class="mb-3"><div class="text-muted small mb-2">Top Hari Tertinggi</div>${_.map(([l,u],y)=>`<div class="d-flex justify-content-between mb-1 ${y>0?"text-muted":""}"><span>${W(l)}</span><span class="fw-semibold">${r(u)}</span></div>`).join("")}</div><hr class="my-3"><div class="d-flex align-items-center gap-2 text-muted small"><i class="fa-solid fa-user-circle fs-5"></i><span class="fw-semibold text-dark">${(g==null?void 0:g.nama_lengkap)??"-"}</span><span>• ${(g==null?void 0:g.jabatan)??"-"}</span></div>`}const o=Object.keys(n).filter(x=>x).sort(),c=o.length>0?o[o.length-1]:null,v=c?n[c]??0:0,e=c?new Date(`${c}-01`).toLocaleDateString("id-ID",{month:"long",year:"numeric"}):"-",p=Object.entries(m).flatMap(([x,b])=>Object.entries(b||{}).map(([k,C])=>[k,C])).filter(([x,b])=>typeof b=="number"&&b>0).sort((x,b)=>(b[1]||0)-(x[1]||0)).slice(0,3),h=t[0]||{};return`<div class="p-2"><div class="mb-4"><small class="text-muted">Ringkasan performa</small></div><div class="mb-4 p-3 rounded-3 border bg-white shadow-sm"><div class="d-flex justify-content-between align-items-center"><div><div class="text-muted small">Bulan Terakhir</div><div class="fw-semibold">${e}</div></div><div class="text-end"><div class="fw-bold fs-5 text-dark">${r(v)}</div></div></div></div><div class="mb-3"><div class="text-muted small mb-2 fw-semibold">Top Hari Tertinggi</div>${p.map(([x,b],k)=>`<div class="d-flex justify-content-between align-items-center mb-2 p-2 rounded ${k===0?"bg-success-subtle":"bg-light"}"><div class="d-flex align-items-center gap-2"><span class="badge ${k===0?"bg-success":"bg-secondary"}">#${k+1}</span><span class="${k===0?"fw-semibold text-dark":"text-muted"}">${W(x)}</span></div><span class="fw-semibold ${k===0?"text-success":"text-dark"}">${r(b)}</span></div>`).join("")}</div><hr class="my-3"><div class="d-flex align-items-center justify-content-between"><div class="d-flex align-items-center gap-2"><i class="bi bi-person-circle fs-4 text-secondary"></i><div><div class="fw-semibold text-dark">${(h==null?void 0:h.nama_lengkap)??"-"}</div><small class="text-muted">${(h==null?void 0:h.jabatan)??"-"}</small></div></div><span class="badge bg-light text-dark border">Karyawan</span></div></div>`}function da(i,a,s,t,r){const n=O[t];return n.manualRoutes.includes(s)?la(a):n.rupiahRoutes.includes(s)?na(i,a,t,r):`<h6 class="fw-semibold mb-3 text-secondary"><i class="fa-solid fa-chart-pie me-2"></i>Chart ${s}</h6><div class="chart-container flex-grow-1"><canvas id="MyChartDoughtnut"></canvas></div>`}function ra(i){const a=i.dataInovasi||[];return`
            <div class="table-responsive mt-3">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 30%;">Judul</th>
                            <th>Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${a.length?a.map(s=>`
                                <tr>
                                    <td class="fw-semibold">${s.judul??"-"}</td>
                                    <td>${s.deskripsi??"-"}</td>
                                </tr>`).join(""):'<tr><td colspan="2" class="text-center text-muted">Tidak ada data inovasi</td></tr>'}
                    </tbody>
                </table>
            </div>`}function oa(i,a){const s=["",...M],t=a==="index"?"menu-icon ":"";return`<div class="row g-4">${(i.analisa_data||[]).map(n=>{const m=a==="index"?n.file_paths?T.KPI_CONFIG.storageBase+"/"+n.file_paths:"#":V(a,n.file_paths);return`<div class="col-md-4 mt-5"><div class="card border-0 shadow-sm h-100"><div class="mb-2 p-3"><h5 class="fw-bold text-primary mb-1">${s[n.month]||"-"}</h5><small class="text-muted">Laporan Analisis Bulanan</small></div><div class="card-body d-flex flex-column" style="overflow-y: scroll; max-height: 280px;"><div class="mb-3"><p class="mb-0" style="text-align: justify;">${n.description||""}</p></div></div><div class="mt-auto p-3"><a href="${m}" class="btn btn-sm btn-outline-primary w-100" ${n.file_paths?"download":""}><i class="${t}fa-solid fa-file-alt"></i></a></div></div></div>`}).join("")}</div>`}function ca(i){var d,p,h,x;const a=i||{},s=a.trend??"stable",t=a.trend_value??0,r=a.consistency??"stable",n=a.target_status??"behind",m=s==="up"?"success":s==="down"?"danger":"secondary",o=r==="stable"?"success":"warning",c=n==="on_track"?"success":n==="at_risk"?"warning":"danger",v=Object.entries(a.division_breakdown||{}).map(([b,k])=>`<div class="col mb-3"><div class="p-2 border rounded-3"><small class="text-muted d-block">${b}</small><h5 class="fw-bold mb-0">${f(k)}%</h5></div></div>`).join(""),e=a.risk_divisions&&a.risk_divisions.length>0?`<div class="col-6"><div class="card shadow-sm border-0 rounded-4"><div class="card-body"><h6 class="fw-semibold text-danger mb-3">DIVISI BERISIKO</h6><div class="row">${a.risk_divisions.map(b=>`<div class="col mb-2"><div class="p-2 border rounded-3 text-center"><small class="text-muted d-block">${b.name||"-"}</small><span class="fw-bold text-danger">${f(b.value)}%</span></div></div>`).join("")}</div></div></div></div>`:"";return`<div class="row g-4 mt-1"><div class="col-lg-4"><div class="card shadow-sm border-0 rounded-4 h-100"><div class="card-body"><h6 class="fw-semibold text-secondary mb-3">RINGKASAN</h6><div class="d-flex justify-content-between mb-2"><span class="text-muted">Total KPI</span><span class="fw-bold">${a.total_kpi??0}</span></div><div class="d-flex justify-content-between mb-2"><span class="text-muted">Total Divisi</span><span class="fw-bold">${a.total_division??0}</span></div><div class="d-flex justify-content-between"><span class="text-muted">Progress</span><span class="fw-bold text-primary">${f(a.progress)}%</span></div></div></div></div><div class="col-lg-4"><div class="card shadow-sm border-0 rounded-4 h-100"><div class="card-body"><h6 class="fw-semibold text-secondary mb-3">PERFORMA DIVISI</h6><div class="mb-3"><small class="text-muted d-block">Top Division</small><span class="fw-bold text-success">${((d=a.top_division)==null?void 0:d.name)??"-"} (${f((p=a.top_division)==null?void 0:p.value)}%)</span></div><div><small class="text-muted d-block">Lowest Division</small><span class="fw-bold text-danger">${((h=a.lowest_division)==null?void 0:h.name)??"-"} (${f((x=a.lowest_division)==null?void 0:x.value)}%)</span></div></div></div></div><div class="col-lg-4"><div class="card shadow-sm border-0 rounded-4 h-100"><div class="card-body"><h6 class="fw-semibold text-secondary mb-3">STATUS</h6><div class="mb-2"><span class="badge bg-${m}">Trend: ${s} (${f(t)})</span></div><div class="mb-2"><span class="badge bg-${o}">Konsistensi: ${r}</span></div><div><span class="badge bg-${c}">Target: ${n}</span></div></div></div></div><div class="col-6"><div class="card shadow-sm border-0 rounded-4"><div class="card-body"><h6 class="fw-semibold text-secondary mb-3">BREAKDOWN DIVISI</h6><div class="row text-center">${v}</div></div></div></div>${e}<div class="col-12"><div class="alert alert-info rounded-4 shadow-sm mb-0"><i class="fa-solid fa-lightbulb me-2"></i>${a.insight??"-"}</div></div></div>`}function X(i,a,s){const t=Array.isArray(i.karyawan)?i.karyawan:[],r=a?"":"disabled",n=Number(i.nilai_target??0),m=t.map((o,c)=>{const v=o.skala_kemampuan??null;let e;if(v===null)e='<span class="badge bg-secondary">Belum dinilai</span>';else{const d=Math.max(0,100-Number(v)*10);e=`<span class="badge bg-${d<=n?"success":"danger"}">Gap ${f(d)}%</span>`}return`
                <div class="row mb-2 align-items-center p-2 rounded">
                    <div class="col-md-6 d-flex justify-content-between align-items-center">
                        <span>${j(o.nama_lengkap??"-")}</span>${e}
                    </div>
                    <div class="col-md-6">
                        <input type="number" min="1" max="10" step="0.5" class="form-control"
                            name="data[${c}][kemampuan]" value="${v??""}"
                            placeholder="1 - 10" ${r}>
                    </div>
                    <input type="hidden" name="data[${c}][id_karyawan]" value="${o.id_karyawan}">
                    <input type="hidden" name="data[${c}][detailTargetKey]" value="${o.detailTargetKey}">
                </div>`}).join("");return`
            <div class="mt-4"><div class="card shadow-sm border-0 rounded-4"><div class="card-body">
                <h6 class="fw-semibold mb-1">Penilaian Kemampuan Tim</h6>
                <small class="text-muted d-block mb-3">Isi skala 1-10 (8 = 80%). Gap dihitung dari rata-rata seluruh anggota tim. Hijau berarti gap individu masih dalam batas target (${f(n)}%), merah berarti melebihi batas.</small>
                <form id="formGapKompetensi">
                    <input type="hidden" name="_token" value="${s}">
                    <div class="row mb-2 fw-semibold text-muted border-bottom pb-2">
                        <div class="col-md-6">Nama Karyawan</div><div class="col-md-6">Nilai (1-10)</div>
                    </div>
                    ${m||'<div class="text-muted small">Tidak ada anggota tim.</div>'}
                    ${a?'<div class="mt-3"><button type="submit" class="btn btn-primary">Simpan</button></div>':""}
                </form>
            </div></div></div>`}function ua(i){return`<div class="mt-4"><div class="card shadow-sm border-0 rounded-4"><div class="card-body"><div class="d-flex justify-content-between mb-3"><h6 class="fw-semibold mb-0">Statistik ${i}</h6><div class="d-flex gap-2"><select class="form-select form-select-sm" id="filterType"><option value="year">Per Tahun</option><option value="month">Per Bulan</option></select><select class="form-select form-select-sm d-none" id="filterMonth"><option value="">Pilih Bulan</option></select></div></div><div style="height:300px"><canvas id="StatisticChart"></canvas></div></div></div></div>`}function ma(i,a,s,t,r,n){var o;if(O[t].manualRoutes.includes(s)||t==="index"&&n)return"";if(t==="index"&&s==="performa kpi departemen")return ca(a);if(s==="inovation adaption rate")return ra(a);if(s==="laporan analisis keuangan")return oa(a,t);if(t==="personal"&&s==="pemasukan bersih")return z(a,t);if(s==="persentase gap kompetensi tim terhadap standar skill"){const c=t==="index"?i.can_edit===!0:i.can_edit??(((o=T.KPI_CONFIG)==null?void 0:o.userJabatan)||"").toLowerCase().trim()==="koordinator itsm";return X(i,c,T.KPI_CONFIG.csrfToken)}return t==="personal"&&n?r:ua(s)}function va(i){const a=Array.isArray(i.instruktur_details)?i.instruktur_details:[],s=i.hari_libur_nasional||{jumlah:0,daftar:{}};if(a.length===0)return"";const t=M,r=["Min","Sen","Sel","Rab","Kam","Jum","Sab"];T.__instrukturData=a,T.__currentMonthIndex={};function n(e,d){const p=e.kalender||{};if(!p[d])return'<div class="text-muted text-center py-4">Tidak ada data untuk bulan ini.</div>';const[h,x]=d.split("-"),b=t[parseInt(x,10)-1],k=new Date(h,x,0).getDate(),C=new Date(h,parseInt(x,10)-1,1).getDay();let P="";r.forEach(g=>{P+=`<div class="kal-header">${g}</div>`});for(let g=0;g<C;g++)P+='<div class="kal-cell kal-empty"></div>';for(let g=1;g<=k;g++){const l=p[d][g]||{status:"empty",keterangan:""};P+=`<div class="kal-cell kal-${l.status}" title="${l.keterangan||""}" data-bs-toggle="tooltip"><div class="kal-day">${g}</div></div>`}const _=Object.values(p[d]).reduce((g,l)=>(g[l.status]=(g[l.status]||0)+1,g),{});return`
                <div class="kalender-bulan-slide" data-bulan="${d}">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-semibold mb-0">${b} ${h}</h6>
                        <div class="d-flex gap-2 small">
                            <span class="badge bg-success">Aktif: ${_.working||0}</span>
                            <span class="badge bg-warning text-dark">Cuti: ${_.cuti||0}</span>
                            <span class="badge bg-danger">Libur: ${_.libur||0}</span>
                        </div>
                    </div>
                    <div class="kalender-grid">${P}</div>
                </div>`}T.showKalenderBulan=function(e,d){const p=T.__instrukturData.find(l=>l.id==e);if(!p)return;const h=p.kalender||{},x=Object.keys(h).sort(),b=x.length;if(b===0)return;d<0&&(d=b-1),d>=b&&(d=0),T.__currentMonthIndex[e]=d;const k=x[d],C=document.getElementById(`kalenderSlide_${e}`);C&&(C.innerHTML=n(p,k));const P=document.getElementById(`bulanCounter_${e}`);P&&(P.textContent=`${d+1} dari ${b} bulan`);const _=document.getElementById(`btnPrevBulan_${e}`),g=document.getElementById(`btnNextBulan_${e}`);_&&(_.disabled=b<=1),g&&(g.disabled=b<=1)};function m(e){const d=Object.entries(e.daftar_cuti||{});return d.length===0?"":`
                <div class="mt-3">
                    <h6 class="fw-semibold small text-muted mb-2"><i class="fa-solid fa-suitcase-rolling me-1"></i>Detail Cuti (${d.length} hari)</h6>
                    <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-warning"><tr><th>Tanggal</th><th>Tipe</th><th>Alasan</th></tr></thead>
                            <tbody>
                                ${d.map(([p,h])=>`
                                    <tr>
                                        <td class="small">${new Date(p).toLocaleDateString("id-ID",{day:"2-digit",month:"short",year:"numeric"})}</td>
                                        <td class="small">${h.tipe||"-"}</td>
                                        <td class="small">${h.alasan||"-"}</td>
                                    </tr>`).join("")}
                            </tbody>
                        </table>
                    </div>
                </div>`}let o="";const c=Object.entries(s.daftar||{});c.length>0&&(o=`
                <div class="alert alert-light border mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0 fw-semibold"><i class="fa-solid fa-flag me-2 text-danger"></i>Hari Libur Nasional</h6>
                        <span class="badge bg-danger">${s.jumlah} Hari</span>
                    </div>
                    <div class="row g-2" style="max-height: 150px; overflow-y: auto;">
                        ${c.map(([e,d])=>`
                            <div class="col-md-6">
                                <div class="small p-2 bg-danger bg-opacity-10 rounded">
                                    <strong>${new Date(e).toLocaleDateString("id-ID",{day:"2-digit",month:"short",year:"numeric"})}</strong>
                                    <div class="text-muted">${d}</div>
                                </div>
                            </div>`).join("")}
                    </div>
                </div>`);let v="";return a.forEach((e,d)=>{const p=e.persentase>=100?"success":e.persentase>=75?"warning":"danger",h=e.kalender||{},x=Object.keys(h).sort(),b=x.length;T.__currentMonthIndex[e.id]=0;const k=m(e);v+=`
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingInstruktur${d}">
                        <button class="accordion-button ${d>0?"collapsed":""}" type="button" data-bs-toggle="collapse" data-bs-target="#collapseInstruktur${d}" aria-expanded="${d===0?"true":"false"}">
                            <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                <div>
                                    <strong>${e.nama}</strong>
                                    <div class="small text-muted">${e.kode_karyawan} • ${e.jabatan}</div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-${p}">${f(e.persentase)}%</div>
                                    <div class="small text-muted">${e.jam_aktif} / ${e.target_jam} jam</div>
                                </div>
                            </div>
                        </button>
                    </h2>
                    <div id="collapseInstruktur${d}" class="accordion-collapse collapse ${d===0?"show":""}" data-bs-parent="#accordionInstruktur">
                        <div class="accordion-body">
                            <div class="row g-2 mb-3">
                                <div class="col-6 col-md-3"><div class="p-2 border rounded text-center bg-success-subtle"><div class="small text-muted">Hari Aktif</div><div class="fw-bold fs-5">${e.total_hari_kerja}</div></div></div>
                                <div class="col-6 col-md-3"><div class="p-2 border rounded text-center bg-warning-subtle"><div class="small text-muted">Hari Cuti</div><div class="fw-bold fs-5">${e.total_hari_cuti}</div></div></div>
                                <div class="col-6 col-md-3"><div class="p-2 border rounded text-center bg-danger-subtle"><div class="small text-muted">Hari Libur</div><div class="fw-bold fs-5">${e.total_hari_libur}</div></div></div>
                                <div class="col-6 col-md-3"><div class="p-2 border rounded text-center bg-primary-subtle"><div class="small text-muted">Jam Aktif</div><div class="fw-bold fs-5">${e.jam_aktif}</div></div></div>
                            </div>
                            <div class="d-flex flex-wrap gap-2 mb-3 small">
                                <span><span class="kal-legend-box kal-working"></span> Hari Aktif</span>
                                <span><span class="kal-legend-box kal-cuti"></span> Cuti</span>
                                <span><span class="kal-legend-box kal-libur"></span> Libur Nasional</span>
                                <span><span class="kal-legend-box kal-weekend"></span> Weekend</span>
                                <span><span class="kal-legend-box kal-empty"></span> Tidak Ada Aktivitas</span>
                            </div>
                            ${k}
                            <div class="mt-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fw-semibold mb-0"><i class="fa-solid fa-calendar me-2"></i>Kalender Bulanan</h6>
                                    <span class="badge bg-info" id="bulanCounter_${e.id}">1 dari ${b} bulan</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center gap-2 mb-3 p-2 bg-light rounded-3">
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnPrevBulan_${e.id}" onclick="showKalenderBulan(${e.id}, window.__currentMonthIndex[${e.id}] - 1)"><i class="fa-solid fa-chevron-left me-1"></i> Bulan Sebelumnya</button>
                                    <div class="flex-grow-1 text-center"><small class="text-muted">Gunakan tombol atau swipe untuk navigasi</small></div>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnNextBulan_${e.id}" onclick="showKalenderBulan(${e.id}, window.__currentMonthIndex[${e.id}] + 1)">Bulan Selanjutnya <i class="fa-solid fa-chevron-right ms-1"></i></button>
                                </div>
                                <div id="kalenderSlide_${e.id}">
                                    ${b>0?n(e,x[0]):'<div class="text-muted text-center py-4">Tidak ada data kalender.</div>'}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`}),`
            <div class="mt-3">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-3"><i class="fa-solid fa-calendar-days me-2"></i>Kalender Kinerja Instruktur</h6>
                        ${o}
                        <div class="accordion" id="accordionInstruktur">
                            ${v}
                        </div>
                    </div>
                </div>
            </div>
            <style>
                .kalender-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 3px; background: #f8f9fa; padding: 6px; border-radius: 8px; }
                .kal-header { text-align: center; font-size: 11px; font-weight: 600; color: #6c757d; padding: 4px 0; }
                .kal-cell { aspect-ratio: 1; display: flex; align-items: center; justify-content: center; border-radius: 4px; font-size: 12px; cursor: pointer; transition: transform 0.15s; position: relative; }
                .kal-cell:hover { transform: scale(1.1); z-index: 2; }
                .kal-day { font-weight: 500; }
                .kal-empty { background: transparent; }
                .kal-working { background: #d1e7dd; color: #0f5132; }
                .kal-cuti { background: #fff3cd; color: #664d03; }
                .kal-libur { background: #f8d7da; color: #842029; }
                .kal-weekend { background: #e2e3e5; color: #41464b; }
                .kal-legend-box { display: inline-block; width: 14px; height: 14px; border-radius: 3px; vertical-align: middle; margin-right: 4px; }
                .kal-legend-box.kal-working { background: #d1e7dd; }
                .kal-legend-box.kal-cuti { background: #fff3cd; }
                .kal-legend-box.kal-libur { background: #f8d7da; }
                .kal-legend-box.kal-weekend { background: #e2e3e5; }
                .kal-legend-box.kal-empty { background: #f8f9fa; border: 1px dashed #adb5bd; }
                .kalender-bulan-slide { animation: slideInBulan 0.3s ease-out; }
                @keyframes slideInBulan { from { opacity: 0; transform: translateX(20px); } to { opacity: 1; transform: translateX(0); } }
            </style>`}function U(i){const a=i.data_detail||{},s=a.feedback_summary;if(!s)return'<div class="alert alert-secondary mt-4 mb-0 small">Belum ada penilaian dari peserta untuk tahun ini.</div>';const t=s.threshold??3.5,r=Number(s.target??i.nilai_target??0),n=s.total||0,m=s.puas||0,o=s.tidak_puas||0,c=Number(a.progress||0),v=a.question_averages||[],e=a.monthly_summary||[],d=a.kategori_summary||[],p=Number(s.skala_maks||4),h=d.map(w=>{const S=v.filter(I=>I.kategori===w.kategori);return`<div class="mb-2"><div class="fw-semibold small">${j(w.kategori)}</div><ul class="small mb-0 ps-3">${S.map(I=>`<li>${j(I.label)}</li>`).join("")}</ul></div>`}).join("");let x=0;r<100&&c<r&&(x=Math.max(0,Math.ceil((r/100*n-m)/(1-r/100))));const b=v.filter(w=>(w.n||0)>0).sort((w,S)=>S.avg-w.avg),k=b.slice(0,5),C=[...b].reverse().slice(0,5),P=[...e].sort((w,S)=>S.pct-w.pct),_=P[0],g=P[P.length-1],l=(w,S,I,B,A,L)=>`
            <div class="col-6 col-lg">
                <div class="card pp-card border-0 shadow-sm h-100" data-view="${w}" role="button" tabindex="0">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted small fw-semibold">${B}</span>
                            <span class="rounded-circle bg-${I} bg-opacity-10 d-flex align-items-center justify-content-center" style="width:34px;height:34px;">
                                <i class="fa-solid ${S} text-${I}"></i>
                            </span>
                        </div>
                        <div class="fs-3 fw-bold text-${I}">${A}</div>
                        <div class="text-muted small">${L}</div>
                        <div class="pp-hint small mt-2 text-primary"><i class="fa-solid fa-table me-1"></i>Klik untuk lihat tabel</div>
                    </div>
                </div>
            </div>`,u=(w,S,I,B)=>`
            <div class="col-md-6 col-xl-3">
                <div class="p-3 rounded-3 border bg-white h-100">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fa-solid ${w} text-${S} me-2"></i>
                        <span class="small fw-semibold text-muted">${I}</span>
                    </div>
                    <div class="small">${B}</div>
                </div>
            </div>`,y=(w,S,I,B)=>{var H,F;const A=Math.min(100,Math.max(0,Number(w.avg)/p*100)),L=(Number((H=w.distribusi)==null?void 0:H[1])||0)+(Number((F=w.distribusi)==null?void 0:F[2])||0);return`
                <div class="d-flex align-items-start py-2 ${S>0?"border-top":""}">
                    <span class="badge rounded-pill bg-${I} me-3 mt-1">${S+1}</span>
                    <div class="flex-grow-1">
                        <div class="small fw-semibold">${j(w.label)}</div>
                        <div class="text-muted small mb-1">${j(w.kategori)}</div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height:8px"><div class="progress-bar bg-${I}" style="width:${A}%"></div></div>
                            <small class="fw-bold text-${I}">${R(w.avg)}</small>
                        </div>
                        <div class="small text-muted mt-1">${f(w.pct_tinggi)}% peserta menilai bagus${B?` &middot; ${L} peserta menilai kurang (nilai 1 atau 2)`:""}</div>
                    </div>
                </div>`},N=(w,S,I,B,A,L)=>`
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-1"><i class="fa-solid ${I} text-${B} me-2"></i>${w}</h6>
                        <small class="text-muted d-block mb-2">${S}</small>
                        ${A.length?A.map((H,F)=>y(H,F,B,L)).join(""):'<div class="text-muted small py-3">Belum ada data.</div>'}
                    </div>
                </div>
            </div>`,K=c>=r?`<div class="alert alert-success mb-0 small"><i class="fa-solid fa-circle-check me-1"></i>Target <strong>${r}%</strong> tercapai (lebih tinggi <strong>${f(c-r)}%</strong> dari target).</div>`:`<div class="alert alert-warning mb-0 small"><i class="fa-solid fa-triangle-exclamation me-1"></i>Target <strong>${r}%</strong> belum tercapai (kurang <strong>${f(r-c)}%</strong>).
               Perlu sekitar <strong>${x}</strong> peserta puas tambahan agar target tercapai.</div>`,E=d.length?d.map(w=>`${j(w.kategori)}: <strong>${R(w.avg)}</strong>`).join("<br>"):"-";return`
        <div class="mt-4" id="ppWrapper">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="fw-semibold mb-0"><i class="fa-solid fa-face-smile me-2 text-primary"></i>Ringkasan Penilaian Peserta Tahun ${j(s.tahun??"")}</h6>
                    <small class="text-muted">Peserta dianggap puas bila nilai rata-ratanya ${t} ke atas (nilai terbaik adalah ${p})</small>
                </div>
            </div>

            <div class="row g-3 mb-4">
                ${l("total","fa-comments","primary","Jumlah Peserta Menilai",n,"Seluruh peserta tahun ini")}
                ${l("puas","fa-thumbs-up","success","Peserta Puas",m,`${n?f(m/n*100):"0.0"}% dari seluruh peserta`)}
                ${l("tidak","fa-thumbs-down","danger","Peserta Belum Puas",o,`${n?f(o/n*100):"0.0"}% dari seluruh peserta`)}
                ${l("question","fa-list-check","info","Nilai Tiap Pertanyaan",R(s.avg_overall),`Nilai rata-rata dari ${p} &middot; ${v.length} pertanyaan`)}
                ${l("monthly","fa-calendar-check","warning","Ringkasan Tiap Bulan",e.length+" bulan","Peserta puas dan belum puas")}
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <h6 class="fw-semibold mb-0" id="ppTableTitle"></h6>
                        <span class="badge bg-secondary" id="ppTableCount"></span>
                    </div>
                    <div class="table-responsive" id="ppTableWrap"></div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                ${N("Nilai Tertinggi","5 pertanyaan yang paling disukai peserta","fa-arrow-trend-up","success",k,!1)}
                ${N("Perlu Diperbaiki","5 pertanyaan dengan nilai paling rendah","fa-arrow-trend-down","danger",C,!0)}
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body">
                    <h6 class="fw-semibold mb-3"><i class="fa-solid fa-calculator me-2 text-primary"></i>Cara Menghitung</h6>
                    <div class="row g-3">
                        <div class="col-lg-6">
                            <ol class="small mb-0 ps-3">
                                <li class="mb-2">Kami ambil semua penilaian peserta pada tahun <strong>${j(s.tahun??"-")}</strong> (1 Januari sampai 31 Desember).</li>
                                <li class="mb-2">Nilai setiap peserta didapat dengan menjumlahkan nilai dari <strong>${v.length} pertanyaan</strong> (daftar ada di bawah), lalu dibagi ${v.length}. Pertanyaan yang tidak diisi dihitung 0.</li>
                                <li class="mb-2">Peserta dianggap <span class="badge bg-success">Puas</span> bila nilainya <strong>${t} ke atas</strong>, dan <span class="badge bg-danger">Belum Puas</span> bila di bawah ${t}.</li>
                                <li class="mb-2"><strong>Persentase peserta puas</strong> = jumlah peserta puas &divide; jumlah seluruh peserta &times; 100%.</li>
                                <li class="mb-0"><strong>Selisih dengan target</strong> = persentase peserta puas &minus; target. Bila target sudah tercapai, selisih dianggap 0.</li>
                            </ol>
                        </div>
                        <div class="col-lg-6">
                            <div class="p-3 rounded-3 bg-light h-100">
                                <div class="small text-muted mb-2">Contoh dengan data tahun ini</div>
                                <div class="small">
                                    Persentase puas = ${m} &divide; ${n} &times; 100 = <strong>${f(c)}%</strong><br>
                                    Target = <strong>${r}%</strong><br>
                                    Selisih = ${f(c)} &minus; ${r} = <strong>${f(c-r)}%</strong>
                                </div>
                                <div class="mt-3">${K}</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="p-3 rounded-3 border">
                                <div class="small fw-semibold mb-2">Pertanyaan yang dinilai</div>
                                ${h||'<div class="small text-muted">-</div>'}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                ${u("fa-building","primary","Nilai Rata-rata per Kelompok",E)}
                ${u("fa-gauge-high","warning","Rentang Nilai Peserta",`Paling rendah <strong>${R(s.min_avg)}</strong> &middot; Paling tinggi <strong>${R(s.max_avg)}</strong><br>Rata-rata <strong>${R(s.avg_overall)}</strong>`)}
                ${u("fa-bullseye","info","Posisi terhadap Target",`Peserta puas <strong>${f(c)}%</strong> dari target <strong>${r}%</strong>`)}
                ${u("fa-users","secondary","Rata-rata Peserta per Bulan",e.length?`<strong>${f(n/e.length)}</strong> peserta mengisi penilaian tiap bulan`:"-")}
                ${u("fa-calendar-plus","success","Bulan Terbaik",_?`<strong>${D(_.bulan)}</strong> &ndash; ${f(_.pct)}% puas (${_.puas} dari ${_.total} peserta)`:"-")}
                ${u("fa-calendar-minus","danger","Bulan Terendah",g?`<strong>${D(g.bulan)}</strong> &ndash; ${f(g.pct)}% puas (${g.puas} dari ${g.total} peserta)`:"-")}
            </div>

            <div class="row g-4 mb-2">
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body">
                        <h6 class="fw-semibold mb-3">Nilai Rata-rata Tiap Pertanyaan</h6>
                        <div style="height:340px"><canvas id="ppChartQuestion"></canvas></div>
                    </div></div>
                </div>
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body">
                        <h6 class="fw-semibold mb-3">Sebaran Nilai Peserta</h6>
                        <div style="height:340px"><canvas id="ppChartDist"></canvas></div>
                    </div></div>
                </div>
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4"><div class="card-body">
                        <h6 class="fw-semibold mb-3">Perkembangan Tiap Bulan: Peserta Puas dan Belum Puas</h6>
                        <div style="height:300px"><canvas id="ppChartMonthly"></canvas></div>
                    </div></div>
                </div>
            </div>
        </div>
        <style>
            .pp-card { border-radius: 14px; cursor: pointer; transition: all .2s ease; border: 2px solid transparent !important; }
            .pp-card:hover { transform: translateY(-4px); box-shadow: 0 10px 25px rgba(0,0,0,.1) !important; }
            .pp-card.active { border-color: #4e73df !important; background: #f4f7ff; }
            .pp-card:not(.active) .pp-hint { opacity: .6; }
            .pp-card.active .pp-hint { display: none; }
        </style>`}function q(i){const a=i.data_detail||{},s=a.feedback_summary;if(!s||!document.getElementById("ppTableWrap"))return;const t=s.threshold??3.5,r=a.question_codes||[],n=a.feedback_rows||[],m=a.question_averages||[],o=a.monthly_summary||[],c=(l,u)=>{const y=String(l??"");return y.length>u?y.slice(0,u-1)+"…":y},v=l=>l?'<span class="badge bg-success">Puas</span>':'<span class="badge bg-danger">Belum Puas</span>',e=l=>`<strong class="${l>=t?"text-success":"text-danger"}">${R(l)}</strong>`,d=r.map(l=>{const u=m.find(N=>N.kode===l),y=u?u.label:l;return{title:`<span title="${j(y)}">${j(c(y,30))}</span>`,className:"text-center"}}),p=[{title:"No"},{title:"Tanggal"},...d,{title:"Nilai Rata-rata",className:"text-center"},{title:"Status",className:"text-center"},{title:"Komentar"}],h=l=>l.map((u,y)=>[y+1,u.tanggal,...u.scores.map(N=>N===null?'<span class="text-muted">-</span>':String(Number(N))),e(u.avg),v(u.puas),u.komentar?`<small>${j(u.komentar)}</small>`:'<span class="text-muted">-</span>']),x={total:{title:'<i class="fa-solid fa-comments text-primary me-2"></i>Semua Penilaian Peserta',columns:p,data:()=>h(n)},puas:{title:`<i class="fa-solid fa-thumbs-up text-success me-2"></i>Peserta Puas (nilai ${t} ke atas)`,columns:p,data:()=>h(n.filter(l=>l.puas))},tidak:{title:`<i class="fa-solid fa-thumbs-down text-danger me-2"></i>Peserta Belum Puas (nilai di bawah ${t})`,columns:p,data:()=>h(n.filter(l=>!l.puas))},question:{title:'<i class="fa-solid fa-list-check text-info me-2"></i>Nilai Rata-rata Tiap Pertanyaan',columns:[{title:"Kelompok"},{title:"Pertanyaan"},{title:"Nilai Rata-rata",className:"text-center"},{title:"Nilai Terendah",className:"text-center"},{title:"Nilai Tertinggi",className:"text-center"},{title:"Jumlah Peserta yang Menjawab",className:"text-center"},{title:`% Peserta Menilai Bagus (${t} ke atas)`,className:"text-center"},{title:"Status",className:"text-center"}],data:()=>m.map(l=>[j(l.kategori),j(l.label),e(l.avg),l.min,l.max,l.n??"-",f(l.pct_tinggi)+"%",v(l.avg>=t)])},monthly:{title:'<i class="fa-solid fa-calendar-check text-warning me-2"></i>Ringkasan Tiap Bulan',columns:[{title:"Bulan"},{title:"Jumlah Peserta",className:"text-center"},{title:"Peserta Puas",className:"text-center"},{title:"Peserta Belum Puas",className:"text-center"},{title:"Nilai Rata-rata",className:"text-center"},{title:"% Peserta Puas",className:"text-center"}],data:()=>o.map(l=>[`<strong>${D(l.bulan)}</strong>`,l.total,`<span class="text-success fw-semibold">${l.puas}</span>`,`<span class="text-danger fw-semibold">${l.tidak}</span>`,e(l.avg),`<div class="d-flex align-items-center gap-2"><div class="progress flex-grow-1" style="height:8px"><div class="progress-bar ${l.pct>=(s.target||0)?"bg-success":"bg-warning"}" style="width:${l.pct}%"></div></div><small class="fw-semibold">${f(l.pct)}%</small></div>`])}};function b(l){const u=x[l];if(!u)return;$.fn.DataTable.isDataTable("#ppTable")&&$("#ppTable").DataTable().destroy(),$("#ppTableWrap").html('<table id="ppTable" class="table table-sm table-hover table-bordered align-middle w-100"></table>');const y=u.data();$("#ppTableTitle").html(u.title),$("#ppTableCount").text(y.length+" baris");const N=$("#ppTable").DataTable({data:y,columns:u.columns,pageLength:10,lengthChange:!1,deferRender:!0,scrollX:!0,language:{search:"Cari:",info:"Menampilkan _START_ - _END_ dari _TOTAL_ data",infoEmpty:"Tidak ada data",emptyTable:"Tidak ada data yang tersedia",zeroRecords:"Data tidak ditemukan",paginate:{next:"Selanjutnya",previous:"Sebelumnya"}}});setTimeout(()=>N.columns.adjust(),50),$(".pp-card").removeClass("active"),$(`.pp-card[data-view="${l}"]`).addClass("active")}if($("#bodyContentDetailTarget").off("click.pp keydown.pp").on("click.pp",".pp-card",function(){b($(this).data("view"))}).on("keydown.pp",".pp-card",function(l){(l.key==="Enter"||l.key===" ")&&(l.preventDefault(),b($(this).data("view")))}),b("total"),typeof Chart>"u")return;const k="rgba(40,207,180,.75)",C="rgba(254,124,150,.75)",P=document.getElementById("ppChartQuestion");P&&new Chart(P,{type:"bar",data:{labels:m.map(l=>c(l.label,32)),datasets:[{label:"Nilai rata-rata",data:m.map(l=>l.avg),backgroundColor:m.map(l=>l.avg>=t?k:C),borderRadius:6}]},options:{indexAxis:"y",responsive:!0,maintainAspectRatio:!1,plugins:{legend:{display:!1},tooltip:{callbacks:{title:l=>m[l[0].dataIndex].label}}},scales:{x:{min:0,max:4}}}});const _=document.getElementById("ppChartDist");if(_){const l=a.rating_distribution||{},u=Object.keys(l);new Chart(_,{type:"bar",data:{labels:u,datasets:[{label:"Jumlah Peserta",data:Object.values(l),backgroundColor:u.map(y=>y.startsWith("3.5")||y==="4.0"?k:C),borderRadius:6}]},options:{responsive:!0,maintainAspectRatio:!1,plugins:{legend:{display:!1}},scales:{y:{beginAtZero:!0,ticks:{precision:0}}}}})}const g=document.getElementById("ppChartMonthly");g&&new Chart(g,{type:"bar",data:{labels:o.map(l=>D(l.bulan)),datasets:[{label:"Puas",data:o.map(l=>l.puas),backgroundColor:k,borderRadius:4,stack:"s"},{label:"Belum Puas",data:o.map(l=>l.tidak),backgroundColor:C,borderRadius:4,stack:"s"},{label:"% Peserta Puas",data:o.map(l=>l.pct),type:"line",borderColor:"#4e73df",backgroundColor:"transparent",tension:.35,yAxisID:"y1",pointRadius:4}]},options:{responsive:!0,maintainAspectRatio:!1,plugins:{legend:{position:"bottom"}},scales:{y:{stacked:!0,beginAtZero:!0,ticks:{precision:0},title:{display:!0,text:"Jumlah peserta"}},y1:{position:"right",min:0,max:100,grid:{drawOnChartArea:!1},ticks:{callback:l=>l+"%"}},x:{stacked:!0}}}})}function pa(i,a){const s=i.data_detail||{},t=i.condition||"",r=J(t),n=aa(i),m=r?U(i):"";return{isPesertaPuas:r,karyawanList:n.list,karyawanHtml:n.html,trafikSales:ea(i,s,t,a),kontribusiSales:ia(s,t,a),pieChart:da(i,s,t,a,n.list),statisticChart:ma(i,s,t,a,m,r),kalenderInstruktur:va(s),pesertaPuas:m}}function ba(i){document.querySelectorAll(".target-input").forEach(a=>{if(!a)return;let s=null;a.addEventListener("input",function(){const t=this,r=t.closest("tr");if(!r)return;const n=r.querySelector(".loading-spinner"),m=r.querySelector(".update-feedback");clearTimeout(s),m&&(m.style.display="none"),s=setTimeout(()=>{const o=t.dataset.idDetail,c=t.dataset.kodeKaryawan,v=(t.value||"").toString().replace(/\./g,"");if(!o){t.classList.add("is-invalid");return}n&&(n.style.display="inline-block"),t.disabled=!0,$.ajax({url:O[i].updateTargetUrl(),method:"POST",data:{_token:T.KPI_CONFIG.csrfToken,id_detailPerson:o,kode_karyawan:c,presentase_kemampuan:v},success:function(e){if(n&&(n.style.display="none"),t.disabled=!1,m&&(m.style.display="inline-block",setTimeout(()=>{m.style.display="none"},2e3)),t.classList.remove("is-invalid"),t.classList.add("is-valid"),setTimeout(()=>t.classList.remove("is-valid"),2e3),e!=null&&e.data){const d=r.querySelector("td:nth-child(5)"),p=r.querySelector("td:nth-child(6)");if(d&&e.data.percentage&&(d.innerHTML=`<strong class="${e.data.status==="achieved"?"text-success":"text-warning"}">${f(e.data.percentage)}%</strong>`),p&&e.data.status){const h=e.data.status==="achieved"?"badge-success":"badge-warning";p.innerHTML=`<span class="badge ${h}">${e.data.status.toUpperCase()}</span>`}}},error:function(){n&&(n.style.display="none"),t.disabled=!1,t.classList.add("is-invalid")}})},1e3)}),a.addEventListener("blur",function(){const t=(this.value||"").toString().replace(/\./g,"");t&&(this.value=Number(t).toLocaleString("id-ID"))}),a.addEventListener("focus",function(){const t=(this.value||"").toString().replace(/\./g,"");t&&(this.value=t)})})}function ga(){$(document).off("submit","#formGapKompetensi").on("submit","#formGapKompetensi",function(i){i.preventDefault(),$.ajax({url:T.KPI_CONFIG.updateGapKompetensi,method:"POST",data:$(this).serialize(),success:function(){typeof Swal<"u"&&Swal.fire({icon:"success",title:"Berhasil!",text:"Berhasil diupdate.",timer:2e3,showConfirmButton:!1}).then(()=>{$("#detailTargetModal").modal("hide"),typeof T.loadContentForm=="function"&&T.loadContentForm()})},error:function(a){var s;typeof Swal<"u"&&Swal.fire({icon:"error",title:"Gagal!",html:((s=a.responseJSON)==null?void 0:s.message)||"Terjadi kesalahan"})}})})}function ha(i){const a=i.monthly_data||{},s=i.daily_breakdown_per_month||{};let t=null;const r=document.getElementById("StatisticChart"),n=(c,v,e)=>{if(!r||typeof Chart>"u")return;t&&t.destroy();const d=(v||[]).map(h=>Number(h)||0),p=d.length>0?Math.max(...d):100;t=new Chart(r,{type:"line",data:{labels:c||[],datasets:[{label:e||"Data",data:d,borderColor:"#4e73df",backgroundColor:"rgba(78, 115, 223, 0.1)",tension:.4,fill:!0}]},options:{responsive:!0,maintainAspectRatio:!1,scales:{y:{beginAtZero:!0,suggestedMax:p+3,ticks:{count:6,precision:0,callback:h=>Math.round(h)}}}}})},m=Object.keys(a).map(D),o=Object.values(a).map(c=>Number(c)||0);n(m,o,"Rata-rata"),$("#filterType").off("change").on("change",function(){this.value==="month"?($("#filterMonth").removeClass("d-none").empty().append('<option value="">Pilih Bulan</option>'),Object.keys(s).forEach(c=>{c&&$("#filterMonth").append(`<option value="${c}">${D(c)}</option>`)}),t&&t.destroy()):($("#filterMonth").addClass("d-none"),n(m,o,"Rata-rata"))}),$("#filterMonth").off("change").on("change",function(){const c=this.value;if(!c||!s[c])return;const v=Object.keys(s[c]).map(d=>(d||"").substring(8)),e=Object.values(s[c]).map(d=>Number(d)||0);n(v,e,`Tanggal ${D(c)}`)})}function fa(i,a,s){const t=i.data_detail||{};s&&s.isPesertaPuas&&q(i);const r=document.getElementById("MyChartDoughtnut");if(r&&typeof Chart<"u"){const n=Chart.getChart?Chart.getChart(r):null;n&&n.destroy();const m=t.pie_chart||{above:0,below:0},o=t.is_inverse===!0?["Dalam batas target","Melebihi target"]:["Above","Below"];new Chart(r,{type:"doughnut",data:{labels:o,datasets:[{label:"Jumlah",data:[m.above??0,m.below??0],backgroundColor:["#B66DFF","#FE7C96"],hoverOffset:4}]},options:{responsive:!0,maintainAspectRatio:!1,plugins:{legend:{position:"bottom",labels:{boxWidth:12,padding:15}}}}})}ba(a),ga(),ha(t),document.querySelectorAll(".btn-toggle-desc").forEach(n=>{n&&n.addEventListener("click",function(){const m=this.getAttribute("data-target"),o=m?document.getElementById(m):null;o&&(o.classList.contains("expanded")?(o.classList.remove("expanded"),this.innerText="Lihat Selengkapnya"):(o.classList.add("expanded"),this.innerText="Sembunyikan"))})})}function xa(i){return i==="index"?`
            <div class="skeleton-modal-content">
                <div class="skeleton-modal-header">
                    <div>
                        <div class="skeleton" style="width: 200px; height: 24px; margin-bottom: 8px;"></div>
                        <div class="skeleton" style="width: 120px; height: 14px;"></div>
                    </div>
                    <div class="skeleton" style="width: 32px; height: 32px; border-radius: 8px;"></div>
                </div>
                <div class="modal-body pt-2">
                    <div class="skeleton-progress-section">
                        <div class="skeleton-progress-header">
                            <div class="skeleton-badge skeleton"></div>
                            <div class="skeleton" style="width: 150px; height: 14px;"></div>
                        </div>
                        <div class="skeleton-progress-stats">
                            <div class="skeleton-stat"><div class="skeleton-label skeleton"></div><div class="skeleton-value skeleton"></div></div>
                            <div class="skeleton-stat"><div class="skeleton-label skeleton"></div><div class="skeleton-value skeleton"></div></div>
                            <div class="skeleton-stat"><div class="skeleton-label skeleton"></div><div class="skeleton-value skeleton"></div></div>
                        </div>
                        <div class="skeleton-progress-bar skeleton"></div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="skeleton-info-card">
                                <div class="skeleton-title skeleton"></div>
                                <div class="skeleton-info-row"><div class="skeleton-label skeleton"></div><div class="skeleton-value skeleton"></div></div>
                                <div class="skeleton-info-row"><div class="skeleton-label skeleton"></div><div class="skeleton-value skeleton"></div></div>
                                <div class="skeleton-info-row"><div class="skeleton-label skeleton"></div><div class="skeleton-value skeleton"></div></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="skeleton-info-card">
                                <div class="skeleton-title skeleton"></div>
                                <div class="skeleton-employee-list">
                                    <div class="skeleton-employee-item"><div class="skeleton-avatar skeleton"></div><div class="skeleton-employee-info"><div class="skeleton-employee-name skeleton"></div><div class="skeleton-employee-position skeleton"></div></div></div>
                                    <div class="skeleton-employee-item"><div class="skeleton-avatar skeleton"></div><div class="skeleton-employee-info"><div class="skeleton-employee-name skeleton"></div><div class="skeleton-employee-position skeleton"></div></div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 p-3 border rounded-4">
                        <div class="skeleton-chart-container">
                            <div class="skeleton-chart-title skeleton"></div>
                            <div class="skeleton-chart-area skeleton"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <div class="skeleton" style="width: 100px; height: 38px; border-radius: 8px;"></div>
                </div>
            </div>`:`
            <div class="skeleton-modal-content p-4">
                <div class="skeleton-modal-header">
                    <div>
                        <div class="skeleton" style="width: 250px; height: 28px; margin-bottom: 8px;"></div>
                        <div class="skeleton" style="width: 120px; height: 14px;"></div>
                    </div>
                    <div class="skeleton" style="width: 32px; height: 32px; border-radius: 50%;"></div>
                </div>
                <div class="card shadow h-100 border-0 mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <div class="skeleton" style="width: 20%; height: 24px;"></div>
                            <div class="skeleton" style="width: 20%; height: 24px;"></div>
                        </div>
                        <div class="skeleton-modal-stats">
                            <div class="skeleton-modal-stat-item"><div class="skeleton-label skeleton"></div><div class="skeleton-value skeleton"></div></div>
                            <div class="skeleton-modal-stat-item"><div class="skeleton-label skeleton"></div><div class="skeleton-value skeleton"></div></div>
                            <div class="skeleton-modal-stat-item"><div class="skeleton-label skeleton"></div><div class="skeleton-value skeleton"></div></div>
                        </div>
                        <div class="skeleton mb-4" style="width: 100%; height: 18px; border-radius: 9px;"></div>
                        <div class="skeleton-modal-cards">
                            <div class="skeleton-modal-card skeleton"></div>
                            <div class="skeleton-modal-card skeleton"></div>
                        </div>
                    </div>
                </div>
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="card shadow border-0">
                            <div class="card-body">
                                <div class="skeleton mb-3" style="width: 40%; height: 20px;"></div>
                                <div class="skeleton" style="width: 100%; height: 150px;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card shadow border-0">
                            <div class="card-body d-flex flex-column">
                                <div class="skeleton mb-3" style="width: 50%; height: 20px;"></div>
                                <div class="skeleton flex-grow-1" style="width: 100%; height: 200px; border-radius: 50%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`}T.isPesertaPuasRoute=J,T.buildPesertaPuasContent=U,T.initPesertaPuas=q,T.KpiDetail={skeleton:xa,buildContents:pa,afterRender:fa,namaBulan:D,buildGapKompetensiForm:X,isPesertaPuasRoute:J,buildPesertaPuasContent:U,initPesertaPuas:q}})(window);
