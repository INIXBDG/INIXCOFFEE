const P=s=>(Number(s)||0).toFixed(1);let Ia=[],ba=[],B=1,na=1,Da=0;const Sa=10,fa=["dorong inovasi pelayanan","inisiatif efisiensi keuangan","mengurangi manual work dan error"],Ya=[],Wa={"pemasukan kotor":"/rkm","target penjualan project tahunan":"/projects/leads","pemasukan bersih":"/office/analysis","kepuasan pelanggan":"/feedback","rasio biaya operasional terhadap revenue":"/kpi-data/table-data","performa kpi departemen":"/kpi-data/table-data","peserta puas dengan pelayanan dan fasilitas training":"/feedback","dorong inovasi pelayanan":"/kpi-data/table-data","penanganan komplain perseta":"/komplain-peserta","report persiapan kelas":"/office/dashboard",outstanding:"/outstanding","inisiatif efisiensi keuangan":"/kpi-data/table-data","mengurangi manual work dan error":"/kpi-data/table-data","laporan analisis keuangan":"/office/analysis","pencairan biaya operasional":"/pengajuanbarang","penyelesaian tagihan perusahaan":"/office/tagihan-perusahaan","akurasi pencatatan masuk":"/outstanding","pelaksanaan kegiatan karyawan":"office/kegiatan/index","pengeluaran biaya karyawan":"/office/administrasi-karyawan","administrasi karyawan":"/office/administrasi-karyawan","perbaikan kendaraan":"/office/kendaraan/index/perbaikan","report kondisi kendaraan":"/office/kendaraan/index/kondisi","kontrol pengeluaran transportasi":"/office/pickup-driver/index","feedback kenyamanan berkendaran":"/feedback","feedback kebersihan dan kenyamanan":"/feedback","penyelesaian tugas harian":"/office/daftar-tugas/Index","meningkatkan kepuasan dan loyalitas peserta/client":"/survey/kepuasan/table","availability sistem internal kritis":"/home","persentase gap kompetensi tim terhadap standar skill":"/kpi-data/table-data","inovation adaption rate":"/ide-inovasi","ketepatan waktu penyelesaian fitur":"/tickets","mengukur kualitas aplikasi agar minim bug":"/tickets","konsistensi campaign digital":"/content-schedules","efektifitas digital marketing":"/colaborator","timeline webinar":"/timeline","keberhasilan support memenuhi sla":"/tickets","kualitas layanan exam":"/registrasi","presentase kinerja instruktur":"/activityinstruktur","kepuasan peserta pelatihan":"/feedback","upseling lanjutan materi":"/office/rekomendasi-lanjutan/index","sertifikasi kompetensi internal":"/development","pelatihan kompetensi eksternal":"/development","pengembangan kurikulum pelatihan":"/materi","peningkatan knowledge sharing":"/activityinstruktur","peningkatan kontribusi pelatihan":"/rkm","evaluasi kinerja instruktur":"/activityinstruktur","target penjualan tahunan":"/rkm","biaya akuisisi perclient":"/crm/peluang/index","peningkatan kemampuan kompetensi sales":"https://elearning.inixindobdg.co.id/login/index.php?loginredirect=1","meningkatkan revenue perusahaan":"/rkm","customer acquisition cost":"/crm/peluang/index","evaluasi kinerja sales":"/crm/aktivitas","laporan mom":"/crm/laporan-harian","akurasi kelengkapan data penjualan":"/crm/laporanPenjualan","todo administrasi":"/crm/todo-administrasi","ketepatan waktu po":"/office/modul/index","kualitas dokumentasi support dan proctor":"/daftar-peserta-exam","pendapatan penjualan project":"/projects/leads","leads project":"/projects/leads"};function R(s){if(!s&&s!=="0")return"";const m=String(s).replace(/[^0-9]/g,"");return m?new Intl.NumberFormat("id-ID").format(m):""}function K(s){return s?String(s).replace(/[^0-9]/g,""):""}function Xa(){$("#manual_value_display").off("input.formatting").on("input.formatting",function(){const s=K($(this).val());$("#manual_value").val(s);const m=$("#manual_format").val();let c=R(s);m==="rupiah"&&s?c="Rp "+c:m==="persen"&&s&&(c=c+"%"),$(this).val(c)}),$("#biaya_gaji_display").off("input.formatting").on("input.formatting",function(){const s=K($(this).val());$("#biaya_gaji_tahunan").val(s),$(this).val(s?"Rp "+R(s):"")}),$("#biaya_bpjs_display").off("input.formatting").on("input.formatting",function(){const s=K($(this).val());$("#biaya_bpjs_tahunan").val(s),$(this).val(s?"Rp "+R(s):"")}),$("#biaya_rekrutmen_display").off("input.formatting").on("input.formatting",function(){const s=K($(this).val());$("#biaya_rekrutmen_tahunan").val(s),$(this).val(s?"Rp "+R(s):"")})}function ma(){$("#formManualValue")[0].reset(),$("#documentPreview").html(""),$("#singleInputArea").show(),$("#doubleInputArea").hide(),$("#manual_value_display").val("").trigger("input"),$("#manual_value").val(""),$("#biaya_gaji_display").val("").trigger("input"),$("#biaya_gaji_tahunan").val(""),$("#biaya_bpjs_display").val("").trigger("input"),$("#biaya_bpjs_tahunan").val(""),$("#biaya_rekrutmen_display").val("").trigger("input"),$("#biaya_rekrutmen_tahunan").val(""),$("#manualValueId").val("")}$(document).ready(function(){if($("#jabatan").select2({theme:"bootstrap-5",allowClear:!0,width:"100%",dropdownParent:$("#modalBuatTarget")}),$("#karyawan").select2({theme:"bootstrap-5",allowClear:!0,width:"100%",dropdownParent:$("#modalBuatTarget")}),$(document).on("click",".buttonHapusTarget, .buttonForm",function(r){r.stopPropagation()}),$(document).on("click",".buttonPanduan",function(r){r.preventDefault(),r.stopPropagation();const t=($(this).data("route")||"").toString().toLowerCase().trim(),e=$(this).data("judul")||"Target KPI",i={"pelaksanaan kegiatan karyawan":"pelaksanaan_kegiatan_karyawan","pengeluaran biaya karyawan":"pengeluaran_biaya_karyawan","administrasi karyawan":"administrasi_karyawan","mengukur kualitas aplikasi agar minim bug":"mengukur_kualitas_aplikasi_agar_minim_bug","ketepatan waktu penyelesaian fitur":"ketepatan_waktu_penyelesaian_fitur","keberhasilan support memenuhi sla":"tingkat_keberhasilan_support_memenuhi_sla","kualitas layanan exam":"kualitas_layanan_exam","meningkatkan kepuasan dan loyalitas peserta/client":"meningkatkan_kepuasan_dan_loyalitas_peserta","availability sistem internal kritis":"availability_sistem_internal_kritis","persentase gap kompetensi tim terhadap standar skill":"persentase_gap_kompetensi","konsistensi campaign digital":"konsistensi_campaign_digital","ketepatan waktu po":"ketepatan_waktu_po","kualitas dokumentasi support dan proctor":"kualitas_dokumentasi_support_dan_proctor","laporan mom":"laporan_mom","akurasi kelengkapan data penjualan":"akurasi_kelengkapan_data_penjualan","todo administrasi":"todo_administrasi","peserta puas dengan pelayanan dan fasilitas training":"peserta_puas_dengan_pelayanan_dan_fasilitas_training","dorong inovasi pelayanan":"dorong_inovasi_pelayanan","penanganan komplain perseta":"penanganan_komplain_peserta","report persiapan kelas":"report_persiapan_kelas","kepuasan client itsm":"kepuasan_client_itsm","inovation adaption rate":"inovation_adaption_rate","perbaikan kendaraan":"perbaikan_kendaraan","kontrol pengeluaran transportasi":"kontrol_pengeluaran_transportasi","report kondisi kendaraan":"report_kondisi_kendaraan","feedback kenyamanan berkendaran":"feedback_kenyamanan_berkendara","pengembangan kurikulum pelatihan":"pengembangan_kurikulum_pelatihan","peningkatan knowledge sharing":"peningkatan_knowledge_sharing","peningkatan kontribusi pelatihan":"peningkatan_kontribusi_pelatihan","evaluasi kinerja instruktur":"evaluasi_kinerja_instruktur","pembuatan artikel":"pembuatan_artikel",outstanding:"outstanding","inisiatif efisiensi keuangan":"inisiatif_efisiensi_keuangan","mengurangi manual work dan error":"mengurangi_manual_work_dan_error","laporan analisis keuangan":"laporan_analisis_keuangan","pencairan biaya operasional":"pencairan_biaya_operasional","penyelesaian tagihan perusahaan":"penyelesaian_tagihan_perusahaan","akurasi pencatatan masuk":"akurasi_pencatatan_masuk","pemasukan kotor":"pemasukan_kotor","pemasukan bersih":"pemasukan_bersih","target penjualan project tahunan":"target_penjualan_project_tahunan","kepuasan pelanggan":"kepuasan_pelanggan","rasio biaya operasional terhadap revenue":"rasio_biaya_operasional_terhadap_revenue","performa kpi departemen":"performa_kpi_departemen","feedback kebersihan dan kenyamanan":"feedback_kebersihan_dan_kenyamanan","penyelesaian tugas harian":"penyelesaian_tugas_harian","efektifitas digital marketing":"efektifitas_digital_marketing","timeline webinar":"timeline_webinar","presentase kinerja instruktur":"presentase_kinerja_instruktur","kepuasan peserta pelatihan":"kepuasan_peserta_pelatihan","upseling lanjutan materi":"upseling_lanjutan_materi","sertifikasi kompetensi internal":"sertifikasi_kompetensi_internal","pelatihan kompetensi eksternal":"pelatihan_kompetensi_eksternal","target penjualan tahunan":"target_penjualan_tahunan","biaya akuisisi perclient":"biaya_akuisisi_perclient","peningkatan kemampuan kompetensi sales":"peningkatan_kemampuan_kompetensi_sales","meningkatkan revenue perusahaan":"meningkatkan_revenue_perusahaan","customer acquisition cost":"customer_acquisition_cost","evaluasi kinerja sales":"evaluasi_kinerja_sales","pendapatan penjualan project":"pendapatan_penjualan_project","leads project":"leads_project"}[t]||null,l=$("#modalPanduan"),d=$("#bodyContentPanduan"),h=$("#panduanTitle");if(h.text("Panduan: "+e),d.html(`
        <div class="text-center py-5">
            <i class="fa-solid fa-spinner fa-spin fa-2x text-primary mb-3"></i>
            <p class="text-muted mb-0">Memuat panduan...</p>
        </div>
    `),typeof bootstrap<"u"&&bootstrap.Modal?bootstrap.Modal.getOrCreateInstance(document.getElementById("modalPanduan")).show():l.modal("show"),!i){d.html(`
            <div class="alert alert-secondary text-center py-5 mb-0">
                <i class="fa-solid fa-book-open fa-2x mb-3 text-muted"></i>
                <p class="mb-0">Panduan untuk target ini belum tersedia.</p>
                <small class="text-muted d-block mt-2">Route: <code>${t||"-"}</code></small>
            </div>
        `);return}$.ajax({url:`/kpi-data/panduan/${i}`,type:"GET",dataType:"json",success:function(w){d.html(w.html||'<div class="alert alert-warning">Konten panduan kosong.</div>'),w.title&&h.text("Panduan: "+w.title)},error:function(){d.html(`
                <div class="alert alert-danger text-center py-4 mb-0">
                    <i class="fa-solid fa-circle-exclamation fa-2x mb-3"></i>
                    <p class="mb-0">Gagal memuat panduan. Silakan coba lagi.</p>
                </div>
            `)}})}),window.KPI_CONFIG&&window.KPI_CONFIG.importErrors&&window.KPI_CONFIG.importErrors.length>0){const r=window.KPI_CONFIG.importErrors,t=$("#errorSummary"),e=t.find("ul");e.empty(),r.slice(0,10).forEach(n=>e.append(`<li class="text-danger">${n}</li>`)),r.length>10&&e.append(`<li class="text-muted">...dan ${r.length-10} error lainnya</li>`),t.removeClass("d-none"),$("#ModalImport").modal("show")}$("#formImport").on("submit",function(r){const t=$("#btnSubmitImport"),e=$("#importPreview");t.prop("disabled",!0).html('<i class="fa-solid fa-spinner fa-spin me-1"></i>Memproses...'),e.removeClass("d-none")}),$.ajax({url:window.KPI_CONFIG.getDataTarget,type:"GET",success:function(r){const t=$("#assistant_route");t.length&&r.routes&&(t.empty().append("<option selected disabled>-- Pilih Assistant Route --</option>"),r.routes.forEach(function(n){t.append(`<option value="${n.asistant_route}">${n.asistant_route}</option>`)}),t.trigger("change"));const e=$("#jabatan");if(e.length&&r.jabatan_list){const n=e.val()||[];e.empty(),r.jabatan_list.forEach(function(i){e.append(`<option value="${i}">${i}</option>`)}),e.val(n),e.trigger("change")}}});let s=null,m=null;$("#jabatan").off("change").on("change",function(){const r=$(this).val()||[],t=$("#karyawan"),e=$("#assistant_route");if(t.empty().trigger("change"),e.empty().prop("disabled",!0).html("<option selected disabled>-- Memuat routes... --</option>").trigger("change"),$("#detail_jangka_container").empty(),r.length===0){e.html("<option selected disabled>-- Pilih Jabatan Terlebih Dahulu --</option>").trigger("change");return}s&&s.abort(),s=$.ajax({url:window.KPI_CONFIG.getKaryawanByJabatan,type:"GET",data:{jabatan:r},success:function(n){t.empty(),n&&n.length>0&&n.forEach(function(i){t.append(`<option value="${i.id}">${i.text}</option>`)}),t.trigger("change")}}),m&&m.abort(),m=$.ajax({url:window.KPI_CONFIG.getRoutesByJabatan,type:"GET",data:{jabatan:r},success:function(n){e.empty(),e.append('<option value="" selected disabled>-- Pilih Assistant Route --</option>'),n&&n.length>0?(n.forEach(i=>{e.append(`<option value="${i.asistant_route}">${i.asistant_route}</option>`)}),e.prop("disabled",!1)):(e.append("<option disabled>-- Tidak ada route tersedia untuk jabatan ini --</option>"),e.prop("disabled",!0)),e.trigger("change")},error:function(n,i){i!=="abort"&&(e.html("<option disabled>-- Gagal memuat routes --</option>").trigger("change"),typeof Swal<"u"&&Swal.fire("Error","Gagal memuat daftar assistant route","error"))}})}),V();let c;$("#searchTarget").on("input",function(){clearTimeout(c),c=setTimeout(()=>{const r=$(this).val().trim();B=1,V(1,r)},300)}),Xa(),$("#modalFormManual").on("show.bs.modal",function(){ma()}),$("#modalFormManual").on("hidden.bs.modal",function(){ma()}),$("#assistant_route").prop("disabled",!0).html("<option selected disabled>-- Pilih Jabatan Terlebih Dahulu --</option>"),$(document).on("change","#assistant_route",function(){const r=$(this).val();$("#jangka_target_display, #tipe_target_display, #nilai_target_display").val("").addClass("bg-light"),$("#jangka_target_hidden, #tipe_target_hidden, #nilai_target_hidden").val(""),$("#detail_jangka_container").empty(),r&&$.ajax({url:window.KPI_CONFIG.getTargetByRoute,type:"GET",data:{route:r},success:function(t){const e=t.jangka_target||"Tidak ditentukan";$("#jangka_target_display").val(e),$("#jangka_target_hidden").val(e);let n="";t.tipe_target==="persen"?n="Persen (%)":t.tipe_target==="rupiah"?n="Rupiah (Nilai Keuangan)":n="Angka (Unit, Jumlah, dll)",$("#tipe_target_display").val(n),$("#tipe_target_hidden").val(t.tipe_target);let i="";t.tipe_target==="rupiah"?i=new Intl.NumberFormat("id-ID",{style:"currency",currency:"IDR",minimumFractionDigits:0}).format(t.nilai_target):t.tipe_target==="persen"?i=`${t.nilai_target} %`:i=new Intl.NumberFormat("id-ID").format(t.nilai_target),$("#nilai_target_display").val(i),$("#nilai_target_hidden").val(t.nilai_target);let l=e.toLowerCase(),d="";if(l==="tahunan"){const h=new Date().getFullYear();d=`<div class="col-md-12 mb-3"><label for="detail_jangka" class="form-label">Tahun Pelaksanaan <span class="text-danger">*</span></label><select name="detail_jangka" id="detail_jangka" class="form-select" required><option value="${h}" selected>${h}</option></select></div>`}else l==="bulanan"?d='<div class="col-md-6 mb-3"><label for="detail_jangka" class="form-label">Bulan & Tahun <span class="text-danger">*</span></label><input type="month" name="detail_jangka" id="detail_jangka" class="form-control" required></div>':l==="kuartalan"?d='<div class="col-md-6 mb-3"><label for="detail_jangka" class="form-label">Tahun & Kuartal <span class="text-danger">*</span></label><input type="text" name="detail_jangka" id="detail_jangka" class="form-control" placeholder="Contoh: 2026-Q1" required></div>':d='<div class="col-md-6 mb-3"><label for="detail_jangka" class="form-label">Detail Jangka <span class="text-danger">*</span></label><input type="text" name="detail_jangka" id="detail_jangka" class="form-control" placeholder="Masukkan periode spesifik" required></div>';$("#detail_jangka_container").html(d)},error:function(){typeof Swal<"u"&&Swal.fire("Error","Gagal memuat konfigurasi target","error")}})}),$(document).on("click",".buttonDetailTarget",function(){let r=$(this).data("id");const t=$("#bodyContentDetailTarget");t.empty(),t.html(A());const e=document.getElementById("detailTargetModal");e&&typeof bootstrap<"u"&&bootstrap.Modal&&bootstrap.Modal.getOrCreateInstance(e).show(),$.ajax({url:window.KPI_CONFIG.detail,method:"GET",data:{id:r},dataType:"json",success:function(n){var La,Ra,Ka,Ga,Ha,Ja,Va,qa,Ua,za;t.empty();const i=Array.isArray(n.detail)?n.detail:[],l=i.length>0&&i[0].data||null;if(!l){t.append(`
                        <div class="modal-header"><h5 class="modal-title">Detail Target</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                        <div class="modal-body text-center">Belum ada data</div>
                        <div class="modal-footer"><button class="btn btn-danger" data-bs-dismiss="modal">Tutup</button></div>`);return}const d=l.data_detail||{},h=d.monthly_data||{},w=d.daily_breakdown_per_month||{},C=new Date(window.KPI_CONFIG.dateNow),Ma=new Date(window.KPI_CONFIG.startOfYear),U=l.tenggat_waktu||"";let L;const Q=new Date(C),ea=new Date(Ma),ta=new Date(U),va=d.is_inverse===!0||l.condition==="persentase gap kompetensi tim terhadap standar skill",la=d.progress??0,x=l.nilai_target??0,G=va?la<=x:la>=x;isNaN(Q)||isNaN(ea)||Q<ea?L="Belum Dimulai":!isNaN(ta)&&(Q>ta||Q.getTime()===ta.getTime())?L=G?"Mencapai Target":"Target Gagal":L=G?"Mencapai Target":"Sedang Berjalan";const z=l.nilai_target??0,Z=d.progress??0,ha=d.gap??0,ka=Array.isArray(l.karyawan)?l.karyawan.length:0,ya=["sertifikasi kompetensi internal","pelatihan kompetensi eksternal"].includes(l.condition),sa=ya&&ka>0?ka*z:z;let da,oa,ra;if(l.tipe_target==="persen")da=z+" %",oa=P(Z)+" %",ra=P(ha)+" %";else if(l.tipe_target==="rupiah"){const a=new Intl.NumberFormat("id-ID",{style:"currency",currency:"IDR",minimumFractionDigits:0,maximumFractionDigits:0});da=a.format(sa),oa=a.format(Z),ra=a.format(Math.abs(ha))}else da=ya?`${sa} <small class="text-muted">(${ka} peserta × ${z})</small>`:z,oa=ya?`${Z} / ${sa}`:Z,ra=ha;let wa="";const $a=Array.isArray(d.instruktur_details)?d.instruktur_details:[],Na=d.hari_libur_nasional||{jumlah:0,daftar:{}};if($a.length>0){let f=function(p,o){const y=p.kalender||{};if(!y[o])return'<div class="text-muted text-center py-4">Tidak ada data untuk bulan ini.</div>';const[_,j]=o.split("-"),M=a[parseInt(j,10)-1],W=new Date(_,j,0).getDate(),aa=new Date(_,parseInt(j,10)-1,1).getDay();let O="";u.forEach(D=>{O+=`<div class="kal-header">${D}</div>`});for(let D=0;D<aa;D++)O+='<div class="kal-cell kal-empty"></div>';for(let D=1;D<=W;D++){const J=y[o][D]||{status:"empty",keterangan:""},we="kal-"+J.status;O+=`
                                <div class="kal-cell ${we}" title="${J.keterangan||""}" data-bs-toggle="tooltip">
                                    <div class="kal-day">${D}</div>
                                </div>
                            `}const H=Object.values(y[o]).reduce((D,J)=>(D[J.status]=(D[J.status]||0)+1,D),{});return`
                            <div class="kalender-bulan-slide" data-bulan="${o}">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fw-semibold mb-0">${M} ${_}</h6>
                                    <div class="d-flex gap-2 small">
                                        <span class="badge bg-success">Aktif: ${H.working||0}</span>
                                        <span class="badge bg-warning text-dark">Cuti: ${H.cuti||0}</span>
                                        <span class="badge bg-danger">Libur: ${H.libur||0}</span>
                                    </div>
                                </div>
                                <div class="kalender-grid">${O}</div>
                            </div>
                        `},k=function(p){const o=Object.entries(p.daftar_cuti||{});return o.length===0?"":`
                            <div class="mt-3">
                                <h6 class="fw-semibold small text-muted mb-2"><i class="fa-solid fa-suitcase-rolling me-1"></i>Detail Cuti (${o.length} hari)</h6>
                                <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="table-warning"><tr><th>Tanggal</th><th>Tipe</th><th>Alasan</th></tr></thead>
                                        <tbody>
                                            ${o.map(([y,_])=>`
                                                <tr>
                                                    <td class="small">${new Date(y).toLocaleDateString("id-ID",{day:"2-digit",month:"short",year:"numeric"})}</td>
                                                    <td class="small">${_.tipe||"-"}</td>
                                                    <td class="small">${_.alasan||"-"}</td>
                                                </tr>
                                            `).join("")}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        `};var xe=f,je=k;const a=["Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"],u=["Min","Sen","Sel","Rab","Kam","Jum","Sab"];window.__instrukturData=$a,window.__currentMonthIndex={},window.showKalenderBulan=function(p,o){const y=window.__instrukturData.find(J=>J.id==p);if(!y)return;const _=y.kalender||{},j=Object.keys(_).sort(),M=j.length;if(M===0)return;o<0&&(o=M-1),o>=M&&(o=0),window.__currentMonthIndex[p]=o;const W=j[o],aa=document.getElementById(`kalenderSlide_${p}`);aa&&(aa.innerHTML=f(y,W));const O=document.getElementById(`bulanCounter_${p}`);O&&(O.textContent=`${o+1} dari ${M} bulan`);const H=document.getElementById(`btnPrevBulan_${p}`),D=document.getElementById(`btnNextBulan_${p}`);H&&(H.disabled=M<=1),D&&(D.disabled=M<=1)};let v="";const I=Object.entries(Na.daftar||{});I.length>0&&(v=`
                            <div class="alert alert-light border mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0 fw-semibold"><i class="fa-solid fa-flag me-2 text-danger"></i>Hari Libur Nasional</h6>
                                    <span class="badge bg-danger">${Na.jumlah} Hari</span>
                                </div>
                                <div class="row g-2" style="max-height: 150px; overflow-y: auto;">
                                    ${I.map(([p,o])=>`
                                        <div class="col-md-6">
                                            <div class="small p-2 bg-danger bg-opacity-10 rounded">
                                                <strong>${new Date(p).toLocaleDateString("id-ID",{day:"2-digit",month:"short",year:"numeric"})}</strong>
                                                <div class="text-muted">${o}</div>
                                            </div>
                                        </div>
                                    `).join("")}
                                </div>
                            </div>
                        `);let b="";$a.forEach((p,o)=>{const y=p.persentase>=100?"success":p.persentase>=75?"warning":"danger",_=p.kalender||{},j=Object.keys(_).sort(),M=j.length;window.__currentMonthIndex[p.id]=0;const W=k(p);b+=`
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingInstruktur${o}">
                                    <button class="accordion-button ${o>0?"collapsed":""}" type="button" data-bs-toggle="collapse" data-bs-target="#collapseInstruktur${o}" aria-expanded="${o===0?"true":"false"}">
                                        <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                            <div>
                                                <strong>${p.nama}</strong>
                                                <div class="small text-muted">${p.kode_karyawan} • ${p.jabatan}</div>
                                            </div>
                                            <div class="text-end">
                                                <div class="fw-bold text-${y}">${P(p.persentase)}%</div>
                                                <div class="small text-muted">${p.jam_aktif} / ${p.target_jam} jam</div>
                                            </div>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapseInstruktur${o}" class="accordion-collapse collapse ${o===0?"show":""}" data-bs-parent="#accordionInstruktur">
                                    <div class="accordion-body">
                                        <div class="row g-2 mb-3">
                                            <div class="col-6 col-md-3">
                                                <div class="p-2 border rounded text-center bg-success-subtle">
                                                    <div class="small text-muted">Hari Aktif</div>
                                                    <div class="fw-bold fs-5">${p.total_hari_kerja}</div>
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="p-2 border rounded text-center bg-warning-subtle">
                                                    <div class="small text-muted">Hari Cuti</div>
                                                    <div class="fw-bold fs-5">${p.total_hari_cuti}</div>
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="p-2 border rounded text-center bg-danger-subtle">
                                                    <div class="small text-muted">Hari Libur</div>
                                                    <div class="fw-bold fs-5">${p.total_hari_libur}</div>
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="p-2 border rounded text-center bg-primary-subtle">
                                                    <div class="small text-muted">Jam Aktif</div>
                                                    <div class="fw-bold fs-5">${p.jam_aktif}</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex flex-wrap gap-2 mb-3 small">
                                            <span><span class="kal-legend-box kal-working"></span> Hari Aktif</span>
                                            <span><span class="kal-legend-box kal-cuti"></span> Cuti</span>
                                            <span><span class="kal-legend-box kal-libur"></span> Libur Nasional</span>
                                            <span><span class="kal-legend-box kal-weekend"></span> Weekend</span>
                                            <span><span class="kal-legend-box kal-empty"></span> Tidak Ada Aktivitas</span>
                                        </div>

                                        ${W}

                                        <div class="mt-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="fw-semibold mb-0"><i class="fa-solid fa-calendar me-2"></i>Kalender Bulanan</h6>
                                                <span class="badge bg-info" id="bulanCounter_${p.id}">1 dari ${M} bulan</span>
                                            </div>

                                            <div class="d-flex justify-content-between align-items-center gap-2 mb-3 p-2 bg-light rounded-3">
                                                <button type="button" class="btn btn-sm btn-outline-primary" id="btnPrevBulan_${p.id}" onclick="showKalenderBulan(${p.id}, window.__currentMonthIndex[${p.id}] - 1)">
                                                    <i class="fa-solid fa-chevron-left me-1"></i> Bulan Sebelumnya
                                                </button>

                                                <div class="flex-grow-1 text-center">
                                                    <small class="text-muted">Gunakan tombol atau swipe untuk navigasi</small>
                                                </div>

                                                <button type="button" class="btn btn-sm btn-outline-primary" id="btnNextBulan_${p.id}" onclick="showKalenderBulan(${p.id}, window.__currentMonthIndex[${p.id}] + 1)">
                                                    Bulan Selanjutnya <i class="fa-solid fa-chevron-right ms-1"></i>
                                                </button>
                                            </div>

                                            <div id="kalenderSlide_${p.id}">
                                                ${M>0?f(p,j[0]):'<div class="text-muted text-center py-4">Tidak ada data kalender.</div>'}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `}),wa=`
                        <div class="mt-3">
                            <div class="card shadow-sm border-0 rounded-4">
                                <div class="card-body">
                                    <h6 class="fw-semibold mb-3"><i class="fa-solid fa-calendar-days me-2"></i>Kalender Kinerja Instruktur</h6>
                                    ${v}
                                    <div class="accordion" id="accordionInstruktur">
                                        ${b}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <style>
                            .kalender-grid {
                                display: grid;
                                grid-template-columns: repeat(7, 1fr);
                                gap: 3px;
                                background: #f8f9fa;
                                padding: 6px;
                                border-radius: 8px;
                            }
                            .kal-header {
                                text-align: center;
                                font-size: 11px;
                                font-weight: 600;
                                color: #6c757d;
                                padding: 4px 0;
                            }
                            .kal-cell {
                                aspect-ratio: 1;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                border-radius: 4px;
                                font-size: 12px;
                                cursor: pointer;
                                transition: transform 0.15s;
                                position: relative;
                            }
                            .kal-cell:hover { transform: scale(1.1); z-index: 2; }
                            .kal-day { font-weight: 500; }
                            .kal-empty { background: transparent; }
                            .kal-working { background: #d1e7dd; color: #0f5132; }
                            .kal-cuti { background: #fff3cd; color: #664d03; }
                            .kal-libur { background: #f8d7da; color: #842029; }
                            .kal-weekend { background: #e2e3e5; color: #41464b; }
                            .kal-legend-box {
                                display: inline-block;
                                width: 14px;
                                height: 14px;
                                border-radius: 3px;
                                vertical-align: middle;
                                margin-right: 4px;
                            }
                            .kal-legend-box.kal-working { background: #d1e7dd; }
                            .kal-legend-box.kal-cuti { background: #fff3cd; }
                            .kal-legend-box.kal-libur { background: #f8d7da; }
                            .kal-legend-box.kal-weekend { background: #e2e3e5; }
                            .kal-legend-box.kal-empty { background: #f8f9fa; border: 1px dashed #adb5bd; }

                            .kalender-bulan-slide {
                                animation: slideInBulan 0.3s ease-out;
                            }
                            @keyframes slideInBulan {
                                from { opacity: 0; transform: translateX(20px); }
                                to { opacity: 1; transform: translateX(0); }
                            }
                        </style>
                    `}let _a=L==="Mencapai Target"?"success":L==="Target Gagal"?"danger":L==="Sedang Berjalan"?"warning":"secondary",ee=L==="Mencapai Target"?"success":"danger";const Fa=d.pie_chart||{above:0,below:0},te={labels:["Above","Below"],datasets:[{label:"Jumlah",data:[Fa.above??0,Fa.below??0],backgroundColor:["#B66DFF","#FE7C96"],hoverOffset:4}]},T=(a=0)=>"Rp "+Number(a||0).toLocaleString("id-ID"),se=a=>{if(!a)return"-";const u=new Date(a);return isNaN(u)?"-":u.toLocaleDateString("id-ID",{day:"2-digit",month:"short"})},Aa=h||{},ne=w||{},Ba=Array.isArray(l.karyawan)?l.karyawan.filter(a=>a):l.karyawan?[l.karyawan]:[],xa=Object.keys(Aa||{}).filter(a=>a).sort(),ca=xa.length>0?xa[xa.length-1]:null,ie=ca?Aa[ca]??0:0,le=ca?new Date(`${ca}-01`).toLocaleDateString("id-ID",{month:"long",year:"numeric"}):"-",de=Object.entries(ne||{}).flatMap(([a,u])=>Object.entries(u||{}).map(([f,k])=>[f,k])).filter(([a,u])=>typeof u=="number"&&u>0).sort((a,u)=>(u[1]||0)-(a[1]||0)).slice(0,3);let oe=1;const re=Ba.map(a=>a?`<div class="d-flex align-items-center py-2 participant-item"><div class="avatar me-3">${oe++}</div><div class="flex-grow-1"><div class="fw-semibold text-dark small">${a.nama_lengkap||"-"}</div><div class="text-muted small">${a.jabatan||"-"}</div></div></div>`:"").join("")||'<div class="text-muted small">Tidak ada karyawan</div>',ce=["pemasukan kotor","meningkatkan revenue perusahaan","target penjualan tahunan","target penjualan project tahunan","pendapatan penjualan project"],ue=["persentase gap kompetensi tim terhadap standar skill"],pe=["target penjualan tahunan","pemasukan kotor"],me=["peningkatan kontribusi pelatihan"],ge=["pemasukan bersih"],be=["performa kpi departemen"],fe=["Kepuasan Pelanggan","kepuasan pelanggan"],ve=["laporan analisis keuangan"],he=["inovation adaption rate"];let Y="";const F=l.condition||"";if(pe.includes(F)){const a=d.sales_performance||{};if(a.data){if(a.type==="individual"){const u=a.data,f=u.status==="achieved"?"badge-success":"badge-warning",k=u.status==="achieved"?"#28a745":"#ffc107",v=Math.min(u.percentage??0,100);Y=`<div class="card shadow-sm mb-4 mt-2"><div class="card-body"><div class="row"><div class="col-md-6"><p class="mb-2"><strong>Sales:</strong> ${u.nama||"-"}</p><p class="mb-2"><strong>Revenue:</strong> ${T(u.revenue)}</p><p class="mb-3"><strong>Target:</strong> ${T(u.presentase_kemampuan)}</p></div><div class="col-md-6"><div class="mb-2"><div class="d-flex justify-content-between"><span>Progress</span><span>${P(u.percentage)}%</span></div><div class="progress" style="height: 10px;"><div class="progress-bar" role="progressbar" style="width: ${v}%; background-color: ${k};"></div></div></div><div class="text-end"><span class="badge ${f} p-2">${(u.status||"").toUpperCase()}</span></div></div></div></div></div>`}else if(a.type==="all"&&Array.isArray(a.data)){let u="";a.data.forEach((v,I)=>{if(!v)return;const b=v.status==="achieved"?"badge-success":"badge-warning",p=v.status==="achieved"?"text-success":"text-warning",o=Number(v.presentase_kemampuan||0).toLocaleString("id-ID",{useGrouping:!1});u+=`<tr id="row-${v.kode_karyawan||I}"><td class="text-center">${I+1}</td><td><strong>${v.nama||"-"}</strong></td><td class="text-end">${T(v.revenue)}</td><td class="text-center"><div class="input-group input-group-sm" style="max-width: 150px; float: right;"><input type="text" class="form-control text-end target-input ${v.id_detailPerson?"":"is-invalid"}" value="${o}" data-id-detail="${v.id_detailPerson||""}" data-kode-karyawan="${v.kode_karyawan||""}" placeholder="Target" ${v.id_detailPerson?"":"disabled"}></div><div class="loading-spinner" style="display: none; float: right; margin-right: 10px;"><span class="spinner-border spinner-border-sm text-primary" role="status"></span></div><div class="update-feedback" style="display: none; float: right; margin-right: 10px; margin-top: 5px;"><i class="menu-icon fa-solid fa-check-circle text-success"></i></div><div class="clearfix"></div></td><td class="text-center ${p}"><strong>${P(v.percentage)}%</strong></td><td class="text-center"><span class="badge ${b}">${(v.status||"").toUpperCase()}</span></td></tr>`});let f="";const k=d.triwulan_data||{};Object.entries(k).forEach(([v,I])=>{const b=(v||"").toString().replace(/_/g," ");f+=`<div class="col-md-6"><div class="card h-100 shadow-sm border-0"><div class="card-body"><h5 class="card-title">${b}</h5><p class="card-text">Rp ${Number(I||0).toLocaleString("id-ID")}</p></div></div></div>`}),Y=`<div class="row"><div class="col"><div class="card shadow-sm mt-3"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead class="thead-light"><tr><th class="text-center" width="5%">No</th><th>Sales</th><th class="text-end" width="20%">Revenue</th><th class="text-end" width="20%">Target (Editable)</th><th class="text-center" width="15%">Persentase</th><th class="text-center" width="15%">Status</th></tr></thead><tbody>${u||'<tr><td colspan="6" class="text-center">Tidak ada data</td></tr>'}</tbody></table></div></div></div></div><div class="col"><div class="card shadow-sm border-0 mt-3"><div class="card-body"><div class="row g-3">${f||'<p class="text-muted">Tidak ada data triwulan</p>'}</div></div><div class="mb-2 text-center"><hr><p>Data Triwulan diambil dari tahun ${l.detail_jangka||"-"}</p></div></div></div></div>`}}}else if(ge.includes(F)){const a=["Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"];Y=`<div class="mt-4"><div class="row g-4">${(((La=d.previous_quarter)==null?void 0:La.data)||[]).map((f,k)=>`<div class="col-12 col-md-6 col-lg-4"><div class="card border-0 shadow-sm h-100 quarter-card"><div class="card-body d-flex flex-column p-4"><div class="d-flex justify-content-between align-items-start mb-3"><div><h6 class="fw-semibold text-muted mb-1">Periode</h6><h5 class="fw-bold mb-0">${a[(f.month||1)-1]??"-"}</h5></div><span class="badge rounded-pill bg-${f.color||"secondary"} bg-opacity-10 text-${f.color||"secondary"} px-3 py-2">Laporan</span></div><div class="mb-3"><h3 class="fw-bold text-dark mb-0">Rp ${f.nilai?Number(f.nilai).toLocaleString("id-ID"):"-"}</h3><small class="text-muted">Total Pemasukan</small></div><div class="flex-grow-1"><p class="text-muted small mb-2 description-text" id="desc-${k}">${f.description??"-"}</p>${f.description&&f.description.length>100?`<button class="btn btn-sm btn-link p-0 text-primary btn-toggle-desc" data-target="desc-${k}">Lihat Selengkapnya</button>`:""}</div><div class="d-flex justify-content-end align-items-center mt-4"><a href="${window.KPI_CONFIG.storageBase}/${f.file_paths?f.file_paths:""}" class="btn btn-sm btn-dark d-flex align-items-center gap-2" download="${f.file_paths?"true":"false"}"><i class="menu-icon fa-solid fa-download"></i> Download</a></div></div></div></div>`).join("")}</div></div><style>.quarter-card { border-radius: 16px; transition: all 0.25s ease; background: #ffffff; }.quarter-card:hover { transform: translateY(-6px) scale(1.01); box-shadow: 0 15px 35px rgba(0,0,0,0.08); }.quarter-card h3 { letter-spacing: 0.5px; }.quarter-card .badge { font-size: 12px; font-weight: 500; }.description-text { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }.description-text.expanded { -webkit-line-clamp: unset; overflow: visible; }.quarter-card .btn { border-radius: 8px; font-size: 13px; }</style>`}else if(fe.includes(F)){const a=d||{};Y=`<div class="row g-4 mt-1"><div class="col-lg-4"><div class="card shadow-sm border-0 rounded-4 h-100"><div class="card-body"><h6 class="fw-semibold text-secondary mb-3">RINGKASAN</h6><div class="d-flex justify-content-between mb-2"><span class="text-muted">Total Feedback</span><span class="fw-bold">${a.total_feedback??0}</span></div><div class="d-flex justify-content-between mb-2"><span class="text-muted">Total Sesi</span><span class="fw-bold">${a.total_sessions??0}</span></div><div class="d-flex justify-content-between"><span class="text-muted">Prediksi</span><span class="fw-bold text-primary">${a.prediction??0}</span></div></div></div></div><div class="col-lg-4"><div class="card shadow-sm border-0 rounded-4 h-100"><div class="card-body"><h6 class="fw-semibold text-secondary mb-3">PERFORMA</h6><div class="mb-3"><small class="text-muted d-block">Top Performer</small><span class="fw-bold text-success">${((Ra=a.top_performer)==null?void 0:Ra.label)??"-"} (${((Ka=a.top_performer)==null?void 0:Ka.value)??0})</span></div><div><small class="text-muted d-block">Lowest Performer</small><span class="fw-bold text-danger">${((Ga=a.lowest_performer)==null?void 0:Ga.label)??"-"} (${((Ha=a.lowest_performer)==null?void 0:Ha.value)??0})</span></div></div></div></div><div class="col-lg-4"><div class="card shadow-sm border-0 rounded-4 h-100"><div class="card-body"><h6 class="fw-semibold text-secondary mb-3">STATUS</h6><div class="mb-2"><span class="badge bg-${a.trend==="up"?"success":a.trend==="down"?"danger":"secondary"}">Trend: ${a.trend||"-"} (${P(a.trend_value)})</span></div><div class="mb-2"><span class="badge bg-${a.consistency==="stable"?"success":"warning"}">Konsistensi: ${a.consistency||"-"}</span></div><div><span class="badge bg-${a.target_status==="on_track"?"success":a.target_status==="at_risk"?"warning":"danger"}">Target: ${a.target_status||"-"}</span></div></div></div></div><div class="col-12"><div class="card shadow-sm border-0 rounded-4"><div class="card-body"><h6 class="fw-semibold text-secondary mb-3">KATEGORI PENILAIAN</h6><div class="row text-center">${Object.entries(a.category_scores||{}).map(([u,f])=>`<div class="col"><div class="p-2 border rounded-3"><small class="text-muted d-block">${u}</small><h5 class="fw-bold mb-0">${f??0}</h5></div></div>`).join("")}</div></div></div></div><div class="col-12"><div class="alert alert-info rounded-4 shadow-sm mb-0"><i class="fa-solid fa-lightbulb me-2"></i>${a.insight??"-"}</div></div></div>`}else if(me.includes(F)){const a=d.class_breakdown||{},u=a.Inhouse||{};Y=`<div class="card mt-4 border-0 rounded-4" style="background:#f8fafc;"><div class="card-body p-4"><div class="d-flex justify-content-between align-items-end mb-4"><div><div class="text-muted small">Total Kelas</div><div class="fs-2 fw-semibold text-dark">${a.total??0}</div></div></div><div class="row g-3 mb-4"><div class="col-md-6"><div class="p-3 rounded-3 bg-white border h-100"><div class="text-muted small mb-2">Kelas Inixindo</div><div class="fs-4 fw-semibold text-dark">${a.kelas_od??0}</div></div></div><div class="col-md-6"><div class="p-3 rounded-3 bg-white border h-100"><div class="text-muted small mb-2">Kelas Orang Luar</div><div class="fs-4 fw-semibold text-dark">${a.kelas_ol??0}</div></div></div><div class="col-md-6"><div class="p-3 rounded-3 bg-white border h-100"><div class="text-muted small mb-2">Kelas Offline</div><div class="fs-4 fw-semibold text-dark">${a.kelas_offline??0}</div></div></div><div class="col-md-6"><div class="p-3 rounded-3 bg-white border h-100"><div class="text-muted small mb-2">Kelas Online</div><div class="fs-4 fw-semibold text-dark">${a.kelas_online??0}</div></div></div></div><div class="p-3 rounded-3 bg-white border"><div class="fw-semibold mb-3">Kelas Inhouse</div><div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted small">Bandung</span><span class="fw-semibold text-dark">${u.kelas_inhouse??0}</span></div><div class="d-flex justify-content-between py-2"><span class="text-muted small">Luar Bandung</span><span class="fw-semibold text-dark">${u.kelas_inhouse_luar??0}</span></div></div></div></div>`}let ja="";const ke=["pemasukan kotor"],ua=d.kontribusi_sales;if(ua&&ke.includes(String(F).toLowerCase().trim())){const a={on_track:["bg-primary","On Track"],selesai:["bg-success","Selesai"],gagal:["bg-danger","Gagal"]},u=b=>{const[p,o]=a[b]||["bg-secondary","-"];return`<span class="badge ${p}">${o}</span>`},f=b=>b<0?"text-danger":"text-success",k=(ua.rows||[]).map((b,p)=>`
                        <tr>
                            <td class="text-center">${p+1}</td>
                            <td class="fw-semibold">${b.nama||"-"}</td>
                            <td class="text-end">${T(b.triwulan[1])}</td>
                            <td class="text-end">${T(b.triwulan[2])}</td>
                            <td class="text-end">${T(b.triwulan[3])}</td>
                            <td class="text-end">${T(b.triwulan[4])}</td>
                            <td class="text-end fw-bold text-primary">${T(b.total)}</td>
                            <td class="text-end">${T(b.target)}</td>
                            <td class="text-end fw-semibold ${f(b.selisih)}">${T(b.selisih)}</td>
                            <td class="text-center">${P(b.persentase)}%</td>
                            <td class="text-center">${u(b.status)}</td>
                        </tr>`).join(""),v=ua.total,I=v?`
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="2" class="text-center">TOTAL</td>
                                <td class="text-end">${T(v.triwulan[1])}</td>
                                <td class="text-end">${T(v.triwulan[2])}</td>
                                <td class="text-end">${T(v.triwulan[3])}</td>
                                <td class="text-end">${T(v.triwulan[4])}</td>
                                <td class="text-end text-primary">${T(v.total)}</td>
                                <td class="text-end">${T(v.target)}</td>
                                <td class="text-end ${f(v.selisih)}">${T(v.selisih)}</td>
                                <td class="text-center">${P(v.persentase)}%</td>
                                <td class="text-center">${u(v.status)}</td>
                            </tr>
                        </tfoot>`:"";ja=`
                        <div class="card shadow-sm border-0 rounded-4">
                            <div class="card-body">
                                <h6 class="fw-semibold mb-3"><i class="fa-solid fa-users me-2"></i>Kontribusi Sales ${ua.tahun??""}</h6>
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered align-middle mb-0">
                                        <thead class="table-light text-center">
                                            <tr>
                                                <th width="4%">No</th><th>Sales</th>
                                                <th>Triwulan I</th><th>Triwulan II</th><th>Triwulan III</th><th>Triwulan IV</th>
                                                <th>Total</th><th>Target</th><th>Selisih</th><th>Persentase</th><th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>${k||'<tr><td colspan="11" class="text-center text-muted">Belum ada target penjualan sales untuk tahun ini</td></tr>'}</tbody>
                                        ${I}
                                    </table>
                                </div>
                            </div>
                        </div>`}let pa="";if(fa.includes(F)){const a=((Ja=d.dataManual)==null?void 0:Ja.manual_document)||"",u=a?a.split("/").pop():"",f=u?u.split(".").pop().toLowerCase():"",k=["jpg","jpeg","png"],v=["pdf"];let I="";const b=a?`/storage/${a}`:"";k.includes(f)?I=`<div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3"><div class="mb-3" style="max-width: 100%; max-height: 300px;"><img src="${b}" alt="${u}" class="img-fluid rounded shadow-sm" style="max-width: 100%; max-height: 300px; object-fit: contain;"></div><div class="mt-2 text-center"><a href="${b}" download="${u}" class="btn btn-primary btn-sm"><i class="fa-solid fa-download me-1"></i>Download Gambar</a></div><div class="mt-2 small text-muted"><i class="fa-solid fa-file-image me-1"></i>${u}</div></div>`:v.includes(f)?I=`<div class="w-100 h-100 d-flex flex-column"><div class="flex-grow-1 mb-3" style="min-height: 250px;"><iframe src="${b}" class="w-100 h-100" style="border: 1px solid #dee2e6; border-radius: 8px;"></iframe></div><div class="text-center"><a href="${b}" download="${u}" class="btn btn-primary btn-sm"><i class="fa-solid fa-download me-1"></i>Download PDF</a></div><div class="mt-2 small text-muted text-center"><i class="fa-solid fa-file-pdf me-1"></i>${u}</div></div>`:I='<div class="text-center py-5"><div class="mb-3"><i class="fa-solid fa-file text-secondary" style="font-size: 4rem;"></i></div><p class="text-muted mb-3">File tidak dapat ditampilkan</p><p class="text-muted small">Hanya gambar dan PDF yang dapat ditampilkan</p></div>',pa=`<h6 class="fw-semibold mb-3 text-secondary"><i class="fa-solid fa-file me-2"></i>Dokumen Manual</h6><div class="manual-document-container flex-grow-1 d-flex flex-column align-items-center justify-content-center p-3" style="background-color: #f8f9fa; border-radius: 8px;">${I}</div><div class="mt-3 small text-muted text-center"><i class="fa-solid fa-info-circle me-1"></i>Klik tombol download untuk menyimpan file</div>`}else if(ce.includes(F)){const a=Ba[0]||{};pa=`<div class="p-2"><div class="mb-4"><small class="text-muted">Ringkasan performa</small></div><div class="mb-4 p-3 rounded-3 border bg-white shadow-sm"><div class="d-flex justify-content-between align-items-center"><div><div class="text-muted small">Bulan Terakhir</div><div class="fw-semibold">${le}</div></div><div class="text-end"><div class="fw-bold fs-5 text-dark">${T(ie)}</div></div></div></div><div class="mb-3"><div class="text-muted small mb-2 fw-semibold">Top Hari Tertinggi</div>${de.map(([u,f],k)=>`<div class="d-flex justify-content-between align-items-center mb-2 p-2 rounded ${k===0?"bg-success-subtle":"bg-light"}"><div class="d-flex align-items-center gap-2"><span class="badge ${k===0?"bg-success":"bg-secondary"}">#${k+1}</span><span class="${k===0?"fw-semibold text-dark":"text-muted"}">${se(u)}</span></div><span class="fw-semibold ${k===0?"text-success":"text-dark"}">${T(f)}</span></div>`).join("")}</div><hr class="my-3"><div class="d-flex align-items-center justify-content-between"><div class="d-flex align-items-center gap-2"><i class="bi bi-person-circle fs-4 text-secondary"></i><div><div class="fw-semibold text-dark">${(a==null?void 0:a.nama_lengkap)??"-"}</div><small class="text-muted">${(a==null?void 0:a.jabatan)??"-"}</small></div></div><span class="badge bg-light text-dark border">Karyawan</span></div></div>`}else pa=`<h6 class="fw-semibold mb-3 text-secondary"><i class="fa-solid fa-chart-pie me-2"></i>Chart ${F}</h6><div class="chart-container flex-grow-1"><canvas id="MyChartDoughtnut"></canvas></div>`;if(!fa.includes(F))if(be.includes(F)){const a=d||{},u=a.trend??"stable",f=a.trend_value??0,k=a.consistency??"stable",v=a.target_status??"behind",I=u==="up"?"success":u==="down"?"danger":"secondary",b=k==="stable"?"success":"warning",p=v==="on_track"?"success":v==="at_risk"?"warning":"danger",o=Object.entries(a.division_breakdown||{}).map(([_,j])=>`<div class="col mb-3"><div class="p-2 border rounded-3"><small class="text-muted d-block">${_}</small><h5 class="fw-bold mb-0">${P(j)}%</h5></div></div>`).join(""),y=a.risk_divisions&&a.risk_divisions.length>0?`<div class="col-6"><div class="card shadow-sm border-0 rounded-4"><div class="card-body"><h6 class="fw-semibold text-danger mb-3">DIVISI BERISIKO</h6><div class="row">${a.risk_divisions.map(_=>`<div class="col mb-2"><div class="p-2 border rounded-3 text-center"><small class="text-muted d-block">${_.name||"-"}</small><span class="fw-bold text-danger">${P(_.value)}%</span></div></div>`).join("")}</div></div></div></div>`:"";`${a.total_kpi??0}${a.total_division??0}${P(a.progress)}${((Va=a.top_division)==null?void 0:Va.name)??"-"}${P((qa=a.top_division)==null?void 0:qa.value)}${((Ua=a.lowest_division)==null?void 0:Ua.name)??"-"}${P((za=a.lowest_division)==null?void 0:za.value)}${I}${u}${P(f)}${b}${k}${p}${v}${o}${y}${a.insight??"-"}`}else if(he.includes(F)){const u=(d||{}).dataInovasi||[];`${u.length?u.map(f=>`
                                                <tr>
                                                    <td class="fw-semibold">
                                                        ${f.judul??"-"}
                                                    </td>
                                                    <td>
                                                        ${f.deskripsi??"-"}
                                                    </td>
                                                </tr>
                                            `).join(""):`
                                                <tr>
                                                    <td colspan="2" class="text-center text-muted">
                                                        Tidak ada data inovasi
                                                    </td>
                                                </tr>
                                            `}`}else if(ve.includes(F)){const a=["","Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"];`${(d.analisa_data||[]).map(f=>{const k=f.file_paths?window.KPI_CONFIG.storageBase+"/"+f.file_paths:"#";return`<div class="col-md-4 mt-5"><div class="card border-0 shadow-sm h-100"><div class="mb-2 p-3"><h5 class="fw-bold text-primary mb-1">${a[f.month]||"-"}</h5><small class="text-muted">Laporan Analisis Bulanan</small></div><div class="card-body d-flex flex-column" style="overflow-y: scroll; max-height: 280px;"><div class="mb-3"><p class="mb-0" style="text-align: justify;">${f.description||""}</p></div></div><div class="mt-auto p-3"><a href="${k}" class="btn btn-sm btn-outline-primary w-100" ${f.file_paths?"download":""}><i class="menu-icon fa-solid fa-file-alt"></i></a></div></div></div>`}).join("")}`}else ue.includes(F)&&S(l,window.KPI_CONFIG.isKoordinatorItsm,window.KPI_CONFIG.csrfToken);let Pa;(l.tipe_target==="rupiah"||l.tipe_target==="angka")&&sa>0?Pa=Math.min(100,Number((Z/sa*100).toFixed(1))):Pa=Math.min(100,Z||0),t.append(`
                    <div class="modal-header border-0 pb-2">
                        <div>
                            <h5 class="modal-title fw-bold mb-1">${l.judul||"Detail Target"}</h5>
                            <small class="text-muted">${l.jangka_target||"-"}</small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body pt-2">
                        <div class="p-4 rounded-4 mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-${_a} px-3 py-2">${L}</span>
                                <span class="text-muted small">
                                    Deadline: <strong>${l.tenggat_waktu||"-"}</strong>
                                </span>
                            </div>

                            <div class="row text-center">
                                <div class="col">
                                    <div class="text-muted small">Target</div>
                                    <div class="fw-bold fs-4">${da}</div>
                                </div>
                                <div class="col">
                                    <div class="text-muted small">Progress</div>
                                    <div class="fw-bold text-${_a}" style="font-size:40px">
                                        ${oa}
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="text-muted small">Gap</div>
                                    <div class="fw-bold text-${ee} fs-4">
                                        ${ra}
                                    </div>
                                </div>
                            </div>

                            <div class="progress mt-3" style="height:10px;">
                                <div class="progress-bar bg-${_a}"
                                    style="width:${Pa}%"></div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="p-3 border rounded-4 h-100">
                                    <h6 class="fw-semibold mb-3">Informasi KPI</h6>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Divisi</span>
                                        <span class="fw-semibold">${l.divisi_kpi||"-"}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Jabatan</span>
                                        <span class="fw-semibold">${l.jabatan_kpi||"-"}</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Pembuat</span>
                                        <span class="fw-semibold">${l.pembuat||"-"}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 border rounded-4 h-100">
                                    <h6 class="fw-semibold mb-3">Karyawan</h6>
                                    <div style="max-height:140px; overflow:auto;">
                                        ${re}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 p-3 border rounded-4">
                            ${pa}
                        </div>

                        ${Y?`
                            <div class="mt-3">
                                ${Y}
                            </div>
                        `:""}

                        ${ja?`
                            <div class="mt-3">
                                ${ja}
                            </div>
                        `:""}

                        ${wa||""}
                    </div>

                    <div class="modal-footer border-0">
                        <button class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Tutup
                        </button>
                    </div>
                `);const ye=["Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"],Ta=a=>{if(!a)return"-";const u=a.split("-");return u.length>=2&&ye[parseInt(u[1],10)-1]||a};setTimeout(function(){const a=document.getElementById("MyChartDoughtnut");if(a&&typeof Chart<"u"){const b=Chart.getChart(a);b&&b.destroy(),new Chart(a,{type:"doughnut",data:te,options:{responsive:!0,maintainAspectRatio:!1,plugins:{legend:{position:"bottom",labels:{boxWidth:12,padding:15}}}}})}document.querySelectorAll(".target-input").forEach(b=>{if(!b)return;let p=null;b.addEventListener("input",function(){const o=this,y=o.closest("tr");if(!y)return;const _=y.querySelector(".loading-spinner"),j=y.querySelector(".update-feedback");clearTimeout(p),j&&(j.style.display="none"),p=setTimeout(()=>{const M=o.dataset.idDetail,W=o.dataset.kodeKaryawan,aa=(o.value||"").toString().replace(/\./g,"");if(!M){o.classList.add("is-invalid");return}_&&(_.style.display="inline-block"),o.disabled=!0,$.ajax({url:window.KPI_CONFIG.updateTargetPerSales,method:"POST",data:{_token:window.KPI_CONFIG.csrfToken,id_detailPerson:M,kode_karyawan:W,presentase_kemampuan:aa},success:function(O){if(_&&(_.style.display="none"),o.disabled=!1,j&&(j.style.display="inline-block",setTimeout(()=>{j.style.display="none"},2e3)),o.classList.remove("is-invalid"),o.classList.add("is-valid"),setTimeout(()=>o.classList.remove("is-valid"),2e3),O!=null&&O.data){const H=y.querySelector("td:nth-child(5)"),D=y.querySelector("td:nth-child(6)");if(H&&O.data.percentage&&(H.innerHTML=`<strong class="${O.data.status==="achieved"?"text-success":"text-warning"}">${P(O.data.percentage)}%</strong>`),D&&O.data.status){const J=O.data.status==="achieved"?"badge-success":"badge-warning";D.innerHTML=`<span class="badge ${J}">${O.data.status.toUpperCase()}</span>`}}},error:function(){_&&(_.style.display="none"),o.disabled=!1,o.classList.add("is-invalid")}})},1e3)}),b.addEventListener("blur",function(){const o=(this.value||"").toString().replace(/\./g,"");o&&(this.value=Number(o).toLocaleString("id-ID"))}),b.addEventListener("focus",function(){const o=(this.value||"").toString().replace(/\./g,"");o&&(this.value=o)})}),$(document).off("submit","#formGapKompetensi").on("submit","#formGapKompetensi",function(b){b.preventDefault(),$.ajax({url:window.KPI_CONFIG.updateGapKompetensi,method:"POST",data:$(this).serialize(),success:function(){typeof Swal<"u"&&Swal.fire({icon:"success",title:"Berhasil!",text:"Berhasil diupdate.",timer:2e3,showConfirmButton:!1}).then(()=>{$("#detailTargetModal").modal("hide"),typeof V=="function"&&V()})},error:function(p){var o;typeof Swal<"u"&&Swal.fire({icon:"error",title:"Gagal!",html:((o=p.responseJSON)==null?void 0:o.message)||"Terjadi kesalahan"})}})});let u=null;const f=document.getElementById("StatisticChart"),k=(b,p,o)=>{if(!f||typeof Chart>"u")return;u&&u.destroy();const y=(p||[]).map(j=>Number(j)||0),_=y.length>0?Math.max(...y):100;u=new Chart(f,{type:"line",data:{labels:b||[],datasets:[{label:o||"Data",data:y,borderColor:"#4e73df",backgroundColor:"rgba(78, 115, 223, 0.1)",tension:.4,fill:!0}]},options:{responsive:!0,maintainAspectRatio:!1,scales:{y:{beginAtZero:!0,suggestedMax:_+3,ticks:{count:6,precision:0,callback:j=>Math.round(j)}}}}})},v=Object.keys(h||{}).map(Ta),I=Object.values(h||{}).map(b=>Number(b)||0);k(v,I,"Rata-rata"),$("#filterType").off("change").on("change",function(){this.value==="month"?($("#filterMonth").removeClass("d-none").empty().append('<option value="">Pilih Bulan</option>'),Object.keys(w||{}).forEach(b=>{b&&$("#filterMonth").append(`<option value="${b}">${Ta(b)}</option>`)}),u&&u.destroy()):($("#filterMonth").addClass("d-none"),k(v,I,"Rata-rata"))}),$("#filterMonth").off("change").on("change",function(){const b=this.value;if(!b||!(w!=null&&w[b]))return;const p=Object.keys(w[b]||{}).map(y=>(y||"").substring(8)),o=Object.values(w[b]||{}).map(y=>Number(y)||0);k(p,o,`Tanggal ${Ta(b)}`)}),document.querySelectorAll(".btn-toggle-desc").forEach(b=>{b&&b.addEventListener("click",function(){const p=this.getAttribute("data-target"),o=p?document.getElementById(p):null;o&&(o.classList.contains("expanded")?(o.classList.remove("expanded"),this.innerText="Lihat Selengkapnya"):(o.classList.add("expanded"),this.innerText="Sembunyikan"))})})},150);const Ea=document.getElementById("detailTargetModal");Ea&&typeof bootstrap<"u"&&bootstrap.Modal&&bootstrap.Modal.getOrCreateInstance(Ea).show(),setTimeout(()=>{t.addClass("modal-content-loaded")},50)},error:function(n){var i;t.empty(),t.html(`
                    <div class="modal-header border-0 pb-2">
                        <h5 class="modal-title fw-bold mb-1 text-danger">Gagal Memuat</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center py-4">
                        <i class="fa-solid fa-circle-exclamation text-danger" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                        <h6 class="fw-semibold">Terjadi Kesalahan Jaringan</h6>
                        <p class="text-muted small">${((i=n.responseJSON)==null?void 0:i.message)||"Silakan coba lagi nanti."}</p>
                    </div>
                    <div class="modal-footer border-0">
                        <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                `),t.addClass("modal-content-loaded")}})});function S(r,t,e){const n=Array.isArray(r.karyawan)?r.karyawan:[],i=t?"":"disabled",l=n.map((d,h)=>{const w=d.skala_kemampuan??null,C=w===null?'<span class="badge bg-secondary">Belum dinilai</span>':`<span class="badge bg-info text-dark">${P(w*10)}%</span>`;return`
                <div class="row mb-2 align-items-center p-2 rounded">
                    <div class="col-md-6 d-flex justify-content-between align-items-center">
                        <span>${d.nama_lengkap??"-"}</span>${C}
                    </div>
                    <div class="col-md-6">
                        <input type="number" min="1" max="10" step="0.5" class="form-control"
                            name="data[${h}][kemampuan]" value="${w??""}"
                            placeholder="1 - 10" ${i}>
                    </div>
                    <input type="hidden" name="data[${h}][id_karyawan]" value="${d.id_karyawan}">
                    <input type="hidden" name="data[${h}][detailTargetKey]" value="${d.detailTargetKey}">
                </div>`}).join("");return`
            <div class="mt-4"><div class="card shadow-sm border-0 rounded-4"><div class="card-body">
                <h6 class="fw-semibold mb-1">Penilaian Kemampuan Tim</h6>
                <small class="text-muted d-block mb-3">Isi skala 1-10 (8 = 80%). Gap dihitung dari rata-rata seluruh anggota tim.</small>
                <form id="formGapKompetensi">
                    <input type="hidden" name="_token" value="${e}">
                    <div class="row mb-2 fw-semibold text-muted border-bottom pb-2">
                        <div class="col-md-6">Nama Karyawan</div><div class="col-md-6">Nilai (1-10)</div>
                    </div>
                    ${l||'<div class="text-muted small">Tidak ada anggota tim.</div>'}
                    ${t?'<div class="mt-3"><button type="submit" class="btn btn-primary">Simpan</button></div>':""}
                </form>
            </div></div></div>`}function A(){return`
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
            </div>
        `}$(document).on("click",".buttonHapusTarget",function(){let r=$(this).data("id");Swal.fire({title:"Yakin ingin menghapus?",text:"Data target ini akan dihapus secara permanen!",icon:"warning",showCancelButton:!0,customClass:{confirmButton:"btn btn-gradient-info me-3",cancelButton:"btn btn-gradient-danger"},confirmButtonText:"Ya, hapus!",cancelButtonText:"Batal"}).then(t=>{t.isConfirmed&&$.ajax({url:window.KPI_CONFIG.hapusTarget.replace("REPLACE_ID",r),type:"DELETE",data:{_token:window.KPI_CONFIG.csrfToken},success:function(e){Swal.fire({icon:"success",title:"Berhasil!",text:"Data target berhasil dihapus.",showConfirmButton:!1,timer:1500}),V(B,$("#searchTarget").val().trim())},error:function(e){var n;Swal.fire({icon:"error",title:"Gagal!",text:((n=e.responseJSON)==null?void 0:n.message)||"Terjadi kesalahan saat menghapus data."})}})})}),$(document).on("click",".buttonForm",function(){const r=$(this).data("route"),t=$(this).data("value")||"",e=$(this).data("id");if($("#manualValueId").val(e),typeof Ya<"u"&&Ya.includes(r)){$("#singleInputArea").hide(),$("#doubleInputArea").show();let n="",i="",l="";if(t&&t.includes(",")){const d=t.split(",");n=d[0]||"",i=d[1]||"",l=d[2]||""}else n=t,i="",l="";$("#biaya_gaji_display").val(n?"Rp "+R(K(n)):"").trigger("input"),$("#biaya_bpjs_display").val(i?"Rp "+R(K(i)):"").trigger("input"),$("#biaya_rekrutmen_display").val(l?"Rp "+R(K(l)):"").trigger("input")}else{$("#singleInputArea").show(),$("#doubleInputArea").hide();const n=$("#manual_format").val(),i=K(t);let l=R(i);n==="rupiah"&&i?l="Rp "+l:n==="persen"&&i&&(l=l+"%"),$("#manual_value_display").val(l),$("#manual_value").val(i)}}),$(document).on("change","#manual_format",function(){if($("#doubleInputArea").is(":visible"))return;const r=$(this).val(),t=K($("#manual_value").val());let e=R(t);r==="rupiah"&&t?e="Rp "+e:r==="persen"&&t&&(e=e+"%"),$("#manual_value_display").val(e)}),$("#formManualValue").on("submit",function(r){r.preventDefault();const t=$(this).find('button[type="submit"]'),e=t.html();t.prop("disabled",!0).html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Menyimpan...');const n=new FormData(this);$("#doubleInputArea").is(":visible")?(n.set("biaya_gaji_tahunan",$("#biaya_gaji_tahunan").val()),n.set("biaya_bpjs_tahunan",$("#biaya_bpjs_tahunan").val()),n.set("biaya_rekrutmen_tahunan",$("#biaya_rekrutmen_tahunan").val())):(n.set("manual_value",$("#manual_value").val()),n.set("manual_format",$("#manual_format").val())),$.ajax({url:window.KPI_CONFIG.manualValue,type:"POST",data:n,contentType:!1,processData:!1,success:function(i){$("#modalFormManual").modal("hide"),ma(),t.prop("disabled",!1).html(e),V(B,$("#searchTarget").val().trim())},error:function(i){var l;if(t.prop("disabled",!1).html(e),i.status===422){const d=((l=i.responseJSON)==null?void 0:l.errors)||{},h=Object.values(d).map(w=>w[0]).join("<br>");alert(h)}else alert("Terjadi kesalahan sistem: "+(i.statusText||"Unknown error"))}})}),$("#targetForm").off("submit").on("submit",function(r){if(r.preventDefault(),!$("#judul_kpi").val().trim()){Swal.fire("Peringatan","Judul KPI wajib diisi.","warning");return}const e=$(this),n=new FormData(this),i=e.find('button[type="submit"]'),l=i.html();i.prop("disabled",!0).html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Menyimpan...'),$.ajax({url:e.attr("action"),type:"POST",data:n,processData:!1,contentType:!1,success:function(d){$("#modalBuatTarget").modal("hide"),e[0].reset(),e.find(".select2").val(null).trigger("change"),$("#detail_jangka_container").empty(),$("#jangka_target_display, #tipe_target_display, #nilai_target_display").val(""),$("#jangka_target_hidden, #tipe_target_hidden, #nilai_target_hidden").val(""),Swal.fire({icon:"success",title:"Berhasil!",text:d.message||"Target berhasil dibuat.",timer:2e3,showConfirmButton:!1}).then(()=>{V()})},error:function(d){var w,C;let h="Terjadi kesalahan. Silakan coba lagi.";(w=d.responseJSON)!=null&&w.errors?h=Object.values(d.responseJSON.errors).flat().join("<br>"):(C=d.responseJSON)!=null&&C.message&&(h=d.responseJSON.message),Swal.fire({icon:"error",title:"Gagal!",html:h})},complete:function(){i.prop("disabled",!1).html(l)}})});const E=document.getElementById("manual_document");E&&E.addEventListener("change",function(r){const t=document.getElementById("documentPreview");t.innerHTML="";const e=r.target.files[0];if(!e)return;const n=e.type;if(n.startsWith("image/")){const i=new FileReader;i.onload=function(l){const d=document.createElement("img");d.src=l.target.result,d.classList.add("img-fluid","rounded"),d.style.maxHeight="300px",t.appendChild(d)},i.readAsDataURL(e)}else if(n==="application/pdf"){const i=document.createElement("div");i.innerHTML=`<p class="mb-2"><strong>PDF:</strong> ${e.name}</p><embed src="${URL.createObjectURL(e)}" type="application/pdf" width="100%" height="300px">`,t.appendChild(i)}else t.innerHTML=`<p><strong>File:</strong> ${e.name}</p>`});const g=document.getElementById("formImport");g&&g.addEventListener("submit",function(r){var d;r.preventDefault();const t=new FormData(g),e=(d=document.getElementById("dryRun"))==null?void 0:d.checked,n=document.getElementById("btnSubmitImport"),i=document.getElementById("importPreview"),l=document.getElementById("errorSummary");n.disabled=!0,n.innerHTML='<i class="fa-solid fa-spinner fa-spin me-1"></i>Memproses...',i.classList.remove("d-none"),l.classList.add("d-none"),fetch(g.action,{method:"POST",body:t,headers:{"X-Requested-With":"XMLHttpRequest",Accept:"application/json"}}).then(h=>h.json()).then(h=>{h.success?(ia(h.success,"success"),e?(n.disabled=!1,n.innerHTML='<i class="fa-solid fa-upload me-1"></i>Import Sekarang',i.classList.add("d-none")):setTimeout(()=>{window.location.reload()},2e3)):X(h.errors||{file:["Terjadi kesalahan tidak diketahui"]})}).catch(h=>{console.error("Error:",h),X({file:["Gagal terhubung ke server. Periksa koneksi internet."]})}).finally(()=>{e||(n.disabled=!1,n.innerHTML='<i class="fa-solid fa-upload me-1"></i>Import Sekarang',i.classList.add("d-none"))})});function ia(r,t){const e=document.createElement("div");e.className=`alert alert-${t} alert-dismissible fade show position-fixed top-0 end-0 m-3`,e.style.zIndex="9999",e.innerHTML=`${r}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`,document.body.appendChild(e),setTimeout(()=>{e.parentNode&&e.parentNode.removeChild(e)},5e3)}function X(r){document.getElementById("importPreview").classList.add("d-none");const t=document.getElementById("errorSummary");t.classList.remove("d-none");const e=t.querySelector("ul");e.innerHTML="",(r.file||r.preview||[]).forEach(i=>{const l=document.createElement("li");l.textContent=i,e.appendChild(l)}),document.getElementById("btnSubmitImport").disabled=!1,document.getElementById("btnSubmitImport").innerHTML='<i class="fa-solid fa-upload me-1"></i>Import Sekarang'}});function V(s=1,m=""){$("#kpi-skeleton-view").removeClass("d-none"),$("#kpi-real-view").addClass("d-none"),$("#dt-space-reserver").css("min-height","auto"),$("#content_target").empty(),$("#paginationContainer").empty(),$.ajax({url:window.KPI_CONFIG.getDataTarget,type:"GET",data:{page:s,per_page:Sa,search:m},cache:!1,success:function(c){if($("#kpi-skeleton-view").addClass("d-none"),$("#kpi-real-view").removeClass("d-none"),!c.detail||c.detail.length===0){$("#content_target").html('<tr><td colspan="10" class="text-center py-4 text-muted">Tidak ada data target</td></tr>'),Ia=[],ba=[],Ca();return}Ia=Qa(c.detail),ba=[...Ia],B=c.pagination.current_page,na=c.pagination.last_page,Da=c.pagination.total,Za(),Ca()},error:function(c){$("#kpi-skeleton-view").addClass("d-none"),$("#kpi-real-view").removeClass("d-none"),$("#content_target").html('<tr><td colspan="10" class="text-center py-4 text-danger">Gagal memuat data</td></tr>')}})}function Qa(s){const m={};s.forEach(A=>{const E=A.id_pembuat;m[E]||(m[E]={targets:[]}),m[E].targets.push(A)});const c=[],N=new Date,S=["sertifikasi kompetensi internal","pelatihan kompetensi eksternal"];return Object.entries(m).forEach(([A,E])=>{E.targets.forEach(function(g){const ia=S.includes(g.asistant_route),X=g.total_peserta||(Array.isArray(g.karyawan)?g.karyawan.length:0)||0,r=ia&&X>0?X*(parseFloat(g.nilai_target)||0):parseFloat(g.nilai_target)||0;let t=g.nilai_target;g.tipe_target==="persen"?t=`${g.nilai_target}%`:g.tipe_target==="rupiah"?t=new Intl.NumberFormat("id-ID",{style:"currency",currency:"IDR",minimumFractionDigits:0}).format(g.nilai_target):g.tipe_target==="angka"&&(t=ia?`${r} <small class="text-muted">(${X} peserta × ${g.nilai_target})</small>`:`${g.nilai_target}`);let e="-";if(g.jabatan){const x=Array.isArray(g.jabatan)?g.jabatan:[g.jabatan];x.length>0&&(e=x.length===1?x[0]:x.map(G=>String(G).substring(0,4)+"...").join(", "))}let n="",i="bg-secondary",l=parseFloat(g.progress)||0,d=l;const h=parseFloat(g.progress)||0;if(g.tipe_target==="rupiah"){const x=P(r>0?Math.min(h/r*100,100):0);l=parseFloat(x),d=`${x}%`}else if(g.tipe_target==="angka"){const x=P(r>0?Math.min(h/r*100,100):0);l=parseFloat(x),d=`${x}%`}else d=P(l)+"%";let w=r>0?Math.min(Math.round(h/r*100),100):0,C=!1;if(g.asistant_route==="persentase gap kompetensi tim terhadap standar skill"){const x=g.progress===null||g.progress===void 0,G=parseFloat(g.progress),z=parseFloat(g.nilai_target)||10;x||isNaN(G)?(C=!1,w=0,d="Belum dinilai"):(C=G<=z,w=Math.max(0,Math.min(100,Math.round(G))),d=`Gap ${P(G)}%`)}else g.tipe_target==="angka"?C=(g.manual_value??0)>=r:g.tipe_target==="rupiah"?C=h>=r:C=l>=g.nilai_target;g.tipe_target==="angka"?C=(g.manual_value??0)>=r:g.tipe_target==="rupiah"?C=h>=r:C=l>=g.nilai_target;let U;if(g.tenggat_waktu.includes("-")){const x=g.tenggat_waktu.split("-");x[0].length===4?U=new Date(x[0],x[1]-1,x[2]):U=new Date(x[2],x[1]-1,x[0])}else U=new Date(g.tenggat_waktu);const L=N>U,Q=N.getFullYear()===U.getFullYear();!L&&Q?(n="Sedang Berjalan",i="bg-warning text-dark"):L?(n=C?"Selesai":"Gagal",i=C?"bg-success":"bg-danger"):(n="Dalam Progress",i="bg-warning text-dark");let ea="";typeof fa<"u"&&fa.includes(g.asistant_route)&&(ea=`<li><button type="button" class="dropdown-item text-dark buttonForm" data-id="${g.id}" data-value="${g.manual_value??0}" data-route="${g.asistant_route}" data-bs-toggle="modal" data-bs-target="#modalFormManual"><i class="fa-solid fa-file-pen me-2"></i> Isi Data</button></li>`);const ta=typeof Wa<"u"&&Wa[g.asistant_route]||"#",va=i==="bg-success"?"#28a745":i==="bg-danger"?"#dc3545":"#ffc107",la=g.jangka_target.charAt(0).toUpperCase()+g.jangka_target.slice(1);c.push({id:g.id,judul:g.judul,jangkaDisplay:la,statusText:n,badgeClass:i,formattedTarget:t,jabatanDisplay:e,divisi:g.divisi||"-",pembuat:g.pembuat||"-",lengthProgress:w,progressValueDisplay:d,progressBg:va,tenggat_waktu:g.tenggat_waktu,buttonIsiForm:ea,routeUrl:ta,asistant_route:g.asistant_route})})}),c}function Za(){const s=$("#content_target");if(ba.length===0){s.html('<tr><td colspan="10" class="text-center py-4 text-muted">Tidak ada data yang ditemukan</td></tr>');return}let m="";ba.forEach(c=>{m+=`
            <tr>
                <td class="fw-bold buttonDetailTarget" data-id="${c.id}" style="cursor: pointer;">${c.judul}</td>
                <td class="buttonDetailTarget" data-id="${c.id}" style="cursor: pointer;"><span class="badge bg-light text-primary border border-primary">${c.jangkaDisplay}</span></td>
                <td class="buttonDetailTarget" data-id="${c.id}" style="cursor: pointer;"><span class="badge ${c.badgeClass}">${c.statusText}</span></td>
                <td class="buttonDetailTarget" data-id="${c.id}" style="cursor: pointer;">${c.formattedTarget}</td>
                <td class="buttonDetailTarget" data-id="${c.id}" style="cursor: pointer;">${c.jabatanDisplay}</td>
                <td class="buttonDetailTarget" data-id="${c.id}" style="cursor: pointer;">${c.divisi}</td>
                <td class="buttonDetailTarget" data-id="${c.id}" style="cursor: pointer;">${c.pembuat}</td>
                <td class="buttonDetailTarget" data-id="${c.id}" style="cursor: pointer; min-width:150px;">
                    <div class="progress" style="height: 12px;"><div class="progress-bar" style="width: ${c.lengthProgress}%; background: ${c.progressBg}"></div></div>
                    <small>${c.progressValueDisplay}</small>
                </td>
                <td class="buttonDetailTarget" data-id="${c.id}" style="cursor: pointer;"><small><i class="fa-solid fa-calendar-days me-1"></i> ${c.tenggat_waktu}</small></td>
                <td>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Aksi</button>
                        <ul class="dropdown-menu">
                            ${c.buttonIsiForm}
                            <li>
                                <a href="${c.routeUrl}" class="dropdown-item text-dark" target="_blank">
                                    <i class="fa-solid fa-arrow-up-right-from-square me-2"></i> Lihat Detail KPI
                                </a>
                            </li>
                            <li>
                                <button type="button" 
                                        class="dropdown-item text-info buttonPanduan" 
                                        data-route="${c.asistant_route||""}"
                                        data-judul="${c.judul||""}">
                                    <i class="fa-solid fa-book-open me-2"></i> Panduan
                                </button>
                            </li>
                            <li>
                                <button type="button" class="dropdown-item text-danger buttonHapusTarget" data-id="${c.id}">
                                    <i class="fa-solid fa-trash-can me-2"></i> Hapus
                                </button>
                            </li>
                        </ul>
                    </div>
                </td>
            </tr>`}),s.html(m)}function Ca(){const s=$("#paginationContainer");if(s.empty(),na<=1)return;let m='<ul class="pagination justify-content-center mb-0">';m+=`<li class="page-item ${B===1?"disabled":""}"><a class="page-link" href="#" onclick="changePage(${B-1}); return false;">Previous</a></li>`;for(let S=1;S<=na;S++)m+=`<li class="page-item ${S===B?"active":""}"><a class="page-link" href="#" onclick="changePage(${S}); return false;">${S}</a></li>`;m+=`<li class="page-item ${B===na?"disabled":""}"><a class="page-link" href="#" onclick="changePage(${B+1}); return false;">Next</a></li>`,m+="</ul>";const c=(B-1)*Sa+1,N=Math.min(B*Sa,Da);m+=`<div class="text-muted small mt-2 text-center">Menampilkan ${c}-${N} dari ${Da} data</div>`,s.html(m)}window.changePage=function(s){if(s<1||s>na)return;B=s;const m=$("#searchTarget").val().trim();V(s,m),$("html, body").animate({scrollTop:$("#content_target").offset().top-100},300)};function ga(s){return String(s??"").replace(/[&<>"']/g,function(m){return{"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[m]})}function q(){const s=$("#panduanPdfOpsi .cek-jabatan"),m=s.length,c=s.filter(":checked").length;$("#cekSemuaPanduan").prop("checked",m>0&&c===m).prop("indeterminate",c>0&&c<m),$("#panduanPdfOpsi .cek-divisi").each(function(){const N=$(this).data("grup"),S=$('#panduanPdfOpsi .cek-jabatan[data-grup="'+N+'"]'),A=S.filter(":checked").length;$(this).prop("checked",A>0&&A===S.length).prop("indeterminate",A>0&&A<S.length)}),$("#btnDownloadPanduanPdf").prop("disabled",c===0),$("#infoPanduanPdf").text(c+" jabatan dipilih")}function ae(s){if(!s||s.length===0){$("#panduanPdfOpsi").html('<div class="alert alert-secondary mb-0">Belum ada panduan yang tersedia.</div>'),q();return}let m="";s.forEach(function(c,N){m+=`<div class="border rounded-3 p-3 mb-3">
            <div class="form-check mb-2">
                <input class="form-check-input cek-divisi" type="checkbox" id="cekDivisi${N}" data-grup="${N}">
                <label class="form-check-label fw-semibold" for="cekDivisi${N}">${ga(c.divisi)}</label>
            </div>
            <div class="row g-2 ps-3">`,(c.items||[]).forEach(function(S,A){const E=`cekJabatan${N}_${A}`;m+=`<div class="col-md-6">
                <div class="form-check">
                    <input class="form-check-input cek-jabatan" type="checkbox" name="jabatan[]" value="${ga(S.value)}" id="${E}" data-grup="${N}">
                    <label class="form-check-label" for="${E}">${ga(S.label)} <span class="badge bg-light text-muted border">${S.total} panduan</span></label>
                </div>
            </div>`}),m+="</div></div>"}),$("#panduanPdfOpsi").html(m),q()}function Oa(){$("#panduanPdfOpsi").html('<div class="text-center py-4 text-muted"><i class="fa-solid fa-spinner fa-spin me-2"></i>Memuat pilihan...</div>'),$("#cekSemuaPanduan").prop("checked",!1).prop("indeterminate",!1),$("#btnDownloadPanduanPdf").prop("disabled",!0),$("#infoPanduanPdf").text("0 jabatan dipilih");const s=window.KPI_CONFIG&&window.KPI_CONFIG.panduanOpsi?window.KPI_CONFIG.panduanOpsi:null;if(!s){$("#panduanPdfOpsi").html('<div class="alert alert-danger mb-0">URL panduanOpsi tidak ditemukan di KPI_CONFIG.</div>');return}$.ajax({url:s,type:"GET",dataType:"json",success:function(m){ae(m.groups||[])},error:function(m){console.error("Gagal load panduan opsi",m.status,m.responseText),$("#panduanPdfOpsi").html('<div class="alert alert-danger mb-0">Gagal memuat pilihan ('+m.status+"). Silakan coba lagi.</div>")}})}$(document).on("click",'[data-bs-target="#modalPanduanPdf"]',function(){setTimeout(Oa,50)});$(document).on("show.bs.modal","#modalPanduanPdf",function(){Oa()});$(document).on("change","#cekSemuaPanduan",function(){$("#panduanPdfOpsi .cek-jabatan").prop("checked",$(this).is(":checked")),q()});$(document).on("change","#panduanPdfOpsi .cek-divisi",function(){const s=$(this).data("grup");$('#panduanPdfOpsi .cek-jabatan[data-grup="'+s+'"]').prop("checked",$(this).is(":checked")),q()});$(document).on("change","#panduanPdfOpsi .cek-jabatan",function(){q()});$(document).on("submit","#formPanduanPdf",function(s){if($("#panduanPdfOpsi .cek-jabatan:checked").length===0){s.preventDefault(),typeof Swal<"u"?Swal.fire("Peringatan","Pilih minimal satu jabatan.","warning"):alert("Pilih minimal satu jabatan.");return}const m=$("#btnDownloadPanduanPdf"),c='<i class="fa-solid fa-download"></i> <span>Download PDF</span>';m.prop("disabled",!0).html('<i class="fa-solid fa-spinner fa-spin me-1"></i><span>Menyiapkan PDF...</span>'),setTimeout(function(){m.html(c),q()},2500)});$(document).on("hidden.bs.modal","#modalPanduanPdf",function(){$("#btnDownloadPanduanPdf").html('<i class="fa-solid fa-download"></i> <span>Download PDF</span>'),q()});$(function(){["modalPanduan","modalPanduanPdf"].forEach(function(s){const m=document.getElementById(s);m&&m.parentElement!==document.body&&document.body.appendChild(m)})});$(document).on("click",'#modalPanduan [data-bs-dismiss="modal"], #modalPanduan [data-dismiss="modal"], #modalPanduanPdf [data-bs-dismiss="modal"], #modalPanduanPdf [data-dismiss="modal"]',function(s){s.preventDefault();const m=$(this).closest(".modal")[0];m&&typeof bootstrap<"u"&&bootstrap.Modal?bootstrap.Modal.getOrCreateInstance(m).hide():$(m).modal("hide")});$(document).on("hidden.bs.modal","#modalPanduan, #modalPanduanPdf",function(){$(".modal.show").length===0&&($(".modal-backdrop").remove(),$("body").removeClass("modal-open").css({overflow:"","padding-right":""}))});Object.assign(window,{formatNumber:R,getRawNumber:K,initInputFormatting:Xa,resetFormManual:ma,loadContentForm:V,processRawData:Qa,renderTable:Za,renderPagination:Ca,escHtml:ga,syncPanduanPdfState:q,renderPanduanPdfOpsi:ae,loadPanduanPdfOpsi:Oa});
