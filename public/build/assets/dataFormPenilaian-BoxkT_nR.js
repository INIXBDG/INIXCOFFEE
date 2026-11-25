$(document).ready(function(){$("#tahun").val(new Date().getFullYear()),u(),$("#tahun").on("change",function(){u()})});function u(){const i=$("#tahun").val();$("#dataform-penilaian-skeleton-container").show(),$("#dataform-penilaian-real-container").hide(),$.ajax({url:window.penilaianConfig.routes.getForm,type:"get",data:{tahun:i},success:function(n){const e=n.data??[],a=$("#dataform-penilaian-tbody");if(a.empty(),$.fn.DataTable.isDataTable("#table_penilaian")&&$("#table_penilaian").DataTable().destroy(),e.length===0)a.append(`
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center">
                                <i class="fa-solid fa-inbox fa-3x text-muted mb-3"></i>
                                <span class="text-muted">Tidak ada Form!</span>
                            </div>
                        </td>
                    </tr>
                `);else{e.forEach(function(t,s){let o=[];Array.isArray(t.evaluated)?o=t.evaluated.map(r=>r.nama):t.evaluated&&(o=[t.evaluated.nama]);let f="";const d=3;o.slice(0,d).forEach(r=>{f+=`<span class="dataform-penilaian-evaluated-badge">${r}</span>`});let p="";o.length>d&&(p=`<a href="javascript:void(0)" class="dataform-penilaian-show-more-link dataform-penilaian-show-more" data-full='${JSON.stringify(o)}'>+${o.length-d} lainnya</a>`),a.append(`
                        <tr>
                            <td class="fw-semibold text-muted">${s+1}</td>
                            <td><code class="bg-light px-2 py-1 rounded">${t.label_kode_form}</code></td>
                            <td>
                                <div class="dataform-penilaian-evaluated-list">
                                    ${f}
                                    ${p}
                                </div>
                            </td>
                            <td>${t.tahun}</td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a class="dataform-penilaian-btn-table-action" href="/penilaian/data-form/edit/${t.kode_form}" title="Edit Form">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <button type="button" class="dataform-penilaian-btn-table-action dataform-penilaian-btn-lihat-evaluator"
                                            data-kode="${t.kode_form}"
                                            data-label="${t.label_kode_form}"
                                            title="Lihat & Perbaiki Evaluator">
                                        <i class="fa-solid fa-users-gear"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `)});const l=$("#table_penilaian").DataTable({pageLength:10,lengthMenu:[5,10,25,50,100],responsive:!0,scrollX:!0,scrollCollapse:!0,autoWidth:!1,dom:"<'row mb-2'<'col-md-6 dataform-penilaian-dt-length'l><'col-md-6 text-end dataform-penilaian-dt-search'f>><'row'<'col-sm-12'tr>><'row mt-2'<'col-md-5 dataform-penilaian-dt-info'i><'col-md-7 dataform-penilaian-dt-pagination'p>>",language:{search:"",searchPlaceholder:"Cari data...",lengthMenu:"_MENU_ per halaman",info:"Menampilkan _START_ - _END_ dari _TOTAL_ entri",infoEmpty:"Tidak ada data",zeroRecords:"Data tidak ditemukan",paginate:{first:"Awal",last:"Akhir",next:"›",previous:"‹"}}});setTimeout(function(){l.columns.adjust().draw(!1)},50)}$("#dataform-penilaian-skeleton-container").hide(),$("#dataform-penilaian-real-container").show()},error:function(n,e,a){console.error("Error loading data:",a),$("#dataform-penilaian-skeleton-container").hide(),$("#dataform-penilaian-real-container").show(),$("#dataform-penilaian-tbody").html(`
                <tr>
                    <td colspan="5" class="text-center py-5">
                        <div class="d-flex flex-column align-items-center">
                            <i class="fa-solid fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                            <span class="text-danger">Gagal memuat data</span>
                        </div>
                    </td>
                </tr>
            `)}})}$(document).on("click",".dataform-penilaian-show-more",function(){const i=JSON.parse($(this).attr("data-full"));let n='<ul class="dataform-penilaian-evaluated-modal-list">';i.forEach((e,a)=>{n+=`
            <li>
                <span class="dataform-penilaian-badge-number">${a+1}</span>
                <span class="fw-semibold text-dark">${e}</span>
            </li>
        `}),n+="</ul>",$("#evaluatedContent").html(n),$("#modalEvaluated").modal("show")});let c=[];$(document).on("click",".dataform-penilaian-btn-lihat-evaluator",function(){const i=$(this).data("kode"),n=$(this).data("label");$("#modalKodeFormLabel").text(`(${n})`),$("#modalEvaluator").modal("show"),$("#evaluatorLoading").removeClass("d-none"),$("#evaluatorContent, #evaluatorEmpty").addClass("d-none"),$("#bodyEvaluator").empty(),$.ajax({url:window.penilaianConfig.routes.getEvaluators,type:"GET",data:{kode_form:i},success:function(e){if($("#evaluatorLoading").addClass("d-none"),c=e.list_divisi||[],!e.evaluators||e.evaluators.length===0){$("#evaluatorEmpty").removeClass("d-none");return}$("#evaluatorContent").removeClass("d-none"),e.evaluators.forEach((a,l)=>{let t='<option value="">-- Pilih Divisi --</option>';c.forEach(s=>{const o=s===a.divisi_evaluator?"selected":"";t+=`<option value="${s}" ${o}>${s}</option>`}),a.divisi_evaluator&&!c.includes(a.divisi_evaluator)&&(t+=`<option value="${a.divisi_evaluator}" selected>${a.divisi_evaluator} (lama)</option>`),$("#bodyEvaluator").append(`
                    <tr data-id="${a.id}">
                        <td>${l+1}</td>
                        <td>
                            <div class="fw-semibold">${a.nama_evaluator}</div>
                            <small class="text-muted">Evaluated: ${a.nama_evaluated}</small>
                        </td>
                        <td><span class="badge bg-secondary">${a.jabatan}</span></td>
                        <td><small>${a.jenis_penilaian}</small></td>
                        <td>
                            <span class="badge bg-primary dataform-penilaian-current-divisi">${a.divisi_evaluator||"-"}</span>
                        </td>
                        <td>
                            <select class="form-select form-select-sm dataform-penilaian-select-divisi">
                                ${t}
                            </select>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-success dataform-penilaian-btn-simpan-divisi" title="Simpan">
                                <i class="fa-solid fa-floppy-disk"></i>
                            </button>
                        </td>
                    </tr>
                `)})},error:function(){$("#evaluatorLoading").addClass("d-none"),Swal.fire("Error","Gagal memuat data evaluator","error")}})});$(document).on("click",".dataform-penilaian-btn-simpan-divisi",function(){const i=$(this).closest("tr"),n=i.data("id"),e=i.find(".dataform-penilaian-select-divisi").val();if(!e){Swal.fire("Peringatan","Pilih divisi terlebih dahulu","warning");return}const a=$(this);a.prop("disabled",!0).html('<i class="fa-solid fa-spinner fa-spin"></i>'),$.ajax({url:window.penilaianConfig.routes.updateDivisiEvaluator,type:"POST",data:{_token:window.penilaianConfig.csrfToken,id:n,divisi_evaluator:e},success:function(l){l.success&&(i.find(".dataform-penilaian-current-divisi").text(e).removeClass("bg-primary").addClass("bg-success"),Swal.fire({icon:"success",title:"Berhasil",text:l.message,timer:1500,showConfirmButton:!1}))},error:function(l){const t=l.responseJSON?.message||"Gagal menyimpan";Swal.fire("Error",t,"error")},complete:function(){a.prop("disabled",!1).html('<i class="fa-solid fa-floppy-disk"></i>')}})});Object.assign(window,{loadData:u});
