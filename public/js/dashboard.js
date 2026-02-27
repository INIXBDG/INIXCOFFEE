function initializeYearlySales() {
    let year = $('#tahun').val(); // Mengambil nilai dari dropdown
    if (year) {
        console.log(year); // Menampilkan nilai tahun di console untuk pengecekan
        getYearlySales(year);
        fetchPenjualanPerSalesPerTahun(year);
        fetchPenjualanPerSalesPerQuartal(year);
        fetchKelasAnalisis(year);
        fetchAbsen(year);
        fetchTabInix(year);
        fetchSouvenir(year);
        fetchTotalMengajar(year, '1');
        fetchTotalMateri(year, 'All');
        fetchTotalMengajarPerMateri(year, 'All');
        fetchAbsenPerbulan(year, 'All');
        dashboardEdu();
        // fetchNilaiFeedback(year, '1');
        // fetchJumlahPICData();
        // fetchJumlahTicketingData();
    }
}
function getYearlySales(year) {
    $.ajax({
        url: `/getYearlySales/${year}`, // URL with year parameter
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            let totalSales = response.totalSales || 0;
            let target = response.target || 0;
            let targetLabels = response.targetLabels.length ? response.targetLabels : Array(9).fill("0");

            // Update totalSalesDisplay element with formatted totalSales
            $('#totalSalesDisplay').text(formatRupiah(totalSales));

            // Update ruler labels and car position based on current totalSales and target
            updateRulerLabels(totalSales, target, targetLabels);
            updateCarPosition(totalSales, target);
        },
        error: function (xhr, status, error) {
            console.error('Error:', error);
        }
    });
}

function updateRulerLabels(totalSales, target, targetLabels) {
    let maxRange = Math.max(totalSales, target); // Set max range to the highest value between totalSales and target
    let isMobile = window.innerWidth <= 768;

    // Clear previous labels
    $('.horizontal-ruler-labels').empty();

    // Dynamically create labels based on targetLabels or totalSales
    $.each(targetLabels, function (index, label) {
        let labelValue = (index / (targetLabels.length - 1)) * maxRange;
        let labelPosition = (labelValue / maxRange) * 100;

        // Use totalSales-based dynamic labels if totalSales > target, otherwise use static targetLabels
        let displayLabel = totalSales > target ? formatTarget(labelValue) : label;

        let labelDiv = $('<div></div>', {
            class: 'label',
            text: displayLabel,
            css: {
                position: 'absolute',
                left: `${labelPosition}%`
            }
        });

        if (isMobile && index % 2 !== 0) {
            labelDiv.hide();
        }

        $('.horizontal-ruler-labels').append(labelDiv);
    });
}

function updateCarPosition(totalSales, target) {
    let maxRange = Math.max(totalSales, target); // Dinamis untuk memperpanjang rentang jika totalSales melebihi target
    let progress = (totalSales / maxRange) * 100; // Hitung progres berdasarkan maxRange
    let targetPosition = (target / maxRange) * 100; // Hitung posisi tujuan berdasarkan maxRange
    let car = $('#car');
    let goal = $('.target-label-right');

    let isMobile = window.innerWidth <= 600;
    let carPosition = progress - (isMobile ? 30 : 7); // Sesuaikan offset agar posisi mobil lebih akurat

    // Set posisi tujuan
    goal.css('left', `${targetPosition}%`);

    // Animasi progress bar terlebih dahulu
    $("#progress-bar").css({
        'width': `${progress}%`,
        'transition': 'width 10s ease' // Durasi animasi disesuaikan (misalnya, 3 detik)
    });
    console.log("Max Range:", maxRange, "Progress:", progress, "Target Position:", targetPosition, "Car", carPosition);

    // Tunggu sampai progress bar selesai, lalu animasi mobil
    setTimeout(() => {
        car.css('left', `${carPosition}%`); // Pindahkan mobil sesuai posisi progres
        car.css('transition', 'left 10s ease-in-out'); // Set animasi mobil, misalnya 2 detik
    }, 10000); // Waktu tunggu sama dengan durasi progress bar
}

function fetchPenjualanPerSalesPerTahun(year) {
    $.ajax({
        url: `/getPerSalesPerTahun/${year}`, // URL endpoint
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            if (response.success && response.data?.length) {
                const data = response.data;

                const judul = response.message;
                const labels = data.map(item => item.sales_key);
                const salesData = data.map(item => item.total_penjualan);

                // Panggil fungsi untuk membuat chart
                renderPenjualanPerSalesPerTahunChart(labels, salesData, judul);
            } else {
                console.warn("Data penjualan per tahun tidak tersedia");
                $('#PenjualanPerSalesPerTahunChart').replaceWith('<p>Data tidak tersedia</p>');
            }
        },
        error: function (xhr, status, error) {
            console.error('Error fetching data:', error);
            $('#PenjualanPerSalesPerTahunChart').replaceWith('<p>Data tidak tersedia</p>');
        }
    });
}
function renderPenjualanPerSalesPerTahunChart(labels, data, judul) {
    const ctx = document.getElementById('PenjualanPerSalesPerTahunChart').getContext('2d');

    // Simpan data untuk digunakan kembali saat resize
    chartLabels = labels;
    chartData = data;
    chartJudul = judul;

    // Cek apakah chart sudah ada dan merupakan instance yang valid, lalu hancurkan jika ada
    if (window.PenjualanPerSalesPerTahunChart instanceof Chart) {
        window.PenjualanPerSalesPerTahunChart.destroy();
    }

    const canvas = document.getElementById('PenjualanPerSalesPerTahunChart');

    if (window.innerWidth <= 500) {
        canvas.height = 300;
    }

    // Tentukan orientasi berdasarkan lebar layar
    const isMobile = window.innerWidth <= 768;
    const chartOrientation = isMobile ? 'x' : 'y'; // 'x' untuk vertikal, 'y' untuk horizontal
    const xTicksOptions = {
        stepSize: 100000000,
        maxTicksLimit: 15,
        callback: function (value) {
            if (value >= 1000000000) {
                return (value / 1000000000).toFixed(1) + ' M'; // Untuk miliaran
            } else if (value >= 1000000) {
                return (value / 1000000) + ' JT'; // Untuk jutaan
            }
            return value.toLocaleString(); // Default format
        }
    };

    const yTicksOptions = {
        stepSize: 100000000,
        maxTicksLimit: 15,
        callback: function (value) {
            if (value >= 1000000000) {
                return (value / 1000000000).toFixed(1) + ' M'; // Untuk miliaran
            } else if (value >= 1000000) {
                return (value / 1000000) + ' JT'; // Untuk jutaan
            }
            return value.toLocaleString(); // Default format
        }
    };

    // Buat chart baru
    window.PenjualanPerSalesPerTahunChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: judul,
                data: data,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(255, 159, 64, 0.2)',
                    'rgba(255, 205, 86, 0.2)',
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(153, 102, 255, 0.2)',
                    'rgba(201, 203, 207, 0.2)'
                ],
                borderColor: [
                    'rgb(255, 99, 132)',
                    'rgb(255, 159, 64)',
                    'rgb(255, 205, 86)',
                    'rgb(75, 192, 192)',
                    'rgb(54, 162, 235)',
                    'rgb(153, 102, 255)',
                    'rgb(201, 203, 207)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            layout: {
                padding: 0
            },
            responsive: true,
            indexAxis: chartOrientation, // Ubah orientasi berdasarkan ukuran layar
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: isMobile ? 'Total Penjualan (Rp)' : 'Sales'
                    },
                    ticks: isMobile ? yTicksOptions : {} // Ticks di y-axis jika mobile
                },
                x: {
                    title: {
                        display: true,
                        text: isMobile ? 'Sales' : 'Total Penjualan (Rp)'
                    },
                    ticks: isMobile ? {} : xTicksOptions // Ticks di x-axis jika desktop
                }
            }
        }
    });
}

function setChartContainerStyle() {
    const container = document.querySelector('#containerCanvasPenjualanPerSalesPerTahun');
    const isMobile = window.innerWidth <= 500;

    container.style.display = 'flex';
    container.style.flexDirection = 'column';
    container.style.justifyContent = 'center';
    container.style.alignItems = 'center';
    container.style.position = 'relative';
}

function debounce(func, wait) {
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}
window.addEventListener('resize', debounce(function () {
    renderPenjualanPerSalesPerTahunChart(chartLabels, chartData, chartJudul);
}, 200));

