const j=F=>(Number(F)||0).toFixed(1);(function(){let F=null,Y=null,O=[];const $a=["dorong inovasi pelayanan","inisiatif efisiensi keuangan","mengurangi manual work dan error"],_a=window.allowedDoubleManualRoutes||[];function L(d){if(!d&&d!=="0")return"";const n=String(d).replace(/[^0-9]/g,"");return n?new Intl.NumberFormat("id-ID").format(n):""}function K(d){return d?String(d).replace(/[^0-9]/g,""):""}function ja(){$("#manual_value_display").off("input.formatting").on("input.formatting",function(){const d=K($(this).val());$("#manual_value").val(d);const n=$("#manual_format").val();let l=L(d);n==="rupiah"&&d?l="Rp "+l:n==="persen"&&d&&(l=l+"%"),$(this).val(l)}),["biaya_gaji","biaya_bpjs","biaya_rekrutmen"].forEach(d=>{$(`#${d}_display`).off("input.formatting").on("input.formatting",function(){const n=K($(this).val());$(`#${d}_tahunan`).val(n),$(this).val(n?"Rp "+L(n):"")})})}function X(){$("#formManualValue")[0].reset(),$("#documentPreview").html(""),$("#singleInputArea").show(),$("#doubleInputArea").hide(),$("#manual_value_display").val("").trigger("input"),$("#manual_value").val(""),["biaya_gaji","biaya_bpjs","biaya_rekrutmen"].forEach(d=>{$(`#${d}_display`).val("").trigger("input"),$(`#${d}_tahunan`).val("")}),$("#manualValueId").val("")}function sa(d){typeof Swal<"u"?Swal.fire({icon:"error",title:"Error",text:d,confirmButtonText:"OK"}):alert(d)}function G(d){const n=d?"block":"none";document.querySelectorAll(".skeleton-overlay").forEach(l=>{l.style.display=n})}function Ia(){return`
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
        `}function ia(){G(!0),$("#targetCardContainer").css("opacity","0.5");const d=window.KPI_CONFIG;$.ajax({url:d.dataPersonalRoute,type:"GET",data:{tahun:d.currentYear,id_karyawan:d.targetId},dataType:"json",success:function(n){if(n.success){G(!1),$("#targetCardContainer").css("opacity","1");const l=(window.KPI_CONFIG?.userJabatan||window.authUser?.jabatan||window.authUser?.jabatan_user_table||"").toLowerCase().trim(),i=["gm","hrd","direktur utama","direktur"],g=l.includes("koordinator itsm")||l==="koordinator itsm"||l.includes("koordinator")||i.includes(l);let b=n.daftar_target_pribadi||[],a=n.statistik_per_target||[];if(!g){const h=f=>{const T=(f.asistant_route||f.assistant_route||f.condition||"").toLowerCase(),p=(f.judul||"").toLowerCase();return T.includes("gap kompetensi")||T.includes("persentase gap")||p.includes("gap performa")||p.includes("gap kompetensi")};b=b.filter(f=>!h(f)),a=a.filter(f=>!h(f)),n.total_target=b.length,n.kpi_aktif=b.filter(f=>["Aktif","Sedang Berjalan"].includes(f.status)).length,n.kpi_selesai=b.filter(f=>["Selesai","Mencapai Target"].includes(f.status)).length,n.grade=b.length?KpiGrade.calc(n.rata_rata_progress):null}console.log("jabatanUser:",l,"isKoordinator:",g),console.log("targets after filter:",b.map(h=>h.judul)),Ta(n),Ca(n),Da(a,n.distribusi_status),la(b),O=b}else G(!1),sa(n.message||"Terjadi kesalahan saat memuat data")},error:function(){G(!1),sa("Gagal memuat data. Silakan coba lagi.")}})}function Ta(d){$("#userName").text(d.user_info?.nama||"User"),$("#userJabatan").text(d.user_info?.jabatan||"-"),$("#userDivisi").text(d.user_info?.divisi||"-")}function Ca(d){$("#stat0").text(d.total_target+" Target"),$("#stat1").html(j(d.rata_rata_progress)+"%"+KpiGrade.badge(d.grade,{nama:d.user_info?.nama,total:d.total_target})),$("#stat2").text(d.kpi_aktif),$("#stat3").text(d.kpi_selesai)}function Da(d,n){F&&F.destroy(),Y&&Y.destroy();const l=d.map(p=>p.judul.length>20?p.judul.substring(0,20)+"...":p.judul),i=d.map(p=>(p.tipe_target==="rupiah"||p.tipe_target==="angka")&&p.target>0?Number(j(p.progress/p.target*100)):Number(j(p.progress))),g=d.map(p=>{let J=p.progress||0;return J===0?"rgba(254, 215, 24, 0.7)":p.tipe_target==="rupiah"?(p.target?J/p.target*100:0)>=100?"rgba(40, 207, 180, 0.7)":"rgba(254, 124, 150, 0.7)":J>=p.target?"rgba(40, 207, 180, 0.7)":"rgba(254, 124, 150, 0.7)"}),b=g.map(p=>p.replace("0.7","1")),a=document.getElementById("performanceChart");a&&(F=new Chart(a.getContext("2d"),{type:"bar",data:{labels:l,datasets:[{label:"Progress (%)",data:i,backgroundColor:g,borderColor:b,borderWidth:1,borderRadius:6}]},options:{responsive:!0,maintainAspectRatio:!1,plugins:{legend:{display:!1},tooltip:{callbacks:{label:p=>"Progress: "+j(p.parsed.y)+"%"}}},scales:{y:{beginAtZero:!0,ticks:{callback:p=>p+"%"}}}}}));const h=Object.keys(n),f=Object.values(n),T=document.getElementById("statusPieChart");T&&(Y=new Chart(T.getContext("2d"),{type:"doughnut",data:{labels:h,datasets:[{data:f,backgroundColor:["#28CFB4","#FE7C96","#FED718"],borderWidth:0,hoverOffset:8}]},options:{responsive:!0,maintainAspectRatio:!1,plugins:{legend:{position:"bottom"}},cutout:"65%"}}))}function la(d){const n=$("#targetCardContainer");if(!d||d.length===0){n.html('<div class="col-12"><div class="content-card"><div class="card-body text-center py-5"><i class="fa-solid fa-inbox fa-4x text-muted mb-3"></i><h5 class="text-muted">Belum ada target KPI</h5></div></div></div>');return}let l="";d.forEach(i=>{let g=i.progress>=i.target?"bg-success":"bg-primary",b=i.status==="Selesai"?"bg-success":"bg-warning text-dark",a=i.progress>=75?"border-success":i.progress>=50?"border-primary":"border-warning",h=i.tipe_target==="rupiah"||i.tipe_target==="angka"?i.progress/i.target*100:i.progress,f=i.tipe_target==="rupiah"?"Rp "+Number(i.target).toLocaleString("id-ID"):i.tipe_target==="persen"?i.target+"%":i.target,T=i.tipe_target==="rupiah"?"Rp "+Number(i.progress).toLocaleString("id-ID"):i.tipe_target==="angka"?i.progress:j(i.progress)+"%",p=$a.includes(i.asistant_route)?"true":"";l+=`
                <div class="col-12 col-sm-6 col-lg-4 mb-4">
                    <div class="target-card border-start border-4 ${a}">
                        ${p?`<button type="button" class="action-btn buttonForm" data-id="${i.id}" data-value="${i.manual_value}" data-route="${i.asistant_route}" data-bs-toggle="modal" data-bs-target="#modalFormManual" title="Isi Data"><i class="fa-solid fa-file-pen"></i></button>`:""}
                        <button type="button" class="btn btn-link p-0 text-start w-100 h-100 text-decoration-none buttonDetailTarget" data-id="${i.id}" data-bs-toggle="modal" data-bs-target="#detailTargetModal" style="outline: none;">
                            <div class="card-body-inner">
                                <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                                    <span class="badge bg-primary bg-opacity-10 text-primary status-badge">${i.periode}</span>
                                    <span class="badge ${b} status-badge">${i.status}</span>
                                </div>
                                <h6 class="fw-bold text-dark mb-2 lh-base title-badge">${i.judul}</h6>
                                <p class="text-muted small mb-3" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 40px;">${i.deskripsi||"Tidak ada deskripsi"}</p>
                                <div class="mt-auto">
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <small class="text-muted fw-semibold" style="font-size: 0.8rem;">Progress</small>
                                            <small class="fw-bold text-primary" style="font-size: 0.8rem;">${i.progress_display}</small>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar ${g}" role="progressbar" style="width: ${h}%"></div>
                                        </div>
                                    </div>
                                    <div class="row g-2 pt-2 border-top">
                                        <div class="col-6">
                                            <small class="text-muted d-block" style="font-size: 0.75rem;">Target</small>
                                            <strong class="text-dark" style="font-size: 0.9rem;">${f}</strong>
                                        </div>
                                        <div class="col-6 text-end">
                                            <small class="text-muted d-block" style="font-size: 0.75rem;">Realisasi</small>
                                            <strong class="text-primary" style="font-size: 0.9rem;">${T}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </button>
                    </div>
                </div>
            `}),n.html(l)}$(document).ready(function(){ja(),ia(),$("#modalFormManual").on("show.bs.modal",X),$("#modalFormManual").on("hidden.bs.modal",X),$(document).on("click",".filter-btn-group .btn",function(){$(".filter-btn-group .btn").removeClass("active"),$(this).addClass("active");let n=$(this).data("filter"),l=O;n==="active"?l=O.filter(i=>["Aktif","Sedang Berjalan"].includes(i.status)):n==="completed"&&(l=O.filter(i=>i.status==="Selesai")),la(l)}),$(document).on("click",".buttonForm",function(){const n=$(this).data("route"),l=$(this).data("value")||"",i=$(this).data("id");if($("#manualValueId").val(i),_a.includes(n)){$("#singleInputArea").hide(),$("#doubleInputArea").show();let g=l&&l.includes(",")?l.split(","):[l,"",""];["biaya_gaji","biaya_bpjs","biaya_rekrutmen"].forEach((b,a)=>{const h=K(g[a]);$(`#${b}_display`).val(h?"Rp "+L(h):"").trigger("input"),$(`#${b}_tahunan`).val(h)})}else{$("#singleInputArea").show(),$("#doubleInputArea").hide();const g=$("#manual_format").val(),b=K(l);let a=L(b);g==="rupiah"&&b?a="Rp "+a:g==="persen"&&b&&(a=a+"%"),$("#manual_value_display").val(a),$("#manual_value").val(b)}}),$(document).on("change","#manual_format",function(){if($("#doubleInputArea").is(":visible"))return;const n=$(this).val(),l=K($("#manual_value").val());let i=L(l);n==="rupiah"&&l?i="Rp "+i:n==="persen"&&l&&(i=i+"%"),$("#manual_value_display").val(i)}),$("#formManualValue").on("submit",function(n){n.preventDefault();const l=$(this).find('button[type="submit"]'),i=l.html();l.prop("disabled",!0).html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Menyimpan...');const g=new FormData(this);$("#doubleInputArea").is(":visible")?(g.set("biaya_gaji_tahunan",$("#biaya_gaji_tahunan").val()),g.set("biaya_bpjs_tahunan",$("#biaya_bpjs_tahunan").val()),g.set("biaya_rekrutmen_tahunan",$("#biaya_rekrutmen_tahunan").val())):(g.set("manual_value",$("#manual_value").val()),g.set("manual_format",$("#manual_format").val())),$.ajax({url:window.KPI_CONFIG.manualValueRoute,type:"POST",data:g,contentType:!1,processData:!1,success:function(){$("#modalFormManual").modal("hide"),X(),l.prop("disabled",!1).html(i),ia()},error:function(b){if(l.prop("disabled",!1).html(i),b.status===422){const a=b.responseJSON?.errors||{};alert(Object.values(a).map(h=>h[0]).join(`
`))}else alert("Terjadi kesalahan sistem.")}})}),$(document).on("click",".buttonDetailTarget",function(){let n=$(this).data("id");const l=$("#bodyContentDetailTarget");l.html(Ia());const i=document.getElementById("detailTargetModal");i&&new bootstrap.Modal(i).show(),$.ajax({url:window.KPI_CONFIG.detailRoute,method:"GET",data:{id:n,idUser:window.KPI_CONFIG.targetId},dataType:"json",success:function(g){l.empty();let b=g.detail,a=b&&b.length>0?b[0].data:null;if(!a)l.append(`
                            <div class="modal-header"><h5 class="modal-title">Detail Target</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                            <div class="modal-body text-center py-5">Belum ada data</div>
                            <div class="modal-footer"><button class="btn btn-danger" data-bs-dismiss="modal">Tutup</button></div>
                        `);else{let na=function(e,t){return new Date(e)<new Date(t)},oa=function(e,t){return new Date(e)>new Date(t)},E=function(e){const t=e.split("-");if(t.length<2)return e;const m=parseInt(t[1],10)-1;return Pa[m]||e},y=function(e){return new Intl.NumberFormat("id-ID",{style:"currency",currency:"IDR",minimumFractionDigits:0}).format(e||0)},ha=function(e){return new Date(e).toLocaleDateString("id-ID",{day:"2-digit",month:"short"})};var h=na,f=oa,T=E,p=y,J=ha;const Z=a.data_detail.monthly_data||{},N=a.data_detail.daily_breakdown_per_month||{},Q=new Date().toISOString().split("T")[0],Aa=new Date(new Date().getFullYear(),0,1).toISOString().split("T")[0],da=a.tenggat_waktu,ra=a.data_detail||{},ca=ra.progress??0,ua=a.nilai_target??0,ma=ra.is_inverse===!0,ba=ma?ca<=ua:ca>=ua;let S;na(Q,Aa)?S="Belum Dimulai":oa(Q,da)||Q===da?S=ba?"Mencapai Target":"Target Gagal":S=ba?"Mencapai Target":"Sedang Berjalan";let V,H,R;if(a.tipe_target==="persen")V=a.nilai_target+" %",H=j(a.data_detail.progress)+" %",R=j(a.data_detail.gap)+" %";else if(a.tipe_target==="rupiah"){const e=new Intl.NumberFormat("id-ID",{style:"currency",currency:"IDR",minimumFractionDigits:0,maximumFractionDigits:0});V=e.format(a.nilai_target),H=e.format(a.data_detail.progress),R=e.format(Math.abs(a.data_detail.gap))}else V=a.nilai_target,H=a.data_detail.progress,R=a.data_detail.gap;let aa=S==="Mencapai Target"?"success":S==="Target Gagal"?"danger":S==="Sedang Berjalan"?"warning":"secondary";const pa=a.data_detail.pie_chart||{above:0,below:0},Ma={labels:["Above","Below"],datasets:[{label:"Jumlah",data:[pa.above??0,pa.below??0],backgroundColor:["#B66DFF","#FE7C96"],hoverOffset:4}]},Pa=["Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"],va=Object.entries(Z),ta=va[va.length-1]||[],Ba=ta[0]?E(ta[0]):"-",Fa=ta[1]||0;let ga=[];Object.values(N).forEach(e=>{Object.entries(e).forEach(([t,m])=>{ga.push([t,m])})});const La=ga.sort((e,t)=>t[1]-e[1]).slice(0,3),fa=a.karyawan?.[0]||null,Ka=Array.isArray(a.karyawan)?a.karyawan:[a.karyawan];let Na=1;const Ra=Ka.map(e=>`
                            <div class="d-flex align-items-center py-2 participant-item">
                                <div class="avatar me-3">${Na++}</div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold text-dark small">${e.nama_lengkap||"-"}</div>
                                    <div class="text-muted small">${e.jabatan||"-"}</div>
                                </div>
                            </div>
                        `).join("");let z=0;a.nilai_target&&a.nilai_target>0&&(a.tipe_target==="rupiah"?z=Math.min(a.data_detail.progress/a.nilai_target*100,100).toFixed(1):a.tipe_target==="angka"?z=(a.data_detail.progress/a.nilai_target*100).toFixed(1):z=Math.min(a.data_detail.progress,100).toFixed(1));const ya=["dorong inovasi pelayanan","inisiatif efisiensi keuangan","mengurangi manual work dan error","pengeluaran biaya karyawan"],Ea=["Pemasukan Kotor","meningkatkan revenue perusahaan"],Oa=["persentase gap kompetensi tim terhadap standar skill"],Ga=["target penjualan tahunan","Pemasukan Kotor"],Ja=["peningkatan kontribusi pelatihan"],Va=["pemasukan bersih"],Ha=["laporan analisis keuangan"],za=["inovation adaption rate"];let q="";if(Ga.includes(a.condition)){const e=a.data_detail?.sales_performance;if(e&&e.data){if(e.type==="individual"){const t=e.data,m=t.status==="achieved"?"badge-success":"badge-warning",c=t.status==="achieved"?"#28a745":"#ffc107",v=Math.min(t.percentage,100);q=`
                                        <div class="card shadow-sm mb-4 mt-2"><div class="card-body"><div class="row">
                                            <div class="col-md-6">
                                                <p class="mb-2"><strong>Sales:</strong> ${t.nama||"-"}</p>
                                                <p class="mb-2"><strong>Revenue:</strong> ${y(t.revenue)}</p>
                                                <p class="mb-3"><strong>Target:</strong> ${y(t.presentase_kemampuan)}</p>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-2">
                                                    <div class="d-flex justify-content-between"><span>Progress</span><span>${j(t.percentage)}%</span></div>
                                                    <div class="progress" style="height: 10px;"><div class="progress-bar" role="progressbar" style="width: ${v}%; background-color: ${c};"></div></div>
                                                </div>
                                                <div class="text-end"><span class="badge ${m} p-2">${(t.status||"").toUpperCase()}</span></div>
                                            </div>
                                        </div></div></div>
                                    `}else if(e.type==="all"){let t="";e.data.forEach((c,v)=>{const u=c.status==="achieved"?"badge-success":"badge-warning",o=c.status==="achieved"?"text-success":"text-warning",s=Number(c.presentase_kemampuan).toLocaleString("id-ID",{useGrouping:!1});t+=`
                                            <tr id="row-${c.kode_karyawan}">
                                                <td class="text-center">${v+1}</td>
                                                <td><strong>${c.nama||"-"}</strong></td>
                                                <td class="text-end">${y(c.revenue)}</td>
                                                <td class="text-center">
                                                    <div class="input-group input-group-sm" style="max-width: 150px; float: right;">
                                                        <input type="text" class="form-control text-end target-input ${c.id_detailPerson?"":"is-invalid"}" value="${s}" data-id-detail="${c.id_detailPerson||""}" data-kode-karyawan="${c.kode_karyawan}" placeholder="Target" ${c.id_detailPerson?"":"disabled"}>
                                                    </div>
                                                    <div class="loading-spinner" style="display: none; float: right; margin-right: 10px;"><span class="spinner-border spinner-border-sm text-primary" role="status"></span></div>
                                                    <div class="update-feedback" style="display: none; float: right; margin-right: 10px; margin-top: 5px;"><i class="fa-solid fa-check-circle text-success"></i></div>
                                                    <div class="clearfix"></div>
                                                </td>
                                                <td class="text-center ${o}"><strong>${j(c.percentage)}%</strong></td>
                                                <td class="text-center"><span class="badge ${u}">${(c.status||"").toUpperCase()}</span></td>
                                            </tr>
                                        `});let m="";Object.entries(a.data_detail.triwulan_data||{}).forEach(([c,v])=>{m+=`<div class="col-md-6"><div class="card h-100 shadow-sm border-0"><div class="card-body"><h5 class="card-title">${c.replace("_"," ")}</h5><p class="card-text">Rp ${Number(v).toLocaleString("id-ID")}</p></div></div></div>`}),q=`
                                        <div class="row">
                                            <div class="col"><div class="card shadow-sm mt-3"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead class="thead-light"><tr><th class="text-center" width="5%">No</th><th>Sales</th><th class="text-end" width="20%">Revenue</th><th class="text-end" width="20%">Target (Editable)</th><th class="text-center" width="15%">Persentase</th><th class="text-center" width="15%">Status</th></tr></thead><tbody>${t}</tbody></table></div></div></div></div>
                                            <div class="col"><div class="card shadow-sm border-0 mt-3"><div class="card-body"><div class="row g-3">${m}</div></div><div class="mb-2 text-center"><hr><p>Data Triwulan diambil dari tahun ${a.detail_jangka||"-"}</p></div></div></div>
                                        </div>
                                    `,setTimeout(()=>{document.querySelectorAll(".target-input").forEach(c=>{let v=null;c.addEventListener("input",function(){const u=this,o=u.closest("tr"),s=o.querySelector(".loading-spinner"),r=o.querySelector(".update-feedback");clearTimeout(v),r&&(r.style.display="none"),v=setTimeout(()=>{const x=u.dataset.idDetail,_=u.dataset.kodeKaryawan,I=u.value.replace(/\./g,"");if(!x){u.classList.add("is-invalid");return}s&&(s.style.display="inline-block"),u.disabled=!0,$.ajax({url:window.KPI_CONFIG.updateTargetPerSalesRoute,method:"POST",data:{_token:window.KPI_CONFIG.csrfToken,id_detailPerson:x,kode_karyawan:_,presentase_kemampuan:I},success:function(w){if(s&&(s.style.display="none"),u.disabled=!1,r&&(r.style.display="inline-block",setTimeout(()=>{r.style.display="none"},2e3)),u.classList.remove("is-invalid"),u.classList.add("is-valid"),w?.data){const D=o.querySelector("td:nth-child(5)"),M=o.querySelector("td:nth-child(6)");if(D&&w.data.percentage&&(D.innerHTML=`<strong class="${w.data.status==="achieved"?"text-success":"text-warning"}">${j(w.data.percentage)}%</strong>`),M&&w.data.status){const C=w.data.status==="achieved"?"badge-success":"badge-warning";M.innerHTML=`<span class="badge ${C}">${w.data.status.toUpperCase()}</span>`}}setTimeout(()=>{u.classList.remove("is-valid")},2e3)},error:function(){s&&(s.style.display="none"),u.disabled=!1,u.classList.add("is-invalid")}})},1e3)}),c.addEventListener("blur",function(){const u=this.value.replace(/\./g,"");u&&(this.value=Number(u).toLocaleString("id-ID"))}),c.addEventListener("focus",function(){const u=this.value.replace(/\./g,"");u&&(this.value=u)})})},100)}}}else if(Ja.includes(a.condition)){const e=a.data_detail.class_breakdown||{},t=e.Inhouse||{};q=`
                                <div class="card mt-4 border-0 rounded-4" style="background:#f8fafc;">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-end mb-4">
                                            <div><div class="text-muted small">Total Kelas</div><div class="fs-2 fw-semibold text-dark">${e.total||0}</div></div>
                                        </div>
                                        <div class="row g-3 mb-4">
                                            <div class="col-md-6"><div class="p-3 rounded-3 bg-white border h-100"><div class="text-muted small mb-2">Kelas Inixindo</div><div class="fs-4 fw-semibold text-dark">${e.kelas_od||0}</div></div></div>
                                            <div class="col-md-6"><div class="p-3 rounded-3 bg-white border h-100"><div class="text-muted small mb-2">Kelas Orang Luar</div><div class="fs-4 fw-semibold text-dark">${e.kelas_ol||0}</div></div></div>
                                            <div class="col-md-6"><div class="p-3 rounded-3 bg-white border h-100"><div class="text-muted small mb-2">Kelas Offline</div><div class="fs-4 fw-semibold text-dark">${e.kelas_offline||0}</div></div></div>
                                            <div class="col-md-6"><div class="p-3 rounded-3 bg-white border h-100"><div class="text-muted small mb-2">Kelas Online</div><div class="fs-4 fw-semibold text-dark">${e.kelas_online||0}</div></div></div>
                                        </div>
                                        <div class="p-3 rounded-3 bg-white border">
                                            <div class="fw-semibold mb-3">Kelas Inhouse</div>
                                            <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted small">Bandung</span><span class="fw-semibold text-dark">${t.kelas_inhouse||0}</span></div>
                                            <div class="d-flex justify-content-between py-2"><span class="text-muted small">Luar Bandung</span><span class="fw-semibold text-dark">${t.kelas_inhouse_luar||0}</span></div>
                                        </div>
                                    </div>
                                </div>
                            `}let wa="";const U=["pemasukan kotor"].includes(String(a.condition).toLowerCase().trim())?a.data_detail?.kontribusi_sales??null:null;if(U){const e={on_track:["bg-primary","On Track"],selesai:["bg-success","Selesai"],gagal:["bg-danger","Gagal"]},t=o=>{const[s,r]=e[o]||["bg-secondary","-"];return`<span class="badge ${s}">${r}</span>`},m=o=>o<0?"text-danger":"text-success",c=(U.rows||[]).map((o,s)=>`
                                <tr>
                                    <td class="text-center">${s+1}</td>
                                    <td class="fw-semibold">${o.nama||"-"}</td>
                                    <td class="text-end">${y(o.triwulan[1])}</td>
                                    <td class="text-end">${y(o.triwulan[2])}</td>
                                    <td class="text-end">${y(o.triwulan[3])}</td>
                                    <td class="text-end">${y(o.triwulan[4])}</td>
                                    <td class="text-end fw-bold text-primary">${y(o.total)}</td>
                                    <td class="text-end">${y(o.target)}</td>
                                    <td class="text-end fw-semibold ${m(o.selisih)}">${y(o.selisih)}</td>
                                    <td class="text-center">${j(o.persentase)}%</td>
                                    <td class="text-center">${t(o.status)}</td>
                                </tr>`).join(""),v=U.total,u=v?`
                                <tfoot class="table-light fw-bold">
                                    <tr>
                                        <td colspan="2" class="text-center">TOTAL</td>
                                        <td class="text-end">${y(v.triwulan[1])}</td>
                                        <td class="text-end">${y(v.triwulan[2])}</td>
                                        <td class="text-end">${y(v.triwulan[3])}</td>
                                        <td class="text-end">${y(v.triwulan[4])}</td>
                                        <td class="text-end text-primary">${y(v.total)}</td>
                                        <td class="text-end">${y(v.target)}</td>
                                        <td class="text-end ${m(v.selisih)}">${y(v.selisih)}</td>
                                        <td class="text-center">${j(v.persentase)}%</td>
                                        <td class="text-center">${t(v.status)}</td>
                                    </tr>
                                </tfoot>`:"";wa=`
                                <div class="card shadow-sm border-0 rounded-4">
                                    <div class="card-body">
                                        <h6 class="fw-semibold mb-3"><i class="fa-solid fa-users me-2"></i>Kontribusi Sales ${U.tahun??""}</h6>
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
                                </div>`}let W="";if(ya.includes(a.condition)){const e=a.data_detail.dataManual?.manual_document||"",t=e?e.split("/").pop():"",m=t?t.split(".").pop().toLowerCase():"",c=["jpg","jpeg","png"],v=["pdf"];let u="";const o=e?`/storage/${e}`:"";c.includes(m)?u=`<div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3"><div class="mb-3" style="max-width: 100%; max-height: 300px;"><img src="${o}" alt="${t}" class="img-fluid rounded shadow-sm" style="max-width: 100%; max-height: 300px; object-fit: contain;"></div><div class="mt-2 text-center"><a href="${o}" download="${t}" class="btn btn-primary btn-sm"><i class="fa-solid fa-download me-1"></i>Download Gambar</a></div><div class="mt-2 small text-muted"><i class="fa-solid fa-file-image me-1"></i>${t}</div></div>`:v.includes(m)?u=`<div class="w-100 h-100 d-flex flex-column"><div class="flex-grow-1 mb-3" style="min-height: 250px;"><iframe src="${o}" class="w-100 h-100" style="border: 1px solid #dee2e6; border-radius: 8px;"></iframe></div><div class="text-center"><a href="${o}" download="${t}" class="btn btn-primary btn-sm"><i class="fa-solid fa-download me-1"></i>Download PDF</a></div><div class="mt-2 small text-muted text-center"><i class="fa-solid fa-file-pdf me-1"></i>${t}</div></div>`:u='<div class="text-center py-5"><div class="mb-3"><i class="fa-solid fa-file text-secondary" style="font-size: 4rem;"></i></div><p class="text-muted mb-3">File tidak dapat ditampilkan</p><p class="text-muted small">Hanya gambar dan PDF yang dapat ditampilkan</p></div>',W=`<h6 class="fw-semibold mb-3 text-secondary"><i class="fa-solid fa-file me-2"></i>Dokumen Manual</h6><div class="manual-document-container flex-grow-1 d-flex flex-column align-items-center justify-content-center p-3" style="background-color: #f8f9fa; border-radius: 8px;">${u}</div><div class="mt-3 small text-muted text-center"><i class="fa-solid fa-info-circle me-1"></i>Klik tombol download untuk menyimpan file</div>`}else Ea.includes(a.condition)?W=`<div class="mb-4"><h6 class="fw-semibold text-primary mb-1"><i class="fa-solid fa-wallet me-2"></i>${a.judul}</h6><small class="text-muted">Ringkasan performa</small></div><div class="mb-4 p-3 rounded bg-light"><div class="text-muted small mb-1">Bulan Terakhir</div><div class="fw-semibold">${Ba}</div><div class="fw-bold fs-6 text-dark">${y(Fa)}</div></div><div class="mb-3"><div class="text-muted small mb-2">Top Hari Tertinggi</div>${La.map(([e,t],m)=>`<div class="d-flex justify-content-between mb-1 ${m>0?"text-muted":""}"><span>${ha(e)}</span><span class="fw-semibold">${y(t)}</span></div>`).join("")}</div><hr class="my-3"><div class="d-flex align-items-center gap-2 text-muted small"><i class="fa-solid fa-user-circle fs-5"></i><span class="fw-semibold text-dark">${fa?.nama_lengkap??"-"}</span><span>• ${fa?.jabatan??"-"}</span></div>`:W=`<h6 class="fw-semibold mb-3 text-secondary"><i class="fa-solid fa-chart-pie me-2"></i>Chart ${a.condition}</h6><div class="chart-container flex-grow-1"><canvas id="MyChartDoughtnut"></canvas></div>`;let A="";if(ya.includes(a.condition))A="";else if(za.includes(a.condition)){const t=(a.data_detail||{}).dataInovasi||[];A=`
                                <div class="table-responsive mt-3">
                                    <table class="table table-bordered table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 30%;">Judul</th>
                                                <th>Deskripsi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${t.length?t.map(m=>`
                                                        <tr>
                                                            <td class="fw-semibold">
                                                                ${m.judul??"-"}
                                                            </td>
                                                            <td>
                                                                ${m.deskripsi??"-"}
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
                            `}else if(Ha.includes(a.condition)){const e=["","Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"];A=`<div class="row g-4">${(a.data_detail.analisa_data||[]).map(t=>`<div class="col-md-4 mt-5"><div class="card border-0 shadow-sm h-100"><div class="mb-2 p-3"><h5 class="fw-bold text-primary mb-1">${e[t.month]||"-"}</h5><small class="text-muted">Laporan Analisis Bulanan</small></div><div class="card-body d-flex flex-column" style="overflow-y: scroll; max-height: 280px;"><div class="mb-3"><p class="mb-0" style="text-align: justify;">${t.description||""}</p></div></div><div class="mt-auto p-3"><a href="/storage/${t.file_paths||""}" class="btn btn-sm btn-outline-primary w-100" ${t.file_paths?"download":""}><i class="fa-solid fa-file-alt"></i></a></div></div></div>`).join("")}</div>`}else if(Va.includes(a.condition)){const e=["Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"];A=`
                                <div class="mt-4"><div class="row g-4">${(a.data_detail.previous_quarter?.data||[]).map((t,m)=>`
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="card border-0 shadow-sm h-100 quarter-card">
                                                <div class="card-body d-flex flex-column p-4">
                                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                                        <div><h6 class="fw-semibold text-muted mb-1">Periode</h6><h5 class="fw-bold mb-0">${e[(t.month||1)-1]??"-"}</h5></div>
                                                        <span class="badge rounded-pill bg-${t.color||"secondary"} bg-opacity-10 text-${t.color||"secondary"} px-3 py-2">Laporan</span>
                                                    </div>
                                                    <div class="mb-3"><h3 class="fw-bold text-dark mb-0">Rp ${t.nilai?Number(t.nilai).toLocaleString("id-ID"):"-"}</h3><small class="text-muted">Total Pemasukan</small></div>
                                                    <div class="flex-grow-1">
                                                        <p class="text-muted small mb-2 description-text" id="desc-${m}">${t.description??"-"}</p>
                                                        ${t.description&&t.description.length>100?`<button class="btn btn-sm btn-link p-0 text-primary btn-toggle-desc" data-target="desc-${m}">Lihat Selengkapnya</button>`:""}
                                                    </div>
                                                    <div class="d-flex justify-content-end align-items-center mt-4">
                                                        <a href="/storage/${t.file_paths||""}" class="btn btn-sm btn-dark d-flex align-items-center gap-2" ${t.file_paths?"download":""}><i class="fa-solid fa-download"></i> Download</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    `).join("")}</div></div>
                                <style>.quarter-card { border-radius: 16px; transition: all 0.25s ease; background: #ffffff; }.quarter-card:hover { transform: translateY(-6px) scale(1.01); box-shadow: 0 15px 35px rgba(0,0,0,0.08); }.quarter-card h3 { letter-spacing: 0.5px; }.quarter-card .badge { font-size: 12px; font-weight: 500; }.description-text { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }.description-text.expanded { -webkit-line-clamp: unset; overflow: visible; }.quarter-card .btn { border-radius: 8px; font-size: 13px; }</style>
                            `,setTimeout(()=>{document.querySelectorAll(".btn-toggle-desc").forEach(t=>{t.addEventListener("click",function(){const m=this.getAttribute("data-target"),c=document.getElementById(m);c&&(c.classList.contains("expanded")?(c.classList.remove("expanded"),this.innerText="Lihat Selengkapnya"):(c.classList.add("expanded"),this.innerText="Sembunyikan"))})})},0)}else if(Oa.includes(a.condition)){const e=a.can_edit??(window.KPI_CONFIG?.userJabatan||"").toLowerCase().trim()==="koordinator itsm";A=d(a,e,window.KPI_CONFIG.csrfToken)}else A=`<div class="mt-4"><div class="card shadow-sm border-0 rounded-4"><div class="card-body"><div class="d-flex justify-content-between mb-3"><h6 class="fw-semibold mb-0">Statistik ${a.condition}</h6><div class="d-flex gap-2"><select class="form-select form-select-sm" id="filterType"><option value="year">Per Tahun</option><option value="month">Per Bulan</option></select><select class="form-select form-select-sm d-none" id="filterMonth"><option value="">Pilih Bulan</option></select></div></div><div style="height:300px"><canvas id="StatisticChart"></canvas></div></div></div></div>`;let xa="";const ea=Array.isArray(a.data_detail.instruktur_details)?a.data_detail.instruktur_details:[],ka=a.data_detail.hari_libur_nasional||{jumlah:0,daftar:{}};if(ea.length>0){let m=function(s,r){const x=s.kalender||{};if(!x[r])return'<div class="text-muted text-center py-4">Tidak ada data untuk bulan ini.</div>';const[_,I]=r.split("-"),w=e[parseInt(I,10)-1],D=new Date(_,I,0).getDate(),M=new Date(_,parseInt(I,10)-1,1).getDay();let C="";t.forEach(k=>{C+=`<div class="kal-header">${k}</div>`});for(let k=0;k<M;k++)C+='<div class="kal-cell kal-empty"></div>';for(let k=1;k<=D;k++){const P=x[r][k]||{status:"empty",keterangan:""},qa="kal-"+P.status;C+=`<div class="kal-cell ${qa}" title="${P.keterangan||""}" data-bs-toggle="tooltip"><div class="kal-day">${k}</div></div>`}const B=Object.values(x[r]).reduce((k,P)=>(k[P.status]=(k[P.status]||0)+1,k),{});return`
                                    <div class="kalender-bulan-slide" data-bulan="${r}">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="fw-semibold mb-0">${w} ${_}</h6>
                                            <div class="d-flex gap-2 small">
                                                <span class="badge bg-success">Aktif: ${B.working||0}</span>
                                                <span class="badge bg-warning text-dark">Cuti: ${B.cuti||0}</span>
                                                <span class="badge bg-danger">Libur: ${B.libur||0}</span>
                                            </div>
                                        </div>
                                        <div class="kalender-grid">${C}</div>
                                    </div>
                                `},c=function(s){const r=Object.entries(s.daftar_cuti||{});return r.length===0?"":`
                                    <div class="mt-3">
                                        <h6 class="fw-semibold small text-muted mb-2"><i class="fa-solid fa-suitcase-rolling me-1"></i>Detail Cuti (${r.length} hari)</h6>
                                        <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                                            <table class="table table-sm table-bordered mb-0">
                                                <thead class="table-warning"><tr><th>Tanggal</th><th>Tipe</th><th>Alasan</th></tr></thead>
                                                <tbody>
                                                    ${r.map(([x,_])=>`
                                                        <tr>
                                                            <td class="small">${new Date(x).toLocaleDateString("id-ID",{day:"2-digit",month:"short",year:"numeric"})}</td>
                                                            <td class="small">${_.tipe||"-"}</td>
                                                            <td class="small">${_.alasan||"-"}</td>
                                                        </tr>
                                                    `).join("")}
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                `};var Sa=m,Ua=c;const e=["Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"],t=["Min","Sen","Sel","Rab","Kam","Jum","Sab"];window.__instrukturData=ea,window.__currentMonthIndex={},window.showKalenderBulan=function(s,r){const x=window.__instrukturData.find(P=>P.id==s);if(!x)return;const _=x.kalender||{},I=Object.keys(_).sort(),w=I.length;if(w===0)return;r<0&&(r=w-1),r>=w&&(r=0),window.__currentMonthIndex[s]=r;const D=I[r],M=document.getElementById(`kalenderSlide_${s}`);M&&(M.innerHTML=m(x,D));const C=document.getElementById(`bulanCounter_${s}`);C&&(C.textContent=`${r+1} dari ${w} bulan`);const B=document.getElementById(`btnPrevBulan_${s}`),k=document.getElementById(`btnNextBulan_${s}`);B&&(B.disabled=w<=1),k&&(k.disabled=w<=1)};let v="";const u=Object.entries(ka.daftar||{});u.length>0&&(v=`
                                    <div class="alert alert-light border mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0 fw-semibold"><i class="fa-solid fa-flag me-2 text-danger"></i>Hari Libur Nasional</h6>
                                            <span class="badge bg-danger">${ka.jumlah} Hari</span>
                                        </div>
                                        <div class="row g-2" style="max-height: 150px; overflow-y: auto;">
                                            ${u.map(([s,r])=>`
                                                <div class="col-md-6">
                                                    <div class="small p-2 bg-danger bg-opacity-10 rounded">
                                                        <strong>${new Date(s).toLocaleDateString("id-ID",{day:"2-digit",month:"short",year:"numeric"})}</strong>
                                                        <div class="text-muted">${r}</div>
                                                    </div>
                                                </div>
                                            `).join("")}
                                        </div>
                                    </div>
                                `);let o="";ea.forEach((s,r)=>{const x=s.persentase>=100?"success":s.persentase>=75?"warning":"danger",_=s.kalender||{},I=Object.keys(_).sort(),w=I.length;window.__currentMonthIndex[s.id]=0;const D=c(s);o+=`
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="headingInstruktur${r}">
                                            <button class="accordion-button ${r>0?"collapsed":""}" type="button" data-bs-toggle="collapse" data-bs-target="#collapseInstruktur${r}" aria-expanded="${r===0?"true":"false"}">
                                                <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                                    <div>
                                                        <strong>${s.nama}</strong>
                                                        <div class="small text-muted">${s.kode_karyawan} • ${s.jabatan}</div>
                                                    </div>
                                                    <div class="text-end">
                                                        <div class="fw-bold text-${x}">${j(s.persentase)}%</div>
                                                        <div class="small text-muted">${s.jam_aktif} / ${s.target_jam} jam</div>
                                                    </div>
                                                </div>
                                            </button>
                                        </h2>
                                        <div id="collapseInstruktur${r}" class="accordion-collapse collapse ${r===0?"show":""}" data-bs-parent="#accordionInstruktur">
                                            <div class="accordion-body">
                                                <div class="row g-2 mb-3">
                                                    <div class="col-6 col-md-3"><div class="p-2 border rounded text-center bg-success-subtle"><div class="small text-muted">Hari Aktif</div><div class="fw-bold fs-5">${s.total_hari_kerja}</div></div></div>
                                                    <div class="col-6 col-md-3"><div class="p-2 border rounded text-center bg-warning-subtle"><div class="small text-muted">Hari Cuti</div><div class="fw-bold fs-5">${s.total_hari_cuti}</div></div></div>
                                                    <div class="col-6 col-md-3"><div class="p-2 border rounded text-center bg-danger-subtle"><div class="small text-muted">Hari Libur</div><div class="fw-bold fs-5">${s.total_hari_libur}</div></div></div>
                                                    <div class="col-6 col-md-3"><div class="p-2 border rounded text-center bg-primary-subtle"><div class="small text-muted">Jam Aktif</div><div class="fw-bold fs-5">${s.jam_aktif}</div></div></div>
                                                </div>
                                                <div class="d-flex flex-wrap gap-2 mb-3 small">
                                                    <span><span class="kal-legend-box kal-working"></span> Hari Aktif</span>
                                                    <span><span class="kal-legend-box kal-cuti"></span> Cuti</span>
                                                    <span><span class="kal-legend-box kal-libur"></span> Libur Nasional</span>
                                                    <span><span class="kal-legend-box kal-weekend"></span> Weekend</span>
                                                    <span><span class="kal-legend-box kal-empty"></span> Tidak Ada Aktivitas</span>
                                                </div>
                                                ${D}
                                                <div class="mt-3">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <h6 class="fw-semibold mb-0"><i class="fa-solid fa-calendar me-2"></i>Kalender Bulanan</h6>
                                                        <span class="badge bg-info" id="bulanCounter_${s.id}">1 dari ${w} bulan</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center gap-2 mb-3 p-2 bg-light rounded-3">
                                                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnPrevBulan_${s.id}" onclick="showKalenderBulan(${s.id}, window.__currentMonthIndex[${s.id}] - 1)"><i class="fa-solid fa-chevron-left me-1"></i> Bulan Sebelumnya</button>
                                                        <div class="flex-grow-1 text-center"><small class="text-muted">Gunakan tombol atau swipe untuk navigasi</small></div>
                                                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnNextBulan_${s.id}" onclick="showKalenderBulan(${s.id}, window.__currentMonthIndex[${s.id}] + 1)">Bulan Selanjutnya <i class="fa-solid fa-chevron-right ms-1"></i></button>
                                                    </div>
                                                    <div id="kalenderSlide_${s.id}">
                                                        ${w>0?m(s,I[0]):'<div class="text-muted text-center py-4">Tidak ada data kalender.</div>'}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `}),xa=`
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
                            `}l.append(`
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
                                                        <span class="badge bg-${aa}">${S}</span>
                                                    </div>
                                                    <div class="row text-center mb-3">
                                                        <div class="col"><small class="text-muted d-block">Target</small><h4 class="fw-bold mb-0" style="font-size: 25px">${V}</h4></div>
                                                        <div class="col"><small class="text-muted d-block">Progress</small><h1 class="fw-bold text-${aa} mb-0" style="font-size: 55px;">${H}</h1></div>
                                                        <div class="col"><small class="text-muted d-block">Gap</small><h4 class="fw-bold text-danger mb-0" style="font-size: 25px">${ma?R:`-${R}`}</h4></div>
                                                    </div>
                                                    <div class="position-relative mb-3">
                                                        <div class="progress" style="height:18px;"><div class="progress-bar bg-${aa} progress-bar-striped progress-bar-animated" style="width: ${z}%"></div></div>
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
                                                                        ${Ra||'<div class="text-muted small">Tidak ada karyawan</div>'}
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
                                                <div class="card-body d-flex flex-column">${W}</div>
                                            </div>
                                        </div>
                                    </div>
                                    ${q}
                                    ${wa}
                                    ${A}
                                    ${xa}
                                </div>
                            </div>
                            <div class="modal-footer border-0"><button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button></div>
                        `),setTimeout(()=>{l.addClass("modal-content-loaded");const e=document.getElementById("MyChartDoughtnut");e&&new Chart(e,{type:"doughnut",data:Ma,options:{responsive:!0,maintainAspectRatio:!1,plugins:{legend:{position:"bottom",labels:{boxWidth:12,padding:15}}}}});let t=null;const m=document.getElementById("StatisticChart");function c(o,s,r){if(!m)return;t&&t.destroy();const _=(s.length>0?Math.max(...s):0)+3;t=new Chart(m,{type:"line",data:{labels:o,datasets:[{label:r,data:s,borderColor:"#4e73df",backgroundColor:"rgba(78, 115, 223, 0.1)",tension:.4,fill:!0}]},options:{responsive:!0,maintainAspectRatio:!1,scales:{y:{beginAtZero:!0,suggestedMax:_,ticks:{count:6,precision:0,callback:function(I){return Math.round(I)}}}}}})}const v=Object.keys(Z).map(o=>E(o)),u=Object.values(Z);c(v,u,"Rata-rata"),$("#filterType").off("change").on("change",function(){this.value==="month"?($("#filterMonth").removeClass("d-none").empty().append('<option value="">Pilih Bulan</option>'),Object.keys(N).forEach(o=>{$("#filterMonth").append(`<option value="${o}">${E(o)}</option>`)}),t&&t.destroy()):($("#filterMonth").addClass("d-none"),c(v,u,"Rata-rata"))}),$("#filterMonth").off("change").on("change",function(){const o=this.value;if(!o||!N[o])return;const s=Object.keys(N[o]).map(x=>x.substring(8)),r=Object.values(N[o]);c(s,r,`Tanggal ${E(o)}`)})},50)}},error:function(){l.empty(),l.html(`
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
                    `),l.addClass("modal-content-loaded")}})});function d(n,l,i){const g=Array.isArray(n.karyawan)?n.karyawan:[],b=l?"":"disabled",a=g.map((h,f)=>{const T=h.skala_kemampuan??null,p=T===null?'<span class="badge bg-secondary">Belum dinilai</span>':`<span class="badge bg-info text-dark">${j(T*10)}%</span>`;return`
                    <div class="row mb-2 align-items-center p-2 rounded">
                        <div class="col-md-6 d-flex justify-content-between align-items-center">
                            <span>${h.nama_lengkap??"-"}</span>${p}
                        </div>
                        <div class="col-md-6">
                            <input type="number" min="1" max="10" step="0.5" class="form-control"
                                name="data[${f}][kemampuan]" value="${T??""}"
                                placeholder="1 - 10" ${b}>
                        </div>
                        <input type="hidden" name="data[${f}][id_karyawan]" value="${h.id_karyawan}">
                        <input type="hidden" name="data[${f}][detailTargetKey]" value="${h.detailTargetKey}">
                    </div>`}).join("");return`
                <div class="mt-4"><div class="card shadow-sm border-0 rounded-4"><div class="card-body">
                    <h6 class="fw-semibold mb-1">Penilaian Kemampuan Tim</h6>
                    <small class="text-muted d-block mb-3">Isi skala 1-10 (8 = 80%). Gap dihitung dari rata-rata seluruh anggota tim.</small>
                    <form id="formGapKompetensi">
                        <input type="hidden" name="_token" value="${i}">
                        <div class="row mb-2 fw-semibold text-muted border-bottom pb-2">
                            <div class="col-md-6">Nama Karyawan</div><div class="col-md-6">Nilai (1-10)</div>
                        </div>
                        ${a||'<div class="text-muted small">Tidak ada anggota tim.</div>'}
                        ${l?'<div class="mt-3"><button type="submit" class="btn btn-primary">Simpan</button></div>':""}
                    </form>
                </div></div></div>`}})})();
