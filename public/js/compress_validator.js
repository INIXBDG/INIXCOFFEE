(function () {
  const MAX_SIZE_MB = 2;
  const MAX_SIZE_BYTES = MAX_SIZE_MB * 1024 * 1024; // 2MB dalam Bytes

  // Konfigurasi Worker untuk pdf.js
  if (window.pdfjsLib) {
    window.pdfjsLib.GlobalWorkerOptions.workerSrc =
      'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
  }

  // Global Event Listener untuk seluruh <input type="file">
  document.addEventListener('change', async function (event) {
    const input = event.target;

    if (input.tagName !== 'INPUT' || input.type !== 'file' || !input.files.length) {
      return;
    }

    if (input.dataset.isProcessing === 'true') return;
    input.dataset.isProcessing = 'true';

    // Kunci tombol submit & simpan teks aslinya selama proses kompresi
    const form = input.closest('form');
    const submitBtn = form ? form.querySelector('[type="submit"], button:not([type="button"])') : null;
    const originalBtnText = submitBtn ? submitBtn.innerHTML : '';

    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.innerHTML = 'Memproses file...';
    }
    input.style.opacity = '0.6';

    const dataTransfer = new DataTransfer();
    const files = Array.from(input.files);

    try {
      for (const file of files) {
        // Lewati jika ukuran file sudah <= 2MB
        if (file.size <= MAX_SIZE_BYTES) {
          dataTransfer.items.add(file);
          continue;
        }

        const sizeInMB = (file.size / 1024 / 1024).toFixed(2);

        // 1. Kompresi Otomatis untuk Gambar (JPEG, PNG, WebP)
        if (file.type.startsWith('image/')) {
          console.log(`[Compressor] Mengompres gambar: ${file.name} (${sizeInMB} MB)...`);
          const compressedImage = await compressImageToTarget(file, MAX_SIZE_BYTES);
          console.log(`[Compressor] Selesai: ${compressedImage.name} (${(compressedImage.size / 1024 / 1024).toFixed(2)} MB)`);
          dataTransfer.items.add(compressedImage);
        }
        // 2. Kompresi Otomatis untuk PDF
        else if (file.type === 'application/pdf') {
          console.log(`[Compressor] Mengompres PDF: ${file.name} (${sizeInMB} MB)...`);
          const compressedPdf = await compressPdfToTarget(file, MAX_SIZE_BYTES, (progressText) => {
            if (submitBtn) submitBtn.innerHTML = progressText;
          });
          console.log(`[Compressor] Selesai: ${compressedPdf.name} (${(compressedPdf.size / 1024 / 1024).toFixed(2)} MB)`);
          dataTransfer.items.add(compressedPdf);
        }
        // 3. File selain Gambar & PDF yang melebihi 2MB
        else {
          alert(`File "${file.name}" (${sizeInMB} MB) melebihi batas 2MB dan tidak dapat dikompres otomatis.`);
        }
      }

      // Perbarui file di dalam elemen <input>
      input.files = dataTransfer.files;

    } catch (error) {
      console.error('[Compressor] Gagal memproses file:', error);
      alert('Terjadi kesalahan saat mengompresi file: ' + error.message);
    } finally {
      delete input.dataset.isProcessing;
      input.style.opacity = '1';
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
      }
    }
  }, true);

  /**
   * ==========================================
   * FUNGSI KOMPRESI PDF (pdf.js + jsPDF)
   * ==========================================
   */
  async function compressPdfToTarget(file, targetBytes, onProgress) {
    if (!window.pdfjsLib || !window.jspdf) {
      throw new Error('Library pdf.js atau jsPDF belum dimuat di halaman ini.');
    }

    const { jsPDF } = window.jspdf;
    const arrayBuffer = await file.arrayBuffer();
    const pdf = await window.pdfjsLib.getDocument({ data: arrayBuffer }).promise;
    const numPages = pdf.numPages;

    // Alokasikan 90% dari 2MB untuk seluruh gambar halaman (sisanya 10% untuk metadata/struktur PDF)
    const maxBytesPerPage = Math.floor((targetBytes * 0.90) / numPages);

    // Tentukan skala render awal berdasarkan jumlah halaman agar teks tetap tajam
    let baseRenderScale = numPages > 15 ? 1.2 : 1.5;

    let newPdf = null;

    for (let pageNum = 1; pageNum <= numPages; pageNum++) {
      if (onProgress) {
        onProgress(`Mengompres PDF (${pageNum}/${numPages})...`);
      }

      const page = await pdf.getPage(pageNum);

      // Ukuran asli halaman dalam satuan point (pt)
      const originalViewport = page.getViewport({ scale: 1.0 });
      const widthPt = originalViewport.width;
      const heightPt = originalViewport.height;
      const orientation = widthPt > heightPt ? 'l' : 'p';

      // Render halaman PDF ke canvas utama
      const renderViewport = page.getViewport({ scale: baseRenderScale });
      const canvas = document.createElement('canvas');
      const ctx = canvas.getContext('2d');
      canvas.width = Math.floor(renderViewport.width);
      canvas.height = Math.floor(renderViewport.height);

      ctx.fillStyle = '#FFFFFF';
      ctx.fillRect(0, 0, canvas.width, canvas.height);

      await page.render({
        canvasContext: ctx,
        viewport: renderViewport,
      }).promise;

      // Kompres canvas halaman ini agar ukurannya <= maxBytesPerPage
      const compressedPageDataUrl = await compressCanvasToMaxBytes(canvas, maxBytesPerPage);

      // Tambahkan halaman ke dokumen jsPDF
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

      newPdf.addImage(compressedPageDataUrl, 'JPEG', 0, 0, widthPt, heightPt, undefined, 'FAST');
    }

    const pdfBlob = newPdf.output('blob');

    return new File([pdfBlob], file.name, {
      type: 'application/pdf',
      lastModified: Date.now(),
    });
  }

  /**
   * Mengompres sebuah <canvas> menjadi DataURL JPEG yang ukurannya <= maxBytes
   */
  async function compressCanvasToMaxBytes(sourceCanvas, maxBytes) {
    let quality = 0.80;
    let scale = 1.0;

    const workCanvas = document.createElement('canvas');
    const workCtx = workCanvas.getContext('2d');

    while (true) {
      workCanvas.width = Math.max(1, Math.floor(sourceCanvas.width * scale));
      workCanvas.height = Math.max(1, Math.floor(sourceCanvas.height * scale));

      workCtx.fillStyle = '#FFFFFF';
      workCtx.fillRect(0, 0, workCanvas.width, workCanvas.height);
      workCtx.drawImage(sourceCanvas, 0, 0, workCanvas.width, workCanvas.height);

      const blob = await canvasToBlob(workCanvas, 'image/jpeg', quality);

      if (blob.size <= maxBytes || (quality <= 0.25 && scale <= 0.35)) {
        return await blobToDataURL(blob);
      }

      // Turunkan kualitas JPEG dulu; jika masih terlalu besar, turunkan resolusi halaman
      if (quality > 0.35) {
        quality -= 0.15;
      } else {
        scale *= 0.80;
      }
    }
  }

  /**
   * ==========================================
   * FUNGSI KOMPRESI GAMBAR (JPEG, PNG, WebP)
   * ==========================================
   */
  async function compressImageToTarget(file, targetBytes) {
    const img = await loadImage(file);
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');

    let scale = 1.0;
    let quality = 0.85;

    const outputType = file.type === 'image/webp' ? 'image/webp' : 'image/jpeg';
    const newFileName = file.name.replace(/\.[^/.]+$/, '') + (outputType === 'image/webp' ? '.webp' : '.jpg');

    let blob = null;

    while (true) {
      canvas.width = Math.max(1, Math.floor(img.width * scale));
      canvas.height = Math.max(1, Math.floor(img.height * scale));

      if (outputType === 'image/jpeg') {
        ctx.fillStyle = '#FFFFFF';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
      }

      ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
      blob = await canvasToBlob(canvas, outputType, quality);

      if (blob.size <= targetBytes || (quality <= 0.2 && scale <= 0.3)) {
        break;
      }

      if (quality > 0.3) {
        quality -= 0.15;
      } else {
        scale *= 0.8;
      }
    }

    return new File([blob], newFileName, {
      type: outputType,
      lastModified: Date.now(),
    });
  }

  /**
   * ==========================================
   * HELPER FUNCTIONS
   * ==========================================
   */
  function loadImage(file) {
    return new Promise((resolve, reject) => {
      const img = new Image();
      const url = URL.createObjectURL(file);
      img.onload = () => {
        URL.revokeObjectURL(url);
        resolve(img);
      };
      img.onerror = (err) => {
        URL.revokeObjectURL(url);
        reject(err);
      };
      img.src = url;
    });
  }

  function canvasToBlob(canvas, type, quality) {
    return new Promise((resolve) => {
      canvas.toBlob((blob) => resolve(blob), type, quality);
    });
  }

  function blobToDataURL(blob) {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.onloadend = () => resolve(reader.result);
      reader.onerror = reject;
      reader.readAsDataURL(blob);
    });
  }
})();