function fetchPenjualanPerSalesPerQuartal(year) {
    $.ajax({
        url: `/getPerSalesPerQuartal/${year}`, // Pastikan URL endpoint sudah benar
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            if (response.success && response.data?.length) {
                initializeDropdown(response.data); // Mengisi dropdown
                updateChart(); // Memperbarui chart berdasarkan pilihan awal
            } else {
                console.warn("Data penjualan per Triwulan tidak tersedia");
                $('#salesKeySelect').html('<option disabled>Data tidak tersedia</option>');
                $('#PenjualanPerSalesPerQuartalChart').replaceWith('<p>Data tidak tersedia</p>');
            }
        },
        error: function (xhr, status, error) {
            console.error('Error fetching data:', error);
            $('#salesKeySelect').html('<option disabled>Data tidak tersedia</option>');
            $('#PenjualanPerSalesPerQuartalChart').replaceWith('<p>Data tidak tersedia</p>');
        }
    });
}
function initializeDropdown(data) {
    const salesKeys = [...new Set(data.map(item => item.sales_key))];
    const $salesKeySelect = $('#salesKeySelect');
    $salesKeySelect.empty(); // Kosongkan dropdown sebelum mengisi ulang

    // Tambahkan opsi ke dropdown untuk setiap sales_key unik
    $.each(salesKeys, function (index, key) {
        $salesKeySelect.append(`<option value="${key}">${key}</option>`);
    });

    // Simpan data asli ke global variable untuk digunakan kembali
    window.salesData = data;

    // Event listener untuk memperbarui chart saat pilihan berubah
    $salesKeySelect.change(function () {
        updateChart(); // Panggil updateChart ketika pilihan diubah
    });
}
function updateChart() {
    const selectedSalesKey = $('#salesKeySelect').val();
    const dataForChart = getDataBySalesKey(selectedSalesKey);

    const labels = dataForChart.map(item => item.quartal); // Labels kuartal (Q1, Q2, ...)
    const salesData = dataForChart.map(item => item.total_penjualan); // Total penjualan per kuartal

    renderPenjualanPerSalesPerQuartalChart(labels, salesData, `Total Penjualan per Triwulan untuk ${selectedSalesKey}`);
}
function getDataBySalesKey(salesKey) {
    return window.salesData
        .filter(item => item.sales_key === salesKey)
        .map(item => ({
            quartal: item.quartal,
            total_penjualan: parseInt(item.total_penjualan, 10)
        }));
}
function renderPenjualanPerSalesPerQuartalChart(labels, data, judul) {
    // Hancurkan chart jika sudah ada
    if (window.salesDoughnutChart instanceof Chart) {
        window.salesDoughnutChart.destroy();
    }

    // Render chart baru
    const ctx = document.getElementById('PenjualanPerSalesPerQuartalChart').getContext('2d');
    window.salesDoughnutChart = new Chart(ctx, {
        type: 'pie', // Bisa diganti menjadi 'pie' untuk Pie Chart
        data: {
            labels: labels,
            datasets: [{
                label: judul,
                data: data,
                backgroundColor: [
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(255, 206, 86, 0.2)',
                    'rgba(153, 102, 255, 0.2)',
                    'rgba(255, 159, 64, 0.2)',
                    'rgba(201, 203, 207, 0.2)'
                ],
                borderColor: [
                    'rgba(75, 192, 192, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(201, 203, 207, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: judul
                }
            }
        }
    });
}
function formatTarget(value) {
    if (value == null) return 'Data tidak tersedia';
    if (value >= 1000000000) {
        return (value / 1000000000).toFixed(1) + ' M'; // For billions
    } else if (value >= 1000000) {
        return (value / 1000000).toFixed(1) + ' JT'; // For millions
    } else {
        return value.toLocaleString(); // Default formatting
    }
}
function formatRupiah(angka) {
    if (angka == null) return 'Data tidak tersedia';
    let rupiah = '';
    let angkarev = angka.toString().split('').reverse().join('');
    for (let i = 0; i < angkarev.length; i++) {
        if (i % 3 === 0) rupiah += angkarev.substr(i, 3) + '.';
    }
    return 'Rp ' + rupiah.split('', rupiah.length - 1).reverse().join('');
}

function fetchKelasAnalisis(year) {
    $.ajax({
        url: `/getAnalisisMarginByYear/${year}`, // URL endpoint
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            if (response.success && response.data) {
                const data = response.data;
                const judul = response.message;
                // Konversi nama bulan dari angka ke nama bulan
                const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

                // Data untuk Bar Chart (Total Profit Bulanan)
                const labels = data.monthlyProfits.map(item => monthNames[item.bulan - 1]);
                const monthlyProfits = data.monthlyProfits.map(item => item.totalMonthlyProfit);

                // console.log(labels);
                // Panggil fungsi untuk membuat chart
                renderKelasAnalisisChart(labels, monthlyProfits, judul);
            } else {
                console.warn("Data penjualan per tahun tidak tersedia");
                $('#KelasAnalisisChart').replaceWith('<p>Data tidak tersedia</p>');
            }
        },
        error: function (xhr, status, error) {
            console.error('Error fetching data:', error);
            $('#KelasAnalisisChart').replaceWith('<p>Data tidak tersedia</p>');
        }
    });
}
function renderKelasAnalisisChart(labels, data, judul) {
    const ctx = document.getElementById('KelasAnalisisChart').getContext('2d');
    if (window.KelasAnalisisChart instanceof Chart) {
        window.KelasAnalisisChart.destroy();
    }

    const canvas = document.getElementById('KelasAnalisisChart');

    if (window.innerWidth <= 500) {
        canvas.height = 400;
    }
    // const ctxMonthly = document.getElementById('monthlyProfitChart').getContext('2d');
    window.KelasAnalisisChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: judul,
                data: data,
                backgroundColor: [
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(255, 206, 86, 0.2)',
                    'rgba(153, 102, 255, 0.2)',
                    'rgba(255, 159, 64, 0.2)',
                    'rgba(201, 203, 207, 0.2)'
                ],
                borderColor: [
                    'rgba(75, 192, 192, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(201, 203, 207, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Bulan'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Profit (IDR)'
                    }
                }
            }
        }
    });
}
function fetchAbsen(year) {
    $.ajax({
        url: `/getAbsensiYearly/${year}`, // URL endpoint
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            if (response.success && response.data) {
                const data = response.data;

                const judul = response.message;
                // Konversi nama bulan dari angka ke nama bulan
                const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
                // data.forEach(item => {
                //     console.log(item.karyawan);  // Log the entire 'karyawan' object to check if 'kode_karyawan' exists
                //     console.log(item.karyawan ? item.karyawan.kode_karyawan : "No kode_karyawan");  // Log 'kode_karyawan' if it exists, or 'No kode_karyawan' if it doesn't
                // });

                // Data untuk Bar Chart (Total Profit Bulanan)
                const labels = data.map(item => item.karyawan.kode_karyawan);
                const total_keterlambatan = data.map(item => item.total_keterlambatan);

                // console.log(labels);
                // Panggil fungsi untuk membuat chart
                renderAbsenChart(labels, total_keterlambatan, judul);
            } else {
                console.warn("Data penjualan per tahun tidak tersedia");
                $('#AbsenChart').replaceWith('<p>Data tidak tersedia</p>');
            }
        },
        error: function (xhr, status, error) {
            console.error('Error fetching data:', error);
            $('#AbsenChart').replaceWith('<p>Data tidak tersedia</p>');
        }
    });
}
function renderAbsenChart(labels, data, judul) {
    const ctx = document.getElementById('AbsenChart').getContext('2d');
    // const ctxMonthly = document.getElementById('monthlyProfitChart').getContext('2d');
    if (window.AbsenChart instanceof Chart) {
        window.AbsenChart.destroy();
    }
    const canvas = document.getElementById('AbsenChart');

    if (window.innerWidth <= 500) {
        canvas.height = 400;
    }

    window.AbsenChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: judul,
                data: data,
                backgroundColor: [
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(255, 206, 86, 0.2)',
                    'rgba(153, 102, 255, 0.2)',
                    'rgba(255, 159, 64, 0.2)',
                    'rgba(201, 203, 207, 0.2)'
                ],
                borderColor: [
                    'rgba(75, 192, 192, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(201, 203, 207, 1)'
                ],
                borderWidth: 1
            }]
        },
        layout: {
            padding: 0
        },
        options: {
            indexAxis: 'x',
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Menit'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Karyawan'
                    }
                }
            }
        }
    });
}
function fetchSouvenir(year) {
    $.ajax({
        url: `/getSouvenirYearly/${year}`, // URL endpoint
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            if (response.success && response.data) {
                const data = response.data;

                const judul = response.message;
                // Konversi nama bulan dari angka ke nama bulan
                const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

                // Data untuk Bar Chart (Total Profit Bulanan)
                const labels = data.map(item => item.nama_souvenir);
                const total_pemilihan = data.map(item => item.count);

                // console.log(labels);
                // Panggil fungsi untuk membuat chart
                renderSouvenirChart(labels, total_pemilihan, judul);
            } else {
                console.warn("Data Souvenir per tahun tidak tersedia");
                $('#SouvenirChart').replaceWith('<p>Data tidak tersedia</p>');
            }
        },
        error: function (xhr, status, error) {
            console.error('Error fetching data:', error);
            $('#SouvenirChart').replaceWith('<p>Data tidak tersedia</p>');
        }
    });
}
function renderSouvenirChart(labels, data, judul) {
    const ctx = document.getElementById('SouvenirChart').getContext('2d');
    if (window.SouvenirChart instanceof Chart) {
        window.SouvenirChart.destroy();
    }

    const canvas = document.getElementById('SouvenirChart');

    if (window.innerWidth <= 500) {
        canvas.height = 400;
    }
    window.SouvenirChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: judul,
                data: data,
                backgroundColor: [
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(255, 206, 86, 0.2)',
                    'rgba(153, 102, 255, 0.2)',
                    'rgba(255, 159, 64, 0.2)',
                    'rgba(201, 203, 207, 0.2)'
                ],
                borderColor: [
                    'rgba(75, 192, 192, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(201, 203, 207, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'x',
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Jumlah'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Souvenir'
                    }
                }
            }
        }
    });
}
function fetchAbsenPerbulan(year, month) {
    $.ajax({
        url: `/getAbsenPerbulan/${year}/${month}`, // Fetch the specific year and month
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
            if (month == 'All') {
                // Extract the data for the chart
                const labels = response.data.map(item => item.karyawan.kode_karyawan);
                const data = response.data.map(item => item.total_keterlambatan);
                renderAbsenPerbulanChart(labels, data, `Total Absen Terlambat Per Tahun`);
            }
            else if (response.success == false) {
                console.warn("Data Total Absen Terlambat tidak tersedia");
                alert(`Data bulan ${monthNames[month - 1]} tidak tersedia`);

            }
            else {
                const labels = response.data.map(item => item.karyawan.kode_karyawan);
                const data = response.data.map(item => item.total_keterlambatan);
                renderAbsenPerbulanChart(labels, data, `Total Absen Terlambat Bulan ${monthNames[month - 1]}`);
            }
        },
        error: function (xhr, status, error) {
            console.error('Error fetching data:', error);
            $('#AbsenPerbulanChart').replaceWith('<p>Data tidak tersedia</p>');
        }
    });
}
function updateChartAbsenPerbulan(bulan) {
    let year = $('#tahun').val(); // Get the selected year from the dropdown
    fetchAbsenPerbulan(year, bulan); // Fetch and render data for the selected year and month
}
function renderAbsenPerbulanChart(labels, data, title) {
    const canvas = document.getElementById('AbsenPerBulanChart');

    if (!canvas) {
        console.error('Canvas element not found!');
        return; // Exit if canvas is not found
    }

    const ctx = canvas.getContext('2d');

    if (window.AbsenPerbulanChart instanceof Chart) {
        window.AbsenPerbulanChart.destroy();  // Destroy the existing chart if it exists
    }

    if (window.innerWidth <= 500) {
        canvas.height = 400;
    }
    console.log(data, labels);
    window.AbsenPerbulanChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: title,
                data: data,
                backgroundColor: [
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(255, 206, 86, 0.2)',
                    'rgba(153, 102, 255, 0.2)',
                    'rgba(255, 159, 64, 0.2)',
                    'rgba(201, 203, 207, 0.2)'
                ],
                borderColor: [
                    'rgba(75, 192, 192, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(201, 203, 207, 1)'
                ],
                borderWidth: 1
            }]
        },
        layout: {
            padding: 0
        },
        options: {
            indexAxis: 'x',
            responsive: true,
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'ID Karyawan'
                    }
                },
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Total Keterlambatan'
                    }
                }
            }
        }
    });
}

function dashboardEdu() {
    console.log('Mulai fetch data...');

    fetch('/api/dashboard/csat-instruktur')
        .then(res => {
            console.log('CSAT status:', res.status);
            return res.json();
        })
        .then(res => {
            console.log('CSAT response:', res);
            if (res.success) {
                document.getElementById('csatValue').innerText = res.rata_rata;
                document.getElementById('csatBar').style.width =
                    (res.rata_rata / 5 * 100) + '%';
            }
        })
        .catch(err => console.error('CSAT error:', err));

    fetch('/api/dashboard/rekomendasi-materi')
        .then(res => {
            console.log('Rekomendasi status:', res.status);
            return res.json();
        })
        .then(res => {
            console.log('Rekomendasi response:', res);
            if (res.success) {
                document.getElementById('rekomendasiValue').innerText = res.persen + '%';
                document.getElementById('rekomendasiBar').style.width =
                    res.persen + '%';
            }
        })
        .catch(err => console.error('Rekomendasi error:', err));

    fetch('/api/dashboard/aktivitas-instruktur')
        .then(res => {
            console.log('Aktivitas status:', res.status);
            return res.json();
        })
        .then(res => {
            console.log('Aktivitas response:', res);
            if (res.success) {
                document.getElementById('sharingValue').innerText = res.sharingKnowledge;
                document.getElementById('materiValue').innerText = res.pembuatanMateri;
                document.getElementById('silabusValue').innerText = res.pembuatanSilabus;
            }
        })
    .catch(err => console.error('Aktivitas error:', err));
}

