let w=[],h=[],c={},T=null,D=null;$(document).ready(function(){B(),x()});function _(e){return e.toLocaleString("id-ID",{minimumFractionDigits:2,maximumFractionDigits:2})}$("#jenis-penilaian-tab").on("click",".modern-tab-btn",function(){$("#jenis-penilaian-tab .modern-tab-btn").removeClass("active-tab"),$(this).addClass("active-tab");const e=$(this).data("jenis");E(e)});function B(){let e=new FormData;e.append("_token",window.PENILAIAN_DETAIL_CONFIG.csrfToken),e.append("kodeForm",$("#kodeForm").val()),e.append("id_karyawan",$("#id_karyawan").val()),e.append("tahun",$("#selectTahun").val()||new Date().getFullYear()),e.append("jenis_form",$("#jenis_form").val()),$.ajax({url:window.PENILAIAN_DETAIL_CONFIG.detailGetRoute,type:"POST",data:e,dataType:"json",contentType:!1,processData:!1,success:function(t){$("#skeletonInfo").addClass("d-none"),$("#skeletonTable").addClass("d-none"),$("#skeletonAbsensi").addClass("d-none");const i=t.data[0];w=i.data.evaluator,h=i.data.dataKriteria,c=i.evaluated,globalChart=i.chart,globalAbsensi=i.dataAbsen,i.evaluated.tahun;let o=$("#content_utama");o.empty();let p=$("#body_content_absensi");p.empty(),globalAbsensi.isEmpty?p.append('<tr><td colspan="3" class="text-center text-muted py-3">Tidak ada data absensi</td></tr>'):p.append(`<tr><td>${globalAbsensi.telat}</td><td>${globalAbsensi.sakit}</td><td>${globalAbsensi.izin}</td></tr>`);const d=[...new Set(w.map(n=>n.jenis_penilaian))];let u=h.length>0?h[0].kodeForm:"",g=c.id_karyawan??"",m='<a class="modern-tab-btn active-tab" data-jenis="all"><i class="fa-solid fa-layer-group me-1"></i> Semua</a>';const y=window.PENILAIAN_DETAIL_CONFIG.pdfPreviewRoute,a=(n,r)=>`${y}?kodeForm=${encodeURIComponent(u)}&id_karyawan=${encodeURIComponent(g)}&tipe=${n}&mode=${r}`;let l=`
                <div class="d-flex flex-wrap gap-2 align-items-start">
                    <button id="kirimEmail" class="btn-action success" data-kodeform="${u}" data-id="${g}">
                        <i class="fa-solid fa-paper-plane"></i> Kirim Email
                    </button>

                    <div class="pdf-dropdown">
                        <button type="button" id="pdfDropdownToggle" class="btn-action danger">
                            <i class="fa-solid fa-file-pdf"></i> Print / Download
                            <i class="fa-solid fa-chevron-down ms-1" style="font-size:.7rem;"></i>
                        </button>
                        <div class="pdf-dropdown-menu" id="pdfDropdownMenu">
                            <div class="pdf-dropdown-title">Print (Preview)</div>
                            <a href="${a("office","print")}" target="_blank" class="pdf-dropdown-item">
                                <i class="fa-solid fa-print"></i> Print Office
                            </a>
                            <a href="${a("non_office","print")}" target="_blank" class="pdf-dropdown-item">
                                <i class="fa-solid fa-print"></i> Print Non-Office
                            </a>
                            <div class="pdf-dropdown-divider"></div>
                            <div class="pdf-dropdown-title">Download PDF</div>
                            <a href="${a("office","download")}" class="pdf-dropdown-item">
                                <i class="fa-solid fa-download"></i> Download Office
                            </a>
                            <a href="${a("non_office","download")}" class="pdf-dropdown-item">
                                <i class="fa-solid fa-download"></i> Download Non-Office
                            </a>
                        </div>
                    </div>
                </div>`;const f={"General Manager":"fa-crown","Manager/SPV/Team Leader (Atasan Langsung)":"fa-user-tie","Rekan Kerja (Satu Divisi)":"fa-users","Pekerja (Beda Divisi)":"fa-people-arrows","Self Apprisial":"fa-user-check"};d.forEach(n=>{let r=n==="Manager/SPV/Team Leader (Atasan Langsung)"?"Koordinator":n==="Rekan Kerja (Satu Divisi)"?"Satu Divisi":n==="Pekerja (Beda Divisi)"?"Beda Divisi":n,b=f[n]||"fa-user";m+=`<a class="modern-tab-btn" data-jenis="${n}"><i class="fa-solid ${b} me-1"></i> ${r}</a>`}),$("#shareEmail").html(l),$("#jenis-penilaian-tab").html(m);let s=w.map(n=>`<li data-target="${n.nama}-${n.jenis_penilaian}" class="list-group-item"><i class="fa-solid fa-user me-2 text-primary"></i>${n.nama}</li>`).join("");o.append(`
                <div class="info-item">
                    <div class="info-label"><i class="fa-solid fa-file-lines me-1"></i> Jenis Form</div>
                    <div class="info-value">${window.PENILAIAN_DETAIL_CONFIG.tipeForm}</div>
                </div>
                <div class="info-item">
                    <div class="info-label"><i class="fa-solid fa-users me-1"></i> Evaluator</div>
                    <ul class="list-group evaluator-list ms-0">${s}</ul>
                </div>
                <div class="info-item">
                    <div class="info-label"><i class="fa-solid fa-user-check me-1"></i> Yang Dinilai</div>
                    <div class="info-value">${c.nama}</div>
                </div>
                <div class="row g-2">
                    <div class="col-12">
                        <div class="info-item mb-0">
                            <div class="info-label"><i class="fa-solid fa-calendar me-1"></i> Tahun</div>
                            <div class="info-value text-center">${c.tahun}</div>
                        </div>
                    </div>
                </div>
                <hr class="my-3">
                <form method="post" action="${window.PENILAIAN_DETAIL_CONFIG.sendCatatanRoute}">
                    <input type="hidden" name="_token" value="${window.PENILAIAN_DETAIL_CONFIG.csrfToken}">
                    <input type="hidden" name="id_karyawan" value="${c.id_karyawan}">
                    <input type="hidden" name="tahun" value="${c.tahun}">
                    <input type="hidden" name="kode_form" value="${c.kode_form}">
                    <div class="info-item">
                        <label class="form-label-modern"><i class="fa-solid fa-note-sticky"></i> Catatan</label>
                        <textarea class="modern-textarea" placeholder="Berikan catatan..." rows="4" name="catatan">${c.catatan==="null"||c.catatan===null?"":c.catatan}</textarea>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn-action primary"><i class="fa-solid fa-save"></i> Simpan Catatan</button>
                    </div>
                </form>
            `),E("all")},error:function(){$("#skeletonInfo, #skeletonTable, #skeletonAbsensi").addClass("d-none")}})}function E(e){let t=$("#body_content");t.empty();const i={"General Manager":35,"Manager/SPV/Team Leader (Atasan Langsung)":30,"Rekan Kerja (Satu Divisi)":20,"Pekerja (Beda Divisi)":10,"Self Apprisial":5};let o={},p=e==="all"?w:w.filter(a=>a.jenis_penilaian===e);if(p.length===0){t.append('<tr><td colspan="6" class="text-center py-5 text-muted"><i class="fa-solid fa-inbox fa-2x mb-2 d-block"></i>Tidak Ada Data</td></tr>');return}p.forEach(a=>{let l=a.nilai,f=0;h.forEach(s=>{s.detailKriteria.forEach(n=>{const r=l[f++]||{nilai:"-"},b=parseFloat(r.nilai);n.tipe_input!=="textarea"&&!isNaN(b)&&(o[a.jenis_penilaian]=o[a.jenis_penilaian]||{},o[a.jenis_penilaian][s.kriteria]=o[a.jenis_penilaian][s.kriteria]||{},o[a.jenis_penilaian][s.kriteria][n.sub_kriteria]=o[a.jenis_penilaian][s.kriteria][n.sub_kriteria]||[],o[a.jenis_penilaian][s.kriteria][n.sub_kriteria].push(b))})})});let d={};for(const a in o){d[a]={};for(const l in o[a]){d[a][l]={};for(const f in o[a][l]){let s=o[a][l][f];d[a][l][f]=s.reduce((n,r)=>n+r,0)/s.length}}}let u={};p.forEach(a=>{t.append(`<tr id="${a.nama}-${a.jenis_penilaian}" class="evaluator-header"><td colspan="6"><i class="fa-solid fa-user me-2"></i>${a.nama} <span class="badge bg-primary bg-opacity-10 text-primary ms-2" style="font-size: .75rem;">${a.jenis_penilaian}</span></td></tr>`);let l=a.nilai,f=0,s=0;h.forEach(r=>{const b=r.detailKriteria,M=b.length;b.forEach((v,K)=>{var L,P;const j=l[f++]||{nilai:"-",pesan:"-"},N=j.nilai,C=j.pesan,Y=v.tipe_input,F=parseFloat(v.bobot);let H=K===0?`<td rowspan="${M}" class="text-left fw-semibold align-middle">${r.kriteria}</td>`:"",V=`<td style="text-align: left;">${v.sub_kriteria}</td>`,I="";if(Y==="textarea")I=`<td colspan="4" style="font-style: italic; color: #64748b;">${C&&C.trim()!==""?C:"-"}</td>`;else{const S=parseFloat(N),O=(P=(L=d[a.jenis_penilaian])==null?void 0:L[r.kriteria])==null?void 0:P[v.sub_kriteria];let k="-",A=0;isNaN(S)||(k=O!==void 0?O:S,A=k*F/100,s+=A),I=`<td><span class="badge bg-light text-dark border">${F}%</span></td><td class="fw-semibold">${N}</td><td>${k==="-"?"-":_(k)}</td><td class="fw-bold text-primary">${k==="-"?"-":_(A)}</td>`}t.append(`<tr>${H}${V}${I}</tr>`)})}),t.append(`<tr class="total-row"><td colspan="5" class="text-end">Total (${a.nama})</td><td class="text-center">${_(s)}</td></tr>`);const n=a.jenis_penilaian;u.hasOwnProperty(n)||(u[n]=s)});let g={};for(const a in u){const l=i[a]||0;g[a]=u[a]*l/100}let m=e==="all"?Object.values(g).reduce((a,l)=>a+l,0):g[e]||0,y=m>=90?"A":m>=80?"B":m>=70?"C":m>=60?"D":"E";t.append(`<tr class="grand-total-row"><td colspan="5" class="text-end">Grade</td><td class="text-center fs-4 fw-bold">${y}</td></tr>`)}$(document).on("click",".evaluator-list .list-group-item",function(){const e=$(this).data("target"),t=document.getElementById(e);t&&$("html, body").animate({scrollTop:$(t).offset().top-100},500)});$(document).on("click","#kirimEmail",function(e){e.preventDefault(),$.ajax({url:window.PENILAIAN_DETAIL_CONFIG.emailRoute,type:"POST",data:{_token:window.PENILAIAN_DETAIL_CONFIG.csrfToken,kodeForm:$(this).data("kodeform"),id_karyawan:$(this).data("id")},dataType:"json",success:function(t){Swal.fire({icon:"success",title:"Berhasil!",text:"Email berhasil dikirim!",confirmButtonColor:"#6366f1"})},error:function(t){Swal.fire({icon:"error",title:"Gagal!",text:`Gagal mengirim email: ${t.responseText}`,confirmButtonColor:"#ef4444"})}})});$(document).on("click","#pdfDropdownToggle",function(e){e.stopPropagation(),$("#pdfDropdownMenu").toggleClass("show")});$(document).on("click",".pdf-dropdown-item",function(){$("#pdfDropdownMenu").removeClass("show")});$(document).on("click",function(e){$(e.target).closest(".pdf-dropdown").length||$("#pdfDropdownMenu").removeClass("show")});function x(){let e=new FormData;e.append("_token",window.PENILAIAN_DETAIL_CONFIG.csrfToken),e.append("kodeForm",$("#kodeForm").val()),e.append("id_karyawan",$("#id_karyawan").val()),e.append("tahun",$("#selectTahun").val()||new Date().getFullYear()),$.ajax({url:window.PENILAIAN_DETAIL_CONFIG.detailChartGetRoute,type:"POST",data:e,dataType:"json",contentType:!1,processData:!1,success:function(t){$("#skeletonChart1").addClass("d-none"),$("#skeletonChart2").addClass("d-none"),t.chartTahunan&&G(t.chartTahunan)},error:function(){$("#skeletonChart1, #skeletonChart2").addClass("d-none")}})}const R=document.getElementById("selectTahun");if(R){const e=new Date().getFullYear();for(let t=e;t<=e+10;t++){const i=document.createElement("option");i.value=t,i.textContent=t,t===e&&(i.selected=!0),R.appendChild(i)}$("#selectTahun").on("change",function(){x()})}function G(e){const t=Object.keys(e).sort(),i=t.map(d=>parseFloat(e[d]).toFixed(2)),o=document.getElementById("barChart").getContext("2d");T&&T.destroy(),T=new Chart(o,{type:"bar",data:{labels:t,datasets:[{label:"Skor per Tahun",data:i,backgroundColor:"rgba(99, 102, 241, 0.7)",borderColor:"#6366f1",borderWidth:2,borderRadius:8}]},options:{responsive:!0,maintainAspectRatio:!1,plugins:{legend:{display:!1},tooltip:{backgroundColor:"rgba(15, 23, 42, 0.9)",padding:12,cornerRadius:8}},scales:{y:{beginAtZero:!0,grid:{color:"#f1f5f9"},ticks:{callback:d=>d.toFixed(2)}},x:{grid:{display:!1}}}}});const p=document.getElementById("lineChart").getContext("2d");D&&D.destroy(),D=new Chart(p,{type:"line",data:{labels:t,datasets:[{label:"Trend Skor Tahunan",data:i,borderColor:"#6366f1",backgroundColor:"rgba(99, 102, 241, 0.15)",borderWidth:3,fill:!0,tension:.4,pointRadius:5,pointHoverRadius:7,pointBackgroundColor:"#fff",pointBorderColor:"#6366f1",pointBorderWidth:2}]},options:{responsive:!0,maintainAspectRatio:!1,plugins:{legend:{position:"bottom",labels:{usePointStyle:!0,padding:15}},tooltip:{backgroundColor:"rgba(15, 23, 42, 0.9)",padding:12,cornerRadius:8}},scales:{y:{beginAtZero:!0,grid:{color:"#f1f5f9"},ticks:{callback:d=>d.toFixed(2)}},x:{grid:{display:!1}}}}})}Object.assign(window,{pengubahFormat:_,loadData:B,renderTabel:E,loadChartData:x,tampilkanChartSkorTahunan:G});
