(function () {
  const MAX_SIZE_MB = 2;
  const MAX_SIZE_BYTES = MAX_SIZE_MB * 1024 * 1024; // 2 MB (2.097.152 Bytes)

  const PDFJS_CDN_URL = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js';
  const PDFJS_WORKER_URL = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
  const JSPDF_CDN_URL = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';

  /**
   * Profil iterasi kompresi PDF bertahap (Multi-Pass).
   * Mulai dari kualitas & DPI tinggi, lalu turun bertahap jika ukuran PDF masih >= 2 MB,
   * dengan batas bawah (floor) yang tetap menjaga keterbacaan teks.
   */
  const PDF_COMPRESSION_PASSES = [
    // Iterasi 1: ~115 DPI, Kualitas tinggi (teks sangat tajam)
    {
      pass: 1,
      renderScale: 1.60,
      maxDimPx: 1800,
      minShortEdgePx: 595,
      initialQuality: 0.82,
      minQuality: 0.62,
      minPageScale: 0.85,
      budgetRatio: 0.90,
    },
    // Iterasi 2: ~100 DPI, Kualitas menengah-tinggi
    {
      pass: 2,
      renderScale: 1.40,
      maxDimPx: 1550,
      minShortEdgePx: 540,
      initialQuality: 0.75,
      minQuality: 0.55,
      minPageScale: 0.80,
      budgetRatio: 0.86,
    },
    // Iterasi 3: ~86 DPI, Kualitas menengah
    {
      pass: 3,
      renderScale: 1.20,
      maxDimPx: 1350,
      minShortEdgePx: 490,
      initialQuality: 0.68,
      minQuality: 0.48,
      minPageScale: 0.76,
      budgetRatio: 0.82,
    },
    // Iterasi 4: ~76 DPI, Kualitas kompak
    {
      pass: 4,
      renderScale: 1.05,
      maxDimPx: 1180,
      minShortEdgePx: 440,
      initialQuality: 0.60,
      minQuality: 0.42,
      minPageScale: 0.72,
      budgetRatio: 0.78,
    },
    // Iterasi 5: ~65 DPI, Batas maksimal kompresi dengan teks tetap terbaca
    {
      pass: 5,
      renderScale: 0.90,
      maxDimPx: 1024,
      minShortEdgePx: 390,
      initialQuality: 0.52,
      minQuality: 0.38,
      minPageScale: 0.68,
      budgetRatio: 0.72,
    },
  ];

  // Pelacak proses kompresi aktif per elemen <input>
  const activeInputPromises = new Map();

  // Konfigurasi awal Worker pdf.js jika sudah tersedia saat script dimuat
  configurePdfWorker();

  function configurePdfWorker() {
    if (window.pdfjsLib && window.pdfjsLib.GlobalWorkerOptions) {
      if (!window.pdfjsLib.GlobalWorkerOptions.workerSrc) {
        window.pdfjsLib.GlobalWorkerOptions.workerSrc = PDFJS_WORKER_URL;
      }
    }
  }

  /**
   * Memastikan library pdf.js dan jsPDF sudah siap digunakan (mengatasi race condition script defer / layout).
   */
  async function ensurePdfLibrariesLoaded() {
    if (!window.pdfjsLib) {
      await loadExternalScript(PDFJS_CDN_URL, () => Boolean(window.pdfjsLib));
    }
    configurePdfWorker();

    if (!window.jspdf || !window.jspdf.jsPDF) {
      await loadExternalScript(JSPDF_CDN_URL, () => Boolean(window.jspdf && window.jspdf.jsPDF));
    }

    if (!window.pdfjsLib || !window.jspdf || !window.jspdf.jsPDF) {
      throw new Error('Pustaka pdf.js atau jsPDF gagal dimuat. Periksa koneksi internet Anda.');
    }
  }

  function loadExternalScript(src, checkReadyFn, timeoutMs = 15000) {
    return new Promise((resolve, reject) => {
      if (checkReadyFn()) {
        resolve();
        return;
      }

      let existingScript = Array.from(document.querySelectorAll('script')).find(
        (s) => s.src && s.src.includes(src.split('/').pop())
      );

      const startTime = Date.now();
      const pollInterval = setInterval(() => {
        if (checkReadyFn()) {
          clearInterval(pollInterval);
          resolve();
        } else if (Date.now() - startTime > timeoutMs) {
          clearInterval(pollInterval);
          reject(new Error(`Timeout saat memuat pustaka: ${src}`));
        }
      }, 50);

      if (!existingScript) {
        const script = document.createElement('script');
        script.src = src;
        script.async = true;
        script.onload = () => {
          if (checkReadyFn()) {
            clearInterval(pollInterval);
            resolve();
          }
        };
        script.onerror = () => {
          clearInterval(pollInterval);
          reject(new Error(`Gagal memuat pustaka dari CDN: ${src}`));
        };
        document.head.appendChild(script);
      }
    });
  }

  /**
   * Menampilkan pesan error/peringatan secara konsisten (SweetAlert2 dengan fallback alert).
   */
  function notifyUser(title, message, icon = 'error') {
    if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
      Swal.fire({
        icon: icon,
        title: title,
        text: message,
        confirmButtonText: 'OK',
        allowOutsideClick: false,
      });
    } else {
      alert(`${title}\n${message}`);
    }
  }

  /**
   * Deteksi tipe file PDF dan Gambar yang tahan terhadap kosongnya MIME type dari OS/Browser.
   */
  function isPdfFile(file) {
    if (!file) return false;
    const mime = (file.type || '').toLowerCase();
    const name = (file.name || '').toLowerCase();
    return mime === 'application/pdf' || mime === 'application/x-pdf' || name.endsWith('.pdf');
  }

  function isCompressibleImageFile(file) {
    if (!file) return false;
    const mime = (file.type || '').toLowerCase();
    const name = (file.name || '').toLowerCase();
    if (mime === 'image/svg+xml' || mime === 'image/gif' || name.endsWith('.svg') || name.endsWith('.gif')) {
      return false;
    }
    return (
      mime.startsWith('image/') ||
      /\.(jpe?g|png|webp|bmp)$/i.test(name)
    );
  }

  /**
   * Mengambil seluruh tombol submit/aksi yang terkait dengan input file (mendukung multiple buttons & atribut form="id").
   */
  function getRelatedSubmitButtons(input) {
    const buttons = new Set();
    const form = input.closest('form');

    if (form) {
      form
        .querySelectorAll('button[type="submit"], input[type="submit"], button:not([type])')
        .forEach((btn) => buttons.add(btn));

      if (form.id) {
        document
          .querySelectorAll(`[form="${CSS.escape(form.id)}"][type="submit"], button[form="${CSS.escape(form.id)}"]:not([type])`)
          .forEach((btn) => buttons.add(btn));
      }
    } else {
      const container = input.closest('.modal, .card, .offcanvas');
      if (container) {
        container
          .querySelectorAll('button[type="submit"], input[type="submit"]')
          .forEach((btn) => buttons.add(btn));
      }
    }

    return Array.from(buttons);
  }

  /**
   * Mengunci tombol submit dengan penghitung referensi agar aman untuk banyak input file dalam satu form.
   */
  function lockFormAndButtons(input, submitButtons) {
    const form = input.closest('form');
    if (form) {
      const count = parseInt(form.dataset.activeCompressions || '0', 10) + 1;
      form.dataset.activeCompressions = String(count);
      form.dataset.isCompressing = 'true';
    }

    submitButtons.forEach((btn) => {
      const lockCount = parseInt(btn.dataset.compressLockCount || '0', 10) + 1;
      btn.dataset.compressLockCount = String(lockCount);

      if (lockCount === 1) {
        btn.dataset.wasDisabledBeforeCompress = btn.disabled ? 'true' : 'false';
        if (btn.tagName === 'INPUT') {
          btn.dataset.originalCompressText = btn.value;
          btn.value = 'Memproses file...';
        } else {
          btn.dataset.originalCompressText = btn.innerHTML;
          btn.innerHTML = 'Memproses file...';
        }
      }
      btn.disabled = true;
    });
  }

  function updateButtonsProgress(submitButtons, text) {
    submitButtons.forEach((btn) => {
      if (btn.tagName === 'INPUT') {
        btn.value = text;
      } else {
        btn.innerHTML = text;
      }
    });
  }

  function unlockFormAndButtons(input, submitButtons) {
    const form = input.closest('form');
    if (form) {
      const count = Math.max(0, parseInt(form.dataset.activeCompressions || '1', 10) - 1);
      if (count === 0) {
        delete form.dataset.activeCompressions;
        delete form.dataset.isCompressing;
      } else {
        form.dataset.activeCompressions = String(count);
      }
    }

    submitButtons.forEach((btn) => {
      const lockCount = Math.max(0, parseInt(btn.dataset.compressLockCount || '1', 10) - 1);
      if (lockCount === 0) {
        delete btn.dataset.compressLockCount;
        const wasDisabled = btn.dataset.wasDisabledBeforeCompress === 'true';
        delete btn.dataset.wasDisabledBeforeCompress;

        if (btn.dataset.originalCompressText !== undefined) {
          if (btn.tagName === 'INPUT') {
            btn.value = btn.dataset.originalCompressText;
          } else {
            btn.innerHTML = btn.dataset.originalCompressText;
          }
          delete btn.dataset.originalCompressText;
        }

        btn.disabled = wasDisabled;
      } else {
        btn.dataset.compressLockCount = String(lockCount);
      }
    });
  }

  /**
   * Cek apakah suatu form atau elemen masih memiliki proses kompresi yang belum selesai.
   */
  function isScopeCompressing(scopeEl) {
    const root = scopeEl || document;
    if (root !== document && root.dataset && root.dataset.isCompressing === 'true') {
      return true;
    }
    if (root.querySelector && root.querySelector('input[type="file"][data-is-processing="true"]')) {
      return true;
    }
    return false;
  }

  /**
   * ============================================================================
   * 1. INTERSEPSI SUBMIT & CLICK SELAMA PROSES ASYNC KOMPRESI BELUM SELESAI
   * ============================================================================
   */
  document.addEventListener(
    'submit',
    function (event) {
      const form = event.target;
      if (!form || form.tagName !== 'FORM') return;

      const hasExternalInputProcessing =
        form.id &&
        document.querySelector(`input[type="file"][form="${CSS.escape(form.id)}"][data-is-processing="true"]`);

      if (isScopeCompressing(form) || hasExternalInputProcessing) {
        event.preventDefault();
        event.stopImmediatePropagation();
        event.stopPropagation();
        notifyUser(
          'Kompresi File Sedang Berlangsung',
          'Mohon tunggu hingga proses kompresi file selesai sebelum mengirim formulir.',
          'warning'
        );
      }
    },
    true
  );

  document.addEventListener(
    'click',
    function (event) {
      const btn = event.target && event.target.closest
        ? event.target.closest('button[type="submit"], input[type="submit"], button:not([type])')
        : null;
      if (!btn) return;

      const form = btn.form || btn.closest('form');
      if (form && isScopeCompressing(form)) {
        event.preventDefault();
        event.stopImmediatePropagation();
        event.stopPropagation();
        notifyUser(
          'Kompresi File Sedang Berlangsung',
          'Mohon tunggu hingga proses kompresi file selesai sebelum melanjutkan.',
          'warning'
        );
      }
    },
    true
  );

  /**
   * ============================================================================
   * 2. GLOBAL CAPTURE LISTENER UNTUK SELURUH <input type="file">
   * ============================================================================
   */
  document.addEventListener(
    'change',
    async function (event) {
      const input = event.target;

      if (!input || input.tagName !== 'INPUT' || input.type !== 'file') {
        return;
      }

      // Lewati event 'change' sintetis yang kita dispatch setelah kompresi selesai
      if (input.dataset.compressBypass === 'true') {
        delete input.dataset.compressBypass;
        return;
      }

      // Jika sedang diproses, tahan propagasi event duplikat
      if (input.dataset.isProcessing === 'true') {
        event.preventDefault();
        event.stopImmediatePropagation();
        event.stopPropagation();
        return;
      }

      if (!input.files || !input.files.length) {
        return;
      }

      const files = Array.from(input.files);

      // Jika seluruh file sudah berukuran <= 2 MB, lewati proses kompresi sepenuhnya
      const needsProcessing = files.some((file) => file.size > MAX_SIZE_BYTES);
      if (!needsProcessing) {
        return;
      }

      // Hentikan propagasi sinkron ke listener lain (validasi lanjut / auto-upload / preview)
      // sampai proses kompresi async benar-benar selesai!
      event.stopImmediatePropagation();
      event.stopPropagation();

      input.dataset.isProcessing = 'true';
      input.setAttribute('data-is-processing', 'true');
      input.style.opacity = '0.6';
      input.style.pointerEvents = 'none';

      const submitButtons = getRelatedSubmitButtons(input);
      lockFormAndButtons(input, submitButtons);

      const dataTransfer = new DataTransfer();
      const errorMessages = [];

      const processTask = (async () => {
        try {
          for (const file of files) {
            // 1. Lewati jika ukuran file sudah <= 2 MB
            if (file.size <= MAX_SIZE_BYTES) {
              dataTransfer.items.add(file);
              continue;
            }

            const sizeInMB = (file.size / 1024 / 1024).toFixed(2);

            try {
              // 2. Kompresi Otomatis untuk PDF (> 2 MB -> target akhir < 2 MB)
              if (isPdfFile(file)) {
                console.log(`[Compressor] Mengompres PDF: ${file.name} (${sizeInMB} MB)...`);
                const compressedPdf = await compressPdfToTarget(
                  file,
                  MAX_SIZE_BYTES,
                  (progressText) => updateButtonsProgress(submitButtons, progressText)
                );

                if (compressedPdf.size >= MAX_SIZE_BYTES) {
                  throw new Error(
                    `File PDF "${file.name}" masih berukuran ${(compressedPdf.size / 1024 / 1024).toFixed(2)} MB setelah kompresi maksimal (batas < ${MAX_SIZE_MB} MB).`
                  );
                }

                console.log(
                  `[Compressor] Selesai PDF: ${compressedPdf.name} (${(compressedPdf.size / 1024 / 1024).toFixed(2)} MB)`
                );
                dataTransfer.items.add(compressedPdf);
              }
              // 3. Kompresi Otomatis untuk Gambar (> 2 MB -> target akhir < 2 MB)
              else if (isCompressibleImageFile(file)) {
                console.log(`[Compressor] Mengompres gambar: ${file.name} (${sizeInMB} MB)...`);
                updateButtonsProgress(submitButtons, 'Mengompres gambar...');
                const compressedImage = await compressImageToTarget(file, MAX_SIZE_BYTES);

                if (compressedImage.size >= MAX_SIZE_BYTES) {
                  throw new Error(
                    `Gambar "${file.name}" masih berukuran ${(compressedImage.size / 1024 / 1024).toFixed(2)} MB setelah kompresi maksimal (batas < ${MAX_SIZE_MB} MB).`
                  );
                }

                console.log(
                  `[Compressor] Selesai Gambar: ${compressedImage.name} (${(compressedImage.size / 1024 / 1024).toFixed(2)} MB)`
                );
                dataTransfer.items.add(compressedImage);
              }
              // 4. File selain Gambar & PDF yang melebihi 2 MB
              else {
                throw new Error(
                  `File "${file.name}" (${sizeInMB} MB) melebihi batas ${MAX_SIZE_MB} MB dan tipe file ini tidak dapat dikompres otomatis.`
                );
              }
            } catch (fileErr) {
              console.error(`[Compressor] Gagal memproses "${file.name}":`, fileErr);
              errorMessages.push(fileErr.message || `Gagal mengompres file "${file.name}".`);
            }
          }
        } finally {
          // Pastikan input.files selalu diperbarui hanya dengan file yang valid (< 2 MB)
          // File yang gagal dikompres atau tetap >= 2 MB tidak boleh tertinggal di input.files
          try {
            input.files = dataTransfer.files;
            if (dataTransfer.files.length === 0) {
              input.value = '';
            }
          } catch (assignErr) {
            console.warn('[Compressor] Gagal memperbarui input.files, mereset nilai input:', assignErr);
            input.value = '';
          }

          delete input.dataset.isProcessing;
          input.removeAttribute('data-is-processing');
          input.style.opacity = '1';
          input.style.pointerEvents = '';
          activeInputPromises.delete(input);

          unlockFormAndButtons(input, submitButtons);
        }
      })();

      activeInputPromises.set(input, processTask);
      await processTask;

      // Tampilkan pesan error yang jelas jika ada file yang gagal dikompres atau tetap >= 2 MB
      if (errorMessages.length > 0) {
        notifyUser(
          'Gagal Memproses File',
          errorMessages.join('\n\n'),
          'error'
        );
      }

      // Picu kembali event 'change' setelah input.files berisi file hasil kompresi (< 2 MB)
      // agar handler validasi/preview/upload di halaman berjalan dengan file yang sudah siap.
      input.dataset.compressBypass = 'true';
      input.dispatchEvent(new Event('change', { bubbles: true, cancelable: true }));
    },
    true
  );

  /**
   * ============================================================================
   * 3. FUNGSI KOMPRESI PDF BERTAHAP / MULTI-PASS (pdf.js + jsPDF)
   * ============================================================================
   * Melakukan iterasi penurunan DPI (renderScale) dan kualitas JPEG secara bertahap
   * hingga ukuran akhir PDF < targetBytes (< 2 MB) tanpa merusak keterbacaan teks.
   */
  async function compressPdfToTarget(file, targetBytes, onProgress) {
    await ensurePdfLibrariesLoaded();

    const { jsPDF } = window.jspdf;
    let arrayBuffer;
    try {
      arrayBuffer = await file.arrayBuffer();
    } catch (readErr) {
      throw new Error(`Gagal membaca file PDF "${file.name}".`);
    }

    let pdf;
    try {
      pdf = await window.pdfjsLib.getDocument({ data: arrayBuffer }).promise;
    } catch (pdfErr) {
      if (pdfErr && pdfErr.name === 'PasswordException') {
        throw new Error(
          `File PDF "${file.name}" dilindungi password sehingga tidak dapat dikompres otomatis. Silakan unggah PDF tanpa password atau berukuran <= ${MAX_SIZE_MB} MB.`
        );
      }
      throw new Error(
        `File PDF "${file.name}" rusak atau formatnya tidak valid sehingga tidak dapat dikompres.`
      );
    }

    const numPages = pdf.numPages;
    if (!numPages || numPages < 1) {
      throw new Error(`File PDF "${file.name}" tidak memiliki halaman yang valid.`);
    }

    // Jika jumlah halaman sangat banyak (> 30 halaman), mulai dari iterasi ke-2 untuk efisiensi
    const startPassIndex = numPages > 30 ? 1 : 0;
    let lastPdfBlob = null;
    let lastTotalImageBytes = 0;

    try {
      for (let passIdx = startPassIndex; passIdx < PDF_COMPRESSION_PASSES.length; passIdx++) {
        const passConfig = PDF_COMPRESSION_PASSES[passIdx];
        const totalPasses = PDF_COMPRESSION_PASSES.length;

        // Hitung total budget byte untuk seluruh gambar halaman pada iterasi ini.
        // Jika iterasi sebelumnya masih >= targetBytes, koreksi budget menggunakan rasio aktual & overhead jsPDF.
        let totalImageBudget = Math.floor(targetBytes * passConfig.budgetRatio);
        if (lastPdfBlob && lastPdfBlob.size >= targetBytes) {
          const measuredOverhead = Math.max(24 * 1024, lastPdfBlob.size - lastTotalImageBytes);
          const safeTargetForImages = Math.max(numPages * 4096, targetBytes - measuredOverhead - 32 * 1024);
          const correctionFactor = Math.min(0.95, (targetBytes * 0.96) / lastPdfBlob.size);
          const adjustedBudget = Math.floor(
            Math.min(safeTargetForImages, lastTotalImageBytes * correctionFactor)
          );
          totalImageBudget = Math.min(totalImageBudget, Math.max(numPages * 4096, adjustedBudget));
        }

        let newPdf = null;
        let accumulatedImageBytes = 0;

        for (let pageNum = 1; pageNum <= numPages; pageNum++) {
          if (onProgress) {
            const passLabel = passIdx > startPassIndex ? ` [Tahap ${passIdx + 1}/${totalPasses}]` : '';
            onProgress(`Mengompres PDF (${pageNum}/${numPages})${passLabel}...`);
          }

          // Beri kesempatan browser memperbarui UI
          await yieldToMainThread();

          const page = await pdf.getPage(pageNum);
          const originalViewport = page.getViewport({ scale: 1.0 });
          const widthPt = originalViewport.width;
          const heightPt = originalViewport.height;
          const orientation = widthPt > heightPt ? 'l' : 'p';

          // Hitung skala render (DPI) halaman dengan menjaga batas maksimum & keterbacaan teks
          const effectiveRenderScale = computeEffectiveRenderScale(
            widthPt,
            heightPt,
            numPages,
            passConfig
          );

          const renderViewport = page.getViewport({ scale: effectiveRenderScale });
          const canvas = document.createElement('canvas');
          const ctx = canvas.getContext('2d', { alpha: false });
          canvas.width = Math.max(1, Math.floor(renderViewport.width));
          canvas.height = Math.max(1, Math.floor(renderViewport.height));

          ctx.fillStyle = '#FFFFFF';
          ctx.fillRect(0, 0, canvas.width, canvas.height);

          await page.render({
            canvasContext: ctx,
            viewport: renderViewport,
          }).promise;

          // Alokasi sisa budget secara dinamis untuk halaman yang tersisa
          // (halaman teks ringan menyisakan budget ekstra untuk halaman bergambar padat)
          const remainingPages = numPages - pageNum + 1;
          const remainingBudget = Math.max(
            remainingPages * 4096,
            totalImageBudget - accumulatedImageBytes
          );
          const pageMaxBytes = Math.floor(remainingBudget / remainingPages);

          const { dataUrl, byteSize } = await compressCanvasToMaxBytes(
            canvas,
            pageMaxBytes,
            passConfig
          );
          accumulatedImageBytes += byteSize;

          // Bersihkan memori canvas & halaman pdf.js
          canvas.width = 0;
          canvas.height = 0;
          if (typeof page.cleanup === 'function') {
            page.cleanup();
          }

          if (pageNum === 1) {
            newPdf = new jsPDF({
              orientation: orientation,
              unit: 'pt',
              format: [widthPt, heightPt],
              compress: true,
            });
          } else {
            newPdf.addPage([widthPt, heightPt], orientation);
          }

          newPdf.addImage(dataUrl, 'JPEG', 0, 0, widthPt, heightPt, undefined, 'FAST');
        }

        const pdfBlob = newPdf.output('blob');
        lastPdfBlob = pdfBlob;
        lastTotalImageBytes = accumulatedImageBytes;

        console.log(
          `[Compressor] PDF "${file.name}" - Iterasi ${passConfig.pass}: ${(pdfBlob.size / 1024 / 1024).toFixed(2)} MB`
        );

        // Pastikan ukuran akhir PDF benar-benar < targetBytes (< 2 MB)
        if (pdfBlob.size < targetBytes) {
          return new File([pdfBlob], file.name, {
            type: 'application/pdf',
            lastModified: Date.now(),
          });
        }

        console.warn(
          `[Compressor] Iterasi ${passConfig.pass} menghasilkan ${(pdfBlob.size / 1024 / 1024).toFixed(2)} MB (masih >= ${MAX_SIZE_MB} MB). Melanjutkan penurunan DPI/kualitas...`
        );
      }
    } finally {
      if (pdf && typeof pdf.destroy === 'function') {
        try {
          await pdf.destroy();
        } catch (_) {}
      }
    }

    const finalSizeMB = lastPdfBlob ? (lastPdfBlob.size / 1024 / 1024).toFixed(2) : 'N/A';
    const origSizeMB = (file.size / 1024 / 1024).toFixed(2);
    throw new Error(
      `File PDF "${file.name}" (${origSizeMB} MB, ${numPages} halaman) tetap berukuran ${finalSizeMB} MB setelah kompresi maksimal tanpa merusak keterbacaan teks (batas maksimal < ${MAX_SIZE_MB} MB). Silakan kurangi jumlah halaman atau pisahkan dokumen.`
    );
  }

  /**
   * Menghitung skala render (DPI) yang optimal untuk halaman PDF pada iterasi tertentu.
   */
  function computeEffectiveRenderScale(widthPt, heightPt, numPages, passConfig) {
    let scale = passConfig.renderScale;

    if (numPages > 20) {
      scale = Math.max(passConfig.renderScale * 0.90, 0.85);
    }

    const maxPt = Math.max(widthPt, heightPt, 1);
    const minPt = Math.min(widthPt, heightPt, 1);

    // Batasi dimensi piksel maksimum agar halaman berformat besar tidak menghabiskan memori
    if (maxPt * scale > passConfig.maxDimPx) {
      scale = passConfig.maxDimPx / maxPt;
    }

    // Jaga batas minimum resolusi sisi pendek agar teks ukuran normal tetap terbaca
    if (minPt >= 300 && minPt * scale < passConfig.minShortEdgePx) {
      scale = Math.min(passConfig.renderScale, passConfig.minShortEdgePx / minPt);
    }

    return Math.max(0.65, scale);
  }

  /**
   * Mengompres sebuah <canvas> menjadi DataURL JPEG yang ukurannya <= maxBytes
   * dengan penurunan kualitas & skala yang seimbang (tanpa menghancurkan keterbacaan teks).
   */
  async function compressCanvasToMaxBytes(sourceCanvas, maxBytes, passConfig) {
    const initialQuality = passConfig ? passConfig.initialQuality : 0.80;
    const minQuality = passConfig ? passConfig.minQuality : 0.38;
    const minPageScale = passConfig ? passConfig.minPageScale : 0.68;
    const minShortEdgePx = passConfig ? passConfig.minShortEdgePx : 390;

    let quality = initialQuality;
    let scale = 1.0;
    let reduceQualityNext = true;

    const workCanvas = document.createElement('canvas');
    const workCtx = workCanvas.getContext('2d', { alpha: false });

    try {
      while (true) {
        const targetW = Math.max(1, Math.floor(sourceCanvas.width * scale));
        const targetH = Math.max(1, Math.floor(sourceCanvas.height * scale));

        workCanvas.width = targetW;
        workCanvas.height = targetH;

        workCtx.imageSmoothingEnabled = true;
        workCtx.imageSmoothingQuality = 'high';
        workCtx.fillStyle = '#FFFFFF';
        workCtx.fillRect(0, 0, targetW, targetH);
        workCtx.drawImage(sourceCanvas, 0, 0, targetW, targetH);

        const blob = await canvasToBlob(workCanvas, 'image/jpeg', Number(quality.toFixed(2)));
        const currentShortEdge = Math.min(targetW, targetH);
        const reachedQualityFloor = quality <= minQuality + 0.005;
        const reachedScaleFloor =
          scale <= minPageScale + 0.005 ||
          (Math.min(sourceCanvas.width, sourceCanvas.height) >= minShortEdgePx &&
            currentShortEdge <= minShortEdgePx);

        if (blob.size <= maxBytes || (reachedQualityFloor && reachedScaleFloor)) {
          const dataUrl = await blobToDataURL(blob);
          return { dataUrl, byteSize: blob.size };
        }

        // Turunkan kualitas dan resolusi secara bergantian & bertahap agar teks tidak pecah/artefak
        if ((reduceQualityNext && !reachedQualityFloor) || reachedScaleFloor) {
          quality = Math.max(minQuality, Number((quality - 0.07).toFixed(2)));
          reduceQualityNext = false;
        } else if (!reachedScaleFloor) {
          const nextScale = Number((scale * 0.90).toFixed(3));
          const minAllowedScaleByPx =
            Math.min(sourceCanvas.width, sourceCanvas.height) >= minShortEdgePx
              ? minShortEdgePx / Math.min(sourceCanvas.width, sourceCanvas.height)
              : minPageScale;
          scale = Math.max(minPageScale, minAllowedScaleByPx, nextScale);
          reduceQualityNext = true;
        } else {
          break;
        }
      }

      // Fallback safety jika keluar dari loop
      const fallbackBlob = await canvasToBlob(workCanvas, 'image/jpeg', Number(quality.toFixed(2)));
      const fallbackDataUrl = await blobToDataURL(fallbackBlob);
      return { dataUrl: fallbackDataUrl, byteSize: fallbackBlob.size };
    } finally {
      workCanvas.width = 0;
      workCanvas.height = 0;
    }
  }

  /**
   * ============================================================================
   * 4. FUNGSI KOMPRESI GAMBAR (JPEG, PNG, WebP) -> Target < 2 MB
   * ============================================================================
   */
  async function compressImageToTarget(file, targetBytes) {
    const img = await loadImage(file);
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');

    let scale = 1.0;
    let quality = 0.85;
    let reduceQualityNext = true;

    const outputType = file.type === 'image/webp' ? 'image/webp' : 'image/jpeg';
    const newFileName =
      file.name.replace(/\.[^/.]+$/, '') + (outputType === 'image/webp' ? '.webp' : '.jpg');

    let blob = null;

    try {
      while (true) {
        canvas.width = Math.max(1, Math.floor(img.width * scale));
        canvas.height = Math.max(1, Math.floor(img.height * scale));

        ctx.imageSmoothingEnabled = true;
        ctx.imageSmoothingQuality = 'high';

        if (outputType === 'image/jpeg') {
          ctx.fillStyle = '#FFFFFF';
          ctx.fillRect(0, 0, canvas.width, canvas.height);
        }

        ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
        blob = await canvasToBlob(canvas, outputType, Number(quality.toFixed(2)));

        const reachedQualityFloor = quality <= 0.30;
        const reachedScaleFloor = scale <= 0.25;

        if (blob.size < targetBytes || (reachedQualityFloor && reachedScaleFloor)) {
          break;
        }

        if ((reduceQualityNext && !reachedQualityFloor) || reachedScaleFloor) {
          quality = Math.max(0.30, Number((quality - 0.10).toFixed(2)));
          reduceQualityNext = false;
        } else {
          scale = Math.max(0.25, Number((scale * 0.85).toFixed(3)));
          reduceQualityNext = true;
        }
      }
    } finally {
      canvas.width = 0;
      canvas.height = 0;
    }

    if (!blob || blob.size >= targetBytes) {
      const finalMB = blob ? (blob.size / 1024 / 1024).toFixed(2) : 'N/A';
      throw new Error(
        `Gambar "${file.name}" tetap berukuran ${finalMB} MB setelah kompresi maksimal (batas maksimal < ${MAX_SIZE_MB} MB).`
      );
    }

    return new File([blob], newFileName, {
      type: outputType,
      lastModified: Date.now(),
    });
  }

  /**
   * ============================================================================
   * 5. HELPER FUNCTIONS & PUBLIC API
   * ============================================================================
   */
  function yieldToMainThread() {
    return new Promise((resolve) => setTimeout(resolve, 0));
  }

  function loadImage(file) {
    return new Promise((resolve, reject) => {
      const img = new Image();
      const url = URL.createObjectURL(file);
      img.onload = () => {
        URL.revokeObjectURL(url);
        resolve(img);
      };
      img.onerror = () => {
        URL.revokeObjectURL(url);
        reject(new Error(`File gambar "${file.name}" rusak atau tidak dapat dibaca.`));
      };
      img.src = url;
    });
  }

  function canvasToBlob(canvas, type, quality) {
    return new Promise((resolve, reject) => {
      canvas.toBlob(
        (blob) => {
          if (blob) {
            resolve(blob);
          } else {
            reject(new Error('Gagal mengonversi halaman ke format gambar terkompresi (canvas.toBlob null).'));
          }
        },
        type,
        quality
      );
    });
  }

  function blobToDataURL(blob) {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.onloadend = () => resolve(reader.result);
      reader.onerror = () => reject(new Error('Gagal membaca data hasil kompresi.'));
      reader.readAsDataURL(blob);
    });
  }

  // Ekspos API global untuk pengecekan sinkron/asinkron oleh modul validasi lain
  window.CompressValidator = {
    MAX_SIZE_MB,
    MAX_SIZE_BYTES,
    isProcessing: isScopeCompressing,
    waitForPending: async function (scopeEl) {
      const root = scopeEl || document;
      const inputs = root.querySelectorAll
        ? Array.from(root.querySelectorAll('input[type="file"]'))
        : [];
      const pending = inputs
        .map((inp) => activeInputPromises.get(inp))
        .filter(Boolean);
      if (pending.length > 0) {
        await Promise.allSettled(pending);
      }
    },
    compressPdfToTarget,
    compressImageToTarget,
  };
})();