function fetchTabInix(year) {
    $.ajax({
        url: `/getTabInix/${year}`, // URL endpoint
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            if (response.success && response.data) {
                const data = response.data;
                console.log(data);
                $('#total_kelas').text(data.total_kelas);
                $('#jumlah_peserta').text(data.jumlah_peserta);
                $('#ratarata_kelas_perbulan').text(data.ratarata_kelas_perbulan);
                $('#jumlah_peserta_perbulan').text(data.jumlah_peserta_perbulan);
                $('#offline').text(data.offline);
                $('#virtual').text(data.virtual);
                $('#materi').text(data.ratarata_feedback.materi);
                $('#pelayanan').text(data.ratarata_feedback.pelayanan);
                $('#fasilitas').text(data.ratarata_feedback.fasilitas);
                $('#instruktur').text(data.ratarata_feedback.instruktur);
                // Objek berisi URL gambar untuk setiap id elemen gambar
                const imageUrls = {
                    "foto_sales": `/storage/posts/${data.sales_terbaik.sales.foto}`,
                    "foto_instruktur": `/storage/posts/${data.instruktur_terbaik.instruktur.foto}`,
                    "foto_office": `/storage/posts/${data.office_terbaik.sales_foto}`,
                    "foto_itsm": `/storage/posts/${data.office_terbaik.itsm_foto}`,
                };

                // Menetapkan src pada setiap elemen .dynamic-image berdasarkan id
                $('.dynamic-image').each(function () {
                    const imageId = $(this).attr('id'); // Mendapatkan id dari setiap elemen gambar

                    // Cek apakah id ada di objek imageUrls, lalu set src-nya
                    if (imageUrls[imageId]) {
                        $(this).attr('src', imageUrls[imageId]);
                    }
                });
                $('#nama_sales').text(data.sales_terbaik.sales.nama_lengkap);
                $('#nama_instruktur').text(data.instruktur_terbaik.instruktur.nama_lengkap);
                $('#nama_itsm').text(data.itsm_terbaik.itsm.itsm_nama);
                $('#nama_office').text(data.office_terbaik.office.office_nama);

                if (data.keterlambatan.length >= 3) {
                    // Mengisi src untuk foto peringkat kedua
                    $('.second-position #present-photo-dua').attr('src', '/storage/' + data.keterlambatan[1].foto);

                    // Mengisi src untuk foto peringkat pertama
                    $('.first-position #present-photo-satu').attr('src', '/storage/' + data.keterlambatan[0].foto);

                    // Mengisi src untuk foto peringkat ketiga
                    $('.third-position #present-photo-tiga').attr('src', '/storage/' + data.keterlambatan[2].foto);
                } else {
                    console.warn('Data keterlambatan kurang dari 3');
                }

            } else {
                console.warn("Data pada tahun ini tidak tersedia");
                alert("Data pada tahun ini tidak tersedia");
            }
        },
        error: function (xhr, status, error) {
            console.error('Error fetching data:', error);
        }
    });
}

function fetchNilaiFeedback(year, month) {
    $.ajax({
        url: `/getTotalFeedbackPerbulan/${year}/${month}`, // Fetch the specific year and month
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
            if (response.success) {
                if (response.data.length > 0) {
                    // Extract the data for the chart
                    const labels = response.data.map(item => item.instruktur_key);
                    const data = response.data.map(item => item.nilairatarata);
                    renderNilaiFeedbackChart(labels, data, `Nilai Feedback Bulan ${monthNames[month - 1]}`);
                } else {
                    console.warn("Data Nilai Feedback tidak tersedia");
                    alert(`Data bulan ${monthNames[month - 1]} tidak tersedia`);
                }
            } else {
                console.warn("Data Nilai Feedback tidak tersedia");
                alert(`Data bulan ${monthNames[month - 1]} tidak tersedia`);
            }
        },
        error: function (xhr, status, error) {
            console.error('Error fetching data:', error);
            $('#NilaiFeedbackChart').replaceWith('<p>Data tidak tersedia</p>');
        }
    });
}
function updateChartNilaiFeedback(bulan) {
    let year = $('#tahun').val(); // Get the selected year from the dropdown
    fetchNilaiFeedback(year, bulan); // Fetch and render data for the selected year and month
}
function renderNilaiFeedbackChart(labels, data, title) {
    const ctx = document.getElementById('NilaiFeedbackChart').getContext('2d');
    // const ctxMonthly = document.getElementById('monthlyProfitChart').getContext('2d');
    if (window.NilaiFeedbackChart instanceof Chart) {
        window.NilaiFeedbackChart.destroy();
    }
    const canvas = document.getElementById('NilaiFeedbackChart');

    if (window.innerWidth <= 500) {
        canvas.height = 400;
    }

    window.NilaiFeedbackChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: title,
                data: data,
                backgroundColor: [
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(255, 206, 86, 0.2)',
                    'rgba(153, 102, 255, 0.2)',
                    'rgba(255, 159, 64, 0.2)',
                    'rgba(201, 203, 207, 0.2)'
                ],
                borderColor: [
                    'rgba(75, 192, 192, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(201, 203, 207, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'x',
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Nilai Feedback Skala 4.00'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Instruktur'
                    }
                }
            }
        }
    });
}
function fetchTotalMengajar(year, month) {
    $.ajax({
        url: `/getTotalMengajarPerbulan/${year}/${month}`, // Fetch the specific year and month
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
            if (response.success == true) {
                // Extract the data for the chart
                const labels = response.data.map(item => item.instruktur_key);
                const data = response.data.map(item => item.total_mengajar);
                renderTotalMengajarChart(labels, data, `Total Mengajar Bulan ${monthNames[month - 1]}`);
            } else {
                console.warn("Data Total Mengajar tidak tersedia");
                alert(`Data bulan ${monthNames[month - 1]} tidak tersedia`);
                // $('#totalMengajarChart').replaceWith(`<p>Data bulan ${monthNames[month - 1]} tidak tersedia</p>`);
            }
        },
        error: function (xhr, status, error) {
            console.error('Error fetching data:', error);
            $('#totalMengajarChart').replaceWith('<p>Data tidak tersedia</p>');
        }
    });
}
function updateChartTotalMengajar(bulan) {
    let year = $('#tahun').val(); // Get the selected year from the dropdown
    fetchTotalMengajar(year, bulan); // Fetch and render data for the selected year and month
}
function renderTotalMengajarChart(labels, data, title) {
    const ctx = document.getElementById('totalMengajarChart').getContext('2d');
    // const ctxMonthly = document.getElementById('monthlyProfitChart').getContext('2d');
    if (window.totalMengajarChart instanceof Chart) {
        window.totalMengajarChart.destroy();
    }
    const canvas = document.getElementById('totalMengajarPerMateriChart');

    if (window.innerWidth <= 500) {
        canvas.height = 400;
    }

    window.totalMengajarChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: title,
                data: data,
                backgroundColor: [
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(255, 206, 86, 0.2)',
                    'rgba(153, 102, 255, 0.2)',
                    'rgba(255, 159, 64, 0.2)',
                    'rgba(201, 203, 207, 0.2)'
                ],
                borderColor: [
                    'rgba(75, 192, 192, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(201, 203, 207, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'x',
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Total Mengajar'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Instruktur'
                    }
                }
            }
        }
    });
}
function fetchTotalMateri(year, month) {
    $.ajax({
        url: `/getTotalMateriPerbulan/${year}/${month}`, // Fetch the specific year and month
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
            if (month == 'All') {
                // Extract the data for the chart
                const labels = response.data.map(item => item.kategori_materi);
                const data = response.data.map(item => item.total_materi);
                renderTotalMateriChart(labels, data, `Total Mengajar Per Tahun`);
            }
            else if (response.success == false) {
                console.warn("Data Total Mengajar tidak tersedia");
                alert(`Data bulan ${monthNames[month - 1]} tidak tersedia`);

            }
            else {
                const labels = response.data.map(item => item.kategori_materi);
                const data = response.data.map(item => item.total_materi);
                renderTotalMateriChart(labels, data, `Total Mengajar Bulan ${monthNames[month - 1]}`);
            }
        },
        error: function (xhr, status, error) {
            console.error('Error fetching data:', error);
            $('#totalMateriChart').replaceWith('<p>Data tidak tersedia</p>');
        }
    });
}
function updateChartTotalMateri(bulan) {
    let year = $('#tahun').val(); // Get the selected year from the dropdown
    fetchTotalMateri(year, bulan); // Fetch and render data for the selected year and month
}
function renderTotalMateriChart(labels, data, title) {
    const ctx = document.getElementById('totalMateriChart').getContext('2d');
    if (window.totalMateriChart instanceof Chart) {
        window.totalMateriChart.destroy();
    }
    const canvas = document.getElementById('totalMateriChart');

    if (window.innerWidth <= 500) {
        canvas.height = 400;
    }

    window.totalMateriChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: title,
                data: data,
                backgroundColor: [
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(255, 206, 86, 0.2)',
                    'rgba(153, 102, 255, 0.2)',
                    'rgba(255, 159, 64, 0.2)',
                    'rgba(201, 203, 207, 0.2)'
                ],
                borderColor: [
                    'rgba(75, 192, 192, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(201, 203, 207, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'x',
            responsive: true,
            padding: {
                top: 20,
            },
            plugins: {
                legend: {
                    position: 'top',
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Total Materi'
                    },
                    ticks: {
                        min: 0,  // Sumbu Y tetap mulai dari 0
                        padding: 0  // Memberikan jarak antara bar dengan sumbu Y
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Jenis Materi'
                    }
                }
            }
        }
    });
}
function fetchTotalMengajarPerMateri(year, month) {
    $.ajax({
        url: `/getTotalMengajarPerJenisMateriPerTahun/${year}/${month}`, // Endpoint untuk tahun dan bulan tertentu
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

            if (month == 'All') {
                const categories = Object.keys(response.data); // Kategori materi (e.g., "Security", "Management")
                const instrukturKeys = []; // Daftar instruktur
                const datasets = []; // Dataset untuk setiap instruktur

                // Loop untuk mengumpulkan semua instruktur yang ada
                categories.forEach(category => {
                    response.data[category].forEach(item => {
                        if (item.instruktur_key && !instrukturKeys.includes(item.instruktur_key)) {
                            instrukturKeys.push(item.instruktur_key); // Tambahkan instruktur yang belum ada
                        }
                    });
                });

                // Siapkan dataset untuk setiap instruktur
                instrukturKeys.forEach(instruktur => {
                    const data = categories.map(category => {
                        // Cari data instruktur berdasarkan kategori
                        const instrukturData = response.data[category].find(item => item.instruktur_key === instruktur);
                        return instrukturData ? instrukturData.total_mengajar : 0; // Jika instruktur tidak ada, beri 0
                    });

                    datasets.push({
                        label: instruktur, // Nama instruktur
                        data: data, // Total mengajar per kategori materi
                        backgroundColor: generateRandomColor(), // Warna acak untuk instruktur
                    });
                });

                // Render Chart.js dengan data yang telah dipersiapkan
                renderTotalMengajarPerMateriChart(categories, datasets, 'Total Mengajar Per Tahun');

            }
            else if (response.success == false) {
                console.warn("Data Total Mengajar tidak tersedia");
                alert(`Data bulan ${monthNames[month - 1]} tidak tersedia`);
            } else {
                // Menyiapkan data untuk chart, menambahkan instruktur_key ke dalam kategori
                const labels = response.data.map(item => `${item.instruktur_key} - ${item.kategori_materi}`);
                const data = response.data.map(item => item.total_mengajar);
                renderTotalMengajarPerMateriChart(labels, data, `Total Mengajar Per ${monthNames[month - 1]}`);
            }
        },
        error: function (xhr, status, error) {
            console.error("Terjadi kesalahan saat mengambil data:", error);
            alert("Terjadi kesalahan saat mengambil data.");
        }
    });
}
function updateChartTotalMengajarPerMateri(bulan) {
    let year = $('#tahun').val(); // Get the selected year from the dropdown
    fetchTotalMengajarPerMateri(year, bulan); // Fetch and render data for the selected year and month
}
function renderTotalMengajarPerMateriChart(labels, data, title) {
    if (title == 'Total Mengajar Per Tahun') {
        const ctx = document.getElementById('totalMengajarPerMateriChart').getContext('2d');

        if (window.totalMengajarPerMateriChart instanceof Chart) {
            window.totalMengajarPerMateriChart.destroy();  // Hapus chart yang lama
        }

        const canvas = document.getElementById('totalMengajarPerMateriChart');

        if (window.innerWidth <= 500) {
            canvas.height = 400;
        }

        window.totalMengajarPerMateriChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels, // Kategori materi
                datasets: data, // Dataset untuk setiap instruktur
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Jenis Materi'
                        },
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Total Mengajar'
                        },
                    }
                },
                plugins: {
                    legend: {
                        position: 'top', // Posisi legend
                    },
                    tooltip: {
                        callbacks: {
                            label: function (tooltipItem) {
                                return `${tooltipItem.dataset.label}: ${tooltipItem.raw}`; // Format label tooltip
                            }
                        }
                    }
                }
            }
        });
    } else {
        const ctx = document.getElementById('totalMengajarPerMateriChart').getContext('2d');

        // Destroy chart lama jika ada
        if (window.totalMengajarPerMateriChart instanceof Chart) {
            window.totalMengajarPerMateriChart.destroy();
        }

        // Membuat chart baru
        window.totalMengajarPerMateriChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels, // Labels gabungan instruktur dan kategori materi
                datasets: [{
                    label: title,
                    data: data, // Data total mengajar
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 159, 64, 0.2)',
                        'rgba(201, 203, 207, 0.2)'
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(201, 203, 207, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function (tooltipItem) {
                                return tooltipItem.raw + ' kali'; // Menambahkan teks tambahan pada tooltip
                            }
                        }
                    }
                }
            }
        });
    }
}

