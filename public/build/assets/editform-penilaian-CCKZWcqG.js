document.addEventListener("DOMContentLoaded",()=>{const v=Array.from(document.querySelectorAll(".editform-penilaian-form-kriteria-block"));let l=0;const m={};let y=[],_=[],h=[];v.forEach(e=>{const d=parseInt(e.getAttribute("data-kriteria-index"),10);if(!isNaN(d)){d>l&&(l=d);const n=Array.from(e.querySelectorAll(".editform-penilaian-form-group-item[data-sub-kriteria-index]"));let a=-1;n.forEach(i=>{const t=parseInt(i.getAttribute("data-sub-kriteria-index"),10);!isNaN(t)&&t>a&&(a=t)}),m[d]=a>=0?a:0}}),v.length===0&&(l=0,m[0]=0);function g(e){e.querySelectorAll(".editform-penilaian-form-group-item[data-sub-kriteria-index]").forEach(n=>{const a=n.querySelector(".editform-penilaian-tipe-kategori"),i=n.querySelector(".editform-penilaian-ket-tipe-section"),t=n.querySelector(".editform-penilaian-ket-tipe-wrapper"),s=n.querySelector(".editform-penilaian-add-ket-tipe"),k=e.getAttribute("data-kriteria-index"),f=n.getAttribute("data-sub-kriteria-index");function p(){if(a&&["checkbox","radio","select"].includes(a.value))i.classList.remove("d-none");else if(i&&(i.classList.add("d-none"),t)){t.querySelectorAll(".input-group").forEach((b,u)=>{u>0&&b.remove()});const r=t.querySelector('input[name*="[ket_tipe]"]'),o=t.querySelector('input[name*="[nilai_ket_tipe]"]');r&&(r.value=""),o&&(o.value="")}}if(a&&(a.removeEventListener("change",p),a.addEventListener("change",p),p()),s&&t){const c=s.__clickHandler;c&&s.removeEventListener("click",c);const r=()=>{const o=document.createElement("div");o.className="input-group mb-2",o.innerHTML=`
                        <input type="text" name="kriteria[${k}][sub_kriteria][${f}][ket_tipe][]" class="form-control" placeholder="Masukkan keterangan tipe">
                        <input type="text" name="kriteria[${k}][sub_kriteria][${f}][nilai_ket_tipe][]" class="form-control" placeholder="Nilai tipe...">
                        <button type="button" class="btn btn-danger btn-sm editform-penilaian-remove-ket-tipe"><i class="fa-solid fa-trash-can"></i></button>
                    `,t.insertBefore(o,s)};s.addEventListener("click",r),s.__clickHandler=r}if(t){const c=t.__removeHandler;c&&t.removeEventListener("click",c);const r=o=>{if(o.target.closest(".editform-penilaian-remove-ket-tipe"))if(t.querySelectorAll(".input-group").length>1){const u=o.target.closest(".input-group").querySelector('input[name*="[id_ket_tipe]"]');u&&u.value&&h.push(u.value),o.target.closest(".input-group").remove()}else{const u=o.target.closest(".input-group"),x=u.querySelector('input[name*="[ket_tipe]"]'),S=u.querySelector('input[name*="[nilai_ket_tipe]"]');x&&(x.value=""),S&&(S.value="")}};t.addEventListener("click",r),t.__removeHandler=r}})}document.querySelectorAll(".editform-penilaian-form-kriteria-block").forEach(e=>g(e)),document.getElementById("editform-penilaian-kriteria-container").addEventListener("click",function(e){var d,n;if(e.target.classList.contains("editform-penilaian-add-sub-kriteria-block")||e.target.closest(".editform-penilaian-add-sub-kriteria-block")){const i=(e.target.classList.contains("editform-penilaian-add-sub-kriteria-block")?e.target:e.target.closest(".editform-penilaian-add-sub-kriteria-block")).closest(".editform-penilaian-form-kriteria-block"),t=i.getAttribute("data-kriteria-index"),s=i.querySelector(".editform-penilaian-form-wrapper-sub-kriteria");let k=i.querySelector(".editform-penilaian-form-group-item[data-sub-kriteria-index]");if(!k)return;typeof m[t]>"u"&&(m[t]=0),m[t]++;const f=m[t],p=k.cloneNode(!0);p.setAttribute("data-sub-kriteria-index",f),p.querySelectorAll("input, select, textarea").forEach(r=>{const o=r.getAttribute("name");if(o){const b=o.replace(/\[sub_kriteria\]\[\d+\]/,`[sub_kriteria][${f}]`).replace(/kriteria\[\d+\]/,`kriteria[${t}]`);r.setAttribute("name",b)}r.tagName==="SELECT"?r.selectedIndex=0:r.type==="checkbox"||r.type==="radio"?r.checked=!1:r.value=""});const c=p.querySelector(".editform-penilaian-ket-tipe-wrapper");c&&(c.innerHTML=`
                    <div class="input-group mb-2">
                        <input type="text" name="kriteria[${t}][sub_kriteria][${f}][ket_tipe][]" class="form-control" placeholder="Masukkan keterangan tipe">
                        <input type="text" name="kriteria[${t}][sub_kriteria][${f}][nilai_ket_tipe][]" class="form-control" placeholder="Nilai tipe...">
                        <button type="button" class="btn btn-danger btn-sm editform-penilaian-remove-ket-tipe"><i class="fa-solid fa-trash-can"></i></button>
                    </div>
                    <button type="button" class="editform-penilaian-btn-action success btn-sm editform-penilaian-add-ket-tipe mt-2"><i class="fa-solid fa-plus"></i> Tambah Keterangan</button>
                `),s.appendChild(p),g(i)}if(e.target.closest(".editform-penilaian-remove-sub-kriteria-block")){const a=e.target.closest(".editform-penilaian-form-group-item[data-sub-kriteria-index]");if(e.target.closest(".editform-penilaian-form-kriteria-block").querySelectorAll(".editform-penilaian-form-group-item[data-sub-kriteria-index]").length<=1)return;const s=(d=a.querySelector('input[name*="[id_judul_kategori]"]'))==null?void 0:d.value;s&&_.push(s),a.remove()}if(e.target.closest(".editform-penilaian-remove-kriteria-block")){if(document.querySelectorAll(".editform-penilaian-form-kriteria-block").length<=1)return;const i=e.target.closest(".editform-penilaian-form-kriteria-block"),t=(n=i.querySelector('input[name*="[id_nama_penilaian]"]'))==null?void 0:n.value;t&&y.push(t),i.remove()}}),document.getElementById("editform-penilaian-add-kriteria-main-block").addEventListener("click",()=>{const e=document.getElementById("editform-penilaian-kriteria-container"),n=(document.querySelector('.editform-penilaian-form-kriteria-block[data-kriteria-index="0"]')||document.querySelector(".editform-penilaian-form-kriteria-block")).cloneNode(!0);l++,m[l]=0,n.setAttribute("data-kriteria-index",l),n.querySelectorAll("input, select, textarea").forEach(i=>{const t=i.getAttribute("name");if(t){const s=t.replace(/kriteria\[\d+\]/,`kriteria[${l}]`);i.setAttribute("name",s)}i.tagName==="SELECT"?i.selectedIndex=0:i.type==="checkbox"||i.type==="radio"?i.checked=!1:i.value=""});const a=n.querySelector(".editform-penilaian-form-wrapper-sub-kriteria");a.innerHTML=`
            <div class="editform-penilaian-form-group-item" data-sub-kriteria-index="0">
                <button type="button" class="editform-penilaian-btn-remove-block editform-penilaian-remove-sub-kriteria-block" title="Hapus Sub Kriteria">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Judul Sub Kriteria <span class="text-danger">*</span></label>
                        <input type="text" name="kriteria[${l}][sub_kriteria][0][judul_kategori]" class="form-control" placeholder="Masukan sub kriteria..." maxlength="250" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tipe <span class="text-danger">*</span></label>
                        <select name="kriteria[${l}][sub_kriteria][0][tipe_kategori]" class="form-select editform-penilaian-tipe-kategori" required>
                            <option disabled selected>Pilih tipe</option>
                            <option value="text">Teks</option>
                            <option value="radio">Pilihan (Radio)</option>
                            <option value="checkbox">Kotak Centang</option>
                            <option value="number">Angka</option>
                            <option value="range">Rentang</option>
                            <option value="textarea">Teks Panjang</option>
                            <option value="select">Pilihan Dropdown</option>
                        </select>
                    </div>
                </div>
                <div class="editform-penilaian-ket-tipe-section d-none mb-3">
                    <label class="form-label fw-semibold"><i class="fa-solid fa-tags text-primary me-1"></i> Keterangan Tipe</label>
                    <div class="editform-penilaian-ket-tipe-wrapper text-end">
                        <div class="input-group mb-2">
                            <input type="text" name="kriteria[${l}][sub_kriteria][0][ket_tipe][]" class="form-control" placeholder="Masukkan keterangan tipe">
                            <input type="text" name="kriteria[${l}][sub_kriteria][0][nilai_ket_tipe][]" class="form-control" placeholder="Nilai tipe...">
                            <button type="button" class="btn btn-danger btn-sm editform-penilaian-remove-ket-tipe"><i class="fa-solid fa-trash-can"></i></button>
                        </div>
                        <button type="button" class="editform-penilaian-btn-action success btn-sm editform-penilaian-add-ket-tipe mt-2"><i class="fa-solid fa-plus"></i> Tambah Keterangan</button>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Bobot <span class="text-danger">*</span></label>
                        <input type="number" name="kriteria[${l}][sub_kriteria][0][bobot]" class="form-control" placeholder="Masukan bobot..." required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Level <span class="text-danger">*</span></label>
                        <select name="kriteria[${l}][sub_kriteria][0][level]" class="form-select" required>
                            <option disabled selected>Pilih</option>
                            <option value="required">Harus Diisi</option>
                            <option value="null">Tidak Harus</option>
                        </select>
                    </div>
                </div>
            </div>
        `,e.appendChild(n),g(n)}),document.querySelector("form").addEventListener("submit",function(){if(!document.querySelector('input[name="deleted_form_ids"]')){let e=document.createElement("input");e.type="hidden",e.name="deleted_form_ids",e.value=y.join(","),this.appendChild(e)}if(!document.querySelector('input[name="deleted_kategori_ids"]')){let e=document.createElement("input");e.type="hidden",e.name="deleted_kategori_ids",e.value=_.join(","),this.appendChild(e)}if(!document.querySelector('input[name="deleted_tipe_ids"]')){let e=document.createElement("input");e.type="hidden",e.name="deleted_tipe_ids",e.value=h.join(","),this.appendChild(e)}})});
