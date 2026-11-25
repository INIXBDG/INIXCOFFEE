(function(T){const h=e=>(Number(e)||0).toFixed(1),R=e=>(Number(e)||0).toFixed(2),C=e=>String(e??"").replace(/[&<>"']/g,a=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"})[a]),M=["Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"],D=e=>{if(!e)return"-";const a=String(e).split("-");return a.length<2?e:M[parseInt(a[1],10)-1]||e},O={personal:{manualRoutes:["dorong inovasi pelayanan","inisiatif efisiensi keuangan","mengurangi manual work dan error","pengeluaran biaya karyawan"],rupiahRoutes:["Pemasukan Kotor","meningkatkan revenue perusahaan"],trafikRoutes:["target penjualan tahunan","Pemasukan Kotor"],updateTargetUrl:()=>T.KPI_CONFIG.updateTargetPerSalesRoute},index:{manualRoutes:["dorong inovasi pelayanan","inisiatif efisiensi keuangan","mengurangi manual work dan error"],rupiahRoutes:["pemasukan kotor","meningkatkan revenue perusahaan","target penjualan tahunan","target penjualan project tahunan","pendapatan penjualan project"],trafikRoutes:["target penjualan tahunan","pemasukan kotor"],updateTargetUrl:()=>T.KPI_CONFIG.updateTargetPerSales}},z=e=>new Intl.NumberFormat("id-ID",{style:"currency",currency:"IDR",minimumFractionDigits:0}).format(e||0),X=(e=0)=>"Rp "+Number(e||0).toLocaleString("id-ID"),H=e=>e==="index"?X:z,U=(e,a)=>e==="index"?`${T.KPI_CONFIG.storageBase}/${a||""}`:`/storage/${a||""}`,q=e=>{if(!e)return"-";const a=new Date(e);return isNaN(a)?"-":a.toLocaleDateString("id-ID",{day:"2-digit",month:"short"})},Y="peserta puas dengan pelayanan dan fasilitas training",F=e=>String(e||"").toLowerCase().trim()===Y;function Z(e){const a=Array.isArray(e.karyawan)?e.karyawan.filter(r=>r):e.karyawan?[e.karyawan]:[];let t=1;const s=a.map(r=>`<div class="d-flex align-items-center py-2 participant-item"><div class="avatar me-3">${t++}</div><div class="flex-grow-1"><div class="fw-semibold text-dark small">${r.nama_lengkap||"-"}</div><div class="text-muted small">${r.jabatan||"-"}</div></div></div>`).join("")||'<div class="text-muted small">Tidak ada karyawan</div>';return{list:a,html:s}}function V(e,a){const t=M,s=e.previous_quarter?.data||[],r=o=>a==="index"?`download="${o?"true":"false"}"`:o?"download":"",n=a==="index"?"menu-icon ":"";return`<div class="mt-4"><div class="row g-4">${s.map((o,m)=>`<div class="col-12 col-md-6 col-lg-4"><div class="card border-0 shadow-sm h-100 quarter-card"><div class="card-body d-flex flex-column p-4"><div class="d-flex justify-content-between align-items-start mb-3"><div><h6 class="fw-semibold text-muted mb-1">Periode</h6><h5 class="fw-bold mb-0">${t[(o.month||1)-1]??"-"}</h5></div><span class="badge rounded-pill bg-${o.color||"secondary"} bg-opacity-10 text-${o.color||"secondary"} px-3 py-2">Laporan</span></div><div class="mb-3"><h3 class="fw-bold text-dark mb-0">Rp ${o.nilai?Number(o.nilai).toLocaleString("id-ID"):"-"}</h3><small class="text-muted">Total Pemasukan</small></div><div class="flex-grow-1"><p class="text-muted small mb-2 description-text" id="desc-${m}">${o.description??"-"}</p>${o.description&&o.description.length>100?`<button class="btn btn-sm btn-link p-0 text-primary btn-toggle-desc" data-target="desc-${m}">Lihat Selengkapnya</button>`:""}</div><div class="d-flex justify-content-end align-items-center mt-4"><a href="${U(a,o.file_paths)}" class="btn btn-sm btn-dark d-flex align-items-center gap-2" ${r(o.file_paths)}><i class="${n}fa-solid fa-download"></i> Download</a></div></div></div></div>`).join("")}</div></div><style>.quarter-card { border-radius: 16px; transition: all 0.25s ease; background: #ffffff; }.quarter-card:hover { transform: translateY(-6px) scale(1.01); box-shadow: 0 15px 35px rgba(0,0,0,0.08); }.quarter-card h3 { letter-spacing: 0.5px; }.quarter-card .badge { font-size: 12px; font-weight: 500; }.description-text { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }.description-text.expanded { -webkit-line-clamp: unset; overflow: visible; }.quarter-card .btn { border-radius: 8px; font-size: 13px; }</style>`}function Q(e){const a=e||{};return`<div class="row g-4 mt-1"><div class="col-lg-4"><div class="card shadow-sm border-0 rounded-4 h-100"><div class="card-body"><h6 class="fw-semibold text-secondary mb-3">RINGKASAN</h6><div class="d-flex justify-content-between mb-2"><span class="text-muted">Total Feedback</span><span class="fw-bold">${a.total_feedback??0}</span></div><div class="d-flex justify-content-between mb-2"><span class="text-muted">Total Sesi</span><span class="fw-bold">${a.total_sessions??0}</span></div><div class="d-flex justify-content-between"><span class="text-muted">Prediksi</span><span class="fw-bold text-primary">${a.prediction??0}</span></div></div></div></div><div class="col-lg-4"><div class="card shadow-sm border-0 rounded-4 h-100"><div class="card-body"><h6 class="fw-semibold text-secondary mb-3">PERFORMA</h6><div class="mb-3"><small class="text-muted d-block">Top Performer</small><span class="fw-bold text-success">${a.top_performer?.label??"-"} (${a.top_performer?.value??0})</span></div><div><small class="text-muted d-block">Lowest Performer</small><span class="fw-bold text-danger">${a.lowest_performer?.label??"-"} (${a.lowest_performer?.value??0})</span></div></div></div></div><div class="col-lg-4"><div class="card shadow-sm border-0 rounded-4 h-100"><div class="card-body"><h6 class="fw-semibold text-secondary mb-3">STATUS</h6><div class="mb-2"><span class="badge bg-${a.trend==="up"?"success":a.trend==="down"?"danger":"secondary"}">Trend: ${a.trend||"-"} (${h(a.trend_value)})</span></div><div class="mb-2"><span class="badge bg-${a.consistency==="stable"?"success":"warning"}">Konsistensi: ${a.consistency||"-"}</span></div><div><span class="badge bg-${a.target_status==="on_track"?"success":a.target_status==="at_risk"?"warning":"danger"}">Target: ${a.target_status||"-"}</span></div></div></div></div><div class="col-12"><div class="card shadow-sm border-0 rounded-4"><div class="card-body"><h6 class="fw-semibold text-secondary mb-3">KATEGORI PENILAIAN</h6><div class="row text-center">${Object.entries(a.category_scores||{}).map(([t,s])=>`<div class="col"><div class="p-2 border rounded-3"><small class="text-muted d-block">${t}</small><h5 class="fw-bold mb-0">${s??0}</h5></div></div>`).join("")}</div></div></div></div><div class="col-12"><div class="alert alert-info rounded-4 shadow-sm mb-0"><i class="fa-solid fa-lightbulb me-2"></i>${a.insight??"-"}</div></div></div>`}function aa(e){const a=e.class_breakdown||{},t=Number(a.internal)||0,s=Number(a.freelance)||0,r=t+s,n=Number(a.target)||0,o=Math.max(0,n-t),m=Math.min(100,Number(e.progress)||0),u=Array.isArray(a.per_bulan)?a.per_bulan:[],v=Array.isArray(a.per_instruktur)?a.per_instruktur:[],i=new Date,d=i.getFullYear()+"-"+String(i.getMonth()+1).padStart(2,"0"),p=u.filter(c=>c.bulan<=d),f=p.length?t/p.length:0,k=[...p].sort((c,x)=>x.internal-c.internal),g=k[0],w=k[k.length-1],j=Math.max(1,...u.map(c=>c.internal+c.freelance)),P=Math.max(1,...v.map(c=>c.jumlah)),_=n/12,b=(c,x,N,K)=>`
            <div class="col-6 col-lg-3">
                <div class="p-3 rounded-3 bg-white border h-100">
                    <div class="text-muted small mb-1">${c}</div>
                    <div class="fs-3 fw-bold text-${K}">${x}</div>
                    <div class="text-muted small">${N}</div>
                </div>
            </div>`,l=u.map(c=>{const x=c.internal+c.freelance,N=c.internal/j*100,K=c.freelance/j*100,E=_>0?c.internal/_*100:0,y=E>=100?"text-success":"text-danger";return`
                <tr>
                    <td class="fw-semibold">${C(D(c.bulan))}</td>
                    <td class="text-center">${c.internal}</td>
                    <td class="text-center">${c.freelance}</td>
                    <td class="text-center fw-semibold">${x}</td>
                    <td style="min-width:180px;">
                        <div class="progress" style="height:10px;">
                            <div class="progress-bar bg-primary" style="width:${N}%"></div>
                            <div class="progress-bar bg-secondary" style="width:${K}%"></div>
                        </div>
                    </td>
                    <td class="text-center fw-semibold ${y}">${h(E)}%</td>
                </tr>`}).join("");return v.map((c,x)=>`
            <tr>
                <td class="text-center">${x+1}</td>
                <td class="fw-semibold">${C(c.nama)}</td>
                <td class="text-center">${c.jumlah}</td>
                <td style="min-width:160px;">
                    <div class="progress" style="height:10px;">
                        <div class="progress-bar bg-success" style="width:${c.jumlah/P*100}%"></div>
                    </div>
                </td>
                <td class="text-center">${h(c.persen)}%</td>
            </tr>`).join(""),`
        <div class="card mt-4 border-0 rounded-4" style="background:#f8fafc;">
            <div class="card-body p-4">
                <h6 class="fw-semibold mb-1"><i class="fa-solid fa-chalkboard-user me-2 text-primary"></i>Kontribusi Kelas yang Dibawakan Instruktur Inixindo</h6>
                <small class="text-muted d-block mb-3">Kelas yang dibawakan orang luar tidak dihitung dalam pencapaian target.</small>

                <div class="row g-3 mb-4">
                    ${b("Seluruh Kelas",r,"Inixindo + orang luar","dark")}
                    ${b("Kelas Instruktur Inixindo",t,"Dihitung untuk target","primary")}
                    ${b("Kelas Orang Luar",s,"Tidak dihitung untuk target","secondary")}
                    ${b("Sisa Menuju Target",o,`Target ${n} kelas`,o===0?"success":"danger")}
                </div>

                <div class="p-3 rounded-3 bg-white border mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="small fw-semibold">Pencapaian target tahun ini</span>
                        <span class="small fw-bold text-primary">${t} dari ${n} kelas (${h(m)}%)</span>
                    </div>
                    <div class="progress" style="height:14px;">
                        <div class="progress-bar bg-primary" style="width:${m}%"></div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    ${b("Rata-rata per Bulan",h(f),`Kelas Inixindo, patokan target ${h(_)} per bulan`,"info")}
                    ${b("Bulan Tertinggi",g?g.internal:"-",g?C(D(g.bulan)):"-","success")}
                    ${b("Bulan Terendah",w?w.internal:"-",w?C(D(w.bulan)):"-","danger")}
                    ${b("Instruktur Terlibat",v.filter(c=>c.jumlah>0).length,`Dari ${v.length} peserta target`,"warning")}
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
        </style>`}function sa(e,a,t,s){const r=O[s],n=H(s),o=s==="index"?"menu-icon ":"";if(r.trafikRoutes.includes(t)){const m=a.sales_performance||{};if(!m.data)return"";if(m.type==="individual"){const u=m.data,v=u.status==="achieved"?"badge-success":"badge-warning",i=u.status==="achieved"?"#28a745":"#ffc107",d=Math.min(u.percentage??0,100);return`<div class="card shadow-sm mb-4 mt-2"><div class="card-body"><div class="row"><div class="col-md-6"><p class="mb-2"><strong>Sales:</strong> ${u.nama||"-"}</p><p class="mb-2"><strong>Revenue:</strong> ${n(u.revenue)}</p><p class="mb-3"><strong>Target:</strong> ${n(u.presentase_kemampuan)}</p></div><div class="col-md-6"><div class="mb-2"><div class="d-flex justify-content-between"><span>Progress</span><span>${h(u.percentage)}%</span></div><div class="progress" style="height: 10px;"><div class="progress-bar" role="progressbar" style="width: ${d}%; background-color: ${i};"></div></div></div><div class="text-end"><span class="badge ${v} p-2">${(u.status||"").toUpperCase()}</span></div></div></div></div></div>`}if(m.type==="all"&&Array.isArray(m.data)){let u="";m.data.forEach((i,d)=>{if(!i)return;const p=i.status==="achieved"?"badge-success":"badge-warning",f=i.status==="achieved"?"text-success":"text-warning",k=Number(i.presentase_kemampuan||0).toLocaleString("id-ID",{useGrouping:!1});u+=`<tr id="row-${i.kode_karyawan||d}"><td class="text-center">${d+1}</td><td><strong>${i.nama||"-"}</strong></td><td class="text-end">${n(i.revenue)}</td><td class="text-center"><div class="input-group input-group-sm" style="max-width: 150px; float: right;"><input type="text" class="form-control text-end target-input ${i.id_detailPerson?"":"is-invalid"}" value="${k}" data-id-detail="${i.id_detailPerson||""}" data-kode-karyawan="${i.kode_karyawan||""}" placeholder="Target" ${i.id_detailPerson?"":"disabled"}></div><div class="loading-spinner" style="display: none; float: right; margin-right: 10px;"><span class="spinner-border spinner-border-sm text-primary" role="status"></span></div><div class="update-feedback" style="display: none; float: right; margin-right: 10px; margin-top: 5px;"><i class="${o}fa-solid fa-check-circle text-success"></i></div><div class="clearfix"></div></td><td class="text-center ${f}"><strong>${h(i.percentage)}%</strong></td><td class="text-center"><span class="badge ${p}">${(i.status||"").toUpperCase()}</span></td></tr>`});let v="";return Object.entries(a.triwulan_data||{}).forEach(([i,d])=>{const p=(i||"").toString().replace(/_/g," ");v+=`<div class="col-md-6"><div class="card h-100 shadow-sm border-0"><div class="card-body"><h5 class="card-title">${p}</h5><p class="card-text">Rp ${Number(d||0).toLocaleString("id-ID")}</p></div></div></div>`}),`<div class="row"><div class="col"><div class="card shadow-sm mt-3"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead class="thead-light"><tr><th class="text-center" width="5%">No</th><th>Sales</th><th class="text-end" width="20%">Revenue</th><th class="text-end" width="20%">Target (Editable)</th><th class="text-center" width="15%">Persentase</th><th class="text-center" width="15%">Status</th></tr></thead><tbody>${u||'<tr><td colspan="6" class="text-center">Tidak ada data</td></tr>'}</tbody></table></div></div></div></div><div class="col"><div class="card shadow-sm border-0 mt-3"><div class="card-body"><div class="row g-3">${v||'<p class="text-muted">Tidak ada data triwulan</p>'}</div></div><div class="mb-2 text-center"><hr><p>Data Triwulan diambil dari tahun ${e.detail_jangka||"-"}</p></div></div></div></div>`}return""}return s==="index"&&t==="pemasukan bersih"?V(a,s):s==="index"&&["Kepuasan Pelanggan","kepuasan pelanggan"].includes(t)?Q(a):t==="peningkatan kontribusi pelatihan"?aa(a):""}function ta(e,a,t){const s=H(t),r=e.kontribusi_sales;if(!r||String(a).toLowerCase().trim()!=="pemasukan kotor")return"";const n={on_track:["bg-primary","On Track"],selesai:["bg-success","Selesai"],gagal:["bg-danger","Gagal"]},o=d=>{const[p,f]=n[d]||["bg-secondary","-"];return`<span class="badge ${p}">${f}</span>`},m=d=>d<0?"text-danger":"text-success",u=(r.rows||[]).map((d,p)=>`
            <tr>
                <td class="text-center">${p+1}</td>
                <td class="fw-semibold">${d.nama||"-"}</td>
                <td class="text-end">${s(d.triwulan[1])}</td>
                <td class="text-end">${s(d.triwulan[2])}</td>
                <td class="text-end">${s(d.triwulan[3])}</td>
                <td class="text-end">${s(d.triwulan[4])}</td>
                <td class="text-end fw-bold text-primary">${s(d.total)}</td>
                <td class="text-end">${s(d.target)}</td>
                <td class="text-end fw-semibold ${m(d.selisih)}">${s(d.selisih)}</td>
                <td class="text-center">${h(d.persentase)}%</td>
                <td class="text-center">${o(d.status)}</td>
            </tr>`).join(""),v=r.total,i=v?`
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="2" class="text-center">TOTAL</td>
                    <td class="text-end">${s(v.triwulan[1])}</td>
                    <td class="text-end">${s(v.triwulan[2])}</td>
                    <td class="text-end">${s(v.triwulan[3])}</td>
                    <td class="text-end">${s(v.triwulan[4])}</td>
                    <td class="text-end text-primary">${s(v.total)}</td>
                    <td class="text-end">${s(v.target)}</td>
                    <td class="text-end ${m(v.selisih)}">${s(v.selisih)}</td>
                    <td class="text-center">${h(v.persentase)}%</td>
                    <td class="text-center">${o(v.status)}</td>
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
                            <tbody>${u||'<tr><td colspan="11" class="text-center text-muted">Belum ada target penjualan sales untuk tahun ini</td></tr>'}</tbody>
                            ${i}
                        </table>
                    </div>
                </div>
            </div>`}function ea(e){const a=e.dataManual?.manual_document||"",t=a?a.split("/").pop():"",s=t?t.split(".").pop().toLowerCase():"",r=a?`/storage/${a}`:"";let n="";return["jpg","jpeg","png"].includes(s)?n=`<div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3"><div class="mb-3" style="max-width: 100%; max-height: 300px;"><img src="${r}" alt="${t}" class="img-fluid rounded shadow-sm" style="max-width: 100%; max-height: 300px; object-fit: contain;"></div><div class="mt-2 text-center"><a href="${r}" download="${t}" class="btn btn-primary btn-sm"><i class="fa-solid fa-download me-1"></i>Download Gambar</a></div><div class="mt-2 small text-muted"><i class="fa-solid fa-file-image me-1"></i>${t}</div></div>`:s==="pdf"?n=`<div class="w-100 h-100 d-flex flex-column"><div class="flex-grow-1 mb-3" style="min-height: 250px;"><iframe src="${r}" class="w-100 h-100" style="border: 1px solid #dee2e6; border-radius: 8px;"></iframe></div><div class="text-center"><a href="${r}" download="${t}" class="btn btn-primary btn-sm"><i class="fa-solid fa-download me-1"></i>Download PDF</a></div><div class="mt-2 small text-muted text-center"><i class="fa-solid fa-file-pdf me-1"></i>${t}</div></div>`:n='<div class="text-center py-5"><div class="mb-3"><i class="fa-solid fa-file text-secondary" style="font-size: 4rem;"></i></div><p class="text-muted mb-3">File tidak dapat ditampilkan</p><p class="text-muted small">Hanya gambar dan PDF yang dapat ditampilkan</p></div>',`<h6 class="fw-semibold mb-3 text-secondary"><i class="fa-solid fa-file me-2"></i>Dokumen Manual</h6><div class="manual-document-container flex-grow-1 d-flex flex-column align-items-center justify-content-center p-3" style="background-color: #f8f9fa; border-radius: 8px;">${n}</div><div class="mt-3 small text-muted text-center"><i class="fa-solid fa-info-circle me-1"></i>Klik tombol download untuk menyimpan file</div>`}function ia(e,a,t,s){const r=H(t),n=a.monthly_data||{},o=a.daily_breakdown_per_month||{};if(t==="personal"){const k=Object.entries(n),g=k[k.length-1]||[],w=g[0]?D(g[0]):"-",j=g[1]||0,P=[];Object.values(o).forEach(l=>{Object.entries(l).forEach(([c,x])=>{P.push([c,x])})});const _=P.sort((l,c)=>c[1]-l[1]).slice(0,3),b=s[0]||null;return`<div class="mb-4"><h6 class="fw-semibold text-primary mb-1"><i class="fa-solid fa-wallet me-2"></i>${e.judul}</h6><small class="text-muted">Ringkasan performa</small></div><div class="mb-4 p-3 rounded bg-light"><div class="text-muted small mb-1">Bulan Terakhir</div><div class="fw-semibold">${w}</div><div class="fw-bold fs-6 text-dark">${r(j)}</div></div><div class="mb-3"><div class="text-muted small mb-2">Top Hari Tertinggi</div>${_.map(([l,c],x)=>`<div class="d-flex justify-content-between mb-1 ${x>0?"text-muted":""}"><span>${q(l)}</span><span class="fw-semibold">${r(c)}</span></div>`).join("")}</div><hr class="my-3"><div class="d-flex align-items-center gap-2 text-muted small"><i class="fa-solid fa-user-circle fs-5"></i><span class="fw-semibold text-dark">${b?.nama_lengkap??"-"}</span><span>• ${b?.jabatan??"-"}</span></div>`}const m=Object.keys(n).filter(k=>k).sort(),u=m.length>0?m[m.length-1]:null,v=u?n[u]??0:0,i=u?new Date(`${u}-01`).toLocaleDateString("id-ID",{month:"long",year:"numeric"}):"-",p=Object.entries(o).flatMap(([k,g])=>Object.entries(g||{}).map(([w,j])=>[w,j])).filter(([k,g])=>typeof g=="number"&&g>0).sort((k,g)=>(g[1]||0)-(k[1]||0)).slice(0,3),f=s[0]||{};return`<div class="p-2"><div class="mb-4"><small class="text-muted">Ringkasan performa</small></div><div class="mb-4 p-3 rounded-3 border bg-white shadow-sm"><div class="d-flex justify-content-between align-items-center"><div><div class="text-muted small">Bulan Terakhir</div><div class="fw-semibold">${i}</div></div><div class="text-end"><div class="fw-bold fs-5 text-dark">${r(v)}</div></div></div></div><div class="mb-3"><div class="text-muted small mb-2 fw-semibold">Top Hari Tertinggi</div>${p.map(([k,g],w)=>`<div class="d-flex justify-content-between align-items-center mb-2 p-2 rounded ${w===0?"bg-success-subtle":"bg-light"}"><div class="d-flex align-items-center gap-2"><span class="badge ${w===0?"bg-success":"bg-secondary"}">#${w+1}</span><span class="${w===0?"fw-semibold text-dark":"text-muted"}">${q(k)}</span></div><span class="fw-semibold ${w===0?"text-success":"text-dark"}">${r(g)}</span></div>`).join("")}</div><hr class="my-3"><div class="d-flex align-items-center justify-content-between"><div class="d-flex align-items-center gap-2"><i class="bi bi-person-circle fs-4 text-secondary"></i><div><div class="fw-semibold text-dark">${f?.nama_lengkap??"-"}</div><small class="text-muted">${f?.jabatan??"-"}</small></div></div><span class="badge bg-light text-dark border">Karyawan</span></div></div>`}function la(e,a,t,s,r){const n=O[s];return n.manualRoutes.includes(t)?ea(a):n.rupiahRoutes.includes(t)?ia(e,a,s,r):`<h6 class="fw-semibold mb-3 text-secondary"><i class="fa-solid fa-chart-pie me-2"></i>Chart ${t}</h6><div class="chart-container flex-grow-1"><canvas id="MyChartDoughtnut"></canvas></div>`}function na(e){const a=e.dataInovasi||[];return`
            <div class="table-responsive mt-3">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 30%;">Judul</th>
                            <th>Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${a.length?a.map(t=>`
                                <tr>
                                    <td class="fw-semibold">${t.judul??"-"}</td>
                                    <td>${t.deskripsi??"-"}</td>
                                </tr>`).join(""):'<tr><td colspan="2" class="text-center text-muted">Tidak ada data inovasi</td></tr>'}
                    </tbody>
                </table>
            </div>`}function da(e,a){const t=["",...M],s=a==="index"?"menu-icon ":"";return`<div class="row g-4">${(e.analisa_data||[]).map(n=>{const o=a==="index"?n.file_paths?T.KPI_CONFIG.storageBase+"/"+n.file_paths:"#":U(a,n.file_paths);return`<div class="col-md-4 mt-5"><div class="card border-0 shadow-sm h-100"><div class="mb-2 p-3"><h5 class="fw-bold text-primary mb-1">${t[n.month]||"-"}</h5><small class="text-muted">Laporan Analisis Bulanan</small></div><div class="card-body d-flex flex-column" style="overflow-y: scroll; max-height: 280px;"><div class="mb-3"><p class="mb-0" style="text-align: justify;">${n.description||""}</p></div></div><div class="mt-auto p-3"><a href="${o}" class="btn btn-sm btn-outline-primary w-100" ${n.file_paths?"download":""}><i class="${s}fa-solid fa-file-alt"></i></a></div></div></div>`}).join("")}</div>`}function ra(e){const a=e||{},t=a.trend??"stable",s=a.trend_value??0,r=a.consistency??"stable",n=a.target_status??"behind",o=t==="up"?"success":t==="down"?"danger":"secondary",m=r==="stable"?"success":"warning",u=n==="on_track"?"success":n==="at_risk"?"warning":"danger",v=Object.entries(a.division_breakdown||{}).map(([d,p])=>`<div class="col mb-3"><div class="p-2 border rounded-3"><small class="text-muted d-block">${d}</small><h5 class="fw-bold mb-0">${h(p)}%</h5></div></div>`).join(""),i=a.risk_divisions&&a.risk_divisions.length>0?`<div class="col-6"><div class="card shadow-sm border-0 rounded-4"><div class="card-body"><h6 class="fw-semibold text-danger mb-3">DIVISI BERISIKO</h6><div class="row">${a.risk_divisions.map(d=>`<div class="col mb-2"><div class="p-2 border rounded-3 text-center"><small class="text-muted d-block">${d.name||"-"}</small><span class="fw-bold text-danger">${h(d.value)}%</span></div></div>`).join("")}</div></div></div></div>`:"";return`<div class="row g-4 mt-1"><div class="col-lg-4"><div class="card shadow-sm border-0 rounded-4 h-100"><div class="card-body"><h6 class="fw-semibold text-secondary mb-3">RINGKASAN</h6><div class="d-flex justify-content-between mb-2"><span class="text-muted">Total KPI</span><span class="fw-bold">${a.total_kpi??0}</span></div><div class="d-flex justify-content-between mb-2"><span class="text-muted">Total Divisi</span><span class="fw-bold">${a.total_division??0}</span></div><div class="d-flex justify-content-between"><span class="text-muted">Progress</span><span class="fw-bold text-primary">${h(a.progress)}%</span></div></div></div></div><div class="col-lg-4"><div class="card shadow-sm border-0 rounded-4 h-100"><div class="card-body"><h6 class="fw-semibold text-secondary mb-3">PERFORMA DIVISI</h6><div class="mb-3"><small class="text-muted d-block">Top Division</small><span class="fw-bold text-success">${a.top_division?.name??"-"} (${h(a.top_division?.value)}%)</span></div><div><small class="text-muted d-block">Lowest Division</small><span class="fw-bold text-danger">${a.lowest_division?.name??"-"} (${h(a.lowest_division?.value)}%)</span></div></div></div></div><div class="col-lg-4"><div class="card shadow-sm border-0 rounded-4 h-100"><div class="card-body"><h6 class="fw-semibold text-secondary mb-3">STATUS</h6><div class="mb-2"><span class="badge bg-${o}">Trend: ${t} (${h(s)})</span></div><div class="mb-2"><span class="badge bg-${m}">Konsistensi: ${r}</span></div><div><span class="badge bg-${u}">Target: ${n}</span></div></div></div></div><div class="col-6"><div class="card shadow-sm border-0 rounded-4"><div class="card-body"><h6 class="fw-semibold text-secondary mb-3">BREAKDOWN DIVISI</h6><div class="row text-center">${v}</div></div></div></div>${i}<div class="col-12"><div class="alert alert-info rounded-4 shadow-sm mb-0"><i class="fa-solid fa-lightbulb me-2"></i>${a.insight??"-"}</div></div></div>`}function W(e,a,t){const s=Array.isArray(e.karyawan)?e.karyawan:[],r=a?"":"disabled",n=s.map((o,m)=>{const u=o.skala_kemampuan??null,v=u===null?'<span class="badge bg-secondary">Belum dinilai</span>':`<span class="badge bg-info text-dark">${h(u*10)}%</span>`;return`
                <div class="row mb-2 align-items-center p-2 rounded">
                    <div class="col-md-6 d-flex justify-content-between align-items-center">
                        <span>${o.nama_lengkap??"-"}</span>${v}
                    </div>
                    <div class="col-md-6">
                        <input type="number" min="1" max="10" step="0.5" class="form-control"
                            name="data[${m}][kemampuan]" value="${u??""}"
                            placeholder="1 - 10" ${r}>
                    </div>
                    <input type="hidden" name="data[${m}][id_karyawan]" value="${o.id_karyawan}">
                    <input type="hidden" name="data[${m}][detailTargetKey]" value="${o.detailTargetKey}">
                </div>`}).join("");return`
            <div class="mt-4"><div class="card shadow-sm border-0 rounded-4"><div class="card-body">
                <h6 class="fw-semibold mb-1">Penilaian Kemampuan Tim</h6>
                <small class="text-muted d-block mb-3">Isi skala 1-10 (8 = 80%). Gap dihitung dari rata-rata seluruh anggota tim.</small>
                <form id="formGapKompetensi">
                    <input type="hidden" name="_token" value="${t}">
                    <div class="row mb-2 fw-semibold text-muted border-bottom pb-2">
                        <div class="col-md-6">Nama Karyawan</div><div class="col-md-6">Nilai (1-10)</div>
                    </div>
                    ${n||'<div class="text-muted small">Tidak ada anggota tim.</div>'}
                    ${a?'<div class="mt-3"><button type="submit" class="btn btn-primary">Simpan</button></div>':""}
                </form>
            </div></div></div>`}function oa(e){return`<div class="mt-4"><div class="card shadow-sm border-0 rounded-4"><div class="card-body"><div class="d-flex justify-content-between mb-3"><h6 class="fw-semibold mb-0">Statistik ${e}</h6><div class="d-flex gap-2"><select class="form-select form-select-sm" id="filterType"><option value="year">Per Tahun</option><option value="month">Per Bulan</option></select><select class="form-select form-select-sm d-none" id="filterMonth"><option value="">Pilih Bulan</option></select></div></div><div style="height:300px"><canvas id="StatisticChart"></canvas></div></div></div></div>`}function ca(e,a,t,s,r,n){if(O[s].manualRoutes.includes(t))return"";if(s==="index"&&t==="performa kpi departemen")return ra(a);if(t==="inovation adaption rate")return na(a);if(t==="laporan analisis keuangan")return da(a,s);if(s==="personal"&&t==="pemasukan bersih")return V(a,s);if(t==="persentase gap kompetensi tim terhadap standar skill"){const m=s==="index"?e.can_edit===!0:e.can_edit??(T.KPI_CONFIG?.userJabatan||"").toLowerCase().trim()==="koordinator itsm";return W(e,m,T.KPI_CONFIG.csrfToken)}return s==="personal"&&n?r:oa(t)}function ua(e){const a=Array.isArray(e.instruktur_details)?e.instruktur_details:[],t=e.hari_libur_nasional||{jumlah:0,daftar:{}};if(a.length===0)return"";const s=M,r=["Min","Sen","Sel","Rab","Kam","Jum","Sab"];T.__instrukturData=a,T.__currentMonthIndex={};function n(i,d){const p=i.kalender||{};if(!p[d])return'<div class="text-muted text-center py-4">Tidak ada data untuk bulan ini.</div>';const[f,k]=d.split("-"),g=s[parseInt(k,10)-1],w=new Date(f,k,0).getDate(),j=new Date(f,parseInt(k,10)-1,1).getDay();let P="";r.forEach(b=>{P+=`<div class="kal-header">${b}</div>`});for(let b=0;b<j;b++)P+='<div class="kal-cell kal-empty"></div>';for(let b=1;b<=w;b++){const l=p[d][b]||{status:"empty",keterangan:""};P+=`<div class="kal-cell kal-${l.status}" title="${l.keterangan||""}" data-bs-toggle="tooltip"><div class="kal-day">${b}</div></div>`}const _=Object.values(p[d]).reduce((b,l)=>(b[l.status]=(b[l.status]||0)+1,b),{});return`
                <div class="kalender-bulan-slide" data-bulan="${d}">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-semibold mb-0">${g} ${f}</h6>
                        <div class="d-flex gap-2 small">
                            <span class="badge bg-success">Aktif: ${_.working||0}</span>
                            <span class="badge bg-warning text-dark">Cuti: ${_.cuti||0}</span>
                            <span class="badge bg-danger">Libur: ${_.libur||0}</span>
                        </div>
                    </div>
                    <div class="kalender-grid">${P}</div>
                </div>`}T.showKalenderBulan=function(i,d){const p=T.__instrukturData.find(l=>l.id==i);if(!p)return;const f=p.kalender||{},k=Object.keys(f).sort(),g=k.length;if(g===0)return;d<0&&(d=g-1),d>=g&&(d=0),T.__currentMonthIndex[i]=d;const w=k[d],j=document.getElementById(`kalenderSlide_${i}`);j&&(j.innerHTML=n(p,w));const P=document.getElementById(`bulanCounter_${i}`);P&&(P.textContent=`${d+1} dari ${g} bulan`);const _=document.getElementById(`btnPrevBulan_${i}`),b=document.getElementById(`btnNextBulan_${i}`);_&&(_.disabled=g<=1),b&&(b.disabled=g<=1)};function o(i){const d=Object.entries(i.daftar_cuti||{});return d.length===0?"":`
                <div class="mt-3">
                    <h6 class="fw-semibold small text-muted mb-2"><i class="fa-solid fa-suitcase-rolling me-1"></i>Detail Cuti (${d.length} hari)</h6>
                    <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-warning"><tr><th>Tanggal</th><th>Tipe</th><th>Alasan</th></tr></thead>
                            <tbody>
                                ${d.map(([p,f])=>`
                                    <tr>
                                        <td class="small">${new Date(p).toLocaleDateString("id-ID",{day:"2-digit",month:"short",year:"numeric"})}</td>
                                        <td class="small">${f.tipe||"-"}</td>
                                        <td class="small">${f.alasan||"-"}</td>
                                    </tr>`).join("")}
                            </tbody>
                        </table>
                    </div>
                </div>`}let m="";const u=Object.entries(t.daftar||{});u.length>0&&(m=`
                <div class="alert alert-light border mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0 fw-semibold"><i class="fa-solid fa-flag me-2 text-danger"></i>Hari Libur Nasional</h6>
                        <span class="badge bg-danger">${t.jumlah} Hari</span>
                    </div>
                    <div class="row g-2" style="max-height: 150px; overflow-y: auto;">
                        ${u.map(([i,d])=>`
                            <div class="col-md-6">
                                <div class="small p-2 bg-danger bg-opacity-10 rounded">
                                    <strong>${new Date(i).toLocaleDateString("id-ID",{day:"2-digit",month:"short",year:"numeric"})}</strong>
                                    <div class="text-muted">${d}</div>
                                </div>
                            </div>`).join("")}
                    </div>
                </div>`);let v="";return a.forEach((i,d)=>{const p=i.persentase>=100?"success":i.persentase>=75?"warning":"danger",f=i.kalender||{},k=Object.keys(f).sort(),g=k.length;T.__currentMonthIndex[i.id]=0;const w=o(i);v+=`
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingInstruktur${d}">
                        <button class="accordion-button ${d>0?"collapsed":""}" type="button" data-bs-toggle="collapse" data-bs-target="#collapseInstruktur${d}" aria-expanded="${d===0?"true":"false"}">
                            <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                <div>
                                    <strong>${i.nama}</strong>
                                    <div class="small text-muted">${i.kode_karyawan} • ${i.jabatan}</div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-${p}">${h(i.persentase)}%</div>
                                    <div class="small text-muted">${i.jam_aktif} / ${i.target_jam} jam</div>
                                </div>
                            </div>
                        </button>
                    </h2>
                    <div id="collapseInstruktur${d}" class="accordion-collapse collapse ${d===0?"show":""}" data-bs-parent="#accordionInstruktur">
                        <div class="accordion-body">
                            <div class="row g-2 mb-3">
                                <div class="col-6 col-md-3"><div class="p-2 border rounded text-center bg-success-subtle"><div class="small text-muted">Hari Aktif</div><div class="fw-bold fs-5">${i.total_hari_kerja}</div></div></div>
                                <div class="col-6 col-md-3"><div class="p-2 border rounded text-center bg-warning-subtle"><div class="small text-muted">Hari Cuti</div><div class="fw-bold fs-5">${i.total_hari_cuti}</div></div></div>
                                <div class="col-6 col-md-3"><div class="p-2 border rounded text-center bg-danger-subtle"><div class="small text-muted">Hari Libur</div><div class="fw-bold fs-5">${i.total_hari_libur}</div></div></div>
                                <div class="col-6 col-md-3"><div class="p-2 border rounded text-center bg-primary-subtle"><div class="small text-muted">Jam Aktif</div><div class="fw-bold fs-5">${i.jam_aktif}</div></div></div>
                            </div>
                            <div class="d-flex flex-wrap gap-2 mb-3 small">
                                <span><span class="kal-legend-box kal-working"></span> Hari Aktif</span>
                                <span><span class="kal-legend-box kal-cuti"></span> Cuti</span>
                                <span><span class="kal-legend-box kal-libur"></span> Libur Nasional</span>
                                <span><span class="kal-legend-box kal-weekend"></span> Weekend</span>
                                <span><span class="kal-legend-box kal-empty"></span> Tidak Ada Aktivitas</span>
                            </div>
                            ${w}
                            <div class="mt-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fw-semibold mb-0"><i class="fa-solid fa-calendar me-2"></i>Kalender Bulanan</h6>
                                    <span class="badge bg-info" id="bulanCounter_${i.id}">1 dari ${g} bulan</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center gap-2 mb-3 p-2 bg-light rounded-3">
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnPrevBulan_${i.id}" onclick="showKalenderBulan(${i.id}, window.__currentMonthIndex[${i.id}] - 1)"><i class="fa-solid fa-chevron-left me-1"></i> Bulan Sebelumnya</button>
                                    <div class="flex-grow-1 text-center"><small class="text-muted">Gunakan tombol atau swipe untuk navigasi</small></div>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnNextBulan_${i.id}" onclick="showKalenderBulan(${i.id}, window.__currentMonthIndex[${i.id}] + 1)">Bulan Selanjutnya <i class="fa-solid fa-chevron-right ms-1"></i></button>
                                </div>
                                <div id="kalenderSlide_${i.id}">
                                    ${g>0?n(i,k[0]):'<div class="text-muted text-center py-4">Tidak ada data kalender.</div>'}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`}),`
            <div class="mt-3">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-3"><i class="fa-solid fa-calendar-days me-2"></i>Kalender Kinerja Instruktur</h6>
                        ${m}
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
            </style>`}function G(e){const a=e.data_detail||{},t=a.feedback_summary;if(!t)return'<div class="alert alert-secondary mt-4 mb-0 small">Belum ada penilaian dari peserta untuk tahun ini.</div>';const s=t.threshold??3.5,r=Number(t.target??e.nilai_target??0),n=t.total||0,o=t.puas||0,m=t.tidak_puas||0,u=Number(a.progress||0),v=a.question_averages||[],i=a.monthly_summary||[],d=a.kategori_summary||[],p=Number(t.skala_maks||4),f=d.map(y=>{const S=v.filter(I=>I.kategori===y.kategori);return`<div class="mb-2"><div class="fw-semibold small">${C(y.kategori)}</div><ul class="small mb-0 ps-3">${S.map(I=>`<li>${C(I.label)}</li>`).join("")}</ul></div>`}).join("");let k=0;r<100&&u<r&&(k=Math.max(0,Math.ceil((r/100*n-o)/(1-r/100))));const g=v.filter(y=>(y.n||0)>0).sort((y,S)=>S.avg-y.avg),w=g.slice(0,5),j=[...g].reverse().slice(0,5),P=[...i].sort((y,S)=>S.pct-y.pct),_=P[0],b=P[P.length-1],l=(y,S,I,B,A,L)=>`
            <div class="col-6 col-lg">
                <div class="card pp-card border-0 shadow-sm h-100" data-view="${y}" role="button" tabindex="0">
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
            </div>`,c=(y,S,I,B)=>`
            <div class="col-md-6 col-xl-3">
                <div class="p-3 rounded-3 border bg-white h-100">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fa-solid ${y} text-${S} me-2"></i>
                        <span class="small fw-semibold text-muted">${I}</span>
                    </div>
                    <div class="small">${B}</div>
                </div>
            </div>`,x=(y,S,I,B)=>{const A=Math.min(100,Math.max(0,Number(y.avg)/p*100)),L=(Number(y.distribusi?.[1])||0)+(Number(y.distribusi?.[2])||0);return`
                <div class="d-flex align-items-start py-2 ${S>0?"border-top":""}">
                    <span class="badge rounded-pill bg-${I} me-3 mt-1">${S+1}</span>
                    <div class="flex-grow-1">
                        <div class="small fw-semibold">${C(y.label)}</div>
                        <div class="text-muted small mb-1">${C(y.kategori)}</div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height:8px"><div class="progress-bar bg-${I}" style="width:${A}%"></div></div>
                            <small class="fw-bold text-${I}">${R(y.avg)}</small>
                        </div>
                        <div class="small text-muted mt-1">${h(y.pct_tinggi)}% peserta menilai bagus${B?` &middot; ${L} peserta menilai kurang (nilai 1 atau 2)`:""}</div>
                    </div>
                </div>`},N=(y,S,I,B,A,L)=>`
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-1"><i class="fa-solid ${I} text-${B} me-2"></i>${y}</h6>
                        <small class="text-muted d-block mb-2">${S}</small>
                        ${A.length?A.map((fa,ka)=>x(fa,ka,B,L)).join(""):'<div class="text-muted small py-3">Belum ada data.</div>'}
                    </div>
                </div>
            </div>`,K=u>=r?`<div class="alert alert-success mb-0 small"><i class="fa-solid fa-circle-check me-1"></i>Target <strong>${r}%</strong> tercapai (lebih tinggi <strong>${h(u-r)}%</strong> dari target).</div>`:`<div class="alert alert-warning mb-0 small"><i class="fa-solid fa-triangle-exclamation me-1"></i>Target <strong>${r}%</strong> belum tercapai (kurang <strong>${h(r-u)}%</strong>).
               Perlu sekitar <strong>${k}</strong> peserta puas tambahan agar target tercapai.</div>`,E=d.length?d.map(y=>`${C(y.kategori)}: <strong>${R(y.avg)}</strong>`).join("<br>"):"-";return`
        <div class="mt-4" id="ppWrapper">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="fw-semibold mb-0"><i class="fa-solid fa-face-smile me-2 text-primary"></i>Ringkasan Penilaian Peserta Tahun ${C(t.tahun??"")}</h6>
                    <small class="text-muted">Peserta dianggap puas bila nilai rata-ratanya ${s} ke atas (nilai terbaik adalah ${p})</small>
                </div>
            </div>

            <div class="row g-3 mb-4">
                ${l("total","fa-comments","primary","Jumlah Peserta Menilai",n,"Seluruh peserta tahun ini")}
                ${l("puas","fa-thumbs-up","success","Peserta Puas",o,`${n?h(o/n*100):"0.0"}% dari seluruh peserta`)}
                ${l("tidak","fa-thumbs-down","danger","Peserta Belum Puas",m,`${n?h(m/n*100):"0.0"}% dari seluruh peserta`)}
                ${l("question","fa-list-check","info","Nilai Tiap Pertanyaan",R(t.avg_overall),`Nilai rata-rata dari ${p} &middot; ${v.length} pertanyaan`)}
                ${l("monthly","fa-calendar-check","warning","Ringkasan Tiap Bulan",i.length+" bulan","Peserta puas dan belum puas")}
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
                ${N("Nilai Tertinggi","5 pertanyaan yang paling disukai peserta","fa-arrow-trend-up","success",w,!1)}
                ${N("Perlu Diperbaiki","5 pertanyaan dengan nilai paling rendah","fa-arrow-trend-down","danger",j,!0)}
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body">
                    <h6 class="fw-semibold mb-3"><i class="fa-solid fa-calculator me-2 text-primary"></i>Cara Menghitung</h6>
                    <div class="row g-3">
                        <div class="col-lg-6">
                            <ol class="small mb-0 ps-3">
                                <li class="mb-2">Kami ambil semua penilaian peserta pada tahun <strong>${C(t.tahun??"-")}</strong> (1 Januari sampai 31 Desember).</li>
                                <li class="mb-2">Nilai setiap peserta didapat dengan menjumlahkan nilai dari <strong>${v.length} pertanyaan</strong> (daftar ada di bawah), lalu dibagi ${v.length}. Pertanyaan yang tidak diisi dihitung 0.</li>
                                <li class="mb-2">Peserta dianggap <span class="badge bg-success">Puas</span> bila nilainya <strong>${s} ke atas</strong>, dan <span class="badge bg-danger">Belum Puas</span> bila di bawah ${s}.</li>
                                <li class="mb-2"><strong>Persentase peserta puas</strong> = jumlah peserta puas &divide; jumlah seluruh peserta &times; 100%.</li>
                                <li class="mb-0"><strong>Selisih dengan target</strong> = persentase peserta puas &minus; target. Bila target sudah tercapai, selisih dianggap 0.</li>
                            </ol>
                        </div>
                        <div class="col-lg-6">
                            <div class="p-3 rounded-3 bg-light h-100">
                                <div class="small text-muted mb-2">Contoh dengan data tahun ini</div>
                                <div class="small">
                                    Persentase puas = ${o} &divide; ${n} &times; 100 = <strong>${h(u)}%</strong><br>
                                    Target = <strong>${r}%</strong><br>
                                    Selisih = ${h(u)} &minus; ${r} = <strong>${h(u-r)}%</strong>
                                </div>
                                <div class="mt-3">${K}</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="p-3 rounded-3 border">
                                <div class="small fw-semibold mb-2">Pertanyaan yang dinilai</div>
                                ${f||'<div class="small text-muted">-</div>'}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                ${c("fa-building","primary","Nilai Rata-rata per Kelompok",E)}
                ${c("fa-gauge-high","warning","Rentang Nilai Peserta",`Paling rendah <strong>${R(t.min_avg)}</strong> &middot; Paling tinggi <strong>${R(t.max_avg)}</strong><br>Rata-rata <strong>${R(t.avg_overall)}</strong>`)}
                ${c("fa-bullseye","info","Posisi terhadap Target",`Peserta puas <strong>${h(u)}%</strong> dari target <strong>${r}%</strong>`)}
                ${c("fa-users","secondary","Rata-rata Peserta per Bulan",i.length?`<strong>${h(n/i.length)}</strong> peserta mengisi penilaian tiap bulan`:"-")}
                ${c("fa-calendar-plus","success","Bulan Terbaik",_?`<strong>${D(_.bulan)}</strong> &ndash; ${h(_.pct)}% puas (${_.puas} dari ${_.total} peserta)`:"-")}
                ${c("fa-calendar-minus","danger","Bulan Terendah",b?`<strong>${D(b.bulan)}</strong> &ndash; ${h(b.pct)}% puas (${b.puas} dari ${b.total} peserta)`:"-")}
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
        </style>`}function J(e){const a=e.data_detail||{},t=a.feedback_summary;if(!t||!document.getElementById("ppTableWrap"))return;const s=t.threshold??3.5,r=a.question_codes||[],n=a.feedback_rows||[],o=a.question_averages||[],m=a.monthly_summary||[],u=(l,c)=>{const x=String(l??"");return x.length>c?x.slice(0,c-1)+"…":x},v=l=>l?'<span class="badge bg-success">Puas</span>':'<span class="badge bg-danger">Belum Puas</span>',i=l=>`<strong class="${l>=s?"text-success":"text-danger"}">${R(l)}</strong>`,d=r.map(l=>{const c=o.find(N=>N.kode===l),x=c?c.label:l;return{title:`<span title="${C(x)}">${C(u(x,30))}</span>`,className:"text-center"}}),p=[{title:"No"},{title:"Tanggal"},...d,{title:"Nilai Rata-rata",className:"text-center"},{title:"Status",className:"text-center"},{title:"Komentar"}],f=l=>l.map((c,x)=>[x+1,c.tanggal,...c.scores.map(N=>N===null?'<span class="text-muted">-</span>':String(Number(N))),i(c.avg),v(c.puas),c.komentar?`<small>${C(c.komentar)}</small>`:'<span class="text-muted">-</span>']),k={total:{title:'<i class="fa-solid fa-comments text-primary me-2"></i>Semua Penilaian Peserta',columns:p,data:()=>f(n)},puas:{title:`<i class="fa-solid fa-thumbs-up text-success me-2"></i>Peserta Puas (nilai ${s} ke atas)`,columns:p,data:()=>f(n.filter(l=>l.puas))},tidak:{title:`<i class="fa-solid fa-thumbs-down text-danger me-2"></i>Peserta Belum Puas (nilai di bawah ${s})`,columns:p,data:()=>f(n.filter(l=>!l.puas))},question:{title:'<i class="fa-solid fa-list-check text-info me-2"></i>Nilai Rata-rata Tiap Pertanyaan',columns:[{title:"Kelompok"},{title:"Pertanyaan"},{title:"Nilai Rata-rata",className:"text-center"},{title:"Nilai Terendah",className:"text-center"},{title:"Nilai Tertinggi",className:"text-center"},{title:"Jumlah Peserta yang Menjawab",className:"text-center"},{title:`% Peserta Menilai Bagus (${s} ke atas)`,className:"text-center"},{title:"Status",className:"text-center"}],data:()=>o.map(l=>[C(l.kategori),C(l.label),i(l.avg),l.min,l.max,l.n??"-",h(l.pct_tinggi)+"%",v(l.avg>=s)])},monthly:{title:'<i class="fa-solid fa-calendar-check text-warning me-2"></i>Ringkasan Tiap Bulan',columns:[{title:"Bulan"},{title:"Jumlah Peserta",className:"text-center"},{title:"Peserta Puas",className:"text-center"},{title:"Peserta Belum Puas",className:"text-center"},{title:"Nilai Rata-rata",className:"text-center"},{title:"% Peserta Puas",className:"text-center"}],data:()=>m.map(l=>[`<strong>${D(l.bulan)}</strong>`,l.total,`<span class="text-success fw-semibold">${l.puas}</span>`,`<span class="text-danger fw-semibold">${l.tidak}</span>`,i(l.avg),`<div class="d-flex align-items-center gap-2"><div class="progress flex-grow-1" style="height:8px"><div class="progress-bar ${l.pct>=(t.target||0)?"bg-success":"bg-warning"}" style="width:${l.pct}%"></div></div><small class="fw-semibold">${h(l.pct)}%</small></div>`])}};function g(l){const c=k[l];if(!c)return;$.fn.DataTable.isDataTable("#ppTable")&&$("#ppTable").DataTable().destroy(),$("#ppTableWrap").html('<table id="ppTable" class="table table-sm table-hover table-bordered align-middle w-100"></table>');const x=c.data();$("#ppTableTitle").html(c.title),$("#ppTableCount").text(x.length+" baris");const N=$("#ppTable").DataTable({data:x,columns:c.columns,pageLength:10,lengthChange:!1,deferRender:!0,scrollX:!0,language:{search:"Cari:",info:"Menampilkan _START_ - _END_ dari _TOTAL_ data",infoEmpty:"Tidak ada data",emptyTable:"Tidak ada data yang tersedia",zeroRecords:"Data tidak ditemukan",paginate:{next:"Selanjutnya",previous:"Sebelumnya"}}});setTimeout(()=>N.columns.adjust(),50),$(".pp-card").removeClass("active"),$(`.pp-card[data-view="${l}"]`).addClass("active")}if($("#bodyContentDetailTarget").off("click.pp keydown.pp").on("click.pp",".pp-card",function(){g($(this).data("view"))}).on("keydown.pp",".pp-card",function(l){(l.key==="Enter"||l.key===" ")&&(l.preventDefault(),g($(this).data("view")))}),g("total"),typeof Chart>"u")return;const w="rgba(40,207,180,.75)",j="rgba(254,124,150,.75)",P=document.getElementById("ppChartQuestion");P&&new Chart(P,{type:"bar",data:{labels:o.map(l=>u(l.label,32)),datasets:[{label:"Nilai rata-rata",data:o.map(l=>l.avg),backgroundColor:o.map(l=>l.avg>=s?w:j),borderRadius:6}]},options:{indexAxis:"y",responsive:!0,maintainAspectRatio:!1,plugins:{legend:{display:!1},tooltip:{callbacks:{title:l=>o[l[0].dataIndex].label}}},scales:{x:{min:0,max:4}}}});const _=document.getElementById("ppChartDist");if(_){const l=a.rating_distribution||{},c=Object.keys(l);new Chart(_,{type:"bar",data:{labels:c,datasets:[{label:"Jumlah Peserta",data:Object.values(l),backgroundColor:c.map(x=>x.startsWith("3.5")||x==="4.0"?w:j),borderRadius:6}]},options:{responsive:!0,maintainAspectRatio:!1,plugins:{legend:{display:!1}},scales:{y:{beginAtZero:!0,ticks:{precision:0}}}}})}const b=document.getElementById("ppChartMonthly");b&&new Chart(b,{type:"bar",data:{labels:m.map(l=>D(l.bulan)),datasets:[{label:"Puas",data:m.map(l=>l.puas),backgroundColor:w,borderRadius:4,stack:"s"},{label:"Belum Puas",data:m.map(l=>l.tidak),backgroundColor:j,borderRadius:4,stack:"s"},{label:"% Peserta Puas",data:m.map(l=>l.pct),type:"line",borderColor:"#4e73df",backgroundColor:"transparent",tension:.35,yAxisID:"y1",pointRadius:4}]},options:{responsive:!0,maintainAspectRatio:!1,plugins:{legend:{position:"bottom"}},scales:{y:{stacked:!0,beginAtZero:!0,ticks:{precision:0},title:{display:!0,text:"Jumlah peserta"}},y1:{position:"right",min:0,max:100,grid:{drawOnChartArea:!1},ticks:{callback:l=>l+"%"}},x:{stacked:!0}}}})}function ma(e,a){const t=e.data_detail||{},s=e.condition||"",r=F(s),n=Z(e),o=r?G(e):"";return{isPesertaPuas:r,karyawanList:n.list,karyawanHtml:n.html,trafikSales:sa(e,t,s,a),kontribusiSales:ta(t,s,a),pieChart:la(e,t,s,a,n.list),statisticChart:ca(e,t,s,a,o,r),kalenderInstruktur:ua(t),pesertaPuas:o}}function va(e){document.querySelectorAll(".target-input").forEach(a=>{if(!a)return;let t=null;a.addEventListener("input",function(){const s=this,r=s.closest("tr");if(!r)return;const n=r.querySelector(".loading-spinner"),o=r.querySelector(".update-feedback");clearTimeout(t),o&&(o.style.display="none"),t=setTimeout(()=>{const m=s.dataset.idDetail,u=s.dataset.kodeKaryawan,v=(s.value||"").toString().replace(/\./g,"");if(!m){s.classList.add("is-invalid");return}n&&(n.style.display="inline-block"),s.disabled=!0,$.ajax({url:O[e].updateTargetUrl(),method:"POST",data:{_token:T.KPI_CONFIG.csrfToken,id_detailPerson:m,kode_karyawan:u,presentase_kemampuan:v},success:function(i){if(n&&(n.style.display="none"),s.disabled=!1,o&&(o.style.display="inline-block",setTimeout(()=>{o.style.display="none"},2e3)),s.classList.remove("is-invalid"),s.classList.add("is-valid"),setTimeout(()=>s.classList.remove("is-valid"),2e3),i?.data){const d=r.querySelector("td:nth-child(5)"),p=r.querySelector("td:nth-child(6)");if(d&&i.data.percentage&&(d.innerHTML=`<strong class="${i.data.status==="achieved"?"text-success":"text-warning"}">${h(i.data.percentage)}%</strong>`),p&&i.data.status){const f=i.data.status==="achieved"?"badge-success":"badge-warning";p.innerHTML=`<span class="badge ${f}">${i.data.status.toUpperCase()}</span>`}}},error:function(){n&&(n.style.display="none"),s.disabled=!1,s.classList.add("is-invalid")}})},1e3)}),a.addEventListener("blur",function(){const s=(this.value||"").toString().replace(/\./g,"");s&&(this.value=Number(s).toLocaleString("id-ID"))}),a.addEventListener("focus",function(){const s=(this.value||"").toString().replace(/\./g,"");s&&(this.value=s)})})}function pa(){$(document).off("submit","#formGapKompetensi").on("submit","#formGapKompetensi",function(e){e.preventDefault(),$.ajax({url:T.KPI_CONFIG.updateGapKompetensi,method:"POST",data:$(this).serialize(),success:function(){typeof Swal<"u"&&Swal.fire({icon:"success",title:"Berhasil!",text:"Berhasil diupdate.",timer:2e3,showConfirmButton:!1}).then(()=>{$("#detailTargetModal").modal("hide"),typeof T.loadContentForm=="function"&&T.loadContentForm()})},error:function(a){typeof Swal<"u"&&Swal.fire({icon:"error",title:"Gagal!",html:a.responseJSON?.message||"Terjadi kesalahan"})}})})}function ba(e){const a=e.monthly_data||{},t=e.daily_breakdown_per_month||{};let s=null;const r=document.getElementById("StatisticChart"),n=(u,v,i)=>{if(!r||typeof Chart>"u")return;s&&s.destroy();const d=(v||[]).map(f=>Number(f)||0),p=d.length>0?Math.max(...d):100;s=new Chart(r,{type:"line",data:{labels:u||[],datasets:[{label:i||"Data",data:d,borderColor:"#4e73df",backgroundColor:"rgba(78, 115, 223, 0.1)",tension:.4,fill:!0}]},options:{responsive:!0,maintainAspectRatio:!1,scales:{y:{beginAtZero:!0,suggestedMax:p+3,ticks:{count:6,precision:0,callback:f=>Math.round(f)}}}}})},o=Object.keys(a).map(D),m=Object.values(a).map(u=>Number(u)||0);n(o,m,"Rata-rata"),$("#filterType").off("change").on("change",function(){this.value==="month"?($("#filterMonth").removeClass("d-none").empty().append('<option value="">Pilih Bulan</option>'),Object.keys(t).forEach(u=>{u&&$("#filterMonth").append(`<option value="${u}">${D(u)}</option>`)}),s&&s.destroy()):($("#filterMonth").addClass("d-none"),n(o,m,"Rata-rata"))}),$("#filterMonth").off("change").on("change",function(){const u=this.value;if(!u||!t[u])return;const v=Object.keys(t[u]).map(d=>(d||"").substring(8)),i=Object.values(t[u]).map(d=>Number(d)||0);n(v,i,`Tanggal ${D(u)}`)})}function ga(e,a,t){const s=e.data_detail||{};t&&t.isPesertaPuas&&J(e);const r=document.getElementById("MyChartDoughtnut");if(r&&typeof Chart<"u"){const n=Chart.getChart?Chart.getChart(r):null;n&&n.destroy();const o=s.pie_chart||{above:0,below:0};new Chart(r,{type:"doughnut",data:{labels:["Above","Below"],datasets:[{label:"Jumlah",data:[o.above??0,o.below??0],backgroundColor:["#B66DFF","#FE7C96"],hoverOffset:4}]},options:{responsive:!0,maintainAspectRatio:!1,plugins:{legend:{position:"bottom",labels:{boxWidth:12,padding:15}}}}})}va(a),a==="index"&&pa(),ba(s),document.querySelectorAll(".btn-toggle-desc").forEach(n=>{n&&n.addEventListener("click",function(){const o=this.getAttribute("data-target"),m=o?document.getElementById(o):null;m&&(m.classList.contains("expanded")?(m.classList.remove("expanded"),this.innerText="Lihat Selengkapnya"):(m.classList.add("expanded"),this.innerText="Sembunyikan"))})})}function ha(e){return e==="index"?`
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
            </div>`}T.isPesertaPuasRoute=F,T.buildPesertaPuasContent=G,T.initPesertaPuas=J,T.KpiDetail={skeleton:ha,buildContents:ma,afterRender:ga,namaBulan:D,buildGapKompetensiForm:W,isPesertaPuasRoute:F,buildPesertaPuasContent:G,initPesertaPuas:J}})(window);