// CHART JUMLAH PEMBUATAN MATERI PER INSTRUKTUR - VERSI FIX
let jumlahUpdateMateriChart = null;

$(document).ready(function () {
    const canvas = document.getElementById('jumlahUpdateMateriPerbulanChart');
    if (!canvas) return;

    initJumlahUpdateMateriChart();

    loadData();

    $('#monthSelect_JumlahUpdateMateriPerbulan').on('change', loadData);
    $('#tahun').on('change', function () {
        $('#monthSelect_JumlahUpdateMateriPerbulan').val('All');
        loadData();
    });
    $('#pills-jumlahUpdateMateriPerbulan-tab').on('shown.bs.tab', loadData);
});

function initJumlahUpdateMateriChart() {
    const ctx = document.getElementById('jumlahUpdateMateriPerbulanChart').getContext('2d');

    if (jumlahUpdateMateriChart) {
        jumlahUpdateMateriChart.destroy();
        jumlahUpdateMateriChart = null;
    }

    jumlahUpdateMateriChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Jumlah Pembuatan Materi',
                data: [],
                backgroundColor: 'rgba(54, 162, 235, 0.8)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Pembuatan Materi Per Instruktur',
                    font: { size: 18, weight: 'bold' },
                    padding: { top: 10, bottom: 20 }
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return `Materi: ${context.raw} (${context.label})`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    title: { display: true, text: 'Nama Instruktur' },
                    ticks: {
                        autoSkip: false,
                        maxRotation: 45,
                        minRotation: 45,
                        font: { size: 11 }
                    }
                },
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Jumlah Pembuatan Materi' },
                    ticks: {
                        stepSize: 1,
                        precision: 0
                    },
                    max: 10
                }
            },
            animation: { duration: 1000 }
        }
    });
}

function loadData() {
    const bulan = $('#monthSelect_JumlahUpdateMateriPerbulan').val() || 'All';
    const tahun = $('#tahun').val() || new Date().getFullYear();

    console.log('[CHART] Loading:', { bulan, tahun });

    // ✅ SHOW LOADING
    $('#chartLoading_JumlahUpdateMateriPerbulan').removeClass('d-none');
    $('#chartEmpty_JumlahUpdateMateriPerbulan').addClass('d-none');

    $.ajax({
        url: '/chart/jumlah-update-materi-perbulan',
        type: 'GET',
        data: { bulan: bulan, tahun: tahun },
        dataType: 'json',
        timeout: 15000,
        success: function (response) {
            console.log('[CHART] Response:', response);
            handleSuccess(response);
        },
        error: function (xhr, status, error) {
            console.error('[CHART] Error:', status, error);
            handleError(xhr, status, error);
        },
        complete: function () {
            $('#chartLoading_JumlahUpdateMateriPerbulan').addClass('d-none');
        }
    });
}

function handleSuccess(response) {
    if (response.error) {
        showError(response.message || 'Error dari server');
        return;
    }

    if (!response.has_data || !response.labels || !response.data) {
        showError('Tidak ada data pembuatan materi pada periode ini');
        return;
    }

    updateChart(response);
}

function updateChart(data) {
    try {
        if (!jumlahUpdateMateriChart || !jumlahUpdateMateriChart.options) {
            console.warn('[CHART] Chart tidak valid, menginisialisasi ulang...');
            initChart();
            setTimeout(() => updateChart(data), 100);
            return;
        }

        const maxValue = Math.max(...data.data);
        const maxY = maxValue + 2;

        let title = `Pembuatan Materi Tahun ${data.tahun}`;
        if (data.bulan_terpilih !== 'All') {
            const bulanNama = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            const idx = parseInt(data.bulan_terpilih) - 1;
            if (idx >= 0 && idx < 12) {
                title = `Pembuatan Materi ${bulanNama[idx]} ${data.tahun}`;
            }
        }

        jumlahUpdateMateriChart.options.plugins.title.text = title;
        jumlahUpdateMateriChart.options.scales.y.max = maxY;
        jumlahUpdateMateriChart.data.labels = data.labels;
        jumlahUpdateMateriChart.data.datasets[0].data = data.data;
        jumlahUpdateMateriChart.update();

        $('#chartEmpty_JumlahUpdateMateriPerbulan').addClass('d-none');

        console.log(`[CHART] Chart updated successfully - Max Y: ${maxY} (Max Data: ${maxValue})`);
    } catch (error) {
        console.error('[CHART] Error updating chart:', error);
        showError('Error saat menampilkan chart: ' + error.message);
    }
}

function handleError(xhr, status, error) {
    let msg = 'Gagal memuat data';

    if (xhr.status === 401 || xhr.status === 403) {
        msg = 'Sesi berakhir. Silakan login ulang.';
        setTimeout(() => window.location.href = '/login', 3000);
    } else if (xhr.status === 404) {
        msg = 'Endpoint tidak ditemukan';
    } else if (status === 'timeout') {
        msg = 'Waktu permintaan habis';
    }

    showError(msg);
}

function showError(message) {
    $('#chartEmpty_JumlahUpdateMateriPerbulan')
        .text(message)
        .removeClass('d-none');

    if (jumlahUpdateMateriChart) {
        jumlahUpdateMateriChart.data.labels = [];
        jumlahUpdateMateriChart.data.datasets[0].data = [];
        jumlahUpdateMateriChart.update();
    }

    console.error('[CHART ERROR]', message);
}

//============== SILABUS PER INSTRUKTUR PER TAHUN ==============
let silabusPerInstrukturPerTahunChart = null;

function loadSilabusPerInstrukturPerTahunChart() {
    const tahun = $('#tahun').val();
    const canvasId = 'silabusPerInstrukturPerTahunChart';
    const $empty = $('#silabusPerInstrukturPerTahunEmpty');

    if (!tahun) {
        $empty.text('Pilih tahun terlebih dahulu').removeClass('d-none');
        return;
    }

    $empty.addClass('d-none');

    $.ajax({
        url: '/chart/silabus-per-instruktur-per-tahun',
        method: 'GET',
        data: { tahun: tahun },
        success: function (response) {

            const ctx = document
                .getElementById('silabusPerInstrukturPerTahunChart')
                .getContext('2d');

            // Destroy lama
            if (silabusPerInstrukturPerTahunChart) {
                silabusPerInstrukturPerTahunChart.destroy();
            }

            let labels = [];
            let data = [];

            if (Array.isArray(response) && response.length > 0) {
                labels = response.map(item => item.nama_instruktur || 'Instruktur');
                data = response.map(item => parseInt(item.jumlah_silabus) || 0);

                $('#silabusPerInstrukturPerTahunEmpty').addClass('d-none');
            } else {
                data = [0];

                $('#silabusPerInstrukturPerTahunEmpty')
                    .text('Tidak ada data silabus untuk tahun ' + tahun)
                    .removeClass('d-none');
            }

            silabusPerInstrukturPerTahunChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Jumlah Silabus',
                        data: data,
                        backgroundColor: 'rgba(54, 162, 235, 0.8)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            font: { size: 16, weight: 'bold' }
                        },
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                precision: 0
                            }
                        }
                    }
                }
            });
        },
        error: function () {
            $empty
                .text('Gagal memuat data chart')
                .removeClass('d-none');
        }
    });
}
$(document).ready(function () {

    $('#pills-silabusPerInstrukturPerTahun-tab').on('shown.bs.tab', function () {
        loadSilabusPerInstrukturPerTahunChart();
    });

    $('#tahun').on('change', function () {
        loadSilabusPerInstrukturPerTahunChart();
    });

    if ($('#pills-silabusPerInstrukturPerTahun').hasClass('show')) {
        loadSilabusPerInstrukturPerTahunChart();
    }
});

