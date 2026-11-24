const j=n=>(Number(n)||0).toFixed(1);let Ia=[],ba=[],B=1,na=1,Da=0;const Sa=10,fa=["dorong inovasi pelayanan","inisiatif efisiensi keuangan","mengurangi manual work dan error"],Ra=[],La={"pemasukan kotor":"/rkm","target penjualan project tahunan":"/projects/leads","pemasukan bersih":"/office/analysis","kepuasan pelanggan":"/feedback","rasio biaya operasional terhadap revenue":"/kpi-data/table-data","performa kpi departemen":"/kpi-data/table-data","peserta puas dengan pelayanan dan fasilitas training":"/feedback","dorong inovasi pelayanan":"/kpi-data/table-data","penanganan komplain perseta":"/komplain-peserta","report persiapan kelas":"/office/dashboard",outstanding:"/outstanding","inisiatif efisiensi keuangan":"/kpi-data/table-data","mengurangi manual work dan error":"/kpi-data/table-data","laporan analisis keuangan":"/office/analysis","pencairan biaya operasional":"/pengajuanbarang","penyelesaian tagihan perusahaan":"/office/tagihan-perusahaan","akurasi pencatatan masuk":"/outstanding","pelaksanaan kegiatan karyawan":"office/kegiatan/index","pengeluaran biaya karyawan":"/office/administrasi-karyawan","administrasi karyawan":"/office/administrasi-karyawan","perbaikan kendaraan":"/office/kendaraan/index/perbaikan","report kondisi kendaraan":"/office/kendaraan/index/kondisi","kontrol pengeluaran transportasi":"/office/pickup-driver/index","feedback kenyamanan berkendaran":"/feedback","feedback kebersihan dan kenyamanan":"/feedback","penyelesaian tugas harian":"/office/daftar-tugas/Index","meningkatkan kepuasan dan loyalitas peserta/client":"/survey/kepuasan/table","availability sistem internal kritis":"/home","persentase gap kompetensi tim terhadap standar skill":"/kpi-data/table-data","inovation adaption rate":"/ide-inovasi","ketepatan waktu penyelesaian fitur":"/tickets","mengukur kualitas aplikasi agar minim bug":"/tickets","konsistensi campaign digital":"/content-schedules","efektifitas digital marketing":"/colaborator","timeline webinar":"/timeline","keberhasilan support memenuhi sla":"/tickets","kualitas layanan exam":"/registrasi","presentase kinerja instruktur":"/activityinstruktur","kepuasan peserta pelatihan":"/feedback","upseling lanjutan materi":"/office/rekomendasi-lanjutan/index","sertifikasi kompetensi internal":"/development","pelatihan kompetensi eksternal":"/development","pengembangan kurikulum pelatihan":"/materi","peningkatan knowledge sharing":"/activityinstruktur","peningkatan kontribusi pelatihan":"/rkm","evaluasi kinerja instruktur":"/activityinstruktur","target penjualan tahunan":"/rkm","biaya akuisisi perclient":"/crm/peluang/index","peningkatan kemampuan kompetensi sales":"https://elearning.inixindobdg.co.id/login/index.php?loginredirect=1","meningkatkan revenue perusahaan":"/rkm","customer acquisition cost":"/crm/peluang/index","evaluasi kinerja sales":"/crm/aktivitas","laporan mom":"/crm/laporan-harian","akurasi kelengkapan data penjualan":"/crm/laporanPenjualan","todo administrasi":"/crm/todo-administrasi","ketepatan waktu po":"/office/modul/index","kualitas dokumentasi support dan proctor":"/daftar-peserta-exam","pendapatan penjualan project":"/projects/leads","leads project":"/projects/leads"};function L(n){if(!n&&n!=="0")return"";const m=String(n).replace(/[^0-9]/g,"");return m?new Intl.NumberFormat("id-ID").format(m):""}function K(n){return n?String(n).replace(/[^0-9]/g,""):""}function Ka(){$("#manual_value_display").off("input.formatting").on("input.formatting",function(){const n=K($(this).val());$("#manual_value").val(n);const m=$("#manual_format").val();let c=L(n);m==="rupiah"&&n?c="Rp "+c:m==="persen"&&n&&(c=c+"%"),$(this).val(c)}),$("#biaya_gaji_display").off("input.formatting").on("input.formatting",function(){const n=K($(this).val());$("#biaya_gaji_tahunan").val(n),$(this).val(n?"Rp "+L(n):"")}),$("#biaya_bpjs_display").off("input.formatting").on("input.formatting",function(){const n=K($(this).val());$("#biaya_bpjs_tahunan").val(n),$(this).val(n?"Rp "+L(n):"")}),$("#biaya_rekrutmen_display").off("input.formatting").on("input.formatting",function(){const n=K($(this).val());$("#biaya_rekrutmen_tahunan").val(n),$(this).val(n?"Rp "+L(n):"")})}function ma(){$("#formManualValue")[0].reset(),$("#documentPreview").html(""),$("#singleInputArea").show(),$("#doubleInputArea").hide(),$("#manual_value_display").val("").trigger("input"),$("#manual_value").val(""),$("#biaya_gaji_display").val("").trigger("input"),$("#biaya_gaji_tahunan").val(""),$("#biaya_bpjs_display").val("").trigger("input"),$("#biaya_bpjs_tahunan").val(""),$("#biaya_rekrutmen_display").val("").trigger("input"),$("#biaya_rekrutmen_tahunan").val(""),$("#manualValueId").val("")}$(document).ready(function(){if($("#jabatan").select2({theme:"bootstrap-5",allowClear:!0,width:"100%",dropdownParent:$("#modalBuatTarget")}),$("#karyawan").select2({theme:"bootstrap-5",allowClear:!0,width:"100%",dropdownParent:$("#modalBuatTarget")}),$(document).on("click",".buttonHapusTarget, .buttonForm",function(o){o.stopPropagation()}),$(document).on("click",".buttonPanduan",function(o){o.preventDefault(),o.stopPropagation();const t=($(this).data("route")||"").toString().toLowerCase().trim(),e=$(this).data("judul")||"Target KPI",d={"pelaksanaan kegiatan karyawan":"pelaksanaan_kegiatan_karyawan","pengeluaran biaya karyawan":"pengeluaran_biaya_karyawan","administrasi karyawan":"administrasi_karyawan","mengukur kualitas aplikasi agar minim bug":"mengukur_kualitas_aplikasi_agar_minim_bug","ketepatan waktu penyelesaian fitur":"ketepatan_waktu_penyelesaian_fitur","keberhasilan support memenuhi sla":"tingkat_keberhasilan_support_memenuhi_sla","kualitas layanan exam":"kualitas_layanan_exam","meningkatkan kepuasan dan loyalitas peserta/client":"meningkatkan_kepuasan_dan_loyalitas_peserta","availability sistem internal kritis":"availability_sistem_internal_kritis","persentase gap kompetensi tim terhadap standar skill":"persentase_gap_kompetensi","konsistensi campaign digital":"konsistensi_campaign_digital","ketepatan waktu po":"ketepatan_waktu_po","kualitas dokumentasi support dan proctor":"kualitas_dokumentasi_support_dan_proctor","laporan mom":"laporan_mom","akurasi kelengkapan data penjualan":"akurasi_kelengkapan_data_penjualan","todo administrasi":"todo_administrasi","peserta puas dengan pelayanan dan fasilitas training":"peserta_puas_dengan_pelayanan_dan_fasilitas_training","dorong inovasi pelayanan":"dorong_inovasi_pelayanan","penanganan komplain perseta":"penanganan_komplain_peserta","report persiapan kelas":"report_persiapan_kelas","kepuasan client itsm":"kepuasan_client_itsm","inovation adaption rate":"inovation_adaption_rate","perbaikan kendaraan":"perbaikan_kendaraan","kontrol pengeluaran transportasi":"kontrol_pengeluaran_transportasi","report kondisi kendaraan":"report_kondisi_kendaraan","feedback kenyamanan berkendaran":"feedback_kenyamanan_berkendara","pengembangan kurikulum pelatihan":"pengembangan_kurikulum_pelatihan","peningkatan knowledge sharing":"peningkatan_knowledge_sharing","peningkatan kontribusi pelatihan":"peningkatan_kontribusi_pelatihan","evaluasi kinerja instruktur":"evaluasi_kinerja_instruktur","pembuatan artikel":"pembuatan_artikel",outstanding:"outstanding","inisiatif efisiensi keuangan":"inisiatif_efisiensi_keuangan","mengurangi manual work dan error":"mengurangi_manual_work_dan_error","laporan analisis keuangan":"laporan_analisis_keuangan","pencairan biaya operasional":"pencairan_biaya_operasional","penyelesaian tagihan perusahaan":"penyelesaian_tagihan_perusahaan","akurasi pencatatan masuk":"akurasi_pencatatan_masuk","pemasukan kotor":"pemasukan_kotor","pemasukan bersih":"pemasukan_bersih","target penjualan project tahunan":"target_penjualan_project_tahunan","kepuasan pelanggan":"kepuasan_pelanggan","rasio biaya operasional terhadap revenue":"rasio_biaya_operasional_terhadap_revenue","performa kpi departemen":"performa_kpi_departemen","feedback kebersihan dan kenyamanan":"feedback_kebersihan_dan_kenyamanan","penyelesaian tugas harian":"penyelesaian_tugas_harian","efektifitas digital marketing":"efektifitas_digital_marketing","timeline webinar":"timeline_webinar","presentase kinerja instruktur":"presentase_kinerja_instruktur","kepuasan peserta pelatihan":"kepuasan_peserta_pelatihan","upseling lanjutan materi":"upseling_lanjutan_materi","sertifikasi kompetensi internal":"sertifikasi_kompetensi_internal","pelatihan kompetensi eksternal":"pelatihan_kompetensi_eksternal","target penjualan tahunan":"target_penjualan_tahunan","biaya akuisisi perclient":"biaya_akuisisi_perclient","peningkatan kemampuan kompetensi sales":"peningkatan_kemampuan_kompetensi_sales","meningkatkan revenue perusahaan":"meningkatkan_revenue_perusahaan","customer acquisition cost":"customer_acquisition_cost","evaluasi kinerja sales":"evaluasi_kinerja_sales","pendapatan penjualan project":"pendapatan_penjualan_project","leads project":"leads_project"}[t]||null,i=$("#modalPanduan"),s=$("#bodyContentPanduan"),y=$("#panduanTitle");if(y.text("Panduan: "+e),s.html(`
        <div class="text-center py-5">
            <i class="fa-solid fa-spinner fa-spin fa-2x text-primary mb-3"></i>
            <p class="text-muted mb-0">Memuat panduan...</p>
        </div>
    `),typeof bootstrap<"u"&&bootstrap.Modal?bootstrap.Modal.getOrCreateInstance(document.getElementById("modalPanduan")).show():i.modal("show"),!d){s.html(`
            <div class="alert alert-secondary text-center py-5 mb-0">
                <i class="fa-solid fa-book-open fa-2x mb-3 text-muted"></i>
                <p class="mb-0">Panduan untuk target ini belum tersedia.</p>
                <small class="text-muted d-block mt-2">Route: <code>${t||"-"}</code></small>
            </div>
        `);return}$.ajax({url:`/kpi-data/panduan/${d}`,type:"GET",dataType:"json",success:function(S){s.html(S.html||'<div class="alert alert-warning">Konten panduan kosong.</div>'),S.title&&y.text("Panduan: "+S.title)},error:function(){s.html(`
                <div class="alert alert-danger text-center py-4 mb-0">
                    <i class="fa-solid fa-circle-exclamation fa-2x mb-3"></i>
                    <p class="mb-0">Gagal memuat panduan. Silakan coba lagi.</p>
                </div>
            `)}})}),window.KPI_CONFIG&&window.KPI_CONFIG.importErrors&&window.KPI_CONFIG.importErrors.length>0){const o=window.KPI_CONFIG.importErrors,t=$("#errorSummary"),e=t.find("ul");e.empty(),o.slice(0,10).forEach(l=>e.append(`<li class="text-danger">${l}</li>`)),o.length>10&&e.append(`<li class="text-muted">...dan ${o.length-10} error lainnya</li>`),t.removeClass("d-none"),$("#ModalImport").modal("show")}$("#formImport").on("submit",function(o){const t=$("#btnSubmitImport"),e=$("#importPreview");t.prop("disabled",!0).html('<i class="fa-solid fa-spinner fa-spin me-1"></i>Memproses...'),e.removeClass("d-none")}),$.ajax({url:window.KPI_CONFIG.getDataTarget,type:"GET",success:function(o){const t=$("#assistant_route");t.length&&o.routes&&(t.empty().append("<option selected disabled>-- Pilih Assistant Route --</option>"),o.routes.forEach(function(l){t.append(`<option value="${l.asistant_route}">${l.asistant_route}</option>`)}),t.trigger("change"));const e=$("#jabatan");if(e.length&&o.jabatan_list){const l=e.val()||[];e.empty(),o.jabatan_list.forEach(function(d){e.append(`<option value="${d}">${d}</option>`)}),e.val(l),e.trigger("change")}}});let n=null,m=null;$("#jabatan").off("change").on("change",function(){const o=$(this).val()||[],t=$("#karyawan"),e=$("#assistant_route");if(t.empty().trigger("change"),e.empty().prop("disabled",!0).html("<option selected disabled>-- Memuat routes... --</option>").trigger("change"),$("#detail_jangka_container").empty(),o.length===0){e.html("<option selected disabled>-- Pilih Jabatan Terlebih Dahulu --</option>").trigger("change");return}n&&n.abort(),n=$.ajax({url:window.KPI_CONFIG.getKaryawanByJabatan,type:"GET",data:{jabatan:o},success:function(l){t.empty(),l&&l.length>0&&l.forEach(function(d){t.append(`<option value="${d.id}">${d.text}</option>`)}),t.trigger("change")}}),m&&m.abort(),m=$.ajax({url:window.KPI_CONFIG.getRoutesByJabatan,type:"GET",data:{jabatan:o},success:function(l){e.empty(),e.append('<option value="" selected disabled>-- Pilih Assistant Route --</option>'),l&&l.length>0?(l.forEach(d=>{e.append(`<option value="${d.asistant_route}">${d.asistant_route}</option>`)}),e.prop("disabled",!1)):(e.append("<option disabled>-- Tidak ada route tersedia untuk jabatan ini --</option>"),e.prop("disabled",!0)),e.trigger("change")},error:function(l,d){d!=="abort"&&(e.html("<option disabled>-- Gagal memuat routes --</option>").trigger("change"),typeof Swal<"u"&&Swal.fire("Error","Gagal memuat daftar assistant route","error"))}})}),V();let c;$("#searchTarget").on("input",function(){clearTimeout(c),c=setTimeout(()=>{const o=$(this).val().trim();B=1,V(1,o)},300)}),Ka(),$("#modalFormManual").on("show.bs.modal",function(){ma()}),$("#modalFormManual").on("hidden.bs.modal",function(){ma()}),$("#assistant_route").prop("disabled",!0).html("<option selected disabled>-- Pilih Jabatan Terlebih Dahulu --</option>"),$(document).on("change","#assistant_route",function(){const o=$(this).val();$("#jangka_target_display, #tipe_target_display, #nilai_target_display").val("").addClass("bg-light"),$("#jangka_target_hidden, #tipe_target_hidden, #nilai_target_hidden").val(""),$("#detail_jangka_container").empty(),o&&$.ajax({url:window.KPI_CONFIG.getTargetByRoute,type:"GET",data:{route:o},success:function(t){const e=t.jangka_target||"Tidak ditentukan";$("#jangka_target_display").val(e),$("#jangka_target_hidden").val(e);let l="";t.tipe_target==="persen"?l="Persen (%)":t.tipe_target==="rupiah"?l="Rupiah (Nilai Keuangan)":l="Angka (Unit, Jumlah, dll)",$("#tipe_target_display").val(l),$("#tipe_target_hidden").val(t.tipe_target);let d="";t.tipe_target==="rupiah"?d=new Intl.NumberFormat("id-ID",{style:"currency",currency:"IDR",minimumFractionDigits:0}).format(t.nilai_target):t.tipe_target==="persen"?d=`${t.nilai_target} %`:d=new Intl.NumberFormat("id-ID").format(t.nilai_target),$("#nilai_target_display").val(d),$("#nilai_target_hidden").val(t.nilai_target);let i=e.toLowerCase(),s="";if(i==="tahunan"){const y=new Date().getFullYear();s=`<div class="col-md-12 mb-3"><label for="detail_jangka" class="form-label">Tahun Pelaksanaan <span class="text-danger">*</span></label><select name="detail_jangka" id="detail_jangka" class="form-select" required><option value="${y}" selected>${y}</option></select></div>`}else i==="bulanan"?s='<div class="col-md-6 mb-3"><label for="detail_jangka" class="form-label">Bulan & Tahun <span class="text-danger">*</span></label><input type="month" name="detail_jangka" id="detail_jangka" class="form-control" required></div>':i==="kuartalan"?s='<div class="col-md-6 mb-3"><label for="detail_jangka" class="form-label">Tahun & Kuartal <span class="text-danger">*</span></label><input type="text" name="detail_jangka" id="detail_jangka" class="form-control" placeholder="Contoh: 2026-Q1" required></div>':s='<div class="col-md-6 mb-3"><label for="detail_jangka" class="form-label">Detail Jangka <span class="text-danger">*</span></label><input type="text" name="detail_jangka" id="detail_jangka" class="form-control" placeholder="Masukkan periode spesifik" required></div>';$("#detail_jangka_container").html(s)},error:function(){typeof Swal<"u"&&Swal.fire("Error","Gagal memuat konfigurasi target","error")}})}),$(document).on("click",".buttonDetailTarget",function(){let o=$(this).data("id");const t=$("#bodyContentDetailTarget");t.empty(),t.html(A());const e=document.getElementById("detailTargetModal");e&&typeof bootstrap<"u"&&bootstrap.Modal&&bootstrap.Modal.getOrCreateInstance(e).show(),$.ajax({url:window.KPI_CONFIG.detail,method:"GET",data:{id:o},dataType:"json",success:function(l){t.empty();const d=Array.isArray(l.detail)?l.detail:[],i=d.length>0&&d[0].data||null;if(!i){t.append(`
                        <div class="modal-header"><h5 class="modal-title">Detail Target</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                        <div class="modal-body text-center">Belum ada data</div>
                        <div class="modal-footer"><button class="btn btn-danger" data-bs-dismiss="modal">Tutup</button></div>`);return}const s=i.data_detail||{},y=s.monthly_data||{},S=s.daily_breakdown_per_month||{},O=new Date(window.KPI_CONFIG.dateNow),Ma=new Date(window.KPI_CONFIG.startOfYear),U=i.tenggat_waktu||"";let R;const Q=new Date(O),ea=new Date(Ma),ta=new Date(U),va=s.is_inverse===!0||i.condition==="persentase gap kompetensi tim terhadap standar skill",la=s.progress??0,_=i.nilai_target??0,G=va?la<=_:la>=_;isNaN(Q)||isNaN(ea)||Q<ea?R="Belum Dimulai":!isNaN(ta)&&(Q>ta||Q.getTime()===ta.getTime())?R=G?"Mencapai Target":"Target Gagal":R=G?"Mencapai Target":"Sedang Berjalan";const z=i.nilai_target??0,Z=s.progress??0,ha=s.gap??0,ka=Array.isArray(i.karyawan)?i.karyawan.length:0,ya=["sertifikasi kompetensi internal","pelatihan kompetensi eksternal"].includes(i.condition),sa=ya&&ka>0?ka*z:z;let da,oa,ra;if(i.tipe_target==="persen")da=z+" %",oa=j(Z)+" %",ra=j(ha)+" %";else if(i.tipe_target==="rupiah"){const a=new Intl.NumberFormat("id-ID",{style:"currency",currency:"IDR",minimumFractionDigits:0,maximumFractionDigits:0});da=a.format(sa),oa=a.format(Z),ra=a.format(Math.abs(ha))}else da=ya?`${sa} <small class="text-muted">(${ka} peserta × ${z})</small>`:z,oa=ya?`${Z} / ${sa}`:Z,ra=ha;let wa="";const $a=Array.isArray(s.instruktur_details)?s.instruktur_details:[],Na=s.hari_libur_nasional||{jumlah:0,daftar:{}};if($a.length>0){let f=function(p,r){const k=p.kalender||{};if(!k[r])return'<div class="text-muted text-center py-4">Tidak ada data untuk bulan ini.</div>';const[w,x]=r.split("-"),M=a[parseInt(x,10)-1],W=new Date(w,x,0).getDate(),aa=new Date(w,parseInt(x,10)-1,1).getDay();let C="";u.forEach(I=>{C+=`<div class="kal-header">${I}</div>`});for(let I=0;I<aa;I++)C+='<div class="kal-cell kal-empty"></div>';for(let I=1;I<=W;I++){const J=k[r][I]||{status:"empty",keterangan:""},ue="kal-"+J.status;C+=`
                                <div class="kal-cell ${ue}" title="${J.keterangan||""}" data-bs-toggle="tooltip">
                                    <div class="kal-day">${I}</div>
                                </div>
                            `}const H=Object.values(k[r]).reduce((I,J)=>(I[J.status]=(I[J.status]||0)+1,I),{});return`
                            <div class="kalender-bulan-slide" data-bulan="${r}">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fw-semibold mb-0">${M} ${w}</h6>
                                    <div class="d-flex gap-2 small">
                                        <span class="badge bg-success">Aktif: ${H.working||0}</span>
                                        <span class="badge bg-warning text-dark">Cuti: ${H.cuti||0}</span>
                                        <span class="badge bg-danger">Libur: ${H.libur||0}</span>
                                    </div>
                                </div>
                                <div class="kalender-grid">${C}</div>
                            </div>
                        `},h=function(p){const r=Object.entries(p.daftar_cuti||{});return r.length===0?"":`
                            <div class="mt-3">
                                <h6 class="fw-semibold small text-muted mb-2"><i class="fa-solid fa-suitcase-rolling me-1"></i>Detail Cuti (${r.length} hari)</h6>
                                <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="table-warning"><tr><th>Tanggal</th><th>Tipe</th><th>Alasan</th></tr></thead>
                                        <tbody>
                                            ${r.map(([k,w])=>`
                                                <tr>
                                                    <td class="small">${new Date(k).toLocaleDateString("id-ID",{day:"2-digit",month:"short",year:"numeric"})}</td>
                                                    <td class="small">${w.tipe||"-"}</td>
                                                    <td class="small">${w.alasan||"-"}</td>
                                                </tr>
                                            `).join("")}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        `};var ge=f,be=h;const a=["Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"],u=["Min","Sen","Sel","Rab","Kam","Jum","Sab"];window.__instrukturData=$a,window.__currentMonthIndex={},window.showKalenderBulan=function(p,r){const k=window.__instrukturData.find(J=>J.id==p);if(!k)return;const w=k.kalender||{},x=Object.keys(w).sort(),M=x.length;if(M===0)return;r<0&&(r=M-1),r>=M&&(r=0),window.__currentMonthIndex[p]=r;const W=x[r],aa=document.getElementById(`kalenderSlide_${p}`);aa&&(aa.innerHTML=f(k,W));const C=document.getElementById(`bulanCounter_${p}`);C&&(C.textContent=`${r+1} dari ${M} bulan`);const H=document.getElementById(`btnPrevBulan_${p}`),I=document.getElementById(`btnNextBulan_${p}`);H&&(H.disabled=M<=1),I&&(I.disabled=M<=1)};let v="";const T=Object.entries(Na.daftar||{});T.length>0&&(v=`
                            <div class="alert alert-light border mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0 fw-semibold"><i class="fa-solid fa-flag me-2 text-danger"></i>Hari Libur Nasional</h6>
                                    <span class="badge bg-danger">${Na.jumlah} Hari</span>
                                </div>
                                <div class="row g-2" style="max-height: 150px; overflow-y: auto;">
                                    ${T.map(([p,r])=>`
                                        <div class="col-md-6">
                                            <div class="small p-2 bg-danger bg-opacity-10 rounded">
                                                <strong>${new Date(p).toLocaleDateString("id-ID",{day:"2-digit",month:"short",year:"numeric"})}</strong>
                                                <div class="text-muted">${r}</div>
                                            </div>
                                        </div>
                                    `).join("")}
                                </div>
                            </div>
                        `);let b="";$a.forEach((p,r)=>{const k=p.persentase>=100?"success":p.persentase>=75?"warning":"danger",w=p.kalender||{},x=Object.keys(w).sort(),M=x.length;window.__currentMonthIndex[p.id]=0;const W=h(p);b+=`
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingInstruktur${r}">
                                    <button class="accordion-button ${r>0?"collapsed":""}" type="button" data-bs-toggle="collapse" data-bs-target="#collapseInstruktur${r}" aria-expanded="${r===0?"true":"false"}">
                                        <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                            <div>
                                                <strong>${p.nama}</strong>
                                                <div class="small text-muted">${p.kode_karyawan} • ${p.jabatan}</div>
                                            </div>
                                            <div class="text-end">
                                                <div class="fw-bold text-${k}">${j(p.persentase)}%</div>
                                                <div class="small text-muted">${p.jam_aktif} / ${p.target_jam} jam</div>
                                            </div>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapseInstruktur${r}" class="accordion-collapse collapse ${r===0?"show":""}" data-bs-parent="#accordionInstruktur">
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
                                                ${M>0?f(p,x[0]):'<div class="text-muted text-center py-4">Tidak ada data kalender.</div>'}
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
                    `}let _a=R==="Mencapai Target"?"success":R==="Target Gagal"?"danger":R==="Sedang Berjalan"?"warning":"secondary",Va=R==="Mencapai Target"?"success":"danger";const Fa=s.pie_chart||{above:0,below:0},qa={labels:["Above","Below"],datasets:[{label:"Jumlah",data:[Fa.above??0,Fa.below??0],backgroundColor:["#B66DFF","#FE7C96"],hoverOffset:4}]},P=(a=0)=>"Rp "+Number(a||0).toLocaleString("id-ID"),Ua=a=>{if(!a)return"-";const u=new Date(a);return isNaN(u)?"-":u.toLocaleDateString("id-ID",{day:"2-digit",month:"short"})},Aa=y||{},za=S||{},Ba=Array.isArray(i.karyawan)?i.karyawan.filter(a=>a):i.karyawan?[i.karyawan]:[],xa=Object.keys(Aa||{}).filter(a=>a).sort(),ca=xa.length>0?xa[xa.length-1]:null,Ya=ca?Aa[ca]??0:0,Wa=ca?new Date(`${ca}-01`).toLocaleDateString("id-ID",{month:"long",year:"numeric"}):"-",Xa=Object.entries(za||{}).flatMap(([a,u])=>Object.entries(u||{}).map(([f,h])=>[f,h])).filter(([a,u])=>typeof u=="number"&&u>0).sort((a,u)=>(u[1]||0)-(a[1]||0)).slice(0,3);let Qa=1;const Za=Ba.map(a=>a?`<div class="d-flex align-items-center py-2 participant-item"><div class="avatar me-3">${Qa++}</div><div class="flex-grow-1"><div class="fw-semibold text-dark small">${a.nama_lengkap||"-"}</div><div class="text-muted small">${a.jabatan||"-"}</div></div></div>`:"").join("")||'<div class="text-muted small">Tidak ada karyawan</div>',ae=["pemasukan kotor","meningkatkan revenue perusahaan","target penjualan tahunan","target penjualan project tahunan","pendapatan penjualan project"],ee=["persentase gap kompetensi tim terhadap standar skill"],te=["target penjualan tahunan","pemasukan kotor"],se=["peningkatan kontribusi pelatihan"],ne=["pemasukan bersih"],ie=["performa kpi departemen"],le=["Kepuasan Pelanggan","kepuasan pelanggan"],de=["laporan analisis keuangan"],oe=["inovation adaption rate"];let Y="";const F=i.condition||"";if(te.includes(F)){const a=s.sales_performance||{};if(a.data){if(a.type==="individual"){const u=a.data,f=u.status==="achieved"?"badge-success":"badge-warning",h=u.status==="achieved"?"#28a745":"#ffc107",v=Math.min(u.percentage??0,100);Y=`<div class="card shadow-sm mb-4 mt-2"><div class="card-body"><div class="row"><div class="col-md-6"><p class="mb-2"><strong>Sales:</strong> ${u.nama||"-"}</p><p class="mb-2"><strong>Revenue:</strong> ${P(u.revenue)}</p><p class="mb-3"><strong>Target:</strong> ${P(u.presentase_kemampuan)}</p></div><div class="col-md-6"><div class="mb-2"><div class="d-flex justify-content-between"><span>Progress</span><span>${j(u.percentage)}%</span></div><div class="progress" style="height: 10px;"><div class="progress-bar" role="progressbar" style="width: ${v}%; background-color: ${h};"></div></div></div><div class="text-end"><span class="badge ${f} p-2">${(u.status||"").toUpperCase()}</span></div></div></div></div></div>`}else if(a.type==="all"&&Array.isArray(a.data)){let u="";a.data.forEach((v,T)=>{if(!v)return;const b=v.status==="achieved"?"badge-success":"badge-warning",p=v.status==="achieved"?"text-success":"text-warning",r=Number(v.presentase_kemampuan||0).toLocaleString("id-ID",{useGrouping:!1});u+=`<tr id="row-${v.kode_karyawan||T}"><td class="text-center">${T+1}</td><td><strong>${v.nama||"-"}</strong></td><td class="text-end">${P(v.revenue)}</td><td class="text-center"><div class="input-group input-group-sm" style="max-width: 150px; float: right;"><input type="text" class="form-control text-end target-input ${v.id_detailPerson?"":"is-invalid"}" value="${r}" data-id-detail="${v.id_detailPerson||""}" data-kode-karyawan="${v.kode_karyawan||""}" placeholder="Target" ${v.id_detailPerson?"":"disabled"}></div><div class="loading-spinner" style="display: none; float: right; margin-right: 10px;"><span class="spinner-border spinner-border-sm text-primary" role="status"></span></div><div class="update-feedback" style="display: none; float: right; margin-right: 10px; margin-top: 5px;"><i class="menu-icon fa-solid fa-check-circle text-success"></i></div><div class="clearfix"></div></td><td class="text-center ${p}"><strong>${j(v.percentage)}%</strong></td><td class="text-center"><span class="badge ${b}">${(v.status||"").toUpperCase()}</span></td></tr>`});let f="";const h=s.triwulan_data||{};Object.entries(h).forEach(([v,T])=>{const b=(v||"").toString().replace(/_/g," ");f+=`<div class="col-md-6"><div class="card h-100 shadow-sm border-0"><div class="card-body"><h5 class="card-title">${b}</h5><p class="card-text">Rp ${Number(T||0).toLocaleString("id-ID")}</p></div></div></div>`}),Y=`<div class="row"><div class="col"><div class="card shadow-sm mt-3"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead class="thead-light"><tr><th class="text-center" width="5%">No</th><th>Sales</th><th class="text-end" width="20%">Revenue</th><th class="text-end" width="20%">Target (Editable)</th><th class="text-center" width="15%">Persentase</th><th class="text-center" width="15%">Status</th></tr></thead><tbody>${u||'<tr><td colspan="6" class="text-center">Tidak ada data</td></tr>'}</tbody></table></div></div></div></div><div class="col"><div class="card shadow-sm border-0 mt-3"><div class="card-body"><div class="row g-3">${f||'<p class="text-muted">Tidak ada data triwulan</p>'}</div></div><div class="mb-2 text-center"><hr><p>Data Triwulan diambil dari tahun ${i.detail_jangka||"-"}</p></div></div></div></div>`}}}else if(ne.includes(F)){const a=["Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"];Y=`<div class="mt-4"><div class="row g-4">${(s.previous_quarter?.data||[]).map((f,h)=>`<div class="col-12 col-md-6 col-lg-4"><div class="card border-0 shadow-sm h-100 quarter-card"><div class="card-body d-flex flex-column p-4"><div class="d-flex justify-content-between align-items-start mb-3"><div><h6 class="fw-semibold text-muted mb-1">Periode</h6><h5 class="fw-bold mb-0">${a[(f.month||1)-1]??"-"}</h5></div><span class="badge rounded-pill bg-${f.color||"secondary"} bg-opacity-10 text-${f.color||"secondary"} px-3 py-2">Laporan</span></div><div class="mb-3"><h3 class="fw-bold text-dark mb-0">Rp ${f.nilai?Number(f.nilai).toLocaleString("id-ID"):"-"}</h3><small class="text-muted">Total Pemasukan</small></div><div class="flex-grow-1"><p class="text-muted small mb-2 description-text" id="desc-${h}">${f.description??"-"}</p>${f.description&&f.description.length>100?`<button class="btn btn-sm btn-link p-0 text-primary btn-toggle-desc" data-target="desc-${h}">Lihat Selengkapnya</button>`:""}</div><div class="d-flex justify-content-end align-items-center mt-4"><a href="${window.KPI_CONFIG.storageBase}/${f.file_paths?f.file_paths:""}" class="btn btn-sm btn-dark d-flex align-items-center gap-2" download="${f.file_paths?"true":"false"}"><i class="menu-icon fa-solid fa-download"></i> Download</a></div></div></div></div>`).join("")}</div></div><style>.quarter-card { border-radius: 16px; transition: all 0.25s ease; background: #ffffff; }.quarter-card:hover { transform: translateY(-6px) scale(1.01); box-shadow: 0 15px 35px rgba(0,0,0,0.08); }.quarter-card h3 { letter-spacing: 0.5px; }.quarter-card .badge { font-size: 12px; font-weight: 500; }.description-text { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }.description-text.expanded { -webkit-line-clamp: unset; overflow: visible; }.quarter-card .btn { border-radius: 8px; font-size: 13px; }</style>`}else if(le.includes(F)){const a=s||{};Y=`<div class="row g-4 mt-1"><div class="col-lg-4"><div class="card shadow-sm border-0 rounded-4 h-100"><div class="card-body"><h6 class="fw-semibold text-secondary mb-3">RINGKASAN</h6><div class="d-flex justify-content-between mb-2"><span class="text-muted">Total Feedback</span><span class="fw-bold">${a.total_feedback??0}</span></div><div class="d-flex justify-content-between mb-2"><span class="text-muted">Total Sesi</span><span class="fw-bold">${a.total_sessions??0}</span></div><div class="d-flex justify-content-between"><span class="text-muted">Prediksi</span><span class="fw-bold text-primary">${a.prediction??0}</span></div></div></div></div><div class="col-lg-4"><div class="card shadow-sm border-0 rounded-4 h-100"><div class="card-body"><h6 class="fw-semibold text-secondary mb-3">PERFORMA</h6><div class="mb-3"><small class="text-muted d-block">Top Performer</small><span class="fw-bold text-success">${a.top_performer?.label??"-"} (${a.top_performer?.value??0})</span></div><div><small class="text-muted d-block">Lowest Performer</small><span class="fw-bold text-danger">${a.lowest_performer?.label??"-"} (${a.lowest_performer?.value??0})</span></div></div></div></div><div class="col-lg-4"><div class="card shadow-sm border-0 rounded-4 h-100"><div class="card-body"><h6 class="fw-semibold text-secondary mb-3">STATUS</h6><div class="mb-2"><span class="badge bg-${a.trend==="up"?"success":a.trend==="down"?"danger":"secondary"}">Trend: ${a.trend||"-"} (${j(a.trend_value)})</span></div><div class="mb-2"><span class="badge bg-${a.consistency==="stable"?"success":"warning"}">Konsistensi: ${a.consistency||"-"}</span></div><div><span class="badge bg-${a.target_status==="on_track"?"success":a.target_status==="at_risk"?"warning":"danger"}">Target: ${a.target_status||"-"}</span></div></div></div></div><div class="col-12"><div class="card shadow-sm border-0 rounded-4"><div class="card-body"><h6 class="fw-semibold text-secondary mb-3">KATEGORI PENILAIAN</h6><div class="row text-center">${Object.entries(a.category_scores||{}).map(([u,f])=>`<div class="col"><div class="p-2 border rounded-3"><small class="text-muted d-block">${u}</small><h5 class="fw-bold mb-0">${f??0}</h5></div></div>`).join("")}</div></div></div></div><div class="col-12"><div class="alert alert-info rounded-4 shadow-sm mb-0"><i class="fa-solid fa-lightbulb me-2"></i>${a.insight??"-"}</div></div></div>`}else if(se.includes(F)){const a=s.class_breakdown||{},u=a.Inhouse||{};Y=`<div class="card mt-4 border-0 rounded-4" style="background:#f8fafc;"><div class="card-body p-4"><div class="d-flex justify-content-between align-items-end mb-4"><div><div class="text-muted small">Total Kelas</div><div class="fs-2 fw-semibold text-dark">${a.total??0}</div></div></div><div class="row g-3 mb-4"><div class="col-md-6"><div class="p-3 rounded-3 bg-white border h-100"><div class="text-muted small mb-2">Kelas Inixindo</div><div class="fs-4 fw-semibold text-dark">${a.kelas_od??0}</div></div></div><div class="col-md-6"><div class="p-3 rounded-3 bg-white border h-100"><div class="text-muted small mb-2">Kelas Orang Luar</div><div class="fs-4 fw-semibold text-dark">${a.kelas_ol??0}</div></div></div><div class="col-md-6"><div class="p-3 rounded-3 bg-white border h-100"><div class="text-muted small mb-2">Kelas Offline</div><div class="fs-4 fw-semibold text-dark">${a.kelas_offline??0}</div></div></div><div class="col-md-6"><div class="p-3 rounded-3 bg-white border h-100"><div class="text-muted small mb-2">Kelas Online</div><div class="fs-4 fw-semibold text-dark">${a.kelas_online??0}</div></div></div></div><div class="p-3 rounded-3 bg-white border"><div class="fw-semibold mb-3">Kelas Inhouse</div><div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted small">Bandung</span><span class="fw-semibold text-dark">${u.kelas_inhouse??0}</span></div><div class="d-flex justify-content-between py-2"><span class="text-muted small">Luar Bandung</span><span class="fw-semibold text-dark">${u.kelas_inhouse_luar??0}</span></div></div></div></div>`}let ja="";const re=["pemasukan kotor"],ua=s.kontribusi_sales;if(ua&&re.includes(String(F).toLowerCase().trim())){const a={on_track:["bg-primary","On Track"],selesai:["bg-success","Selesai"],gagal:["bg-danger","Gagal"]},u=b=>{const[p,r]=a[b]||["bg-secondary","-"];return`<span class="badge ${p}">${r}</span>`},f=b=>b<0?"text-danger":"text-success",h=(ua.rows||[]).map((b,p)=>`
                        <tr>
                            <td class="text-center">${p+1}</td>
                            <td class="fw-semibold">${b.nama||"-"}</td>
                            <td class="text-end">${P(b.triwulan[1])}</td>
                            <td class="text-end">${P(b.triwulan[2])}</td>
                            <td class="text-end">${P(b.triwulan[3])}</td>
                            <td class="text-end">${P(b.triwulan[4])}</td>
                            <td class="text-end fw-bold text-primary">${P(b.total)}</td>
                            <td class="text-end">${P(b.target)}</td>
                            <td class="text-end fw-semibold ${f(b.selisih)}">${P(b.selisih)}</td>
                            <td class="text-center">${j(b.persentase)}%</td>
                            <td class="text-center">${u(b.status)}</td>
                        </tr>`).join(""),v=ua.total,T=v?`
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="2" class="text-center">TOTAL</td>
                                <td class="text-end">${P(v.triwulan[1])}</td>
                                <td class="text-end">${P(v.triwulan[2])}</td>
                                <td class="text-end">${P(v.triwulan[3])}</td>
                                <td class="text-end">${P(v.triwulan[4])}</td>
                                <td class="text-end text-primary">${P(v.total)}</td>
                                <td class="text-end">${P(v.target)}</td>
                                <td class="text-end ${f(v.selisih)}">${P(v.selisih)}</td>
                                <td class="text-center">${j(v.persentase)}%</td>
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
                                        <tbody>${h||'<tr><td colspan="11" class="text-center text-muted">Belum ada target penjualan sales untuk tahun ini</td></tr>'}</tbody>
                                        ${T}
                                    </table>
                                </div>
                            </div>
                        </div>`}let pa="";if(fa.includes(F)){const a=s.dataManual?.manual_document||"",u=a?a.split("/").pop():"",f=u?u.split(".").pop().toLowerCase():"",h=["jpg","jpeg","png"],v=["pdf"];let T="";const b=a?`/storage/${a}`:"";h.includes(f)?T=`<div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3"><div class="mb-3" style="max-width: 100%; max-height: 300px;"><img src="${b}" alt="${u}" class="img-fluid rounded shadow-sm" style="max-width: 100%; max-height: 300px; object-fit: contain;"></div><div class="mt-2 text-center"><a href="${b}" download="${u}" class="btn btn-primary btn-sm"><i class="fa-solid fa-download me-1"></i>Download Gambar</a></div><div class="mt-2 small text-muted"><i class="fa-solid fa-file-image me-1"></i>${u}</div></div>`:v.includes(f)?T=`<div class="w-100 h-100 d-flex flex-column"><div class="flex-grow-1 mb-3" style="min-height: 250px;"><iframe src="${b}" class="w-100 h-100" style="border: 1px solid #dee2e6; border-radius: 8px;"></iframe></div><div class="text-center"><a href="${b}" download="${u}" class="btn btn-primary btn-sm"><i class="fa-solid fa-download me-1"></i>Download PDF</a></div><div class="mt-2 small text-muted text-center"><i class="fa-solid fa-file-pdf me-1"></i>${u}</div></div>`:T='<div class="text-center py-5"><div class="mb-3"><i class="fa-solid fa-file text-secondary" style="font-size: 4rem;"></i></div><p class="text-muted mb-3">File tidak dapat ditampilkan</p><p class="text-muted small">Hanya gambar dan PDF yang dapat ditampilkan</p></div>',pa=`<h6 class="fw-semibold mb-3 text-secondary"><i class="fa-solid fa-file me-2"></i>Dokumen Manual</h6><div class="manual-document-container flex-grow-1 d-flex flex-column align-items-center justify-content-center p-3" style="background-color: #f8f9fa; border-radius: 8px;">${T}</div><div class="mt-3 small text-muted text-center"><i class="fa-solid fa-info-circle me-1"></i>Klik tombol download untuk menyimpan file</div>`}else if(ae.includes(F)){const a=Ba[0]||{};pa=`<div class="p-2"><div class="mb-4"><small class="text-muted">Ringkasan performa</small></div><div class="mb-4 p-3 rounded-3 border bg-white shadow-sm"><div class="d-flex justify-content-between align-items-center"><div><div class="text-muted small">Bulan Terakhir</div><div class="fw-semibold">${Wa}</div></div><div class="text-end"><div class="fw-bold fs-5 text-dark">${P(Ya)}</div></div></div></div><div class="mb-3"><div class="text-muted small mb-2 fw-semibold">Top Hari Tertinggi</div>${Xa.map(([u,f],h)=>`<div class="d-flex justify-content-between align-items-center mb-2 p-2 rounded ${h===0?"bg-success-subtle":"bg-light"}"><div class="d-flex align-items-center gap-2"><span class="badge ${h===0?"bg-success":"bg-secondary"}">#${h+1}</span><span class="${h===0?"fw-semibold text-dark":"text-muted"}">${Ua(u)}</span></div><span class="fw-semibold ${h===0?"text-success":"text-dark"}">${P(f)}</span></div>`).join("")}</div><hr class="my-3"><div class="d-flex align-items-center justify-content-between"><div class="d-flex align-items-center gap-2"><i class="bi bi-person-circle fs-4 text-secondary"></i><div><div class="fw-semibold text-dark">${a?.nama_lengkap??"-"}</div><small class="text-muted">${a?.jabatan??"-"}</small></div></div><span class="badge bg-light text-dark border">Karyawan</span></div></div>`}else pa=`<h6 class="fw-semibold mb-3 text-secondary"><i class="fa-solid fa-chart-pie me-2"></i>Chart ${F}</h6><div class="chart-container flex-grow-1"><canvas id="MyChartDoughtnut"></canvas></div>`;if(!fa.includes(F))if(ie.includes(F)){const a=s||{},u=a.trend??"stable",f=a.trend_value??0,h=a.consistency??"stable",v=a.target_status??"behind",T=u==="up"?"success":u==="down"?"danger":"secondary",b=h==="stable"?"success":"warning",p=v==="on_track"?"success":v==="at_risk"?"warning":"danger",r=Object.entries(a.division_breakdown||{}).map(([w,x])=>`<div class="col mb-3"><div class="p-2 border rounded-3"><small class="text-muted d-block">${w}</small><h5 class="fw-bold mb-0">${j(x)}%</h5></div></div>`).join(""),k=a.risk_divisions&&a.risk_divisions.length>0?`<div class="col-6"><div class="card shadow-sm border-0 rounded-4"><div class="card-body"><h6 class="fw-semibold text-danger mb-3">DIVISI BERISIKO</h6><div class="row">${a.risk_divisions.map(w=>`<div class="col mb-2"><div class="p-2 border rounded-3 text-center"><small class="text-muted d-block">${w.name||"-"}</small><span class="fw-bold text-danger">${j(w.value)}%</span></div></div>`).join("")}</div></div></div></div>`:"";`${a.total_kpi??0}${a.total_division??0}${j(a.progress)}${a.top_division?.name??"-"}${j(a.top_division?.value)}${a.lowest_division?.name??"-"}${j(a.lowest_division?.value)}${T}${u}${j(f)}${b}${h}${p}${v}${r}${k}${a.insight??"-"}`}else if(oe.includes(F)){const u=(s||{}).dataInovasi||[];`${u.length?u.map(f=>`
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
                                            `}`}else if(de.includes(F)){const a=["","Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"];`${(s.analisa_data||[]).map(f=>{const h=f.file_paths?window.KPI_CONFIG.storageBase+"/"+f.file_paths:"#";return`<div class="col-md-4 mt-5"><div class="card border-0 shadow-sm h-100"><div class="mb-2 p-3"><h5 class="fw-bold text-primary mb-1">${a[f.month]||"-"}</h5><small class="text-muted">Laporan Analisis Bulanan</small></div><div class="card-body d-flex flex-column" style="overflow-y: scroll; max-height: 280px;"><div class="mb-3"><p class="mb-0" style="text-align: justify;">${f.description||""}</p></div></div><div class="mt-auto p-3"><a href="${h}" class="btn btn-sm btn-outline-primary w-100" ${f.file_paths?"download":""}><i class="menu-icon fa-solid fa-file-alt"></i></a></div></div></div>`}).join("")}`}else ee.includes(F)&&D(i,i.can_edit===!0,window.KPI_CONFIG.csrfToken);let Pa;(i.tipe_target==="rupiah"||i.tipe_target==="angka")&&sa>0?Pa=Math.min(100,Number((Z/sa*100).toFixed(1))):Pa=Math.min(100,Z||0),t.append(`
                    <div class="modal-header border-0 pb-2">
                        <div>
                            <h5 class="modal-title fw-bold mb-1">${i.judul||"Detail Target"}</h5>
                            <small class="text-muted">${i.jangka_target||"-"}</small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body pt-2">
                        <div class="p-4 rounded-4 mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-${_a} px-3 py-2">${R}</span>
                                <span class="text-muted small">
                                    Deadline: <strong>${i.tenggat_waktu||"-"}</strong>
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
                                    <div class="fw-bold text-${Va} fs-4">
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
                                        <span class="fw-semibold">${i.divisi_kpi||"-"}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Jabatan</span>
                                        <span class="fw-semibold">${i.jabatan_kpi||"-"}</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Pembuat</span>
                                        <span class="fw-semibold">${i.pembuat||"-"}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 border rounded-4 h-100">
                                    <h6 class="fw-semibold mb-3">Karyawan</h6>
                                    <div style="max-height:140px; overflow:auto;">
                                        ${Za}
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
                `);const ce=["Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"],Ta=a=>{if(!a)return"-";const u=a.split("-");return u.length>=2&&ce[parseInt(u[1],10)-1]||a};setTimeout(function(){const a=document.getElementById("MyChartDoughtnut");if(a&&typeof Chart<"u"){const b=Chart.getChart(a);b&&b.destroy(),new Chart(a,{type:"doughnut",data:qa,options:{responsive:!0,maintainAspectRatio:!1,plugins:{legend:{position:"bottom",labels:{boxWidth:12,padding:15}}}}})}document.querySelectorAll(".target-input").forEach(b=>{if(!b)return;let p=null;b.addEventListener("input",function(){const r=this,k=r.closest("tr");if(!k)return;const w=k.querySelector(".loading-spinner"),x=k.querySelector(".update-feedback");clearTimeout(p),x&&(x.style.display="none"),p=setTimeout(()=>{const M=r.dataset.idDetail,W=r.dataset.kodeKaryawan,aa=(r.value||"").toString().replace(/\./g,"");if(!M){r.classList.add("is-invalid");return}w&&(w.style.display="inline-block"),r.disabled=!0,$.ajax({url:window.KPI_CONFIG.updateTargetPerSales,method:"POST",data:{_token:window.KPI_CONFIG.csrfToken,id_detailPerson:M,kode_karyawan:W,presentase_kemampuan:aa},success:function(C){if(w&&(w.style.display="none"),r.disabled=!1,x&&(x.style.display="inline-block",setTimeout(()=>{x.style.display="none"},2e3)),r.classList.remove("is-invalid"),r.classList.add("is-valid"),setTimeout(()=>r.classList.remove("is-valid"),2e3),C?.data){const H=k.querySelector("td:nth-child(5)"),I=k.querySelector("td:nth-child(6)");if(H&&C.data.percentage&&(H.innerHTML=`<strong class="${C.data.status==="achieved"?"text-success":"text-warning"}">${j(C.data.percentage)}%</strong>`),I&&C.data.status){const J=C.data.status==="achieved"?"badge-success":"badge-warning";I.innerHTML=`<span class="badge ${J}">${C.data.status.toUpperCase()}</span>`}}},error:function(){w&&(w.style.display="none"),r.disabled=!1,r.classList.add("is-invalid")}})},1e3)}),b.addEventListener("blur",function(){const r=(this.value||"").toString().replace(/\./g,"");r&&(this.value=Number(r).toLocaleString("id-ID"))}),b.addEventListener("focus",function(){const r=(this.value||"").toString().replace(/\./g,"");r&&(this.value=r)})}),$(document).off("submit","#formGapKompetensi").on("submit","#formGapKompetensi",function(b){b.preventDefault(),$.ajax({url:window.KPI_CONFIG.updateGapKompetensi,method:"POST",data:$(this).serialize(),success:function(){typeof Swal<"u"&&Swal.fire({icon:"success",title:"Berhasil!",text:"Berhasil diupdate.",timer:2e3,showConfirmButton:!1}).then(()=>{$("#detailTargetModal").modal("hide"),typeof V=="function"&&V()})},error:function(p){typeof Swal<"u"&&Swal.fire({icon:"error",title:"Gagal!",html:p.responseJSON?.message||"Terjadi kesalahan"})}})});let u=null;const f=document.getElementById("StatisticChart"),h=(b,p,r)=>{if(!f||typeof Chart>"u")return;u&&u.destroy();const k=(p||[]).map(x=>Number(x)||0),w=k.length>0?Math.max(...k):100;u=new Chart(f,{type:"line",data:{labels:b||[],datasets:[{label:r||"Data",data:k,borderColor:"#4e73df",backgroundColor:"rgba(78, 115, 223, 0.1)",tension:.4,fill:!0}]},options:{responsive:!0,maintainAspectRatio:!1,scales:{y:{beginAtZero:!0,suggestedMax:w+3,ticks:{count:6,precision:0,callback:x=>Math.round(x)}}}}})},v=Object.keys(y||{}).map(Ta),T=Object.values(y||{}).map(b=>Number(b)||0);h(v,T,"Rata-rata"),$("#filterType").off("change").on("change",function(){this.value==="month"?($("#filterMonth").removeClass("d-none").empty().append('<option value="">Pilih Bulan</option>'),Object.keys(S||{}).forEach(b=>{b&&$("#filterMonth").append(`<option value="${b}">${Ta(b)}</option>`)}),u&&u.destroy()):($("#filterMonth").addClass("d-none"),h(v,T,"Rata-rata"))}),$("#filterMonth").off("change").on("change",function(){const b=this.value;if(!b||!S?.[b])return;const p=Object.keys(S[b]||{}).map(k=>(k||"").substring(8)),r=Object.values(S[b]||{}).map(k=>Number(k)||0);h(p,r,`Tanggal ${Ta(b)}`)}),document.querySelectorAll(".btn-toggle-desc").forEach(b=>{b&&b.addEventListener("click",function(){const p=this.getAttribute("data-target"),r=p?document.getElementById(p):null;r&&(r.classList.contains("expanded")?(r.classList.remove("expanded"),this.innerText="Lihat Selengkapnya"):(r.classList.add("expanded"),this.innerText="Sembunyikan"))})})},150);const Ea=document.getElementById("detailTargetModal");Ea&&typeof bootstrap<"u"&&bootstrap.Modal&&bootstrap.Modal.getOrCreateInstance(Ea).show(),setTimeout(()=>{t.addClass("modal-content-loaded")},50)},error:function(l){t.empty(),t.html(`
                    <div class="modal-header border-0 pb-2">
                        <h5 class="modal-title fw-bold mb-1 text-danger">Gagal Memuat</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center py-4">
                        <i class="fa-solid fa-circle-exclamation text-danger" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                        <h6 class="fw-semibold">Terjadi Kesalahan Jaringan</h6>
                        <p class="text-muted small">${l.responseJSON?.message||"Silakan coba lagi nanti."}</p>
                    </div>
                    <div class="modal-footer border-0">
                        <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                `),t.addClass("modal-content-loaded")}})});function D(o,t,e){const l=Array.isArray(o.karyawan)?o.karyawan:[],d=t?"":"disabled",i=l.map((s,y)=>{const S=s.skala_kemampuan??null,O=S===null?'<span class="badge bg-secondary">Belum dinilai</span>':`<span class="badge bg-info text-dark">${j(S*10)}%</span>`;return`
                <div class="row mb-2 align-items-center p-2 rounded">
                    <div class="col-md-6 d-flex justify-content-between align-items-center">
                        <span>${s.nama_lengkap??"-"}</span>${O}
                    </div>
                    <div class="col-md-6">
                        <input type="number" min="1" max="10" step="0.5" class="form-control"
                            name="data[${y}][kemampuan]" value="${S??""}"
                            placeholder="1 - 10" ${d}>
                    </div>
                    <input type="hidden" name="data[${y}][id_karyawan]" value="${s.id_karyawan}">
                    <input type="hidden" name="data[${y}][detailTargetKey]" value="${s.detailTargetKey}">
                </div>`}).join("");return`
            <div class="mt-4"><div class="card shadow-sm border-0 rounded-4"><div class="card-body">
                <h6 class="fw-semibold mb-1">Penilaian Kemampuan Tim</h6>
                <small class="text-muted d-block mb-3">Isi skala 1-10 (8 = 80%). Gap dihitung dari rata-rata seluruh anggota tim.</small>
                <form id="formGapKompetensi">
                    <input type="hidden" name="_token" value="${e}">
                    <div class="row mb-2 fw-semibold text-muted border-bottom pb-2">
                        <div class="col-md-6">Nama Karyawan</div><div class="col-md-6">Nilai (1-10)</div>
                    </div>
                    ${i||'<div class="text-muted small">Tidak ada anggota tim.</div>'}
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
        `}$(document).on("click",".buttonHapusTarget",function(){let o=$(this).data("id");Swal.fire({title:"Yakin ingin menghapus?",text:"Data target ini akan dihapus secara permanen!",icon:"warning",showCancelButton:!0,customClass:{confirmButton:"btn btn-gradient-info me-3",cancelButton:"btn btn-gradient-danger"},confirmButtonText:"Ya, hapus!",cancelButtonText:"Batal"}).then(t=>{t.isConfirmed&&$.ajax({url:window.KPI_CONFIG.hapusTarget.replace("REPLACE_ID",o),type:"DELETE",data:{_token:window.KPI_CONFIG.csrfToken},success:function(e){Swal.fire({icon:"success",title:"Berhasil!",text:"Data target berhasil dihapus.",showConfirmButton:!1,timer:1500}),V(B,$("#searchTarget").val().trim())},error:function(e){Swal.fire({icon:"error",title:"Gagal!",text:e.responseJSON?.message||"Terjadi kesalahan saat menghapus data."})}})})}),$(document).on("click",".buttonForm",function(){const o=$(this).data("route"),t=$(this).data("value")||"",e=$(this).data("id");if($("#manualValueId").val(e),typeof Ra<"u"&&Ra.includes(o)){$("#singleInputArea").hide(),$("#doubleInputArea").show();let l="",d="",i="";if(t&&t.includes(",")){const s=t.split(",");l=s[0]||"",d=s[1]||"",i=s[2]||""}else l=t,d="",i="";$("#biaya_gaji_display").val(l?"Rp "+L(K(l)):"").trigger("input"),$("#biaya_bpjs_display").val(d?"Rp "+L(K(d)):"").trigger("input"),$("#biaya_rekrutmen_display").val(i?"Rp "+L(K(i)):"").trigger("input")}else{$("#singleInputArea").show(),$("#doubleInputArea").hide();const l=$("#manual_format").val(),d=K(t);let i=L(d);l==="rupiah"&&d?i="Rp "+i:l==="persen"&&d&&(i=i+"%"),$("#manual_value_display").val(i),$("#manual_value").val(d)}}),$(document).on("change","#manual_format",function(){if($("#doubleInputArea").is(":visible"))return;const o=$(this).val(),t=K($("#manual_value").val());let e=L(t);o==="rupiah"&&t?e="Rp "+e:o==="persen"&&t&&(e=e+"%"),$("#manual_value_display").val(e)}),$("#formManualValue").on("submit",function(o){o.preventDefault();const t=$(this).find('button[type="submit"]'),e=t.html();t.prop("disabled",!0).html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Menyimpan...');const l=new FormData(this);$("#doubleInputArea").is(":visible")?(l.set("biaya_gaji_tahunan",$("#biaya_gaji_tahunan").val()),l.set("biaya_bpjs_tahunan",$("#biaya_bpjs_tahunan").val()),l.set("biaya_rekrutmen_tahunan",$("#biaya_rekrutmen_tahunan").val())):(l.set("manual_value",$("#manual_value").val()),l.set("manual_format",$("#manual_format").val())),$.ajax({url:window.KPI_CONFIG.manualValue,type:"POST",data:l,contentType:!1,processData:!1,success:function(d){$("#modalFormManual").modal("hide"),ma(),t.prop("disabled",!1).html(e),V(B,$("#searchTarget").val().trim())},error:function(d){if(t.prop("disabled",!1).html(e),d.status===422){const i=d.responseJSON?.errors||{},s=Object.values(i).map(y=>y[0]).join("<br>");alert(s)}else alert("Terjadi kesalahan sistem: "+(d.statusText||"Unknown error"))}})}),$("#targetForm").off("submit").on("submit",function(o){if(o.preventDefault(),!$("#judul_kpi").val().trim()){Swal.fire("Peringatan","Judul KPI wajib diisi.","warning");return}const e=$(this),l=new FormData(this),d=e.find('button[type="submit"]'),i=d.html();d.prop("disabled",!0).html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Menyimpan...'),$.ajax({url:e.attr("action"),type:"POST",data:l,processData:!1,contentType:!1,success:function(s){$("#modalBuatTarget").modal("hide"),e[0].reset(),e.find(".select2").val(null).trigger("change"),$("#detail_jangka_container").empty(),$("#jangka_target_display, #tipe_target_display, #nilai_target_display").val(""),$("#jangka_target_hidden, #tipe_target_hidden, #nilai_target_hidden").val(""),Swal.fire({icon:"success",title:"Berhasil!",text:s.message||"Target berhasil dibuat.",timer:2e3,showConfirmButton:!1}).then(()=>{V()})},error:function(s){let y="Terjadi kesalahan. Silakan coba lagi.";s.responseJSON?.errors?y=Object.values(s.responseJSON.errors).flat().join("<br>"):s.responseJSON?.message&&(y=s.responseJSON.message),Swal.fire({icon:"error",title:"Gagal!",html:y})},complete:function(){d.prop("disabled",!1).html(i)}})});const E=document.getElementById("manual_document");E&&E.addEventListener("change",function(o){const t=document.getElementById("documentPreview");t.innerHTML="";const e=o.target.files[0];if(!e)return;const l=e.type;if(l.startsWith("image/")){const d=new FileReader;d.onload=function(i){const s=document.createElement("img");s.src=i.target.result,s.classList.add("img-fluid","rounded"),s.style.maxHeight="300px",t.appendChild(s)},d.readAsDataURL(e)}else if(l==="application/pdf"){const d=document.createElement("div");d.innerHTML=`<p class="mb-2"><strong>PDF:</strong> ${e.name}</p><embed src="${URL.createObjectURL(e)}" type="application/pdf" width="100%" height="300px">`,t.appendChild(d)}else t.innerHTML=`<p><strong>File:</strong> ${e.name}</p>`});const g=document.getElementById("formImport");g&&g.addEventListener("submit",function(o){o.preventDefault();const t=new FormData(g),e=document.getElementById("dryRun")?.checked,l=document.getElementById("btnSubmitImport"),d=document.getElementById("importPreview"),i=document.getElementById("errorSummary");l.disabled=!0,l.innerHTML='<i class="fa-solid fa-spinner fa-spin me-1"></i>Memproses...',d.classList.remove("d-none"),i.classList.add("d-none"),fetch(g.action,{method:"POST",body:t,headers:{"X-Requested-With":"XMLHttpRequest",Accept:"application/json"}}).then(s=>s.json()).then(s=>{s.success?(ia(s.success,"success"),e?(l.disabled=!1,l.innerHTML='<i class="fa-solid fa-upload me-1"></i>Import Sekarang',d.classList.add("d-none")):setTimeout(()=>{window.location.reload()},2e3)):X(s.errors||{file:["Terjadi kesalahan tidak diketahui"]})}).catch(s=>{console.error("Error:",s),X({file:["Gagal terhubung ke server. Periksa koneksi internet."]})}).finally(()=>{e||(l.disabled=!1,l.innerHTML='<i class="fa-solid fa-upload me-1"></i>Import Sekarang',d.classList.add("d-none"))})});function ia(o,t){const e=document.createElement("div");e.className=`alert alert-${t} alert-dismissible fade show position-fixed top-0 end-0 m-3`,e.style.zIndex="9999",e.innerHTML=`${o}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`,document.body.appendChild(e),setTimeout(()=>{e.parentNode&&e.parentNode.removeChild(e)},5e3)}function X(o){document.getElementById("importPreview").classList.add("d-none");const t=document.getElementById("errorSummary");t.classList.remove("d-none");const e=t.querySelector("ul");e.innerHTML="",(o.file||o.preview||[]).forEach(d=>{const i=document.createElement("li");i.textContent=d,e.appendChild(i)}),document.getElementById("btnSubmitImport").disabled=!1,document.getElementById("btnSubmitImport").innerHTML='<i class="fa-solid fa-upload me-1"></i>Import Sekarang'}});function V(n=1,m=""){$("#kpi-skeleton-view").removeClass("d-none"),$("#kpi-real-view").addClass("d-none"),$("#dt-space-reserver").css("min-height","auto"),$("#content_target").empty(),$("#paginationContainer").empty(),$.ajax({url:window.KPI_CONFIG.getDataTarget,type:"GET",data:{page:n,per_page:Sa,search:m},cache:!1,success:function(c){if($("#kpi-skeleton-view").addClass("d-none"),$("#kpi-real-view").removeClass("d-none"),!c.detail||c.detail.length===0){$("#content_target").html('<tr><td colspan="10" class="text-center py-4 text-muted">Tidak ada data target</td></tr>'),Ia=[],ba=[],Ca();return}Ia=Ga(c.detail),ba=[...Ia],B=c.pagination.current_page,na=c.pagination.last_page,Da=c.pagination.total,Ha(),Ca()},error:function(c){$("#kpi-skeleton-view").addClass("d-none"),$("#kpi-real-view").removeClass("d-none"),$("#content_target").html('<tr><td colspan="10" class="text-center py-4 text-danger">Gagal memuat data</td></tr>')}})}function Ga(n){const m={};n.forEach(A=>{const E=A.id_pembuat;m[E]||(m[E]={targets:[]}),m[E].targets.push(A)});const c=[],N=new Date,D=["sertifikasi kompetensi internal","pelatihan kompetensi eksternal"];return Object.entries(m).forEach(([A,E])=>{E.targets.forEach(function(g){const ia=D.includes(g.asistant_route),X=g.total_peserta||(Array.isArray(g.karyawan)?g.karyawan.length:0)||0,o=ia&&X>0?X*(parseFloat(g.nilai_target)||0):parseFloat(g.nilai_target)||0;let t=g.nilai_target;g.tipe_target==="persen"?t=`${g.nilai_target}%`:g.tipe_target==="rupiah"?t=new Intl.NumberFormat("id-ID",{style:"currency",currency:"IDR",minimumFractionDigits:0}).format(g.nilai_target):g.tipe_target==="angka"&&(t=ia?`${o} <small class="text-muted">(${X} peserta × ${g.nilai_target})</small>`:`${g.nilai_target}`);let e="-";if(g.jabatan){const _=Array.isArray(g.jabatan)?g.jabatan:[g.jabatan];_.length>0&&(e=_.length===1?_[0]:_.map(G=>String(G).substring(0,4)+"...").join(", "))}let l="",d="bg-secondary",i=parseFloat(g.progress)||0,s=i;const y=parseFloat(g.progress)||0;if(g.tipe_target==="rupiah"){const _=j(o>0?Math.min(y/o*100,100):0);i=parseFloat(_),s=`${_}%`}else if(g.tipe_target==="angka"){const _=j(o>0?Math.min(y/o*100,100):0);i=parseFloat(_),s=`${_}%`}else s=j(i)+"%";let S=o>0?Math.min(Math.round(y/o*100),100):0,O=!1;if(g.asistant_route==="persentase gap kompetensi tim terhadap standar skill"){const _=g.progress===null||g.progress===void 0,G=parseFloat(g.progress),z=parseFloat(g.nilai_target)||10;_||isNaN(G)?(O=!1,S=0,s="Belum dinilai"):(O=G<=z,S=Math.max(0,Math.min(100,Math.round(G))),s=`Gap ${j(G)}%`)}else g.tipe_target==="angka"?O=(g.manual_value??0)>=o:g.tipe_target==="rupiah"?O=y>=o:O=i>=g.nilai_target;g.tipe_target==="angka"?O=(g.manual_value??0)>=o:g.tipe_target==="rupiah"?O=y>=o:O=i>=g.nilai_target;let U;if(g.tenggat_waktu.includes("-")){const _=g.tenggat_waktu.split("-");_[0].length===4?U=new Date(_[0],_[1]-1,_[2]):U=new Date(_[2],_[1]-1,_[0])}else U=new Date(g.tenggat_waktu);const R=N>U,Q=N.getFullYear()===U.getFullYear();!R&&Q?(l="Sedang Berjalan",d="bg-warning text-dark"):R?(l=O?"Selesai":"Gagal",d=O?"bg-success":"bg-danger"):(l="Dalam Progress",d="bg-warning text-dark");let ea="";typeof fa<"u"&&fa.includes(g.asistant_route)&&(ea=`<li><button type="button" class="dropdown-item text-dark buttonForm" data-id="${g.id}" data-value="${g.manual_value??0}" data-route="${g.asistant_route}" data-bs-toggle="modal" data-bs-target="#modalFormManual"><i class="fa-solid fa-file-pen me-2"></i> Isi Data</button></li>`);const ta=typeof La<"u"&&La[g.asistant_route]||"#",va=d==="bg-success"?"#28a745":d==="bg-danger"?"#dc3545":"#ffc107",la=g.jangka_target.charAt(0).toUpperCase()+g.jangka_target.slice(1);c.push({id:g.id,judul:g.judul,jangkaDisplay:la,statusText:l,badgeClass:d,formattedTarget:t,jabatanDisplay:e,divisi:g.divisi||"-",pembuat:g.pembuat||"-",lengthProgress:S,progressValueDisplay:s,progressBg:va,tenggat_waktu:g.tenggat_waktu,buttonIsiForm:ea,routeUrl:ta,asistant_route:g.asistant_route})})}),c}function Ha(){const n=$("#content_target");if(ba.length===0){n.html('<tr><td colspan="10" class="text-center py-4 text-muted">Tidak ada data yang ditemukan</td></tr>');return}let m="";ba.forEach(c=>{m+=`
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
            </tr>`}),n.html(m)}function Ca(){const n=$("#paginationContainer");if(n.empty(),na<=1)return;let m='<ul class="pagination justify-content-center mb-0">';m+=`<li class="page-item ${B===1?"disabled":""}"><a class="page-link" href="#" onclick="changePage(${B-1}); return false;">Previous</a></li>`;for(let D=1;D<=na;D++)m+=`<li class="page-item ${D===B?"active":""}"><a class="page-link" href="#" onclick="changePage(${D}); return false;">${D}</a></li>`;m+=`<li class="page-item ${B===na?"disabled":""}"><a class="page-link" href="#" onclick="changePage(${B+1}); return false;">Next</a></li>`,m+="</ul>";const c=(B-1)*Sa+1,N=Math.min(B*Sa,Da);m+=`<div class="text-muted small mt-2 text-center">Menampilkan ${c}-${N} dari ${Da} data</div>`,n.html(m)}window.changePage=function(n){if(n<1||n>na)return;B=n;const m=$("#searchTarget").val().trim();V(n,m),$("html, body").animate({scrollTop:$("#content_target").offset().top-100},300)};function ga(n){return String(n??"").replace(/[&<>"']/g,function(m){return{"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[m]})}function q(){const n=$("#panduanPdfOpsi .cek-jabatan"),m=n.length,c=n.filter(":checked").length;$("#cekSemuaPanduan").prop("checked",m>0&&c===m).prop("indeterminate",c>0&&c<m),$("#panduanPdfOpsi .cek-divisi").each(function(){const N=$(this).data("grup"),D=$('#panduanPdfOpsi .cek-jabatan[data-grup="'+N+'"]'),A=D.filter(":checked").length;$(this).prop("checked",A>0&&A===D.length).prop("indeterminate",A>0&&A<D.length)}),$("#btnDownloadPanduanPdf").prop("disabled",c===0),$("#infoPanduanPdf").text(c+" jabatan dipilih")}function Ja(n){if(!n||n.length===0){$("#panduanPdfOpsi").html('<div class="alert alert-secondary mb-0">Belum ada panduan yang tersedia.</div>'),q();return}let m="";n.forEach(function(c,N){m+=`<div class="border rounded-3 p-3 mb-3">
            <div class="form-check mb-2">
                <input class="form-check-input cek-divisi" type="checkbox" id="cekDivisi${N}" data-grup="${N}">
                <label class="form-check-label fw-semibold" for="cekDivisi${N}">${ga(c.divisi)}</label>
            </div>
            <div class="row g-2 ps-3">`,(c.items||[]).forEach(function(D,A){const E=`cekJabatan${N}_${A}`;m+=`<div class="col-md-6">
                <div class="form-check">
                    <input class="form-check-input cek-jabatan" type="checkbox" name="jabatan[]" value="${ga(D.value)}" id="${E}" data-grup="${N}">
                    <label class="form-check-label" for="${E}">${ga(D.label)} <span class="badge bg-light text-muted border">${D.total} panduan</span></label>
                </div>
            </div>`}),m+="</div></div>"}),$("#panduanPdfOpsi").html(m),q()}function Oa(){$("#panduanPdfOpsi").html('<div class="text-center py-4 text-muted"><i class="fa-solid fa-spinner fa-spin me-2"></i>Memuat pilihan...</div>'),$("#cekSemuaPanduan").prop("checked",!1).prop("indeterminate",!1),$("#btnDownloadPanduanPdf").prop("disabled",!0),$("#infoPanduanPdf").text("0 jabatan dipilih");const n=window.KPI_CONFIG&&window.KPI_CONFIG.panduanOpsi?window.KPI_CONFIG.panduanOpsi:null;if(!n){$("#panduanPdfOpsi").html('<div class="alert alert-danger mb-0">URL panduanOpsi tidak ditemukan di KPI_CONFIG.</div>');return}$.ajax({url:n,type:"GET",dataType:"json",success:function(m){Ja(m.groups||[])},error:function(m){console.error("Gagal load panduan opsi",m.status,m.responseText),$("#panduanPdfOpsi").html('<div class="alert alert-danger mb-0">Gagal memuat pilihan ('+m.status+"). Silakan coba lagi.</div>")}})}$(document).on("click",'[data-bs-target="#modalPanduanPdf"]',function(){setTimeout(Oa,50)});$(document).on("show.bs.modal","#modalPanduanPdf",function(){Oa()});$(document).on("change","#cekSemuaPanduan",function(){$("#panduanPdfOpsi .cek-jabatan").prop("checked",$(this).is(":checked")),q()});$(document).on("change","#panduanPdfOpsi .cek-divisi",function(){const n=$(this).data("grup");$('#panduanPdfOpsi .cek-jabatan[data-grup="'+n+'"]').prop("checked",$(this).is(":checked")),q()});$(document).on("change","#panduanPdfOpsi .cek-jabatan",function(){q()});$(document).on("submit","#formPanduanPdf",function(n){if($("#panduanPdfOpsi .cek-jabatan:checked").length===0){n.preventDefault(),typeof Swal<"u"?Swal.fire("Peringatan","Pilih minimal satu jabatan.","warning"):alert("Pilih minimal satu jabatan.");return}const m=$("#btnDownloadPanduanPdf"),c='<i class="fa-solid fa-download"></i> <span>Download PDF</span>';m.prop("disabled",!0).html('<i class="fa-solid fa-spinner fa-spin me-1"></i><span>Menyiapkan PDF...</span>'),setTimeout(function(){m.html(c),q()},2500)});$(document).on("hidden.bs.modal","#modalPanduanPdf",function(){$("#btnDownloadPanduanPdf").html('<i class="fa-solid fa-download"></i> <span>Download PDF</span>'),q()});$(function(){["modalPanduan","modalPanduanPdf"].forEach(function(n){const m=document.getElementById(n);m&&m.parentElement!==document.body&&document.body.appendChild(m)})});$(document).on("click",'#modalPanduan [data-bs-dismiss="modal"], #modalPanduan [data-dismiss="modal"], #modalPanduanPdf [data-bs-dismiss="modal"], #modalPanduanPdf [data-dismiss="modal"]',function(n){n.preventDefault();const m=$(this).closest(".modal")[0];m&&typeof bootstrap<"u"&&bootstrap.Modal?bootstrap.Modal.getOrCreateInstance(m).hide():$(m).modal("hide")});$(document).on("hidden.bs.modal","#modalPanduan, #modalPanduanPdf",function(){$(".modal.show").length===0&&($(".modal-backdrop").remove(),$("body").removeClass("modal-open").css({overflow:"","padding-right":""}))});Object.assign(window,{formatNumber:L,getRawNumber:K,initInputFormatting:Ka,resetFormManual:ma,loadContentForm:V,processRawData:Ga,renderTable:Ha,renderPagination:Ca,escHtml:ga,syncPanduanPdfState:q,renderPanduanPdfOpsi:Ja,loadPanduanPdfOpsi:Oa});
