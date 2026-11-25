let o=[],u=null,p=null,f="",m=null;document.addEventListener("DOMContentLoaded",()=>{c();const n=document.getElementById("selectPeriode");n&&n.addEventListener("change",function(){m=this.value,c(m)})});function c(n=null){document.getElementById("personal-skeleton-container").style.display="block",document.getElementById("personal-real-container").style.display="none";let i=window.personalConfig.routes.getData;n&&(i+=`?tahun=${encodeURIComponent(n)}`),document.getElementById("groupButtonJenisPenilaian").innerHTML=`
        <div class="personal-loading-state w-100">
            <i class="fa-solid fa-spinner fa-spin"></i>
            <p>Memuat jenis penilaian...</p>
        </div>
    `,document.getElementById("groupButtonEvaluator").innerHTML=`
        <div class="personal-empty-state w-100">
            <i class="fa-solid fa-hourglass-start"></i>
            <p>Memuat evaluator...</p>
        </div>
    `,document.getElementById("formContainer").innerHTML=`
        <div class="personal-loading-state">
            <i class="fa-solid fa-spinner fa-spin"></i>
            <p>Memuat form penilaian...</p>
        </div>
    `,$.ajax({url:i,type:"GET",dataType:"json",success:function(a){var l;if(!a||a.message){document.getElementById("groupButtonJenisPenilaian").innerHTML=`
                    <div class="personal-empty-state w-100">
                        <i class="fa-solid fa-inbox"></i>
                        <p>${a.message??"Data kosong"}</p>
                    </div>
                `,document.getElementById("groupButtonEvaluator").innerHTML=`
                    <div class="personal-empty-state w-100">
                        <i class="fa-solid fa-minus"></i>
                        <p>-</p>
                    </div>
                `,document.getElementById("formContainer").innerHTML=`
                    <div class="personal-empty-state">
                        <i class="fa-solid fa-inbox"></i>
                        <p>Data kosong</p>
                    </div>
                `,r();return}if(a.listPeriode&&document.getElementById("selectPeriode").options.length===0){let e="";a.listPeriode.forEach(t=>{let s=t.tahun==a.tahun?"selected":"";e+=`<option value="${t.tahun}" ${s}>${t.label}</option>`}),document.getElementById("selectPeriode").innerHTML=e}v(a),o=Array.isArray(a.data)?a.data:[],f=((l=a.nama_evaluated)==null?void 0:l[0])??"-",o.length>0?y(o):(document.getElementById("groupButtonJenisPenilaian").innerHTML=`
                    <div class="personal-empty-state w-100">
                        <i class="fa-solid fa-inbox"></i>
                        <p>Tidak ada data penilaian</p>
                    </div>
                `,document.getElementById("groupButtonEvaluator").innerHTML=`
                    <div class="personal-empty-state w-100">
                        <i class="fa-solid fa-minus"></i>
                        <p>-</p>
                    </div>
                `,document.getElementById("formContainer").innerHTML=`
                    <div class="personal-empty-state w-100">
                        <i class="fa-solid fa-inbox"></i>
                        <p>Tidak ada form penilaian</p>
                    </div>
                `),r()},error:function(a){document.getElementById("groupButtonJenisPenilaian").innerHTML=`
                <div class="personal-empty-state w-100">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <p>Gagal memuat data</p>
                </div>
            `,document.getElementById("groupButtonEvaluator").innerHTML=`
                <div class="personal-empty-state w-100">
                    <i class="fa-solid fa-minus"></i>
                    <p>-</p>
                </div>
            `,document.getElementById("formContainer").innerHTML=`
                <div class="personal-empty-state">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <p>Gagal memuat data</p>
                </div>
            `,console.error(a.responseText),r()}})}function r(){document.getElementById("personal-skeleton-container").style.display="none",document.getElementById("personal-real-container").style.display="block"}function v(n){const i=n.dataAbsen??{};document.getElementById("content_body_absen").innerHTML=`
        <tr>
            <td>${i.telat??0}</td>
            <td>${i.sakit??0}</td>
            <td>${i.izin??0}</td>
        </tr>
    `;let a="Belum ada catatan mengenai Anda.";Array.isArray(n.catatan)?a=n.catatan.join("<br>"):n.catatan&&n.catatan!=="null"&&(a=n.catatan),document.getElementById("content_footer_absen").innerHTML=`
        <div class="personal-catatan-box">
            <div class="catatan-label">
                <i class="fa-solid fa-calendar-day"></i> Periode Absensi
            </div>
            <div class="catatan-content">
                Tahun <strong>${n.tahun??"-"}</strong>
            </div>
        </div>
        <div class="personal-catatan-box">
            <div class="catatan-label">
                <i class="fa-solid fa-note-sticky"></i> Catatan
            </div>
            <div class="catatan-content">${a}</div>
        </div>
    `}function y(n){const i=document.getElementById("groupButtonJenisPenilaian");i.innerHTML="";const a={"General Manager":"fa-crown","Manager/SPV/Team Leader (Atasan Langsung)":"fa-user-tie","Rekan Kerja (Satu Divisi)":"fa-users","Pekerja (Beda Divisi)":"fa-people-arrows","Self Apprisial":"fa-user-check"};n.forEach(e=>{const t=a[e.jenis_penilaian]||"fa-file-lines",s=document.createElement("button");s.className="personal-tab-btn personal-jenis-btn",s.setAttribute("data-jenis",e.jenis_penilaian),s.innerHTML=`<i class="fa-solid ${t} me-1"></i> ${e.jenis_penilaian}`,i.appendChild(s)}),document.querySelectorAll(".personal-jenis-btn").forEach(e=>{e.addEventListener("click",function(){document.querySelectorAll(".personal-jenis-btn").forEach(t=>t.classList.remove("active")),this.classList.add("active"),u=this.getAttribute("data-jenis"),g(u)})});const l=document.querySelector(".personal-jenis-btn");l&&l.click()}function g(n){const i=o.find(e=>e.jenis_penilaian===n);if(!i)return;const a=document.getElementById("groupButtonEvaluator");if(a.innerHTML="",!i.evaluator||!Array.isArray(i.evaluator)||i.evaluator.length===0){a.innerHTML=`
            <div class="personal-empty-state w-100">
                <i class="fa-solid fa-inbox"></i>
                <p>Tidak ada evaluator</p>
            </div>
        `,document.getElementById("formContainer").innerHTML=`
            <div class="personal-empty-state w-100">
                <i class="fa-solid fa-inbox"></i>
                <p>Tidak ada form penilaian</p>
            </div>
        `;return}i.evaluator.forEach((e,t)=>{const s=document.createElement("button");s.className="personal-evaluator-pill personal-evaluator-btn",s.setAttribute("data-nama",e.nama_evaluator),s.innerHTML=`
            <span class="pill-number">${t+1}</span>
            <span>Evaluator ${t+1}</span>
        `,a.appendChild(s)}),document.querySelectorAll(".personal-evaluator-btn").forEach(e=>{e.addEventListener("click",function(){document.querySelectorAll(".personal-evaluator-btn").forEach(t=>t.classList.remove("active")),this.classList.add("active"),p=this.getAttribute("data-nama"),b(n,p)})});const l=document.querySelector(".personal-evaluator-btn");l&&l.click()}function b(n,i){const a=o.find(t=>t.jenis_penilaian===n),l=a==null?void 0:a.evaluator.find(t=>t.nama_evaluator===i);if(!l){document.getElementById("formContainer").innerHTML=`
            <div class="personal-empty-state w-100">
                <i class="fa-solid fa-inbox"></i>
                <p>Data evaluator tidak ditemukan</p>
            </div>
        `;return}let e=`
        <div class="personal-form-header-card">
            <h4><i class="fa-solid fa-clipboard-check me-2" style="color: #4f46e5;"></i>Penilaian ${n}</h4>
            <span class="evaluated-name">
                <i class="fa-solid fa-user"></i> ${f}
            </span>
        </div>
    `;l.kriteria&&Array.isArray(l.kriteria)&&l.kriteria.length>0?l.kriteria.forEach(t=>{e+='<div class="personal-kriteria-section">',e+=`<div class="personal-kriteria-header">${t.kriteria??"-"}</div>`,t.subKriteria&&Array.isArray(t.subKriteria)&&t.subKriteria.length>0?t.subKriteria.forEach(s=>{const d=s.deskripsi?s.deskripsi.toString().trim():"";e+=`
                        <div class="personal-sub-kriteria-item">
                            <div class="sub-label">${s.subKriteria??"-"}</div>
                            <div class="personal-nilai-display">
                                <i class="fa-solid fa-star me-1" style="font-size: 0.85rem;"></i>
                                ${s.nilai??"-"}
                            </div>
                            ${d&&d!=="null"&&d!==""?`<div class="personal-deskripsi-display"><i class="fa-solid fa-comment-dots me-1" style="color: #4f46e5;"></i> ${s.deskripsi}</div>`:""}
                        </div>
                    `}):e+=`
                    <div class="personal-empty-state">
                        <i class="fa-solid fa-inbox"></i>
                        <p>Tidak ada sub kriteria</p>
                    </div>
                `,e+="</div>"}):e+=`
            <div class="personal-empty-state">
                <i class="fa-solid fa-inbox"></i>
                <p>Tidak ada kriteria</p>
            </div>
        `,document.getElementById("formContainer").innerHTML=e}Object.assign(window,{loadData:c,toggleContainers:r,renderAbsen:v,renderJenisPenilaian:y,renderEvaluators:g,renderForm:b});