// ===================== CHART FEEDBACK INSTRUKTUR =====================
let feedbackChart = null;

$(document).ready(function () {
    const feedbackCanvas = document.getElementById('feedbackChart');
    if (feedbackCanvas) {
        initFeedbackChart();
        loadFeedback();

        $('#tahun').on('change', function () {
            resetChart();
            loadFeedback();
        });
    }
});

function initFeedbackChart() {
    const ctx = document.getElementById('feedbackChart').getContext('2d');

    if (feedbackChart) {
        feedbackChart.destroy();
        feedbackChart = null;
    }

    feedbackChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Rata-rata Nilai',
                data: [],
                backgroundColor: 'rgba(54, 162, 235, 0.8)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1,
                hoverBorderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return `Nilai: ${context.raw} (${context.label})`;
                        }
                    },
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleFont: {
                        size: 14
                    },
                    bodyFont: {
                        size: 13
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 4,
                    ticks: {
                        stepSize: 0.4,
                        font: {
                            size: 12
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                        color: function (context) {
                            if (context.tick.value === 3.2) {
                                return 'rgba(255, 0, 0, 0.3)';
                            }
                            return 'rgba(0, 0, 0, 0.05)';
                        }
                    }
                },
                x: {
                    ticks: {
                        autoSkip: false,
                        maxRotation: 30,
                        minRotation: 15,
                        font: {
                            size: 11
                        }
                    },
                    grid: {
                        display: false
                    }
                }
            },
            animation: {
                duration: 1000,
                easing: 'easeOutBounce'
            }
        }
    });
}

function resetChart() {
    if (!feedbackChart) return;

    feedbackChart.data.labels = [];
    feedbackChart.data.datasets[0].data = [];

    // Pastikan warna tetap statis saat reset, jangan dikosongkan
    feedbackChart.data.datasets[0].backgroundColor = 'rgba(54, 162, 235, 0.8)';
    feedbackChart.data.datasets[0].borderColor = 'rgba(54, 162, 235, 1)';

    feedbackChart.update();
}

function loadFeedback() {
    const apiUrl = window.FEEDBACK_API_URL || '/getTotalFeedbackPertahun';
    const tahun = $('#tahun').val() || new Date().getFullYear();

    $.ajax({
        url: apiUrl,
        type: "GET",
        data: {
            tahun: tahun
        },
        beforeSend: function () {
            $('#nilaiInstrukturEmpty').text('Memuat data...').removeClass('d-none');
        },
        success: function (res) {
            if (!Array.isArray(res) || res.length === 0) {
                $('#nilaiInstrukturEmpty').text('Tidak ada data feedback').removeClass('d-none');
                resetChart();
                return;
            }

            $('#nilaiInstrukturEmpty').addClass('d-none');

            const labels = [];
            const data = [];

            res.forEach((item) => {
                if (item && item.nama_instruktur && item.nilai_rata_rata !== undefined) {
                    labels.push(item.nama_instruktur.trim() || 'Instruktur');
                    data.push(parseFloat(item.nilai_rata_rata) || 0);
                }
            });

            if (labels.length > 0 && data.length > 0) {
                feedbackChart.data.labels = labels;
                feedbackChart.data.datasets[0].data = data;

                // Terapkan Warna Statis Langsung ke Dataset
                feedbackChart.data.datasets[0].backgroundColor = 'rgba(54, 162, 235, 0.8)';
                feedbackChart.data.datasets[0].borderColor = 'rgba(54, 162, 235, 1)';

                feedbackChart.update();
            } else {
                resetChart();
                $('#nilaiInstrukturEmpty').text('Data tidak valid').removeClass('d-none');
            }
        },
        error: function (xhr) {
            console.error('AJAX Error:', xhr.status, xhr.responseText);
            let errorMsg = 'Gagal memuat data';
            if (xhr.responseJSON?.message) {
                errorMsg += ': ' + xhr.responseJSON.message;
            } else if (xhr.status === 404) {
                errorMsg = 'Endpoint tidak ditemukan. Periksa route.';
            }
            $('#nilaiInstrukturEmpty')
                .text(errorMsg)
                .removeClass('d-none');
            resetChart();
        }
    });
}

$(document).ready(function () {
    console.log('✅ Chart Hari Mengajar Instruktur loaded');
    const $filterLocal = $('#tahun');
    const $filterGlobal = $('#filterTahunGlobal');
    const $tabBtn = $('#pills-hariMengajarInstrukturPerTahun-tab');
    const $tabContent = $('#pills-hariMengajarInstrukturPerTahun');
    const $canvas = $('#hariMengajarInstrukturPerTahunChart');
    const $empty = $('#hariMengajarInstrukturPerTahunEmpty');

    let chartInstance = null;

    function showEmpty() {
        $empty.removeClass('d-none');
        if ($canvas.length) {
            $canvas.closest('div').css('opacity', '0.3');
        }
    }

    function hideEmpty() {
        $empty.addClass('d-none');
        if ($canvas.length) {
            $canvas.closest('div').css('opacity', '1');
        }
    }

    function resetChart() {
        if (chartInstance) {
            chartInstance.destroy();
            chartInstance = null;
        }
        hideEmpty();
    }

    function renderChart(data) {
        resetChart();

        if (!data.has_data || !data.data || data.data.length === 0) {
            console.log('⚠️ No data available');
            showEmpty();
            return;
        }

        hideEmpty();

        if (!$canvas.length) {
            console.error('❌ Canvas not found!');
            return;
        }

        const ctx = $canvas[0].getContext('2d');

        // ============================================
        // 🎨 WARNA STATIS (Sesuai Permintaan)
        // ============================================
        const staticBackgroundColor = 'rgba(54, 162, 235, 0.8)';
        const staticBorderColor = 'rgba(54, 162, 235, 1)';

        chartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Hari Mengajar',
                    data: data.data,
                    backgroundColor: staticBackgroundColor,
                    borderColor: staticBorderColor,
                    borderWidth: 1,
                    barPercentage: 1,
                    hoverBackgroundColor: 'rgba(54, 162, 235, 1)',
                    hoverBorderColor: 'rgba(54, 162, 235, 1)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: `Total Hari Mengajar Instruktur Tahun ${data.tahun}`,
                        font: { size: 16, weight: 'bold' },
                        padding: { top: 10, bottom: 15 }
                    },
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        titleFont: { size: 14 },
                        bodyFont: { size: 13 },
                        padding: 12,
                        callbacks: {
                            label: function (context) {
                                return `Hari Mengajar: ${context.parsed.y} hari`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Nama Instruktur',
                            font: { weight: 'bold' }
                        },
                        grid: { display: false },
                        ticks: {
                            autoSkip: false,
                            maxRotation: 45,
                            minRotation: 45,
                            padding: 10
                        }
                    },
                    y: {
                        beginAtZero: true,
                        max: data.max_value,
                        title: {
                            display: true,
                            text: 'Jumlah Hari',
                            font: { weight: 'bold' }
                        },
                        ticks: {
                            precision: 0,
                            stepSize: Math.ceil(data.max_value / 10)
                        },
                        grid: { color: 'rgba(0, 0, 0, 0.08)' }
                    }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeOutQuart'
                }
            }
        });

        console.log('✅ Chart rendered successfully!');
    }

    // ========== LOAD DATA ==========
    function loadChart() {
        let tahun = $filterLocal.val()?.trim();

        if (!tahun && $filterGlobal.length) {
            tahun = $filterGlobal.val()?.trim();
        }

        if (!tahun || isNaN(tahun)) {
            console.error('❌ Tahun tidak valid:', tahun);
            showEmpty();
            return;
        }

        console.log('📥 Loading chart data for tahun:', tahun);
        hideEmpty();

        $.ajax({
            url: '/chart/hari-mengajar-instruktur-per-tahun',
            method: 'GET',
            data: { tahun: tahun },
            dataType: 'json',
            timeout: 15000,
            beforeSend: function () {
                if ($canvas.length) {
                    $canvas.closest('div').css('opacity', '0.5');
                }
            },
            success: function (response) {
                console.log('✅ Data received:', response);
                if ($canvas.length) {
                    $canvas.closest('div').css('opacity', '1');
                }
                renderChart(response);
            },
            error: function (xhr, status, error) {
                console.error('❌ AJAX Error:', status, error);
                if (xhr.responseText) {
                    console.error('Response:', xhr.responseText);
                }
                if (xhr.status === 404) {
                    console.error('Route not found! Check web.php');
                }
                if (xhr.status === 500) {
                    console.error('Server error! Check controller');
                }
                showEmpty();
                resetChart();
                if ($canvas.length) {
                    $canvas.closest('div').css('opacity', '1');
                }
            }
        });
    }

    // ========== EVENT HANDLERS ==========
    $tabBtn.on('shown.bs.tab', function () {
        console.log('탭 Tab "Hari Mengajar" ditampilkan');
        setTimeout(loadChart, 300);
    });

    $filterLocal.on('change', function () {
        const isActive = $tabContent.hasClass('show') || $tabContent.hasClass('active');
        if (isActive) {
            console.log('🔄 Filter lokal berubah, reload chart');
            loadChart();
        }
    });

    if ($filterGlobal.length) {
        $filterGlobal.on('change', function () {
            const isActive = $tabContent.hasClass('show') || $tabContent.hasClass('active');
            if (isActive) {
                console.log('🔄 Filter global berubah, reload chart');
                loadChart();
            }
        });
    }

    setTimeout(function () {
        const isActive = $tabBtn.hasClass('active') && $tabContent.hasClass('active');
        const isShowing = $tabContent.hasClass('show');

        console.log('Initial check - isActive:', isActive, 'isShowing:', isShowing);

        if (isActive || isShowing) {
            console.log('🚀 Auto-loading chart on page load');
            loadChart();
        }
    }, 600);
});

function generateRandomColor() {
    // Fungsi untuk menghasilkan warna acak
    const letters = '0123456789ABCDEF';
    let color = '#';
    for (let i = 0; i < 6; i++) {
        color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
}
function secondsToHMS(seconds) {
    // Handle null atau undefined seconds
    if (seconds === null || typeof seconds === 'undefined') {
        return '00:00:00';
    }
    const hrs = Math.floor(seconds / 3600);
    const mins = Math.floor((seconds % 3600) / 60);
    const secs = Math.floor(seconds % 60);
    return [
        hrs.toString().padStart(2, '0'),
        mins.toString().padStart(2, '0'),
        secs.toString().padStart(2, '0')
    ].join(':');
}
function dashboardEdu() {

    // CSAT
    fetch('/api/dashboard/csat-instruktur')
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                document.getElementById('csatValue').innerText = res.rata_rata;
                document.getElementById('csatBar').style.width = (res.rata_rata / 5 * 100) + '%';
            }
        });

    // Rekomendasi Materi
    fetch('/api/dashboard/rekomendasi-materi')
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                document.getElementById('rekomendasiValue').innerText = res.persen + '%';
                document.getElementById('rekomendasiBar').style.width = res.persen + '%';
            }
        });

    // Sharing Knowledge
    fetch('/api/dashboard/aktivitas-instruktur')
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                document.getElementById('sharingValue').innerText = res.sharingKnowledge;
                document.getElementById('materiValue').innerText = res.pembuatanMateri;
            }
        });
}



