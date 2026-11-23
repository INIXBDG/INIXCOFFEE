const I=K=>(Number(K)||0).toFixed(1);(function(){let K=null,Z=null,J=[];const Aa=["dorong inovasi pelayanan","inisiatif efisiensi keuangan","mengurangi manual work dan error"],Ma=window.allowedDoubleManualRoutes||[];function N(n){if(!n&&n!=="0")return"";const l=String(n).replace(/[^0-9]/g,"");return l?new Intl.NumberFormat("id-ID").format(l):""}function E(n){return n?String(n).replace(/[^0-9]/g,""):""}function Pa(){$("#manual_value_display").off("input.formatting").on("input.formatting",function(){const n=E($(this).val());$("#manual_value").val(n);const l=$("#manual_format").val();let d=N(n);l==="rupiah"&&n?d="Rp "+d:l==="persen"&&n&&(d=d+"%"),$(this).val(d)}),["biaya_gaji","biaya_bpjs","biaya_rekrutmen"].forEach(n=>{$(`#${n}_display`).off("input.formatting").on("input.formatting",function(){const l=E($(this).val());$(`#${n}_tahunan`).val(l),$(this).val(l?"Rp "+N(l):"")})})}function Q(){$("#formManualValue")[0].reset(),$("#documentPreview").html(""),$("#singleInputArea").show(),$("#doubleInputArea").hide(),$("#manual_value_display").val("").trigger("input"),$("#manual_value").val(""),["biaya_gaji","biaya_bpjs","biaya_rekrutmen"].forEach(n=>{$(`#${n}_display`).val("").trigger("input"),$(`#${n}_tahunan`).val("")}),$("#manualValueId").val("")}function da(n){typeof Swal<"u"?Swal.fire({icon:"error",title:"Error",text:n,confirmButtonText:"OK"}):alert(n)}function V(n){const l=n?"block":"none";document.querySelectorAll(".skeleton-overlay").forEach(d=>{d.style.display=l})}function Ba(){return`
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
            </div>
        `}function na(){V(!0),$("#targetCardContainer").css("opacity","0.5");const n=window.KPI_CONFIG;$.ajax({url:n.dataPersonalRoute,type:"GET",data:{tahun:n.currentYear,id_karyawan:n.targetId},dataType:"json",success:function(l){var d,e,g;if(l.success){V(!1),$("#targetCardContainer").css("opacity","1");const p=(((d=window.KPI_CONFIG)==null?void 0:d.userJabatan)||((e=window.authUser)==null?void 0:e.jabatan)||((g=window.authUser)==null?void 0:g.jabatan_user_table)||"").toLowerCase().trim(),a=["gm","hrd","direktur utama","direktur"],x=p.includes("koordinator itsm")||p==="koordinator itsm"||p.includes("koordinator")||a.includes(p);let h=l.daftar_target_pribadi||[],C=l.statistik_per_target||[];if(!x){const m=w=>{const H=(w.asistant_route||w.assistant_route||w.condition||"").toLowerCase(),aa=(w.judul||"").toLowerCase();return H.includes("gap kompetensi")||H.includes("persentase gap")||aa.includes("gap performa")||aa.includes("gap kompetensi")};h=h.filter(w=>!m(w)),C=C.filter(w=>!m(w)),l.total_target=h.length,l.kpi_aktif=h.filter(w=>["Aktif","Sedang Berjalan"].includes(w.status)).length,l.kpi_selesai=h.filter(w=>["Selesai","Mencapai Target"].includes(w.status)).length,l.grade=h.length?KpiGrade.calc(l.rata_rata_progress):null}console.log("jabatanUser:",p,"isKoordinator:",x),console.log("targets after filter:",h.map(m=>m.judul)),Fa(l),La(l),Ka(C,l.distribusi_status),oa(h),J=h}else V(!1),da(l.message||"Terjadi kesalahan saat memuat data")},error:function(){V(!1),da("Gagal memuat data. Silakan coba lagi.")}})}function Fa(n){var l,d,e;$("#userName").text(((l=n.user_info)==null?void 0:l.nama)||"User"),$("#userJabatan").text(((d=n.user_info)==null?void 0:d.jabatan)||"-"),$("#userDivisi").text(((e=n.user_info)==null?void 0:e.divisi)||"-")}function La(n){var l;$("#stat0").text(n.total_target+" Target"),$("#stat1").html(I(n.rata_rata_progress)+"%"+KpiGrade.badge(n.grade,{nama:(l=n.user_info)==null?void 0:l.nama,total:n.total_target})),$("#stat2").text(n.kpi_aktif),$("#stat3").text(n.kpi_selesai)}function Ka(n,l){K&&K.destroy(),Z&&Z.destroy();const d=n.map(m=>m.judul.length>20?m.judul.substring(0,20)+"...":m.judul),e=n.map(m=>(m.tipe_target==="rupiah"||m.tipe_target==="angka")&&m.target>0?Number(I(m.progress/m.target*100)):Number(I(m.progress))),g=n.map(m=>{let w=m.progress||0;return w===0?"rgba(254, 215, 24, 0.7)":m.tipe_target==="rupiah"?(m.target?w/m.target*100:0)>=100?"rgba(40, 207, 180, 0.7)":"rgba(254, 124, 150, 0.7)":w>=m.target?"rgba(40, 207, 180, 0.7)":"rgba(254, 124, 150, 0.7)"}),p=g.map(m=>m.replace("0.7","1")),a=document.getElementById("performanceChart");a&&(K=new Chart(a.getContext("2d"),{type:"bar",data:{labels:d,datasets:[{label:"Progress (%)",data:e,backgroundColor:g,borderColor:p,borderWidth:1,borderRadius:6}]},options:{responsive:!0,maintainAspectRatio:!1,plugins:{legend:{display:!1},tooltip:{callbacks:{label:m=>"Progress: "+I(m.parsed.y)+"%"}}},scales:{y:{beginAtZero:!0,ticks:{callback:m=>m+"%"}}}}}));const x=Object.keys(l),h=Object.values(l),C=document.getElementById("statusPieChart");C&&(Z=new Chart(C.getContext("2d"),{type:"doughnut",data:{labels:x,datasets:[{data:h,backgroundColor:["#28CFB4","#FE7C96","#FED718"],borderWidth:0,hoverOffset:8}]},options:{responsive:!0,maintainAspectRatio:!1,plugins:{legend:{position:"bottom"}},cutout:"65%"}}))}function oa(n){const l=$("#targetCardContainer");if(!n||n.length===0){l.html('<div class="col-12"><div class="content-card"><div class="card-body text-center py-5"><i class="fa-solid fa-inbox fa-4x text-muted mb-3"></i><h5 class="text-muted">Belum ada target KPI</h5></div></div></div>');return}let d="";n.forEach(e=>{let g=e.progress>=e.target?"bg-success":"bg-primary",p=e.status==="Selesai"?"bg-success":"bg-warning text-dark",a=e.progress>=75?"border-success":e.progress>=50?"border-primary":"border-warning",x=e.tipe_target==="rupiah"||e.tipe_target==="angka"?e.progress/e.target*100:e.progress,h=e.tipe_target==="rupiah"?"Rp "+Number(e.target).toLocaleString("id-ID"):e.tipe_target==="persen"?e.target+"%":e.target,C=e.tipe_target==="rupiah"?"Rp "+Number(e.progress).toLocaleString("id-ID"):e.tipe_target==="angka"?e.progress:I(e.progress)+"%",m=Aa.includes(e.asistant_route)?"true":"";d+=`
                <div class="col-12 col-sm-6 col-lg-4 mb-4">
                    <div class="target-card border-start border-4 ${a}">
                        ${m?`<button type="button" class="action-btn buttonForm" data-id="${e.id}" data-value="${e.manual_value}" data-route="${e.asistant_route}" data-bs-toggle="modal" data-bs-target="#modalFormManual" title="Isi Data"><i class="fa-solid fa-file-pen"></i></button>`:""}
                        <button type="button" class="btn btn-link p-0 text-start w-100 h-100 text-decoration-none buttonDetailTarget" data-id="${e.id}" data-bs-toggle="modal" data-bs-target="#detailTargetModal" style="outline: none;">
                            <div class="card-body-inner">
                                <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                                    <span class="badge bg-primary bg-opacity-10 text-primary status-badge">${e.periode}</span>
                                    <span class="badge ${p} status-badge">${e.status}</span>
                                </div>
                                <h6 class="fw-bold text-dark mb-2 lh-base title-badge">${e.judul}</h6>
                                <p class="text-muted small mb-3" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 40px;">${e.deskripsi||"Tidak ada deskripsi"}</p>
                                <div class="mt-auto">
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <small class="text-muted fw-semibold" style="font-size: 0.8rem;">Progress</small>
                                            <small class="fw-bold text-primary" style="font-size: 0.8rem;">${e.progress_display}</small>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar ${g}" role="progressbar" style="width: ${x}%"></div>
                                        </div>
                                    </div>
                                    <div class="row g-2 pt-2 border-top">
                                        <div class="col-6">
                                            <small class="text-muted d-block" style="font-size: 0.75rem;">Target</small>
                                            <strong class="text-dark" style="font-size: 0.9rem;">${h}</strong>
                                        </div>
                                        <div class="col-6 text-end">
                                            <small class="text-muted d-block" style="font-size: 0.75rem;">Realisasi</small>
                                            <strong class="text-primary" style="font-size: 0.9rem;">${C}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </button>
                    </div>
                </div>
            `}),l.html(d)}$(document).ready(function(){Pa(),na(),$("#modalFormManual").on("show.bs.modal",Q),$("#modalFormManual").on("hidden.bs.modal",Q),$(document).on("click",".filter-btn-group .btn",function(){$(".filter-btn-group .btn").removeClass("active"),$(this).addClass("active");let l=$(this).data("filter"),d=J;l==="active"?d=J.filter(e=>["Aktif","Sedang Berjalan"].includes(e.status)):l==="completed"&&(d=J.filter(e=>e.status==="Selesai")),oa(d)}),$(document).on("click",".buttonForm",function(){const l=$(this).data("route"),d=$(this).data("value")||"",e=$(this).data("id");if($("#manualValueId").val(e),Ma.includes(l)){$("#singleInputArea").hide(),$("#doubleInputArea").show();let g=d&&d.includes(",")?d.split(","):[d,"",""];["biaya_gaji","biaya_bpjs","biaya_rekrutmen"].forEach((p,a)=>{const x=E(g[a]);$(`#${p}_display`).val(x?"Rp "+N(x):"").trigger("input"),$(`#${p}_tahunan`).val(x)})}else{$("#singleInputArea").show(),$("#doubleInputArea").hide();const g=$("#manual_format").val(),p=E(d);let a=N(p);g==="rupiah"&&p?a="Rp "+a:g==="persen"&&p&&(a=a+"%"),$("#manual_value_display").val(a),$("#manual_value").val(p)}}),$(document).on("change","#manual_format",function(){if($("#doubleInputArea").is(":visible"))return;const l=$(this).val(),d=E($("#manual_value").val());let e=N(d);l==="rupiah"&&d?e="Rp "+e:l==="persen"&&d&&(e=e+"%"),$("#manual_value_display").val(e)}),$("#formManualValue").on("submit",function(l){l.preventDefault();const d=$(this).find('button[type="submit"]'),e=d.html();d.prop("disabled",!0).html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Menyimpan...');const g=new FormData(this);$("#doubleInputArea").is(":visible")?(g.set("biaya_gaji_tahunan",$("#biaya_gaji_tahunan").val()),g.set("biaya_bpjs_tahunan",$("#biaya_bpjs_tahunan").val()),g.set("biaya_rekrutmen_tahunan",$("#biaya_rekrutmen_tahunan").val())):(g.set("manual_value",$("#manual_value").val()),g.set("manual_format",$("#manual_format").val())),$.ajax({url:window.KPI_CONFIG.manualValueRoute,type:"POST",data:g,contentType:!1,processData:!1,success:function(){$("#modalFormManual").modal("hide"),Q(),d.prop("disabled",!1).html(e),na()},error:function(p){var a;if(d.prop("disabled",!1).html(e),p.status===422){const x=((a=p.responseJSON)==null?void 0:a.errors)||{};alert(Object.values(x).map(h=>h[0]).join(`
`))}else alert("Terjadi kesalahan sistem.")}})}),$(document).on("click",".buttonDetailTarget",function(){let l=$(this).data("id");const d=$("#bodyContentDetailTarget");d.html(Ba());const e=document.getElementById("detailTargetModal");e&&new bootstrap.Modal(e).show(),$.ajax({url:window.KPI_CONFIG.detailRoute,method:"GET",data:{id:l,idUser:window.KPI_CONFIG.targetId},dataType:"json",success:function(g){var ra,ca,ua,ma,ba,va;d.empty();let p=g.detail,a=p&&p.length>0?p[0].data:null;if(!a)d.append(`
                            <div class="modal-header"><h5 class="modal-title">Detail Target</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                            <div class="modal-body text-center py-5">Belum ada data</div>
                            <div class="modal-footer"><button class="btn btn-danger" data-bs-dismiss="modal">Tutup</button></div>
                        `);else{let ga=function(s,t){return new Date(s)<new Date(t)},fa=function(s,t){return new Date(s)>new Date(t)},G=function(s){const t=s.split("-");if(t.length<2)return s;const b=parseInt(t[1],10)-1;return Oa[b]||s},y=function(s){return new Intl.NumberFormat("id-ID",{style:"currency",currency:"IDR",minimumFractionDigits:0}).format(s||0)},Ia=function(s){return new Date(s).toLocaleDateString("id-ID",{day:"2-digit",month:"short"})};var x=ga,h=fa,C=G,m=y,w=Ia;const ta=a.data_detail.monthly_data||{},O=a.data_detail.daily_breakdown_per_month||{},ea=new Date().toISOString().split("T")[0],Na=new Date(new Date().getFullYear(),0,1).toISOString().split("T")[0],pa=a.tenggat_waktu,ha=a.data_detail||{},ya=ha.progress??0,xa=a.nilai_target??0,wa=ha.is_inverse===!0,ka=wa?ya<=xa:ya>=xa;let A;ga(ea,Na)?A="Belum Dimulai":fa(ea,pa)||ea===pa?A=ka?"Mencapai Target":"Target Gagal":A=ka?"Mencapai Target":"Sedang Berjalan";let z,q,R;if(a.tipe_target==="persen")z=a.nilai_target+" %",q=I(a.data_detail.progress)+" %",R=I(a.data_detail.gap)+" %";else if(a.tipe_target==="rupiah"){const s=new Intl.NumberFormat("id-ID",{style:"currency",currency:"IDR",minimumFractionDigits:0,maximumFractionDigits:0});z=s.format(a.nilai_target),q=s.format(a.data_detail.progress),R=s.format(Math.abs(a.data_detail.gap))}else z=a.nilai_target,q=a.data_detail.progress,R=a.data_detail.gap;let sa=A==="Mencapai Target"?"success":A==="Target Gagal"?"danger":A==="Sedang Berjalan"?"warning":"secondary";const $a=a.data_detail.pie_chart||{above:0,below:0},Ea={labels:["Above","Below"],datasets:[{label:"Jumlah",data:[$a.above??0,$a.below??0],backgroundColor:["#B66DFF","#FE7C96"],hoverOffset:4}]},Oa=["Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"],_a=Object.entries(ta),ia=_a[_a.length-1]||[],Ra=ia[0]?G(ia[0]):"-",Ga=ia[1]||0;let ja=[];Object.values(O).forEach(s=>{Object.entries(s).forEach(([t,b])=>{ja.push([t,b])})});const Ja=ja.sort((s,t)=>t[1]-s[1]).slice(0,3),F=((ra=a.karyawan)==null?void 0:ra[0])||null,Va=Array.isArray(a.karyawan)?a.karyawan:[a.karyawan];let Ha=1;const za=Va.map(s=>`
                            <div class="d-flex align-items-center py-2 participant-item">
                                <div class="avatar me-3">${Ha++}</div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold text-dark small">${s.nama_lengkap||"-"}</div>
                                    <div class="text-muted small">${s.jabatan||"-"}</div>
                                </div>
                            </div>
                        `).join("");let U=0;a.nilai_target&&a.nilai_target>0&&(a.tipe_target==="rupiah"?U=Math.min(a.data_detail.progress/a.nilai_target*100,100).toFixed(1):a.tipe_target==="angka"?U=(a.data_detail.progress/a.nilai_target*100).toFixed(1):U=Math.min(a.data_detail.progress,100).toFixed(1));const Ca=["dorong inovasi pelayanan","inisiatif efisiensi keuangan","mengurangi manual work dan error","pengeluaran biaya karyawan"],qa=["Pemasukan Kotor","meningkatkan revenue perusahaan"],Ua=["persentase gap kompetensi tim terhadap standar skill"],Wa=["target penjualan tahunan","Pemasukan Kotor"],Ya=["peningkatan kontribusi pelatihan"],Xa=["pemasukan bersih"],Za=["laporan analisis keuangan"],Qa=["inovation adaption rate"];let W="";if(Wa.includes(a.condition)){const s=(ca=a.data_detail)==null?void 0:ca.sales_performance;if(s&&s.data){if(s.type==="individual"){const t=s.data,b=t.status==="achieved"?"badge-success":"badge-warning",c=t.status==="achieved"?"#28a745":"#ffc107",v=Math.min(t.percentage,100);W=`
                                        <div class="card shadow-sm mb-4 mt-2"><div class="card-body"><div class="row">
                                            <div class="col-md-6">
                                                <p class="mb-2"><strong>Sales:</strong> ${t.nama||"-"}</p>
                                                <p class="mb-2"><strong>Revenue:</strong> ${y(t.revenue)}</p>
                                                <p class="mb-3"><strong>Target:</strong> ${y(t.presentase_kemampuan)}</p>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-2">
                                                    <div class="d-flex justify-content-between"><span>Progress</span><span>${I(t.percentage)}%</span></div>
                                                    <div class="progress" style="height: 10px;"><div class="progress-bar" role="progressbar" style="width: ${v}%; background-color: ${c};"></div></div>
                                                </div>
                                                <div class="text-end"><span class="badge ${b} p-2">${(t.status||"").toUpperCase()}</span></div>
                                            </div>
                                        </div></div></div>
                                    `}else if(s.type==="all"){let t="";s.data.forEach((c,v)=>{const u=c.status==="achieved"?"badge-success":"badge-warning",o=c.status==="achieved"?"text-success":"text-warning",i=Number(c.presentase_kemampuan).toLocaleString("id-ID",{useGrouping:!1});t+=`
                                            <tr id="row-${c.kode_karyawan}">
                                                <td class="text-center">${v+1}</td>
                                                <td><strong>${c.nama||"-"}</strong></td>
                                                <td class="text-end">${y(c.revenue)}</td>
                                                <td class="text-center">
                                                    <div class="input-group input-group-sm" style="max-width: 150px; float: right;">
                                                        <input type="text" class="form-control text-end target-input ${c.id_detailPerson?"":"is-invalid"}" value="${i}" data-id-detail="${c.id_detailPerson||""}" data-kode-karyawan="${c.kode_karyawan}" placeholder="Target" ${c.id_detailPerson?"":"disabled"}>
                                                    </div>
                                                    <div class="loading-spinner" style="display: none; float: right; margin-right: 10px;"><span class="spinner-border spinner-border-sm text-primary" role="status"></span></div>
                                                    <div class="update-feedback" style="display: none; float: right; margin-right: 10px; margin-top: 5px;"><i class="fa-solid fa-check-circle text-success"></i></div>
                                                    <div class="clearfix"></div>
                                                </td>
                                                <td class="text-center ${o}"><strong>${I(c.percentage)}%</strong></td>
                                                <td class="text-center"><span class="badge ${u}">${(c.status||"").toUpperCase()}</span></td>
                                            </tr>
                                        `});let b="";Object.entries(a.data_detail.triwulan_data||{}).forEach(([c,v])=>{b+=`<div class="col-md-6"><div class="card h-100 shadow-sm border-0"><div class="card-body"><h5 class="card-title">${c.replace("_"," ")}</h5><p class="card-text">Rp ${Number(v).toLocaleString("id-ID")}</p></div></div></div>`}),W=`
                                        <div class="row">
                                            <div class="col"><div class="card shadow-sm mt-3"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead class="thead-light"><tr><th class="text-center" width="5%">No</th><th>Sales</th><th class="text-end" width="20%">Revenue</th><th class="text-end" width="20%">Target (Editable)</th><th class="text-center" width="15%">Persentase</th><th class="text-center" width="15%">Status</th></tr></thead><tbody>${t}</tbody></table></div></div></div></div>
                                            <div class="col"><div class="card shadow-sm border-0 mt-3"><div class="card-body"><div class="row g-3">${b}</div></div><div class="mb-2 text-center"><hr><p>Data Triwulan diambil dari tahun ${a.detail_jangka||"-"}</p></div></div></div>
                                        </div>
                                    `,setTimeout(()=>{document.querySelectorAll(".target-input").forEach(c=>{let v=null;c.addEventListener("input",function(){const u=this,o=u.closest("tr"),i=o.querySelector(".loading-spinner"),r=o.querySelector(".update-feedback");clearTimeout(v),r&&(r.style.display="none"),v=setTimeout(()=>{const k=u.dataset.idDetail,j=u.dataset.kodeKaryawan,T=u.value.replace(/\./g,"");if(!k){u.classList.add("is-invalid");return}i&&(i.style.display="inline-block"),u.disabled=!0,$.ajax({url:window.KPI_CONFIG.updateTargetPerSalesRoute,method:"POST",data:{_token:window.KPI_CONFIG.csrfToken,id_detailPerson:k,kode_karyawan:j,presentase_kemampuan:T},success:function(f){if(i&&(i.style.display="none"),u.disabled=!1,r&&(r.style.display="inline-block",setTimeout(()=>{r.style.display="none"},2e3)),u.classList.remove("is-invalid"),u.classList.add("is-valid"),f!=null&&f.data){const S=o.querySelector("td:nth-child(5)"),P=o.querySelector("td:nth-child(6)");if(S&&f.data.percentage&&(S.innerHTML=`<strong class="${f.data.status==="achieved"?"text-success":"text-warning"}">${I(f.data.percentage)}%</strong>`),P&&f.data.status){const D=f.data.status==="achieved"?"badge-success":"badge-warning";P.innerHTML=`<span class="badge ${D}">${f.data.status.toUpperCase()}</span>`}}setTimeout(()=>{u.classList.remove("is-valid")},2e3)},error:function(){i&&(i.style.display="none"),u.disabled=!1,u.classList.add("is-invalid")}})},1e3)}),c.addEventListener("blur",function(){const u=this.value.replace(/\./g,"");u&&(this.value=Number(u).toLocaleString("id-ID"))}),c.addEventListener("focus",function(){const u=this.value.replace(/\./g,"");u&&(this.value=u)})})},100)}}}else if(Ya.includes(a.condition)){const s=a.data_detail.class_breakdown||{},t=s.Inhouse||{};W=`
                                <div class="card mt-4 border-0 rounded-4" style="background:#f8fafc;">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-end mb-4">
                                            <div><div class="text-muted small">Total Kelas</div><div class="fs-2 fw-semibold text-dark">${s.total||0}</div></div>
                                        </div>
                                        <div class="row g-3 mb-4">
                                            <div class="col-md-6"><div class="p-3 rounded-3 bg-white border h-100"><div class="text-muted small mb-2">Kelas Inixindo</div><div class="fs-4 fw-semibold text-dark">${s.kelas_od||0}</div></div></div>
                                            <div class="col-md-6"><div class="p-3 rounded-3 bg-white border h-100"><div class="text-muted small mb-2">Kelas Orang Luar</div><div class="fs-4 fw-semibold text-dark">${s.kelas_ol||0}</div></div></div>
                                            <div class="col-md-6"><div class="p-3 rounded-3 bg-white border h-100"><div class="text-muted small mb-2">Kelas Offline</div><div class="fs-4 fw-semibold text-dark">${s.kelas_offline||0}</div></div></div>
                                            <div class="col-md-6"><div class="p-3 rounded-3 bg-white border h-100"><div class="text-muted small mb-2">Kelas Online</div><div class="fs-4 fw-semibold text-dark">${s.kelas_online||0}</div></div></div>
                                        </div>
                                        <div class="p-3 rounded-3 bg-white border">
                                            <div class="fw-semibold mb-3">Kelas Inhouse</div>
                                            <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted small">Bandung</span><span class="fw-semibold text-dark">${t.kelas_inhouse||0}</span></div>
                                            <div class="d-flex justify-content-between py-2"><span class="text-muted small">Luar Bandung</span><span class="fw-semibold text-dark">${t.kelas_inhouse_luar||0}</span></div>
                                        </div>
                                    </div>
                                </div>
                            `}let Ta="";const Y=["pemasukan kotor"].includes(String(a.condition).toLowerCase().trim())?((ua=a.data_detail)==null?void 0:ua.kontribusi_sales)??null:null;if(Y){const s={on_track:["bg-primary","On Track"],selesai:["bg-success","Selesai"],gagal:["bg-danger","Gagal"]},t=o=>{const[i,r]=s[o]||["bg-secondary","-"];return`<span class="badge ${i}">${r}</span>`},b=o=>o<0?"text-danger":"text-success",c=(Y.rows||[]).map((o,i)=>`
                                <tr>
                                    <td class="text-center">${i+1}</td>
                                    <td class="fw-semibold">${o.nama||"-"}</td>
                                    <td class="text-end">${y(o.triwulan[1])}</td>
                                    <td class="text-end">${y(o.triwulan[2])}</td>
                                    <td class="text-end">${y(o.triwulan[3])}</td>
                                    <td class="text-end">${y(o.triwulan[4])}</td>
                                    <td class="text-end fw-bold text-primary">${y(o.total)}</td>
                                    <td class="text-end">${y(o.target)}</td>
                                    <td class="text-end fw-semibold ${b(o.selisih)}">${y(o.selisih)}</td>
                                    <td class="text-center">${I(o.persentase)}%</td>
                                    <td class="text-center">${t(o.status)}</td>
                                </tr>`).join(""),v=Y.total,u=v?`
                                <tfoot class="table-light fw-bold">
                                    <tr>
                                        <td colspan="2" class="text-center">TOTAL</td>
                                        <td class="text-end">${y(v.triwulan[1])}</td>
                                        <td class="text-end">${y(v.triwulan[2])}</td>
                                        <td class="text-end">${y(v.triwulan[3])}</td>
                                        <td class="text-end">${y(v.triwulan[4])}</td>
                                        <td class="text-end text-primary">${y(v.total)}</td>
                                        <td class="text-end">${y(v.target)}</td>
                                        <td class="text-end ${b(v.selisih)}">${y(v.selisih)}</td>
                                        <td class="text-center">${I(v.persentase)}%</td>
                                        <td class="text-center">${t(v.status)}</td>
                                    </tr>
                                </tfoot>`:"";Ta=`
                                <div class="card shadow-sm border-0 rounded-4">
                                    <div class="card-body">
                                        <h6 class="fw-semibold mb-3"><i class="fa-solid fa-users me-2"></i>Kontribusi Sales ${Y.tahun??""}</h6>
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
                                                ${u}
                                            </table>
                                        </div>
                                    </div>
                                </div>`}let X="";if(Ca.includes(a.condition)){const s=((ma=a.data_detail.dataManual)==null?void 0:ma.manual_document)||"",t=s?s.split("/").pop():"",b=t?t.split(".").pop().toLowerCase():"",c=["jpg","jpeg","png"],v=["pdf"];let u="";const o=s?`/storage/${s}`:"";c.includes(b)?u=`<div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3"><div class="mb-3" style="max-width: 100%; max-height: 300px;"><img src="${o}" alt="${t}" class="img-fluid rounded shadow-sm" style="max-width: 100%; max-height: 300px; object-fit: contain;"></div><div class="mt-2 text-center"><a href="${o}" download="${t}" class="btn btn-primary btn-sm"><i class="fa-solid fa-download me-1"></i>Download Gambar</a></div><div class="mt-2 small text-muted"><i class="fa-solid fa-file-image me-1"></i>${t}</div></div>`:v.includes(b)?u=`<div class="w-100 h-100 d-flex flex-column"><div class="flex-grow-1 mb-3" style="min-height: 250px;"><iframe src="${o}" class="w-100 h-100" style="border: 1px solid #dee2e6; border-radius: 8px;"></iframe></div><div class="text-center"><a href="${o}" download="${t}" class="btn btn-primary btn-sm"><i class="fa-solid fa-download me-1"></i>Download PDF</a></div><div class="mt-2 small text-muted text-center"><i class="fa-solid fa-file-pdf me-1"></i>${t}</div></div>`:u='<div class="text-center py-5"><div class="mb-3"><i class="fa-solid fa-file text-secondary" style="font-size: 4rem;"></i></div><p class="text-muted mb-3">File tidak dapat ditampilkan</p><p class="text-muted small">Hanya gambar dan PDF yang dapat ditampilkan</p></div>',X=`<h6 class="fw-semibold mb-3 text-secondary"><i class="fa-solid fa-file me-2"></i>Dokumen Manual</h6><div class="manual-document-container flex-grow-1 d-flex flex-column align-items-center justify-content-center p-3" style="background-color: #f8f9fa; border-radius: 8px;">${u}</div><div class="mt-3 small text-muted text-center"><i class="fa-solid fa-info-circle me-1"></i>Klik tombol download untuk menyimpan file</div>`}else qa.includes(a.condition)?X=`<div class="mb-4"><h6 class="fw-semibold text-primary mb-1"><i class="fa-solid fa-wallet me-2"></i>${a.judul}</h6><small class="text-muted">Ringkasan performa</small></div><div class="mb-4 p-3 rounded bg-light"><div class="text-muted small mb-1">Bulan Terakhir</div><div class="fw-semibold">${Ra}</div><div class="fw-bold fs-6 text-dark">${y(Ga)}</div></div><div class="mb-3"><div class="text-muted small mb-2">Top Hari Tertinggi</div>${Ja.map(([s,t],b)=>`<div class="d-flex justify-content-between mb-1 ${b>0?"text-muted":""}"><span>${Ia(s)}</span><span class="fw-semibold">${y(t)}</span></div>`).join("")}</div><hr class="my-3"><div class="d-flex align-items-center gap-2 text-muted small"><i class="fa-solid fa-user-circle fs-5"></i><span class="fw-semibold text-dark">${(F==null?void 0:F.nama_lengkap)??"-"}</span><span>• ${(F==null?void 0:F.jabatan)??"-"}</span></div>`:X=`<h6 class="fw-semibold mb-3 text-secondary"><i class="fa-solid fa-chart-pie me-2"></i>Chart ${a.condition}</h6><div class="chart-container flex-grow-1"><canvas id="MyChartDoughtnut"></canvas></div>`;let M="";if(Ca.includes(a.condition))M="";else if(Qa.includes(a.condition)){const t=(a.data_detail||{}).dataInovasi||[];M=`
                                <div class="table-responsive mt-3">
                                    <table class="table table-bordered table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 30%;">Judul</th>
                                                <th>Deskripsi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${t.length?t.map(b=>`
                                                        <tr>
                                                            <td class="fw-semibold">
                                                                ${b.judul??"-"}
                                                            </td>
                                                            <td>
                                                                ${b.deskripsi??"-"}
                                                            </td>
                                                        </tr>
                                                    `).join(""):`
                                                        <tr>
                                                            <td colspan="2" class="text-center text-muted">
                                                                Tidak ada data inovasi
                                                            </td>
                                                        </tr>
                                                    `}
                                        </tbody>
                                    </table>
                                </div>
                            `}else if(Za.includes(a.condition)){const s=["","Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"];M=`<div class="row g-4">${(a.data_detail.analisa_data||[]).map(t=>`<div class="col-md-4 mt-5"><div class="card border-0 shadow-sm h-100"><div class="mb-2 p-3"><h5 class="fw-bold text-primary mb-1">${s[t.month]||"-"}</h5><small class="text-muted">Laporan Analisis Bulanan</small></div><div class="card-body d-flex flex-column" style="overflow-y: scroll; max-height: 280px;"><div class="mb-3"><p class="mb-0" style="text-align: justify;">${t.description||""}</p></div></div><div class="mt-auto p-3"><a href="/storage/${t.file_paths||""}" class="btn btn-sm btn-outline-primary w-100" ${t.file_paths?"download":""}><i class="fa-solid fa-file-alt"></i></a></div></div></div>`).join("")}</div>`}else if(Xa.includes(a.condition)){const s=["Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"];M=`
                                <div class="mt-4"><div class="row g-4">${(((ba=a.data_detail.previous_quarter)==null?void 0:ba.data)||[]).map((t,b)=>`
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="card border-0 shadow-sm h-100 quarter-card">
                                                <div class="card-body d-flex flex-column p-4">
                                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                                        <div><h6 class="fw-semibold text-muted mb-1">Periode</h6><h5 class="fw-bold mb-0">${s[(t.month||1)-1]??"-"}</h5></div>
                                                        <span class="badge rounded-pill bg-${t.color||"secondary"} bg-opacity-10 text-${t.color||"secondary"} px-3 py-2">Laporan</span>
                                                    </div>
                                                    <div class="mb-3"><h3 class="fw-bold text-dark mb-0">Rp ${t.nilai?Number(t.nilai).toLocaleString("id-ID"):"-"}</h3><small class="text-muted">Total Pemasukan</small></div>
                                                    <div class="flex-grow-1">
                                                        <p class="text-muted small mb-2 description-text" id="desc-${b}">${t.description??"-"}</p>
                                                        ${t.description&&t.description.length>100?`<button class="btn btn-sm btn-link p-0 text-primary btn-toggle-desc" data-target="desc-${b}">Lihat Selengkapnya</button>`:""}
                                                    </div>
                                                    <div class="d-flex justify-content-end align-items-center mt-4">
                                                        <a href="/storage/${t.file_paths||""}" class="btn btn-sm btn-dark d-flex align-items-center gap-2" ${t.file_paths?"download":""}><i class="fa-solid fa-download"></i> Download</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    `).join("")}</div></div>
                                <style>.quarter-card { border-radius: 16px; transition: all 0.25s ease; background: #ffffff; }.quarter-card:hover { transform: translateY(-6px) scale(1.01); box-shadow: 0 15px 35px rgba(0,0,0,0.08); }.quarter-card h3 { letter-spacing: 0.5px; }.quarter-card .badge { font-size: 12px; font-weight: 500; }.description-text { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }.description-text.expanded { -webkit-line-clamp: unset; overflow: visible; }.quarter-card .btn { border-radius: 8px; font-size: 13px; }</style>
                            `,setTimeout(()=>{document.querySelectorAll(".btn-toggle-desc").forEach(t=>{t.addEventListener("click",function(){const b=this.getAttribute("data-target"),c=document.getElementById(b);c&&(c.classList.contains("expanded")?(c.classList.remove("expanded"),this.innerText="Lihat Selengkapnya"):(c.classList.add("expanded"),this.innerText="Sembunyikan"))})})},0)}else if(Ua.includes(a.condition)){const s=a.can_edit??(((va=window.KPI_CONFIG)==null?void 0:va.userJabatan)||"").toLowerCase().trim()==="koordinator itsm";M=n(a,s,window.KPI_CONFIG.csrfToken)}else M=`<div class="mt-4"><div class="card shadow-sm border-0 rounded-4"><div class="card-body"><div class="d-flex justify-content-between mb-3"><h6 class="fw-semibold mb-0">Statistik ${a.condition}</h6><div class="d-flex gap-2"><select class="form-select form-select-sm" id="filterType"><option value="year">Per Tahun</option><option value="month">Per Bulan</option></select><select class="form-select form-select-sm d-none" id="filterMonth"><option value="">Pilih Bulan</option></select></div></div><div style="height:300px"><canvas id="StatisticChart"></canvas></div></div></div></div>`;let Da="";const la=Array.isArray(a.data_detail.instruktur_details)?a.data_detail.instruktur_details:[],Sa=a.data_detail.hari_libur_nasional||{jumlah:0,daftar:{}};if(la.length>0){let b=function(i,r){const k=i.kalender||{};if(!k[r])return'<div class="text-muted text-center py-4">Tidak ada data untuk bulan ini.</div>';const[j,T]=r.split("-"),f=s[parseInt(T,10)-1],S=new Date(j,T,0).getDate(),P=new Date(j,parseInt(T,10)-1,1).getDay();let D="";t.forEach(_=>{D+=`<div class="kal-header">${_}</div>`});for(let _=0;_<P;_++)D+='<div class="kal-cell kal-empty"></div>';for(let _=1;_<=S;_++){const B=k[r][_]||{status:"empty",keterangan:""},at="kal-"+B.status;D+=`<div class="kal-cell ${at}" title="${B.keterangan||""}" data-bs-toggle="tooltip"><div class="kal-day">${_}</div></div>`}const L=Object.values(k[r]).reduce((_,B)=>(_[B.status]=(_[B.status]||0)+1,_),{});return`
                                    <div class="kalender-bulan-slide" data-bulan="${r}">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="fw-semibold mb-0">${f} ${j}</h6>
                                            <div class="d-flex gap-2 small">
                                                <span class="badge bg-success">Aktif: ${L.working||0}</span>
                                                <span class="badge bg-warning text-dark">Cuti: ${L.cuti||0}</span>
                                                <span class="badge bg-danger">Libur: ${L.libur||0}</span>
                                            </div>
                                        </div>
                                        <div class="kalender-grid">${D}</div>
                                    </div>
                                `},c=function(i){const r=Object.entries(i.daftar_cuti||{});return r.length===0?"":`
                                    <div class="mt-3">
                                        <h6 class="fw-semibold small text-muted mb-2"><i class="fa-solid fa-suitcase-rolling me-1"></i>Detail Cuti (${r.length} hari)</h6>
                                        <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                                            <table class="table table-sm table-bordered mb-0">
                                                <thead class="table-warning"><tr><th>Tanggal</th><th>Tipe</th><th>Alasan</th></tr></thead>
                                                <tbody>
                                                    ${r.map(([k,j])=>`
                                                        <tr>
                                                            <td class="small">${new Date(k).toLocaleDateString("id-ID",{day:"2-digit",month:"short",year:"numeric"})}</td>
                                                            <td class="small">${j.tipe||"-"}</td>
                                                            <td class="small">${j.alasan||"-"}</td>
                                                        </tr>
                                                    `).join("")}
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                `};var H=b,aa=c;const s=["Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"],t=["Min","Sen","Sel","Rab","Kam","Jum","Sab"];window.__instrukturData=la,window.__currentMonthIndex={},window.showKalenderBulan=function(i,r){const k=window.__instrukturData.find(B=>B.id==i);if(!k)return;const j=k.kalender||{},T=Object.keys(j).sort(),f=T.length;if(f===0)return;r<0&&(r=f-1),r>=f&&(r=0),window.__currentMonthIndex[i]=r;const S=T[r],P=document.getElementById(`kalenderSlide_${i}`);P&&(P.innerHTML=b(k,S));const D=document.getElementById(`bulanCounter_${i}`);D&&(D.textContent=`${r+1} dari ${f} bulan`);const L=document.getElementById(`btnPrevBulan_${i}`),_=document.getElementById(`btnNextBulan_${i}`);L&&(L.disabled=f<=1),_&&(_.disabled=f<=1)};let v="";const u=Object.entries(Sa.daftar||{});u.length>0&&(v=`
                                    <div class="alert alert-light border mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0 fw-semibold"><i class="fa-solid fa-flag me-2 text-danger"></i>Hari Libur Nasional</h6>
                                            <span class="badge bg-danger">${Sa.jumlah} Hari</span>
                                        </div>
                                        <div class="row g-2" style="max-height: 150px; overflow-y: auto;">
                                            ${u.map(([i,r])=>`
                                                <div class="col-md-6">
                                                    <div class="small p-2 bg-danger bg-opacity-10 rounded">
                                                        <strong>${new Date(i).toLocaleDateString("id-ID",{day:"2-digit",month:"short",year:"numeric"})}</strong>
                                                        <div class="text-muted">${r}</div>
                                                    </div>
                                                </div>
                                            `).join("")}
                                        </div>
                                    </div>
                                `);let o="";la.forEach((i,r)=>{const k=i.persentase>=100?"success":i.persentase>=75?"warning":"danger",j=i.kalender||{},T=Object.keys(j).sort(),f=T.length;window.__currentMonthIndex[i.id]=0;const S=c(i);o+=`
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="headingInstruktur${r}">
                                            <button class="accordion-button ${r>0?"collapsed":""}" type="button" data-bs-toggle="collapse" data-bs-target="#collapseInstruktur${r}" aria-expanded="${r===0?"true":"false"}">
                                                <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                                    <div>
                                                        <strong>${i.nama}</strong>
                                                        <div class="small text-muted">${i.kode_karyawan} • ${i.jabatan}</div>
                                                    </div>
                                                    <div class="text-end">
                                                        <div class="fw-bold text-${k}">${I(i.persentase)}%</div>
                                                        <div class="small text-muted">${i.jam_aktif} / ${i.target_jam} jam</div>
                                                    </div>
                                                </div>
                                            </button>
                                        </h2>
                                        <div id="collapseInstruktur${r}" class="accordion-collapse collapse ${r===0?"show":""}" data-bs-parent="#accordionInstruktur">
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
                                                ${S}
                                                <div class="mt-3">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <h6 class="fw-semibold mb-0"><i class="fa-solid fa-calendar me-2"></i>Kalender Bulanan</h6>
                                                        <span class="badge bg-info" id="bulanCounter_${i.id}">1 dari ${f} bulan</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center gap-2 mb-3 p-2 bg-light rounded-3">
                                                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnPrevBulan_${i.id}" onclick="showKalenderBulan(${i.id}, window.__currentMonthIndex[${i.id}] - 1)"><i class="fa-solid fa-chevron-left me-1"></i> Bulan Sebelumnya</button>
                                                        <div class="flex-grow-1 text-center"><small class="text-muted">Gunakan tombol atau swipe untuk navigasi</small></div>
                                                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnNextBulan_${i.id}" onclick="showKalenderBulan(${i.id}, window.__currentMonthIndex[${i.id}] + 1)">Bulan Selanjutnya <i class="fa-solid fa-chevron-right ms-1"></i></button>
                                                    </div>
                                                    <div id="kalenderSlide_${i.id}">
                                                        ${f>0?b(i,T[0]):'<div class="text-muted text-center py-4">Tidak ada data kalender.</div>'}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `}),Da=`
                                <div class="mt-3">
                                    <div class="card shadow-sm border-0 rounded-4">
                                        <div class="card-body">
                                            <h6 class="fw-semibold mb-3"><i class="fa-solid fa-calendar-days me-2"></i>Kalender Kinerja Instruktur</h6>
                                            ${v}
                                            <div class="accordion" id="accordionInstruktur">
                                                ${o}
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
                                </style>
                            `}d.append(`
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title fw-bold">${a.judul}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body pt-3">
                                <div class="container-fluid p-3">
                                    <div class="row g-4">
                                        <div class="col-lg-8">
                                            <div class="card shadow h-100">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                                        <span class="badge bg-primary">${a.jangka_target}</span>
                                                        <span class="badge bg-${sa}">${A}</span>
                                                    </div>
                                                    <div class="row text-center mb-3">
                                                        <div class="col"><small class="text-muted d-block">Target</small><h4 class="fw-bold mb-0" style="font-size: 25px">${z}</h4></div>
                                                        <div class="col"><small class="text-muted d-block">Progress</small><h1 class="fw-bold text-${sa} mb-0" style="font-size: 55px;">${q}</h1></div>
                                                        <div class="col"><small class="text-muted d-block">Gap</small><h4 class="fw-bold text-danger mb-0" style="font-size: 25px">${wa?R:`-${R}`}</h4></div>
                                                    </div>
                                                    <div class="position-relative mb-3">
                                                        <div class="progress" style="height:18px;"><div class="progress-bar bg-${sa} progress-bar-striped progress-bar-animated" style="width: ${U}%"></div></div>
                                                        <div class="position-absolute bg-light top-0" style="left:${a.nilai_target}%; height:18px; width:2px;"></div>
                                                    </div>
                                                    <div class="text-muted mb-4"><i class="fa-solid fa-calendar-days me-1"></i> Deadline: <strong>${a.tenggat_waktu}</strong></div>
                                                    <div class="row g-4">
                                                        <div class="col-md-6">
                                                            <div class="card border-0 shadow-sm rounded-4 kpi-card">
                                                                <div class="card-body px-4 py-3">
                                                                    <div class="d-flex align-items-center mb-3"><div class="me-2 rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width:36px;height:36px;"><i class="fa-solid fa-chart-line text-primary"></i></div><h6 class="mb-0 fw-semibold text-secondary">INFORMASI KPI</h6></div>
                                                                    <div class="row mb-3"><div class="col-4 label">KPI Divisi</div><div class="col-8 value">${a.divisi_kpi||"-"}</div></div>
                                                                    <div class="row mb-3"><div class="col-4 label">KPI Jabatan</div><div class="col-8 value">${a.jabatan_kpi||"-"}</div></div>
                                                                    <div class="row"><div class="col-4 label">Pembuat</div><div class="col-8 value">${a.pembuat||"-"}</div></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="card border-0 shadow-sm rounded-4 participant-card h-100">
                                                                <div class="card-body px-4 py-3">
                                                                    <div class="d-flex align-items-center mb-3"><div class="me-2 rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style="width:36px;height:36px;"><i class="fa-solid fa-users text-success"></i></div><h6 class="mb-0 fw-semibold text-secondary">KARYAWAN</h6></div>
                                                                    <div class="participant-list" style="overflow-y: scroll; max-height: 140px;">
                                                                        ${za||'<div class="text-muted small">Tidak ada karyawan</div>'}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="card shadow h-100 border-0 rounded-4">
                                                <div class="card-body d-flex flex-column">${X}</div>
                                            </div>
                                        </div>
                                    </div>
                                    ${W}
                                    ${Ta}
                                    ${M}
                                    ${Da}
                                </div>
                            </div>
                            <div class="modal-footer border-0"><button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button></div>
                        `),setTimeout(()=>{d.addClass("modal-content-loaded");const s=document.getElementById("MyChartDoughtnut");s&&new Chart(s,{type:"doughnut",data:Ea,options:{responsive:!0,maintainAspectRatio:!1,plugins:{legend:{position:"bottom",labels:{boxWidth:12,padding:15}}}}});let t=null;const b=document.getElementById("StatisticChart");function c(o,i,r){if(!b)return;t&&t.destroy();const j=(i.length>0?Math.max(...i):0)+3;t=new Chart(b,{type:"line",data:{labels:o,datasets:[{label:r,data:i,borderColor:"#4e73df",backgroundColor:"rgba(78, 115, 223, 0.1)",tension:.4,fill:!0}]},options:{responsive:!0,maintainAspectRatio:!1,scales:{y:{beginAtZero:!0,suggestedMax:j,ticks:{count:6,precision:0,callback:function(T){return Math.round(T)}}}}}})}const v=Object.keys(ta).map(o=>G(o)),u=Object.values(ta);c(v,u,"Rata-rata"),$("#filterType").off("change").on("change",function(){this.value==="month"?($("#filterMonth").removeClass("d-none").empty().append('<option value="">Pilih Bulan</option>'),Object.keys(O).forEach(o=>{$("#filterMonth").append(`<option value="${o}">${G(o)}</option>`)}),t&&t.destroy()):($("#filterMonth").addClass("d-none"),c(v,u,"Rata-rata"))}),$("#filterMonth").off("change").on("change",function(){const o=this.value;if(!o||!O[o])return;const i=Object.keys(O[o]).map(k=>k.substring(8)),r=Object.values(O[o]);c(i,r,`Tanggal ${G(o)}`)})},50)}},error:function(){d.empty(),d.html(`
                        <div class="modal-header border-0 pb-2">
                            <h5 class="modal-title fw-bold mb-1 text-danger">Gagal Memuat</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center py-4">
                            <i class="fa-solid fa-circle-exclamation text-danger" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                            <h6 class="fw-semibold">Terjadi Kesalahan Jaringan</h6>
                            <p class="text-muted small">Silakan coba lagi nanti.</p>
                        </div>
                        <div class="modal-footer border-0">
                            <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    `),d.addClass("modal-content-loaded")}})});function n(l,d,e){const g=Array.isArray(l.karyawan)?l.karyawan:[],p=d?"":"disabled",a=g.map((x,h)=>{const C=x.skala_kemampuan??null,m=C===null?'<span class="badge bg-secondary">Belum dinilai</span>':`<span class="badge bg-info text-dark">${I(C*10)}%</span>`;return`
                    <div class="row mb-2 align-items-center p-2 rounded">
                        <div class="col-md-6 d-flex justify-content-between align-items-center">
                            <span>${x.nama_lengkap??"-"}</span>${m}
                        </div>
                        <div class="col-md-6">
                            <input type="number" min="1" max="10" step="0.5" class="form-control"
                                name="data[${h}][kemampuan]" value="${C??""}"
                                placeholder="1 - 10" ${p}>
                        </div>
                        <input type="hidden" name="data[${h}][id_karyawan]" value="${x.id_karyawan}">
                        <input type="hidden" name="data[${h}][detailTargetKey]" value="${x.detailTargetKey}">
                    </div>`}).join("");return`
                <div class="mt-4"><div class="card shadow-sm border-0 rounded-4"><div class="card-body">
                    <h6 class="fw-semibold mb-1">Penilaian Kemampuan Tim</h6>
                    <small class="text-muted d-block mb-3">Isi skala 1-10 (8 = 80%). Gap dihitung dari rata-rata seluruh anggota tim.</small>
                    <form id="formGapKompetensi">
                        <input type="hidden" name="_token" value="${e}">
                        <div class="row mb-2 fw-semibold text-muted border-bottom pb-2">
                            <div class="col-md-6">Nama Karyawan</div><div class="col-md-6">Nilai (1-10)</div>
                        </div>
                        ${a||'<div class="text-muted small">Tidak ada anggota tim.</div>'}
                        ${d?'<div class="mt-3"><button type="submit" class="btn btn-primary">Simpan</button></div>':""}
                    </form>
                </div></div></div>`}})})();
