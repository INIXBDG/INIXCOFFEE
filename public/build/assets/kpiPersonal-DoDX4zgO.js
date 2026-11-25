const b=f=>(Number(f)||0).toFixed(1);(function(){let f=null,j=null,_=[];const G=["dorong inovasi pelayanan","inisiatif efisiensi keuangan","mengurangi manual work dan error"],O=window.allowedDoubleManualRoutes||[];function v(a){if(!a&&a!=="0")return"";const t=String(a).replace(/[^0-9]/g,"");return t?new Intl.NumberFormat("id-ID").format(t):""}function h(a){return a?String(a).replace(/[^0-9]/g,""):""}function E(){$("#manual_value_display").off("input.formatting").on("input.formatting",function(){const a=h($(this).val());$("#manual_value").val(a);const t=$("#manual_format").val();let i=v(a);t==="rupiah"&&a?i="Rp "+i:t==="persen"&&a&&(i=i+"%"),$(this).val(i)}),["biaya_gaji","biaya_bpjs","biaya_rekrutmen"].forEach(a=>{$(`#${a}_display`).off("input.formatting").on("input.formatting",function(){const t=h($(this).val());$(`#${a}_tahunan`).val(t),$(this).val(t?"Rp "+v(t):"")})})}function I(){$("#formManualValue")[0].reset(),$("#documentPreview").html(""),$("#singleInputArea").show(),$("#doubleInputArea").hide(),$("#manual_value_display").val("").trigger("input"),$("#manual_value").val(""),["biaya_gaji","biaya_bpjs","biaya_rekrutmen"].forEach(a=>{$(`#${a}_display`).val("").trigger("input"),$(`#${a}_tahunan`).val("")}),$("#manualValueId").val("")}function S(a){typeof Swal<"u"?Swal.fire({icon:"error",title:"Error",text:a,confirmButtonText:"OK"}):alert(a)}function w(a){const t=a?"block":"none";document.querySelectorAll(".skeleton-overlay").forEach(i=>{i.style.display=t})}function P(){w(!0),$("#targetCardContainer").css("opacity","0.5");const a=window.KPI_CONFIG;$.ajax({url:a.dataPersonalRoute,type:"GET",data:{tahun:a.currentYear,id_karyawan:a.targetId},dataType:"json",success:function(t){if(t.success){w(!1),$("#targetCardContainer").css("opacity","1");const i=(window.KPI_CONFIG?.userJabatan||window.authUser?.jabatan||window.authUser?.jabatan_user_table||"").toLowerCase().trim(),s=["gm","hrd","direktur utama","direktur"],r=i.includes("koordinator itsm")||i==="koordinator itsm"||i.includes("koordinator")||s.includes(i);let e=t.daftar_target_pribadi||[],o=t.statistik_per_target||[];if(!r){const d=n=>{const c=(n.asistant_route||n.assistant_route||n.condition||"").toLowerCase(),l=(n.judul||"").toLowerCase();return c.includes("gap kompetensi")||c.includes("persentase gap")||l.includes("gap performa")||l.includes("gap kompetensi")};e=e.filter(n=>!d(n)),o=o.filter(n=>!d(n)),t.total_target=e.length,t.kpi_aktif=e.filter(n=>["Aktif","Sedang Berjalan"].includes(n.status)).length,t.kpi_selesai=e.filter(n=>["Selesai","Mencapai Target"].includes(n.status)).length,t.grade=e.length?KpiGrade.calc(t.rata_rata_progress):null}console.log("jabatanUser:",i,"isKoordinator:",r),console.log("targets after filter:",e.map(d=>d.judul)),V(t),z(t),L(o,t.distribusi_status),R(e),_=e}else w(!1),S(t.message||"Terjadi kesalahan saat memuat data")},error:function(){w(!1),S("Gagal memuat data. Silakan coba lagi.")}})}function V(a){$("#userName").text(a.user_info?.nama||"User"),$("#userJabatan").text(a.user_info?.jabatan||"-"),$("#userDivisi").text(a.user_info?.divisi||"-")}function z(a){$("#stat0").text(a.total_target+" Target"),$("#stat1").html(b(a.rata_rata_progress)+"%"+KpiGrade.badge(a.grade,{nama:a.user_info?.nama,total:a.total_target})),$("#stat2").text(a.kpi_aktif),$("#stat3").text(a.kpi_selesai)}function L(a,t){f&&f.destroy(),j&&j.destroy();const i=a.map(l=>l.judul.length>20?l.judul.substring(0,20)+"...":l.judul),s=a.map(l=>(l.tipe_target==="rupiah"||l.tipe_target==="angka")&&l.target>0?Number(b(l.progress/l.target*100)):Number(b(l.progress))),r=a.map(l=>{let g=l.progress||0;return g===0?"rgba(254, 215, 24, 0.7)":l.tipe_target==="rupiah"?(l.target?g/l.target*100:0)>=100?"rgba(40, 207, 180, 0.7)":"rgba(254, 124, 150, 0.7)":g>=l.target?"rgba(40, 207, 180, 0.7)":"rgba(254, 124, 150, 0.7)"}),e=r.map(l=>l.replace("0.7","1")),o=document.getElementById("performanceChart");o&&(f=new Chart(o.getContext("2d"),{type:"bar",data:{labels:i,datasets:[{label:"Progress (%)",data:s,backgroundColor:r,borderColor:e,borderWidth:1,borderRadius:6}]},options:{responsive:!0,maintainAspectRatio:!1,plugins:{legend:{display:!1},tooltip:{callbacks:{label:l=>"Progress: "+b(l.parsed.y)+"%"}}},scales:{y:{beginAtZero:!0,ticks:{callback:l=>l+"%"}}}}}));const d=Object.keys(t),n=Object.values(t),c=document.getElementById("statusPieChart");c&&(j=new Chart(c.getContext("2d"),{type:"doughnut",data:{labels:d,datasets:[{data:n,backgroundColor:["#28CFB4","#FE7C96","#FED718"],borderWidth:0,hoverOffset:8}]},options:{responsive:!0,maintainAspectRatio:!1,plugins:{legend:{position:"bottom"}},cutout:"65%"}}))}function R(a){const t=$("#targetCardContainer");if(!a||a.length===0){t.html('<div class="col-12"><div class="content-card"><div class="card-body text-center py-5"><i class="fa-solid fa-inbox fa-4x text-muted mb-3"></i><h5 class="text-muted">Belum ada target KPI</h5></div></div></div>');return}let i="";a.forEach(s=>{let r=s.progress>=s.target?"bg-success":"bg-primary",e=s.status==="Selesai"?"bg-success":"bg-warning text-dark",o=s.progress>=75?"border-success":s.progress>=50?"border-primary":"border-warning",d=s.tipe_target==="rupiah"||s.tipe_target==="angka"?s.progress/s.target*100:s.progress,n=s.tipe_target==="rupiah"?"Rp "+Number(s.target).toLocaleString("id-ID"):s.tipe_target==="persen"?s.target+"%":s.target,c=s.tipe_target==="rupiah"?"Rp "+Number(s.progress).toLocaleString("id-ID"):s.tipe_target==="angka"?s.progress:b(s.progress)+"%",l=G.includes(s.asistant_route)?"true":"";i+=`
                <div class="col-12 col-sm-6 col-lg-4 mb-4">
                    <div class="target-card border-start border-4 ${o}">
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
                                            <div class="progress-bar ${r}" role="progressbar" style="width: ${d}%"></div>
                                        </div>
                                    </div>
                                    <div class="row g-2 pt-2 border-top">
                                        <div class="col-6">
                                            <small class="text-muted d-block" style="font-size: 0.75rem;">Target</small>
                                            <strong class="text-dark" style="font-size: 0.9rem;">${n}</strong>
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
            `}),t.html(i)}$(document).ready(function(){E(),P(),$("#modalFormManual").on("show.bs.modal",I),$("#modalFormManual").on("hidden.bs.modal",I),$(document).on("click",".filter-btn-group .btn",function(){$(".filter-btn-group .btn").removeClass("active"),$(this).addClass("active");let a=$(this).data("filter"),t=_;a==="active"?t=_.filter(i=>["Aktif","Sedang Berjalan"].includes(i.status)):a==="completed"&&(t=_.filter(i=>i.status==="Selesai")),R(t)}),$(document).on("click",".buttonForm",function(){const a=$(this).data("route"),t=$(this).data("value")||"",i=$(this).data("id");if($("#manualValueId").val(i),O.includes(a)){$("#singleInputArea").hide(),$("#doubleInputArea").show();let s=t&&t.includes(",")?t.split(","):[t,"",""];["biaya_gaji","biaya_bpjs","biaya_rekrutmen"].forEach((r,e)=>{const o=h(s[e]);$(`#${r}_display`).val(o?"Rp "+v(o):"").trigger("input"),$(`#${r}_tahunan`).val(o)})}else{$("#singleInputArea").show(),$("#doubleInputArea").hide();const s=$("#manual_format").val(),r=h(t);let e=v(r);s==="rupiah"&&r?e="Rp "+e:s==="persen"&&r&&(e=e+"%"),$("#manual_value_display").val(e),$("#manual_value").val(r)}}),$(document).on("change","#manual_format",function(){if($("#doubleInputArea").is(":visible"))return;const a=$(this).val(),t=h($("#manual_value").val());let i=v(t);a==="rupiah"&&t?i="Rp "+i:a==="persen"&&t&&(i=i+"%"),$("#manual_value_display").val(i)}),$("#formManualValue").on("submit",function(a){a.preventDefault();const t=$(this).find('button[type="submit"]'),i=t.html();t.prop("disabled",!0).html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Menyimpan...');const s=new FormData(this);$("#doubleInputArea").is(":visible")?(s.set("biaya_gaji_tahunan",$("#biaya_gaji_tahunan").val()),s.set("biaya_bpjs_tahunan",$("#biaya_bpjs_tahunan").val()),s.set("biaya_rekrutmen_tahunan",$("#biaya_rekrutmen_tahunan").val())):(s.set("manual_value",$("#manual_value").val()),s.set("manual_format",$("#manual_format").val())),$.ajax({url:window.KPI_CONFIG.manualValueRoute,type:"POST",data:s,contentType:!1,processData:!1,success:function(){$("#modalFormManual").modal("hide"),I(),t.prop("disabled",!1).html(i),P()},error:function(r){if(t.prop("disabled",!1).html(i),r.status===422){const e=r.responseJSON?.errors||{};alert(Object.values(e).map(o=>o[0]).join(`
`))}else alert("Terjadi kesalahan sistem.")}})}),$(document).on("click",".buttonDetailTarget",function(){let a=$(this).data("id");const t=$("#bodyContentDetailTarget");t.html(KpiDetail.skeleton("personal"));const i=document.getElementById("detailTargetModal");i&&new bootstrap.Modal(i).show(),$.ajax({url:window.KPI_CONFIG.detailRoute,method:"GET",data:{id:a,idUser:window.KPI_CONFIG.targetId},dataType:"json",success:function(s){t.empty();let r=s.detail,e=r&&r.length>0?r[0].data:null;if(!e)t.append(`
                            <div class="modal-header"><h5 class="modal-title">Detail Target</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                            <div class="modal-body text-center py-5">Belum ada data</div>
                            <div class="modal-footer"><button class="btn btn-danger" data-bs-dismiss="modal">Tutup</button></div>
                        `);else{let g=function(p,F){return new Date(p)<new Date(F)},D=function(p,F){return new Date(p)>new Date(F)};var o=g,d=D;const n=new Date().toISOString().split("T")[0],c=new Date(new Date().getFullYear(),0,1).toISOString().split("T")[0],l=e.tenggat_waktu,M=e.data_detail||{},A=M.progress??0,K=e.nilai_target??0,N=M.is_inverse===!0,B=N?A<=K:A>=K;let u;g(n,c)?u="Belum Dimulai":D(n,l)||n===l?u=B?"Mencapai Target":"Target Gagal":u=B?"Mencapai Target":"Sedang Berjalan";let x,k,y;if(e.tipe_target==="persen")x=e.nilai_target+" %",k=b(e.data_detail.progress)+" %",y=b(e.data_detail.gap)+" %";else if(e.tipe_target==="rupiah"){const p=new Intl.NumberFormat("id-ID",{style:"currency",currency:"IDR",minimumFractionDigits:0,maximumFractionDigits:0});x=p.format(e.nilai_target),k=p.format(e.data_detail.progress),y=p.format(Math.abs(e.data_detail.gap))}else x=e.nilai_target,k=e.data_detail.progress,y=e.data_detail.gap;let T=u==="Mencapai Target"?"success":u==="Target Gagal"?"danger":u==="Sedang Berjalan"?"warning":"secondary",C=0;e.nilai_target&&e.nilai_target>0&&(e.tipe_target==="rupiah"?C=Math.min(e.data_detail.progress/e.nilai_target*100,100).toFixed(1):e.tipe_target==="angka"?C=(e.data_detail.progress/e.nilai_target*100).toFixed(1):C=Math.min(e.data_detail.progress,100).toFixed(1));const m=KpiDetail.buildContents(e,"personal");t.append(`
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
                                                        <span class="badge bg-${T}">${u}</span>
                                                    </div>
                                                    <div class="row text-center mb-3">
                                                        <div class="col"><small class="text-muted d-block">Target</small><h4 class="fw-bold mb-0" style="font-size: 25px">${x}</h4></div>
                                                        <div class="col"><small class="text-muted d-block">Progress</small><h1 class="fw-bold text-${T} mb-0" style="font-size: 55px;">${k}</h1></div>
                                                        <div class="col"><small class="text-muted d-block">Gap</small><h4 class="fw-bold text-danger mb-0" style="font-size: 25px">${N?y:`-${y}`}</h4></div>
                                                    </div>
                                                    <div class="position-relative mb-3">
                                                        <div class="progress" style="height:18px;"><div class="progress-bar bg-${T} progress-bar-striped progress-bar-animated" style="width: ${C}%"></div></div>
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
                                                                        ${m.karyawanHtml}
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
                                                <div class="card-body d-flex flex-column">${m.pieChart}</div>
                                            </div>
                                        </div>
                                    </div>
                                    ${m.trafikSales}
                                    ${m.kontribusiSales}
                                    ${m.statisticChart}
                                    ${m.kalenderInstruktur}
                                </div>
                            </div>
                            <div class="modal-footer border-0"><button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button></div>
                        `),setTimeout(()=>{t.addClass("modal-content-loaded"),KpiDetail.afterRender(e,"personal",m)},50)}},error:function(){t.empty(),t.html(`
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
                    `),t.addClass("modal-content-loaded")}})})})})();