/**
 * Mengambil daftar bulan dari server dan mengisi dropdown.
 * Fungsi ini sekarang reusable untuk SEMUA dropdown filter bulan.
 * @param {string} dropdownId - ID dari elemen <select> (e.g., '#filterBulan')
 */
function loadBulanFilter(dropdownId) {
    $.ajax({
        url: '/list-bulan',
        method: 'GET',
        dataType: 'json',
        success: function (bulanList) {
            const dropdown = $(dropdownId);
            if (dropdown.length === 0) {
                console.error(`loadBulanFilter: Dropdown with ID "${dropdownId}" not found.`);
                return;
            }
            dropdown.empty();
            dropdown.append('<option value="all">Semua Bulan</option>');
            bulanList.forEach(function (bulan) {
                dropdown.append(`<option value="${bulan}">${bulan}</option>`);
            });
        },
        error: function () {
            console.error(`Gagal mengambil daftar bulan untuk ${dropdownId}.`);
        }
    });
}


// ===================================================================
// 2. CHART VARIABLES & BLOK FUNGSI
// ===================================================================

// --- 2.1. JUMLAH TICKETING ---
let jumlahTicketingChart = null;
const backgroundColors = [
    'rgba(255, 99, 132, 0.7)', 'rgba(54, 162, 235, 0.7)', 'rgba(255, 206, 86, 0.7)',
    'rgba(255, 159, 64, 0.7)', 'rgba(75, 192, 192, 0.7)', 'rgba(153, 102, 255, 0.7)',
    'rgba(255, 99, 255, 0.7)', 'rgba(99, 255, 132, 0.7)'
];
const borderColors = [
    'rgba(255, 99, 132, 1)', 'rgba(54, 162, 235, 1)', 'rgba(255, 206, 86, 1)',
    'rgba(255, 159, 64, 1)', 'rgba(75, 192, 192, 1)', 'rgba(153, 102, 255, 1)',
    'rgba(255, 99, 255, 1)', 'rgba(99, 255, 132, 1)'
];

function fetchJumlahTicketingData(filterValue = 'all') {
    console.log("fetchJumlahTicketingData: memulai fetch dengan filter bulan =", filterValue);
    $.ajax({
        url: '/ticketing-data',
        method: 'GET',
        data: { filterMonth: filterValue },
        dataType: 'json',
        cache: false,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function (response) {
            console.log("fetchJumlahTicketingData: data diterima dari server =", response);
            if (response && Array.isArray(response.labels) && Array.isArray(response.values)) {
                renderJumlahTicketingChart(response.labels, response.values);
            } else {
                console.error('fetchJumlahTicketingData: Format data tidak sesuai:', response);
                renderJumlahTicketingChart(['No Data'], [0]);
            }
        },
        error: function (xhr, status, error) {
            console.error('fetchJumlahTicketingData: Gagal mendapatkan data:', error);
            renderJumlahTicketingChart(['Error'], [0]);
        }
    });
}

function renderJumlahTicketingChart(labels, values) {
    console.log("renderJumlahTicketingChart: menerima labels =", labels, ", values =", values);
    const canvas = document.getElementById('jumlahTicketingChart');
    if (!canvas) {
        console.error("renderJumlahTicketingChart: element canvas tidak ditemukan");
        return;
    }
    const ctx = canvas.getContext('2d');
    if (jumlahTicketingChart) {
        try {
            jumlahTicketingChart.destroy();
        } catch (e) {
            console.warn("renderJumlahTicketingChart: error saat menghancurkan chart lama", e);
        }
    }
    if (labels.length === 0 || values.length === 0 || (labels[0] === 'No Data')) {
        labels = ['No Data'];
        values = [0];
    }
    const bgColors = labels.map((_, i) => backgroundColors[i % backgroundColors.length]);
    const bColors = labels.map((_, i) => borderColors[i % borderColors.length]);

    jumlahTicketingChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                label: 'Jumlah Permintaan Ticketing',
                data: values,
                backgroundColor: bgColors,
                borderColor: bColors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: false,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            let label = context.label || '';
                            let value = context.parsed || 0;
                            let dataSet = context.chart.data.datasets[context.datasetIndex];
                            let total = dataSet.data.reduce((a, b) => a + b, 0);
                            let percentage = total ? ((value / total) * 100).toFixed(1) : 0;
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                },
                datalabels: {
                    color: '#fff',
                    font: { weight: 'bold', size: 14 },
                    formatter: (value) => value,
                    anchor: 'center',
                    align: 'center'
                }
            }
        },
        plugins: [ChartDataLabels]
    });
    console.log("renderJumlahTicketingChart: chart baru berhasil dibuat");
}

function updateChartJumlahTicketing(filterValue) {
    console.log('updateChartJumlahTicketing: filter bulan dipilih =', filterValue);
    fetchJumlahTicketingData(filterValue);
}


// --- 2.2. JUMLAH PIC ---
let jumlahPICChart = null;

function renderJumlahPICChart(labels, data) {
    const ctx = document.getElementById('jumlahPICChart').getContext('2d');
    if (jumlahPICChart) {
        jumlahPICChart.destroy();
    }
    jumlahPICChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Jumlah PIC',
                data: data,
                backgroundColor: [
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    'rgba(54, 162, 235, 0.7)'
                ],
                borderColor: [
                    'rgba(75, 192, 192, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(54, 162, 235, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: { title: { display: true, text: 'Kategori Keperluan' } },
                y: { beginAtZero: true, title: { display: true, text: 'Jumlah PIC' } }
            }
        }
    });
}

function updateJumlahPICChart(labels, data) {
    if (jumlahPICChart) {
        jumlahPICChart.data.labels = labels;
        jumlahPICChart.data.datasets[0].data = data;
        jumlahPICChart.update();
    } else {
        renderJumlahPICChart(labels, data);
    }
}

function fetchJumlahPICData(filterMonth = 'all') {
    let url = '/jumlah-pic';
    if (filterMonth !== 'all') {
        url += '?filterMonth=' + encodeURIComponent(filterMonth);
    }
    fetch(url)
        .then(res => {
            if (!res.ok) throw new Error('Network response was not ok');
            return res.json();
        })
        .then(data => {
            if (data.labels && data.values) {
                const order = ['Programming', 'Digital', 'Technical Support'];
                const sortedValues = order.map(cat => {
                    const index = data.labels.indexOf(cat);
                    return index !== -1 ? data.values[index] : 0;
                });
                updateJumlahPICChart(order, sortedValues);
            } else {
                console.error('Data dari API tidak sesuai format', data);
            }
        })
        .catch(err => {
            console.error('Gagal mengambil data jumlah PIC:', err);
        });
}


// --- 2.3. RERATA DURASI PENGERJAAN ---
let rerataDurasiChartInstance = null;

async function fetchRerataDurasiData(selectedMonth = 'all') {
    try {
        let url = '/rerata-durasi-data';
        if (selectedMonth !== 'all') {
            url += '?filterMonth=' + encodeURIComponent(selectedMonth);
        }
        const response = await fetch(url);
        if (!response.ok) throw new Error('Network response was not ok');
        const data = await response.json();
        renderRerataDurasiChart(data.labels, data.values);
    } catch (error) {
        console.error('Fetch Rata Rata Durasi Data Error:', error);
    }
}

function renderRerataDurasiChart(labels, values) {
    const ctx = document.getElementById('rerataDurasiChart').getContext('2d');
    if (rerataDurasiChartInstance) {
        rerataDurasiChartInstance.destroy();
    }
    rerataDurasiChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Rata-rata Durasi (HH:mm:ss)',
                data: values,
                backgroundColor: 'rgba(54, 162, 235, 0.3)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 2,
                fill: true,
                tension: 0.3,
                pointRadius: 6,
                pointHoverRadius: 10,
                pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                pointHoverBackgroundColor: 'rgba(0,123,255,1)'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: { display: true, text: 'Rata Rata Durasi Pengerjaan Ticketing', font: { size: 18, weight: 'bold' } },
                tooltip: {
                    callbacks: {
                        label: (context) => `Durasi: ${secondsToHMS(context.raw)}`
                    }
                },
                legend: { display: true, position: 'top' }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: value => secondsToHMS(value) },
                    title: { display: true, text: 'Durasi (HH:mm:ss)' }
                },
                x: { title: { display: true, text: 'Kategori/Keperluan' } }
            }
        }
    });
}


// --- 2.4. RERATA KETEPATAN RESPONSE ---
let rerataKetepatanResponseChart = null;

function secondsToHMS(seconds) {
    if (isNaN(seconds) || seconds < 0) return '00:00:00';
    // Pastikan detik adalah angka
    seconds = Number(seconds);
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = Math.floor(seconds % 60);
    return [
        h.toString().padStart(2, '0'),
        m.toString().padStart(2, '0'),
        s.toString().padStart(2, '0')
    ].join(':');
}

async function fetchRerataKetepatanResponseData(selectedMonth = 'all') {
    try {
        let url = '/rerata-ketepatan-response-data';
        if (selectedMonth !== 'all') {
            // --- INI PERBAIKANNYA ---
            url += '?filterMonthKetepatan=' + encodeURIComponent(selectedMonth);
            // -------------------------
        }
        const response = await fetch(url);
        if (!response.ok) throw new Error('Network response was not ok');
        const data = await response.json();
        renderRerataKetepatanResponseChart(data.labels, data.values);
    } catch (error) {
        console.error('Fetch Rata Rata Ketepatan Response Data Error:', error);
    }
}

/**
 * Merender chart ketepatan respons
 */
function renderRerataKetepatanResponseChart(labels, values) {
    const ctx = document.getElementById('rerataKetepatanResponseChart').getContext('2d');
    if (rerataKetepatanResponseChart) {
        rerataKetepatanResponseChart.destroy();
    }
    rerataKetepatanResponseChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Rata-rata Ketepatan Response (HH:mm:ss)',
                data: values,
                backgroundColor: 'rgba(54, 162, 235, 0.3)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 2,
                fill: true,
                tension: 0.3,
                pointRadius: 6,
                pointHoverRadius: 10,
                pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                pointHoverBackgroundColor: 'rgba(0,123,255,1)'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: { display: true, text: 'Rata Rata Ketepatan Response Ticketing', font: { size: 18, weight: 'bold' } },
                tooltip: {
                    callbacks: {
                        label: (context) => `Ketepatan: ${secondsToHMS(context.raw)}`
                    }
                },
                legend: { display: true, position: 'top' },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: value => secondsToHMS(value) },
                    title: { display: true, text: 'Durasi (HH:mm:ss)' },
                    grid: { color: '#eee' }
                },
                x: {
                    title: { display: true, text: 'Kategori/Keperluan' },
                    ticks: { color: '#444', font: { size: 14 } },
                    grid: { display: false }
                }
            }
        }
    });
}


