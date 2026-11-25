document.addEventListener("DOMContentLoaded",()=>{const g=Array.from(document.querySelectorAll(".editform-penilaian-form-kriteria-block"));let n=0;const m={};let v=[],y=[],_=[];g.forEach(e=>{const s=parseInt(e.getAttribute("data-kriteria-index"),10);if(!isNaN(s)){s>n&&(n=s);const i=Array.from(e.querySelectorAll(".editform-penilaian-form-group-item[data-sub-kriteria-index]"));let t=-1;i.forEach(r=>{const a=parseInt(r.getAttribute("data-sub-kriteria-index"),10);!isNaN(a)&&a>t&&(t=a)}),m[s]=t>=0?t:0}}),g.length===0&&(n=0,m[0]=0);function b(e){e.querySelectorAll(".editform-penilaian-form-group-item[data-sub-kriteria-index]").forEach(i=>{const t=i.querySelector(".editform-penilaian-tipe-kategori"),r=i.querySelector(".editform-penilaian-ket-tipe-section"),a=i.querySelector(".editform-penilaian-ket-tipe-wrapper"),o=i.querySelector(".editform-penilaian-add-ket-tipe"),f=e.getAttribute("data-kriteria-index"),k=i.getAttribute("data-sub-kriteria-index");function l(){if(t&&["checkbox","radio","select"].includes(t.value))r.classList.remove("d-none");else if(r&&(r.classList.add("d-none"),a)){a.querySelectorAll(".input-group").forEach((h,u)=>{u>0&&h.remove()});const c=a.querySelector('input[name*="[ket_tipe]"]'),d=a.querySelector('input[name*="[nilai_ket_tipe]"]');c&&(c.value=""),d&&(d.value="")}}if(t&&(t.removeEventListener("change",l),t.addEventListener("change",l),l()),o&&a){const p=o.__clickHandler;p&&o.removeEventListener("click",p);const c=()=>{const d=document.createElement("div");d.className="input-group mb-2",d.innerHTML=`
                        <input type="text" name="kriteria[${f}][sub_kriteria][${k}][ket_tipe][]" class="form-control" placeholder="Masukkan keterangan tipe">
                        <input type="text" name="kriteria[${f}][sub_kriteria][${k}][nilai_ket_tipe][]" class="form-control" placeholder="Nilai tipe...">
                        <button type="button" class="btn btn-danger btn-sm editform-penilaian-remove-ket-tipe"><i class="fa-solid fa-trash-can"></i></button>
                    `,a.insertBefore(d,o)};o.addEventListener("click",c),o.__clickHandler=c}if(a){const p=a.__removeHandler;p&&a.removeEventListener("click",p);const c=d=>{if(d.target.closest(".editform-penilaian-remove-ket-tipe"))if(a.querySelectorAll(".input-group").length>1){const u=d.target.closest(".input-group").querySelector('input[name*="[id_ket_tipe]"]');u&&u.value&&_.push(u.value),d.target.closest(".input-group").remove()}else{const u=d.target.closest(".input-group"),x=u.querySelector('input[name*="[ket_tipe]"]'),S=u.querySelector('input[name*="[nilai_ket_tipe]"]');x&&(x.value=""),S&&(S.value="")}};a.addEventListener("click",c),a.__removeHandler=c}})}document.querySelectorAll(".editform-penilaian-form-kriteria-block").forEach(e=>b(e)),document.getElementById("editform-penilaian-kriteria-container").addEventListener("click",function(e){if(e.target.classList.contains("editform-penilaian-add-sub-kriteria-block")||e.target.closest(".editform-penilaian-add-sub-kriteria-block")){const i=(e.target.classList.contains("editform-penilaian-add-sub-kriteria-block")?e.target:e.target.closest(".editform-penilaian-add-sub-kriteria-block")).closest(".editform-penilaian-form-kriteria-block"),t=i.getAttribute("data-kriteria-index"),r=i.querySelector(".editform-penilaian-form-wrapper-sub-kriteria");let a=i.querySelector(".editform-penilaian-form-group-item[data-sub-kriteria-index]");if(!a)return;typeof m[t]>"u"&&(m[t]=0),m[t]++;const o=m[t],f=a.cloneNode(!0);f.setAttribute("data-sub-kriteria-index",o),f.querySelectorAll("input, select, textarea").forEach(l=>{const p=l.getAttribute("name");if(p){const c=p.replace(/\[sub_kriteria\]\[\d+\]/,`[sub_kriteria][${o}]`).replace(/kriteria\[\d+\]/,`kriteria[${t}]`);l.setAttribute("name",c)}l.tagName==="SELECT"?l.selectedIndex=0:l.type==="checkbox"||l.type==="radio"?l.checked=!1:l.value=""});const k=f.querySelector(".editform-penilaian-ket-tipe-wrapper");k&&(k.innerHTML=`
                    <div class="input-group mb-2">
                        <input type="text" name="kriteria[${t}][sub_kriteria][${o}][ket_tipe][]" class="form-control" placeholder="Masukkan keterangan tipe">
                        <input type="text" name="kriteria[${t}][sub_kriteria][${o}][nilai_ket_tipe][]" class="form-control" placeholder="Nilai tipe...">
                        <button type="button" class="btn btn-danger btn-sm editform-penilaian-remove-ket-tipe"><i class="fa-solid fa-trash-can"></i></button>
                    </div>
                    <button type="button" class="editform-penilaian-btn-action success btn-sm editform-penilaian-add-ket-tipe mt-2"><i class="fa-solid fa-plus"></i> Tambah Keterangan</button>
                `),r.appendChild(f),b(i)}if(e.target.closest(".editform-penilaian-remove-sub-kriteria-block")){const s=e.target.closest(".editform-penilaian-form-group-item[data-sub-kriteria-index]");if(e.target.closest(".editform-penilaian-form-kriteria-block").querySelectorAll(".editform-penilaian-form-group-item[data-sub-kriteria-index]").length<=1)return;const r=s.querySelector('input[name*="[id_judul_kategori]"]')?.value;r&&y.push(r),s.remove()}if(e.target.closest(".editform-penilaian-remove-kriteria-block")){if(document.querySelectorAll(".editform-penilaian-form-kriteria-block").length<=1)return;const i=e.target.closest(".editform-penilaian-form-kriteria-block"),t=i.querySelector('input[name*="[id_nama_penilaian]"]')?.value;t&&v.push(t),i.remove()}}),document.getElementById("editform-penilaian-add-kriteria-main-block").addEventListener("click",()=>{const e=document.getElementById("editform-penilaian-kriteria-container"),i=(document.querySelector('.editform-penilaian-form-kriteria-block[data-kriteria-index="0"]')||document.querySelector(".editform-penilaian-form-kriteria-block")).cloneNode(!0);n++,m[n]=0,i.setAttribute("data-kriteria-index",n),i.querySelectorAll("input, select, textarea").forEach(r=>{const a=r.getAttribute("name");if(a){const o=a.replace(/kriteria\[\d+\]/,`kriteria[${n}]`);r.setAttribute("name",o)}r.tagName==="SELECT"?r.selectedIndex=0:r.type==="checkbox"||r.type==="radio"?r.checked=!1:r.value=""});const t=i.querySelector(".editform-penilaian-form-wrapper-sub-kriteria");t.innerHTML=`
            <div class="editform-penilaian-form-group-item" data-sub-kriteria-index="0">
                <button type="button" class="editform-penilaian-btn-remove-block editform-penilaian-remove-sub-kriteria-block" title="Hapus Sub Kriteria">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Judul Sub Kriteria <span class="text-danger">*</span></label>
                        <input type="text" name="kriteria[${n}][sub_kriteria][0][judul_kategori]" class="form-control" placeholder="Masukan sub kriteria..." maxlength="250" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tipe <span class="text-danger">*</span></label>
                        <select name="kriteria[${n}][sub_kriteria][0][tipe_kategori]" class="form-select editform-penilaian-tipe-kategori" required>
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
                            <input type="text" name="kriteria[${n}][sub_kriteria][0][ket_tipe][]" class="form-control" placeholder="Masukkan keterangan tipe">
                            <input type="text" name="kriteria[${n}][sub_kriteria][0][nilai_ket_tipe][]" class="form-control" placeholder="Nilai tipe...">
                            <button type="button" class="btn btn-danger btn-sm editform-penilaian-remove-ket-tipe"><i class="fa-solid fa-trash-can"></i></button>
                        </div>
                        <button type="button" class="editform-penilaian-btn-action success btn-sm editform-penilaian-add-ket-tipe mt-2"><i class="fa-solid fa-plus"></i> Tambah Keterangan</button>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Bobot <span class="text-danger">*</span></label>
                        <input type="number" name="kriteria[${n}][sub_kriteria][0][bobot]" class="form-control" placeholder="Masukan bobot..." required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Level <span class="text-danger">*</span></label>
                        <select name="kriteria[${n}][sub_kriteria][0][level]" class="form-select" required>
                            <option disabled selected>Pilih</option>
                            <option value="required">Harus Diisi</option>
                            <option value="null">Tidak Harus</option>
                        </select>
                    </div>
                </div>
            </div>
        `,e.appendChild(i),b(i)}),document.querySelector("form").addEventListener("submit",function(){if(!document.querySelector('input[name="deleted_form_ids"]')){let e=document.createElement("input");e.type="hidden",e.name="deleted_form_ids",e.value=v.join(","),this.appendChild(e)}if(!document.querySelector('input[name="deleted_kategori_ids"]')){let e=document.createElement("input");e.type="hidden",e.name="deleted_kategori_ids",e.value=y.join(","),this.appendChild(e)}if(!document.querySelector('input[name="deleted_tipe_ids"]')){let e=document.createElement("input");e.type="hidden",e.name="deleted_tipe_ids",e.value=_.join(","),this.appendChild(e)}})});
