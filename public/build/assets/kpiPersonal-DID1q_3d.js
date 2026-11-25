const g=v=>(Number(v)||0).toFixed(1);(function(){let v=null,D=null,x=[];const G=["dorong inovasi pelayanan","inisiatif efisiensi keuangan","mengurangi manual work dan error"],O=window.allowedDoubleManualRoutes||[];function h(t){if(!t&&t!=="0")return"";const a=String(t).replace(/[^0-9]/g,"");return a?new Intl.NumberFormat("id-ID").format(a):""}function y(t){return t?String(t).replace(/[^0-9]/g,""):""}function E(){$("#manual_value_display").off("input.formatting").on("input.formatting",function(){const t=y($(this).val());$("#manual_value").val(t);const a=$("#manual_format").val();let i=h(t);a==="rupiah"&&t?i="Rp "+i:a==="persen"&&t&&(i=i+"%"),$(this).val(i)}),["biaya_gaji","biaya_bpjs","biaya_rekrutmen"].forEach(t=>{$(`#${t}_display`).off("input.formatting").on("input.formatting",function(){const a=y($(this).val());$(`#${t}_tahunan`).val(a),$(this).val(a?"Rp "+h(a):"")})})}function T(){$("#formManualValue")[0].reset(),$("#documentPreview").html(""),$("#singleInputArea").show(),$("#doubleInputArea").hide(),$("#manual_value_display").val("").trigger("input"),$("#manual_value").val(""),["biaya_gaji","biaya_bpjs","biaya_rekrutmen"].forEach(t=>{$(`#${t}_display`).val("").trigger("input"),$(`#${t}_tahunan`).val("")}),$("#manualValueId").val("")}function P(t){typeof Swal<"u"?Swal.fire({icon:"error",title:"Error",text:t,confirmButtonText:"OK"}):alert(t)}function k(t){const a=t?"block":"none";document.querySelectorAll(".skeleton-overlay").forEach(i=>{i.style.display=a})}function R(){k(!0),$("#targetCardContainer").css("opacity","0.5");const t=window.KPI_CONFIG;$.ajax({url:t.dataPersonalRoute,type:"GET",data:{tahun:t.currentYear,id_karyawan:t.targetId},dataType:"json",success:function(a){var i,s,r;if(a.success){k(!1),$("#targetCardContainer").css("opacity","1");const e=(((i=window.KPI_CONFIG)==null?void 0:i.userJabatan)||((s=window.authUser)==null?void 0:s.jabatan)||((r=window.authUser)==null?void 0:r.jabatan_user_table)||"").toLowerCase().trim(),d=["gm","hrd","direktur utama","direktur"],u=e.includes("koordinator itsm")||e==="koordinator itsm"||e.includes("koordinator")||d.includes(e);let o=a.daftar_target_pribadi||[],c=a.statistik_per_target||[];if(!u){const l=n=>{const f=(n.asistant_route||n.assistant_route||n.condition||"").toLowerCase(),_=(n.judul||"").toLowerCase();return f.includes("gap kompetensi")||f.includes("persentase gap")||_.includes("gap performa")||_.includes("gap kompetensi")};o=o.filter(n=>!l(n)),c=c.filter(n=>!l(n)),a.total_target=o.length,a.kpi_aktif=o.filter(n=>["Aktif","Sedang Berjalan"].includes(n.status)).length,a.kpi_selesai=o.filter(n=>["Selesai","Mencapai Target"].includes(n.status)).length,a.grade=o.length?KpiGrade.calc(a.rata_rata_progress):null}console.log("jabatanUser:",e,"isKoordinator:",u),console.log("targets after filter:",o.map(l=>l.judul)),V(a),z(a),L(c,a.distribusi_status),M(o),x=o}else k(!1),P(a.message||"Terjadi kesalahan saat memuat data")},error:function(){k(!1),P("Gagal memuat data. Silakan coba lagi.")}})}function V(t){var a,i,s;$("#userName").text(((a=t.user_info)==null?void 0:a.nama)||"User"),$("#userJabatan").text(((i=t.user_info)==null?void 0:i.jabatan)||"-"),$("#userDivisi").text(((s=t.user_info)==null?void 0:s.divisi)||"-")}function z(t){var a;$("#stat0").text(t.total_target+" Target"),$("#stat1").html(g(t.rata_rata_progress)+"%"+KpiGrade.badge(t.grade,{nama:(a=t.user_info)==null?void 0:a.nama,total:t.total_target})),$("#stat2").text(t.kpi_aktif),$("#stat3").text(t.kpi_selesai)}function L(t,a){v&&v.destroy(),D&&D.destroy();const i=t.map(l=>l.judul.length>20?l.judul.substring(0,20)+"...":l.judul),s=t.map(l=>(l.tipe_target==="rupiah"||l.tipe_target==="angka")&&l.target>0?Number(g(l.progress/l.target*100)):Number(g(l.progress))),r=t.map(l=>{let n=l.progress||0;return n===0?"rgba(254, 215, 24, 0.7)":l.tipe_target==="rupiah"?(l.target?n/l.target*100:0)>=100?"rgba(40, 207, 180, 0.7)":"rgba(254, 124, 150, 0.7)":n>=l.target?"rgba(40, 207, 180, 0.7)":"rgba(254, 124, 150, 0.7)"}),e=r.map(l=>l.replace("0.7","1")),d=document.getElementById("performanceChart");d&&(v=new Chart(d.getContext("2d"),{type:"bar",data:{labels:i,datasets:[{label:"Progress (%)",data:s,backgroundColor:r,borderColor:e,borderWidth:1,borderRadius:6}]},options:{responsive:!0,maintainAspectRatio:!1,plugins:{legend:{display:!1},tooltip:{callbacks:{label:l=>"Progress: "+g(l.parsed.y)+"%"}}},scales:{y:{beginAtZero:!0,ticks:{callback:l=>l+"%"}}}}}));const u=Object.keys(a),o=Object.values(a),c=document.getElementById("statusPieChart");c&&(D=new Chart(c.getContext("2d"),{type:"doughnut",data:{labels:u,datasets:[{data:o,backgroundColor:["#28CFB4","#FE7C96","#FED718"],borderWidth:0,hoverOffset:8}]},options:{responsive:!0,maintainAspectRatio:!1,plugins:{legend:{position:"bottom"}},cutout:"65%"}}))}function M(t){const a=$("#targetCardContainer");if(!t||t.length===0){a.html('<div class="col-12"><div class="content-card"><div class="card-body text-center py-5"><i class="fa-solid fa-inbox fa-4x text-muted mb-3"></i><h5 class="text-muted">Belum ada target KPI</h5></div></div></div>');return}let i="";t.forEach(s=>{let r=s.progress>=s.target?"bg-success":"bg-primary",e=s.status==="Selesai"?"bg-success":"bg-warning text-dark",d=s.progress>=75?"border-success":s.progress>=50?"border-primary":"border-warning",u=s.tipe_target==="rupiah"||s.tipe_target==="angka"?s.progress/s.target*100:s.progress,o=s.tipe_target==="rupiah"?"Rp "+Number(s.target).toLocaleString("id-ID"):s.tipe_target==="persen"?s.target+"%":s.target,c=s.tipe_target==="rupiah"?"Rp "+Number(s.progress).toLocaleString("id-ID"):s.tipe_target==="angka"?s.progress:g(s.progress)+"%",l=G.includes(s.asistant_route)?"true":"";i+=`
                <div class="col-12 col-sm-6 col-lg-4 mb-4">
                    <div class="target-card border-start border-4 ${d}">
                        ${l?`<button type="button" class="action-btn buttonForm" data-id="${s.id}" data-value="${s.manual_value}" data-route="${s.asistant_route}" data-bs-toggle="modal" data-bs-target="#modalFormManual" title="Isi Data"><i class="fa-solid fa-file-pen"></i></button>`:""}
                        <button type="button" class="btn btn-link p-0 text-start w-100 h-100 text-decoration-none buttonDetailTarget" data-id="${s.id}" data-bs-toggle="modal" data-bs-target="#detailTargetModal" style="outline: none;">
                            <div class="card-body-inner">
                                <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                                    <span class="badge bg-primary bg-opacity-10 text-primary status-badge">${s.periode}</span>
                                    <span class="badge ${e} status-badge">${s.status}</span>
                                </div>
                                <h6 class="fw-bold text-dark mb-2 lh-base title-badge">${s.judul}</h6>
                                <p class="text-muted small mb-3" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 40px;">${s.deskripsi||"Tidak ada deskripsi"}</p>
                                <div class="mt-auto">
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <small class="text-muted fw-semibold" style="font-size: 0.8rem;">Progress</small>
                                            <small class="fw-bold text-primary" style="font-size: 0.8rem;">${s.progress_display}</small>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar ${r}" role="progressbar" style="width: ${u}%"></div>
                                        </div>
                                    </div>
                                    <div class="row g-2 pt-2 border-top">
                                        <div class="col-6">
                                            <small class="text-muted d-block" style="font-size: 0.75rem;">Target</small>
                                            <strong class="text-dark" style="font-size: 0.9rem;">${o}</strong>
                                        </div>
                                        <div class="col-6 text-end">
                                            <small class="text-muted d-block" style="font-size: 0.75rem;">Realisasi</small>
                                            <strong class="text-primary" style="font-size: 0.9rem;">${c}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </button>
                    </div>
                </div>
            `}),a.html(i)}$(document).ready(function(){E(),R(),$("#modalFormManual").on("show.bs.modal",T),$("#modalFormManual").on("hidden.bs.modal",T),$(document).on("click",".filter-btn-group .btn",function(){$(".filter-btn-group .btn").removeClass("active"),$(this).addClass("active");let t=$(this).data("filter"),a=x;t==="active"?a=x.filter(i=>["Aktif","Sedang Berjalan"].includes(i.status)):t==="completed"&&(a=x.filter(i=>i.status==="Selesai")),M(a)}),$(document).on("click",".buttonForm",function(){const t=$(this).data("route"),a=$(this).data("value")||"",i=$(this).data("id");if($("#manualValueId").val(i),O.includes(t)){$("#singleInputArea").hide(),$("#doubleInputArea").show();let s=a&&a.includes(",")?a.split(","):[a,"",""];["biaya_gaji","biaya_bpjs","biaya_rekrutmen"].forEach((r,e)=>{const d=y(s[e]);$(`#${r}_display`).val(d?"Rp "+h(d):"").trigger("input"),$(`#${r}_tahunan`).val(d)})}else{$("#singleInputArea").show(),$("#doubleInputArea").hide();const s=$("#manual_format").val(),r=y(a);let e=h(r);s==="rupiah"&&r?e="Rp "+e:s==="persen"&&r&&(e=e+"%"),$("#manual_value_display").val(e),$("#manual_value").val(r)}}),$(document).on("change","#manual_format",function(){if($("#doubleInputArea").is(":visible"))return;const t=$(this).val(),a=y($("#manual_value").val());let i=h(a);t==="rupiah"&&a?i="Rp "+i:t==="persen"&&a&&(i=i+"%"),$("#manual_value_display").val(i)}),$("#formManualValue").on("submit",function(t){t.preventDefault();const a=$(this).find('button[type="submit"]'),i=a.html();a.prop("disabled",!0).html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Menyimpan...');const s=new FormData(this);$("#doubleInputArea").is(":visible")?(s.set("biaya_gaji_tahunan",$("#biaya_gaji_tahunan").val()),s.set("biaya_bpjs_tahunan",$("#biaya_bpjs_tahunan").val()),s.set("biaya_rekrutmen_tahunan",$("#biaya_rekrutmen_tahunan").val())):(s.set("manual_value",$("#manual_value").val()),s.set("manual_format",$("#manual_format").val())),$.ajax({url:window.KPI_CONFIG.manualValueRoute,type:"POST",data:s,contentType:!1,processData:!1,success:function(){$("#modalFormManual").modal("hide"),T(),a.prop("disabled",!1).html(i),R()},error:function(r){var e;if(a.prop("disabled",!1).html(i),r.status===422){const d=((e=r.responseJSON)==null?void 0:e.errors)||{};alert(Object.values(d).map(u=>u[0]).join(`
`))}else alert("Terjadi kesalahan sistem.")}})}),$(document).on("click",".buttonDetailTarget",function(){let t=$(this).data("id");const a=$("#bodyContentDetailTarget");a.html(KpiDetail.skeleton("personal"));const i=document.getElementById("detailTargetModal");i&&new bootstrap.Modal(i).show(),$.ajax({url:window.KPI_CONFIG.detailRoute,method:"GET",data:{id:t,idUser:window.KPI_CONFIG.targetId},dataType:"json",success:function(s){a.empty();let r=s.detail,e=r&&r.length>0?r[0].data:null;if(!e)a.append(`
                            <div class="modal-header"><h5 class="modal-title">Detail Target</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                            <div class="modal-body text-center py-5">Belum ada data</div>
                            <div class="modal-footer"><button class="btn btn-danger" data-bs-dismiss="modal">Tutup</button></div>
                        `);else{let n=function(b,S){return new Date(b)<new Date(S)},f=function(b,S){return new Date(b)>new Date(S)};var d=n,u=f;const o=new Date().toISOString().split("T")[0],c=new Date(new Date().getFullYear(),0,1).toISOString().split("T")[0],l=e.tenggat_waktu,_=e.data_detail||{},A=_.progress??0,K=e.nilai_target??0,N=_.is_inverse===!0,B=N?A<=K:A>=K;let m;n(o,c)?m="Belum Dimulai":f(o,l)||o===l?m=B?"Mencapai Target":"Target Gagal":m=B?"Mencapai Target":"Sedang Berjalan";let C,j,w;if(e.tipe_target==="persen")C=e.nilai_target+" %",j=g(e.data_detail.progress)+" %",w=g(e.data_detail.gap)+" %";else if(e.tipe_target==="rupiah"){const b=new Intl.NumberFormat("id-ID",{style:"currency",currency:"IDR",minimumFractionDigits:0,maximumFractionDigits:0});C=b.format(e.nilai_target),j=b.format(e.data_detail.progress),w=b.format(Math.abs(e.data_detail.gap))}else C=e.nilai_target,j=e.data_detail.progress,w=e.data_detail.gap;let F=m==="Mencapai Target"?"success":m==="Target Gagal"?"danger":m==="Sedang Berjalan"?"warning":"secondary",I=0;e.nilai_target&&e.nilai_target>0&&(e.tipe_target==="rupiah"?I=Math.min(e.data_detail.progress/e.nilai_target*100,100).toFixed(1):e.tipe_target==="angka"?I=(e.data_detail.progress/e.nilai_target*100).toFixed(1):I=Math.min(e.data_detail.progress,100).toFixed(1));const p=KpiDetail.buildContents(e,"personal");a.append(`
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title fw-bold">${e.judul}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body pt-3">
                                <div class="container-fluid p-3">
                                    <div class="row g-4">
                                        <div class="col-lg-8">
                                            <div class="card shadow h-100">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                                        <span class="badge bg-primary">${e.jangka_target}</span>
                                                        <span class="badge bg-${F}">${m}</span>
                                                    </div>
                                                    <div class="row text-center mb-3">
                                                        <div class="col"><small class="text-muted d-block">Target</small><h4 class="fw-bold mb-0" style="font-size: 25px">${C}</h4></div>
                                                        <div class="col"><small class="text-muted d-block">Progress</small><h1 class="fw-bold text-${F} mb-0" style="font-size: 55px;">${j}</h1></div>
                                                        <div class="col"><small class="text-muted d-block">Gap</small><h4 class="fw-bold text-danger mb-0" style="font-size: 25px">${N?w:`-${w}`}</h4></div>
                                                    </div>
                                                    <div class="position-relative mb-3">
                                                        <div class="progress" style="height:18px;"><div class="progress-bar bg-${F} progress-bar-striped progress-bar-animated" style="width: ${I}%"></div></div>
                                                        <div class="position-absolute bg-light top-0" style="left:${e.nilai_target}%; height:18px; width:2px;"></div>
                                                    </div>
                                                    <div class="text-muted mb-4"><i class="fa-solid fa-calendar-days me-1"></i> Deadline: <strong>${e.tenggat_waktu}</strong></div>
                                                    <div class="row g-4">
                                                        <div class="col-md-6">
                                                            <div class="card border-0 shadow-sm rounded-4 kpi-card">
                                                                <div class="card-body px-4 py-3">
                                                                    <div class="d-flex align-items-center mb-3"><div class="me-2 rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width:36px;height:36px;"><i class="fa-solid fa-chart-line text-primary"></i></div><h6 class="mb-0 fw-semibold text-secondary">INFORMASI KPI</h6></div>
                                                                    <div class="row mb-3"><div class="col-4 label">KPI Divisi</div><div class="col-8 value">${e.divisi_kpi||"-"}</div></div>
                                                                    <div class="row mb-3"><div class="col-4 label">KPI Jabatan</div><div class="col-8 value">${e.jabatan_kpi||"-"}</div></div>
                                                                    <div class="row"><div class="col-4 label">Pembuat</div><div class="col-8 value">${e.pembuat||"-"}</div></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="card border-0 shadow-sm rounded-4 participant-card h-100">
                                                                <div class="card-body px-4 py-3">
                                                                    <div class="d-flex align-items-center mb-3"><div class="me-2 rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style="width:36px;height:36px;"><i class="fa-solid fa-users text-success"></i></div><h6 class="mb-0 fw-semibold text-secondary">KARYAWAN</h6></div>
                                                                    <div class="participant-list" style="overflow-y: scroll; max-height: 140px;">
                                                                        ${p.karyawanHtml}
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
                                                <div class="card-body d-flex flex-column">${p.pieChart}</div>
                                            </div>
                                        </div>
                                    </div>
                                    ${p.trafikSales}
                                    ${p.kontribusiSales}
                                    ${p.statisticChart}
                                    ${p.kalenderInstruktur}
                                </div>
                            </div>
                            <div class="modal-footer border-0"><button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button></div>
                        `),setTimeout(()=>{a.addClass("modal-content-loaded"),KpiDetail.afterRender(e,"personal",p)},50)}},error:function(){a.empty(),a.html(`
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
                    `),a.addClass("modal-content-loaded")}})})})})();