// --- 2.5. JUMLAH PERMINTAAN PER BULAN ---
let jumlahPermintaanPerBulanChart;

function renderJumlahPermintaanPerBulanChart(labels, values) {
    const ctx = document.getElementById('jumlahPermintaanPerBulanChart').getContext('2d');
    if (jumlahPermintaanPerBulanChart) {
        jumlahPermintaanPerBulanChart.destroy();
    }
    jumlahPermintaanPerBulanChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Jumlah Permintaan per Bulan',
                data: values,
                backgroundColor: 'rgba(54, 162, 235, 0.85)',
                borderColor: 'rgba(54, 132, 235, 1)',
                borderWidth: 2,
                borderRadius: 6,
                barPercentage: 0.6,
                categoryPercentage: 0.7,
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    // max: 110, // Sebaiknya jangan di-hardcode, biarkan Chart.js yg menentukan
                    ticks: { stepSize: 20, color: '#444', font: { size: 12 } },
                    grid: { color: '#ddd', borderDash: [3, 3] }
                },
                x: {
                    ticks: { color: '#222', font: { size: 14 } },
                    grid: { display: false }
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    enabled: true,
                    callbacks: {
                        label: (context) => context.dataset.label + ': ' + context.parsed.y
                    }
                },
                datalabels: {
                    anchor: 'end',
                    align: 'start',
                    color: '#0056b3',
                    font: { weight: 'bold', size: 14 },
                    formatter: (value) => value,
                    offset: -10,
                }
            }
        },
        plugins: [ChartDataLabels]
    });
}

async function fetchJumlahPermintaanPerBulanData() {
    try {
        const response = await fetch('/jumlah-permintaan-per-bulan');
        if (!response.ok) throw new Error('Network response was not ok');
        const data = await response.json();
        console.log("Data Jumlah Permintaan per Bulan:", data);
        renderJumlahPermintaanPerBulanChart(data.labels, data.values);
    } catch (error) {
        console.error('Fetch Jumlah Permintaan per Bulan Error:', error);
    }
}


// --- 2.6. PERMINTAAN SERING DIAJUKAN ---
let permintaanSeringDiajukanChart;

async function fetchPermintaanSeringDiajukanData() {
    try {
        const response = await fetch('/permintaan-sering-diajukan');
        if (!response.ok) throw new Error('Network response was not ok');
        const data = await response.json();
        console.log("Data Permintaan Sering Diajukan:", data);
        renderPermintaanSeringDiajukanChart(data.labels, data.values);
    } catch (error) {
        console.error('Fetch Permintaan Sering Diajukan Error:', error);
    }
}

function renderPermintaanSeringDiajukanChart(labels, values) {
    const ctx = document.getElementById('permintaanSeringDiajukanChart').getContext('2d');
    if (permintaanSeringDiajukanChart) {
        permintaanSeringDiajukanChart.destroy();
    }
    permintaanSeringDiajukanChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Permintaan yang sering diajukan',
                data: values,
                backgroundColor: 'rgba(54, 162, 235, 0.9)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1,
                barPercentage: 0.7,
                categoryPercentage: 0.8,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: { stepSize: 10, color: '#444', font: { size: 12 } },
                    grid: { color: '#eee' }
                },
                y: {
                    ticks: { color: '#444', font: { size: 12 } },
                    grid: { display: false }
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    enabled: true,
                    callbacks: {
                        label: (context) => context.parsed.x
                    }
                },
                datalabels: {
                    anchor: 'end',
                    align: 'right',
                    color: '#000',
                    font: { weight: 'bold', size: 13 },
                    formatter: (value) => value
                }
            }
        },
        plugins: [ChartDataLabels]
    });
}

$(document).ready(function () {

    // =======================================================================
    // 1. INISIALISASI & TAB LAMA
    // =======================================================================

    // Inisialisasi dropdown filter bulan
    if(typeof loadBulanFilter === 'function') {
        loadBulanFilter('#filterBulan');
        loadBulanFilter('#filterMonthPIC');
        loadBulanFilter('#filterMonth');
        loadBulanFilter('#filterMonthKetepatan');
    }

    // Event Listeners Filter Dropdown
    $('#filterBulan').on('change', function () { fetchJumlahTicketingData(this.value); });
    $('#filterMonthPIC').on('change', function () { fetchJumlahPICData(this.value); });
    $('#filterMonth').on('change', function () { fetchRerataDurasiData(this.value); });
    $('#filterMonthKetepatan').on('change', function () { fetchRerataKetepatanResponseData(this.value); });

    // Event Listeners PILLS (Tab Lama)
    $('#pills-jumlah-ticketing-tab').on('shown.bs.tab', function () { fetchJumlahTicketingData($('#filterBulan').val() || 'all'); });
    $('#pills-jumlah-pic-tab').on('shown.bs.tab', function () { fetchJumlahPICData($('#filterMonthPIC').val() || 'all'); });
    $('#pills-rerata-durasi-tab').on('shown.bs.tab', function () { fetchRerataDurasiData($('#filterMonth').val() || 'all'); });
    $('#pills-rerata-ketepatan-response-tab').on('shown.bs.tab', function () { fetchRerataKetepatanResponseData($('#filterMonthKetepatan').val() || 'all'); });
    $('#pills-jumlah-permintaan-tab').on('shown.bs.tab', function () { fetchJumlahPermintaanPerBulanData(); });
    $('#pills-permintaan-sering-diajukan-tab').on('shown.bs.tab', function () { fetchPermintaanSeringDiajukanData(); });


    // =======================================================================
    // 2. HELPER UMUM SLA
    // =======================================================================
    const formatPercent = (val) => `${parseFloat(val).toFixed(1)}%`;
    const formatHours = (val) => `${parseFloat(val).toFixed(1)} jam`;
    const formatValue = (val) => parseFloat(val).toFixed(0);
    const getSlaClass = (val) => (val >= 90 ? 'text-success' : (val >= 80 ? 'text-warning' : 'text-danger'));

    function updateFilterDisplay(filters, elementId) {
        try {
            const startDate = new Date(filters.start);
            const year = startDate.getFullYear();
            const month = startDate.getMonth();
            const semester = (month < 6) ? 1 : 2;
            const el = document.getElementById(elementId);
            if (el) {
                el.innerHTML = `<strong>Tahun: ${year} - Semester: ${semester}</strong><br><small class="text-muted">(Data: ${filters.start.split(' ')[0]} s/d ${filters.end.split(' ')[0]})</small>`;
            }
        } catch (e) { console.error("Gagal update filter display", e); }
    }


    // =======================================================================
    // 3. FUNGSI SLA PROGRAMMER
    // =======================================================================
    const slaProgTimUrl = "/dashboard-sla/programmer/tim";
    const slaProgUserUrl = "/dashboard-sla/programmer/user";
    const slaProgKritisUrl = "/dashboard-sla/programmer/kritis";
    let slaProgrammerChart;

    async function loadSlaTim() {
        try {
            const response = await fetch(slaProgTimUrl); const kpi = await response.json();
            updateFilterDisplay(kpi.filters, 'sla_current_period');

            const resEl = document.getElementById('tim-sla-resolution');
            resEl.textContent = formatPercent(kpi.sla_resolution_compliance);
            resEl.className = `fs-2 fw-bold ${getSlaClass(kpi.sla_resolution_compliance)}`;

            const respEl = document.getElementById('tim-sla-response');
            respEl.textContent = formatPercent(kpi.sla_response_compliance);
            respEl.className = `fs-2 fw-bold ${getSlaClass(kpi.sla_response_compliance)}`;

            document.getElementById('tim-avg-resolution').textContent = formatHours(kpi.avg_resolution_time);
            document.getElementById('tim-total-tickets').textContent = formatValue(kpi.total_tickets);

            const chartCtx = document.getElementById('slaTimPriorityChart').getContext('2d');
            const pData = kpi.tickets_by_priority;
            if (slaProgrammerChart) slaProgrammerChart.destroy();
            if (kpi.total_tickets > 0) {
                $('#slaTimPriorityChart').show();
                slaProgrammerChart = new Chart(chartCtx, { type: 'bar', data: { labels: ['High', 'Medium', 'Low', 'Other'], datasets: [{ label: 'Jumlah Tiket', data: [pData.High, pData.Medium, pData.Low, pData.Other], backgroundColor: ['#dc3545', '#ffc107', '#198754', '#6c757d'] }] }, options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } }, plugins: { legend: { display: false } } } });
            } else { $('#slaTimPriorityChart').hide(); }
        } catch (error) { console.error('Gagal SLA Prog Tim:', error); }
    }

    async function loadSlaUser() {
        try {
            const response = await fetch(slaProgUserUrl); const data = await response.json();
            const tableBody = document.getElementById('sla-user-table-body'); tableBody.innerHTML = '';
            if (data.kpi.length === 0) { tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Tidak ada data.</td></tr>'; return; }
            data.kpi.sort((a, b) => b.total_tickets - a.total_tickets).forEach(item => {
                tableBody.innerHTML += `<tr><td><strong>${item.nama_programmer}</strong></td><td class="${getSlaClass(item.sla_resolution_compliance)}">${formatPercent(item.sla_resolution_compliance)}</td><td class="${getSlaClass(item.sla_response_compliance)}">${formatPercent(item.sla_response_compliance)}</td><td>${formatHours(item.avg_resolution_time)}</td><td><strong>${formatValue(item.total_tickets)}</strong></td><td><span class="badge bg-danger">H:${item.tickets_by_priority.High}</span> <span class="badge bg-warning">M:${item.tickets_by_priority.Medium}</span> <span class="badge bg-success">L:${item.tickets_by_priority.Low}</span></td></tr>`;
            });
        } catch (error) { console.error('Gagal SLA Prog User:', error); }
    }

    async function loadSlaKritis() {
        try {
            const response = await fetch(slaProgKritisUrl); const data = await response.json();
            const kpi = data.kpi;
            document.getElementById('kritis-sla-resolution').textContent = formatPercent(kpi.sla_resolution_compliance);
            document.getElementById('kritis-sla-response').textContent = formatPercent(kpi.sla_response_compliance);
            document.getElementById('kritis-avg-resolution').textContent = formatHours(kpi.avg_resolution_time);
            document.getElementById('kritis-total-insiden').textContent = formatValue(kpi.total_insiden);

            const tableBody = document.getElementById('sla-kritis-table-body'); tableBody.innerHTML = '';
            if (data.details.length === 0) { tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Tidak ada insiden kritis.</td></tr>'; return; }
            data.details.forEach(item => {
                const badge = item.sla_resolution_met ? '<span class="badge bg-success">Met</span>' : '<span class="badge bg-danger">Breached</span>';
                tableBody.innerHTML += `<tr><td>${item.id}</td><td>${item.laporan.substring(0, 50)}...</td><td>${badge}</td><td>${formatHours(item.actual_resolution_hours)}</td><td>${item.actual_response_hours ? formatHours(item.actual_response_hours) : '-'}</td><td>${item.responder}</td></tr>`;
            });
        } catch (error) { console.error('Gagal SLA Prog Kritis:', error); }
    }


    // =======================================================================
    // 4. FUNGSI SLA TECHNICAL SUPPORT
    // =======================================================================
    const slaTsTimUrl = "/dashboard-sla/tech-support/tim";
    const slaTsUserUrl = "/dashboard-sla/tech-support/user";
    const slaTsKritisUrl = "/dashboard-sla/tech-support/kritis";
    let slaTsTimChart;

    async function loadSlaTsTim() {
        try {
            const response = await fetch(slaTsTimUrl); const kpi = await response.json();
            updateFilterDisplay(kpi.filters, 'ts_sla_current_period');

            document.getElementById('ts-tim-sla-resolution').textContent = formatPercent(kpi.sla_resolution_compliance);
            document.getElementById('ts-tim-sla-response').textContent = formatPercent(kpi.sla_response_compliance);
            document.getElementById('ts-tim-avg-resolution').textContent = formatHours(kpi.avg_resolution_time);
            document.getElementById('ts-tim-total-tickets').textContent = formatValue(kpi.total_tickets);

            const chartCtx = document.getElementById('tsSlaTimPriorityChart').getContext('2d');
            const pData = kpi.tickets_by_priority;
            if (slaTsTimChart) slaTsTimChart.destroy();
            if (kpi.total_tickets > 0) {
                $('#tsSlaTimPriorityChart').show();
                slaTsTimChart = new Chart(chartCtx, { type: 'bar', data: { labels: ['High', 'Medium', 'Low', 'Other'], datasets: [{ label: 'Jumlah Tiket', data: [pData.High, pData.Medium, pData.Low, pData.Other], backgroundColor: ['#dc3545', '#ffc107', '#198754', '#6c757d'] }] }, options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } }, plugins: { legend: { display: false } } } });
            } else { $('#tsSlaTimPriorityChart').hide(); }
        } catch (error) { console.error('Gagal SLA TS Tim:', error); }
    }

    async function loadSlaTsUser() {
        try {
            const response = await fetch(slaTsUserUrl); const data = await response.json();
            const tableBody = document.getElementById('ts-sla-user-table-body'); tableBody.innerHTML = '';
            if (data.kpi.length === 0) { tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Tidak ada data.</td></tr>'; return; }
            data.kpi.sort((a, b) => b.total_tickets - a.total_tickets).forEach(item => {
                tableBody.innerHTML += `<tr><td><strong>${item.nama_programmer}</strong></td><td class="${getSlaClass(item.sla_resolution_compliance)}">${formatPercent(item.sla_resolution_compliance)}</td><td class="${getSlaClass(item.sla_response_compliance)}">${formatPercent(item.sla_response_compliance)}</td><td>${formatHours(item.avg_resolution_time)}</td><td><strong>${formatValue(item.total_tickets)}</strong></td><td><span class="badge bg-danger">H:${item.tickets_by_priority.High}</span> <span class="badge bg-warning">M:${item.tickets_by_priority.Medium}</span></td></tr>`;
            });
        } catch (error) { console.error('Gagal SLA TS User:', error); }
    }

    async function loadSlaTsKritis() {
        try {
            const response = await fetch(slaTsKritisUrl); const data = await response.json();
            const kpi = data.kpi;
            document.getElementById('ts-kritis-sla-resolution').textContent = formatPercent(kpi.sla_resolution_compliance);
            document.getElementById('ts-kritis-sla-response').textContent = formatPercent(kpi.sla_response_compliance);
            document.getElementById('ts-kritis-avg-resolution').textContent = formatHours(kpi.avg_resolution_time);
            document.getElementById('ts-kritis-total-insiden').textContent = formatValue(kpi.total_insiden);

            const tableBody = document.getElementById('ts-sla-kritis-table-body'); tableBody.innerHTML = '';
            if (data.details.length === 0) { tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Tidak ada insiden kritis.</td></tr>'; return; }
            data.details.forEach(item => {
                const badge = item.sla_resolution_met ? '<span class="badge bg-success">Met</span>' : '<span class="badge bg-danger">Breached</span>';
                tableBody.innerHTML += `<tr><td>${item.id}</td><td>${item.laporan.substring(0, 50)}...</td><td>${badge}</td><td>${formatHours(item.actual_resolution_hours)}</td><td>${item.actual_response_hours ? formatHours(item.actual_response_hours) : '-'}</td><td>${item.responder}</td></tr>`;
            });
        } catch (error) { console.error('Gagal SLA TS Kritis:', error); }
    }


    // =======================================================================
    // 5. FUNGSI SLA EVENT (WEBINAR)
    // =======================================================================
    async function loadSlaEvent(mappingId) {
        if (!mappingId || mappingId == 0) {
            $('#event-sla-table-body').html('<tr><td colspan="6" class="text-center p-4 text-danger">Event belum dipilih.</td></tr>');
            return;
        }

        $('#event-sla-content').hide();
        $('#event-sla-empty').html('<div class="spinner-border text-primary" role="status"></div><p>Memuat data...</p>').show();

        try {
            const response = await fetch(`/dashboard-sla/event/${mappingId}`);
            if (!response.ok) throw new Error('Network error');

            const data = await response.json();
            const kpi = data.kpi;
            const details = data.details;

            const getKpiClass = (val) => val >= 100 ? 'text-success' : 'text-primary';
            const getSlaClass = (val) => val >= 90 ? 'text-success' : (val >= 80 ? 'text-warning' : 'text-danger');

            $('#event-title').text(kpi.event_title);
            $('#event-date').text(`D-Day: ${kpi.event_date}`);
            $('#event-kpi-completion').text(formatPercent(kpi.completion_rate)).attr('class', `fs-2 fw-bold ${getKpiClass(kpi.completion_rate)}`);
            $('#event-kpi-compliance').text(formatPercent(kpi.sla_compliance)).attr('class', `fs-2 fw-bold ${getSlaClass(kpi.sla_compliance)}`);
            $('#event-kpi-late').text(kpi.total_late);
            $('#event-kpi-overdue').text(kpi.total_overdue);

            const tableBody = $('#event-sla-table-body');
            tableBody.empty();

            if (!details || Object.keys(details).length === 0) {
                tableBody.html('<tr><td colspan="6" class="text-center p-4">Tidak ada data checklist ditemukan.</td></tr>');
            } else {
                $.each(details, function (stage, items) {
                    // HEADER GROUP (colspan=6)
                    tableBody.append(`
                        <tr class="table-secondary">
                            <td colspan="6" class="fw-bold text-uppercase px-3 py-2">
                                <i class="bi bi-bookmark-fill me-1"></i> ${stage}
                            </td>
                        </tr>
                    `);

                    // ITEM ROWS
                    items.forEach(item => {
                        let badge = '';
                        let rowClass = '';

                        // Logika Badge
                        switch (item.status) {
                            case 'On Time': badge = '<span class="badge bg-success">On Time</span>'; break;
                            case 'Late': badge = '<span class="badge bg-warning text-dark">Terlambat</span>'; break;
                            case 'Overdue': badge = '<span class="badge bg-danger">Overdue</span>'; rowClass = 'bg-danger bg-opacity-10'; break;
                            case 'On Progress': badge = '<span class="badge bg-info text-dark">On Progress</span>'; break;
                            default: badge = '<span class="badge bg-secondary">Pending</span>';
                        }
                        const picBadge = item.pic !== '-' ? `<span class="badge bg-light text-dark border">${item.pic}</span>` : '<span class="text-muted">-</span>';
                        tableBody.append(`<tr class="${rowClass}"><td class="fw-medium ps-4 align-middle">${item.activity}</td><td class="align-middle">${picBadge}</td><td class="text-center align-middle"><small class="text-muted fw-bold">${item.sla_label}</small></td><td class="text-center font-monospace small align-middle">${item.target_date}</td><td class="text-center font-monospace small fw-bold align-middle">${item.actual_date}</td><td class="text-center align-middle">${badge}</td></tr>`);
                    });
                });
            }
            $('#event-sla-empty').hide();
            $('#event-sla-content').fadeIn();

        } catch (error) {
            console.error('Gagal SLA Event:', error);
            $('#event-sla-empty').html(`<p class="text-danger">Gagal memuat data: ${error.message}</p>`).show();
        }
    }

    // Listener Dropdown Event SLA
    $('#eventSlaFilter').on('change', function () {
        loadSlaEvent($(this).val());
    });

    // =======================================================================
    // 8. EVENT LISTENER UTAMA (UPDATED)
    // =======================================================================
    $('#itsm-pills-tab').on('shown.bs.tab', 'button[data-bs-toggle="pill"], a[data-bs-toggle="pill"]', function (event) {
        const activePill = $(event.target);
        const activePillId = activePill.attr('id');
        const isLoaded = activePill.data('loaded') || false;

        console.log(`Tab ${activePillId} ditampilkan.`);

        // --- Logika Tab SLA ---
        if (activePill.hasClass('sla-tab-trigger')) {

            // A. Logika Tab Digital Kreatif (BARU)
            if (activePillId === 'pills-sla-digital-tab') {
                if (!isLoaded) {
                    loadSlaDigital();
                    activePill.data('loaded', true);
                }
                return;
            }

            // B. Logika Tab Event Webinar
            if (activePillId === 'pills-sla-event-tab') {
                if (!isLoaded) {
                    const selectedEvent = $('#eventSlaFilter').val();
                    if (selectedEvent) {
                        loadSlaEvent(selectedEvent);
                        activePill.data('loaded', true);
                    }
                }
                return;
            }

            // C. Logika Tab Tim IT (Programmer & TS)
            const team = activePill.data('team');
            if (!isLoaded && team) {
                if (team === 'programmer') {
                    loadSlaTim(); loadSlaUser(); loadSlaKritis();
                } else if (team === 'tech-support') {
                    loadSlaTsTim(); loadSlaTsUser(); loadSlaTsKritis();
                }
                activePill.data('loaded', true);
            }

        // --- Logika Tab Lama (Legacy) ---
        } else {
            if (!isLoaded) {
                switch (activePillId) {
                    case 'pills-jumlah-ticketing-tab': fetchJumlahTicketingData($('#filterBulan').val() || 'all'); break;
                    case 'pills-jumlah-pic-tab': fetchJumlahPICData($('#filterMonthPIC').val() || 'all'); break;
                    case 'pills-rerata-durasi-tab': fetchRerataDurasiData($('#filterMonth').val() || 'all'); break;
                    case 'pills-rerata-ketepatan-response-tab': fetchRerataKetepatanResponseData($('#filterMonthKetepatan').val() || 'all'); break;
                    case 'pills-jumlah-permintaan-tab': fetchJumlahPermintaanPerBulanData(); break;
                    case 'pills-permintaan-sering-diajukan-tab': fetchPermintaanSeringDiajukanData(); break;
                }
                activePill.data('loaded', true);
            }
        }
    });

    // Trigger Awal
    setTimeout(() => {
        const activePill = $('#itsm-pills-tab .nav-link.active');
        if (activePill.length) activePill.trigger('shown.bs.tab');
        else $('#itsm-pills-tab button:first').trigger('shown.bs.tab');
    }, 50);

});
