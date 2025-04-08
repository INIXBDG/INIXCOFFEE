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
        fetchTotalMengajar(year, '1')
        fetchTotalMateri(year, 'All')
        fetchTotalMengajarPerMateri(year, 'All')
        fetchAbsenPerbulan(year, 'All')
        // fetchNilaiFeedback(year, '1')
    }
}
function getYearlySales(year) {
    $.ajax({
        url: `/getYearlySales/${year}`, // URL with year parameter
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            let totalSales = response.totalSales || 0;
            let target = response.target || 0;
            let targetLabels = response.targetLabels.length ? response.targetLabels : Array(9).fill("0");

            // Update totalSalesDisplay element with formatted totalSales
            $('#totalSalesDisplay').text(formatRupiah(totalSales));

            // Update ruler labels and car position based on current totalSales and target
            updateRulerLabels(totalSales, target, targetLabels);
            updateCarPosition(totalSales, target);
        },
        error: function(xhr, status, error) {
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
    $.each(targetLabels, function(index, label) {
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
        success: function(response) {
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
        error: function(xhr, status, error) {
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

    // Tentukan orientasi berdasarkan lebar layar
    const isMobile = window.innerWidth <= 768;
    const chartOrientation = isMobile ? 'x' : 'y'; // 'x' untuk vertikal, 'y' untuk horizontal
    const xTicksOptions = {
        stepSize: 100000000, 
        maxTicksLimit: 15,
        callback: function(value) {
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
        maxTicksLimit: 5,
        callback: function(value) {
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
                padding: 20
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
function debounce(func, wait) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}
window.addEventListener('resize', debounce(function() {
    renderPenjualanPerSalesPerTahunChart(chartLabels, chartData, chartJudul);
}, 200));

function fetchPenjualanPerSalesPerQuartal(year) {
    $.ajax({
        url: `/getPerSalesPerQuartal/${year}`, // Pastikan URL endpoint sudah benar
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data?.length) {
                initializeDropdown(response.data); // Mengisi dropdown
                updateChart(); // Memperbarui chart berdasarkan pilihan awal
            } else {
                console.warn("Data penjualan per Triwulan tidak tersedia");
                $('#salesKeySelect').html('<option disabled>Data tidak tersedia</option>');
                $('#PenjualanPerSalesPerQuartalChart').replaceWith('<p>Data tidak tersedia</p>');
            }
        },
        error: function(xhr, status, error) {
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
    $.each(salesKeys, function(index, key) {
        $salesKeySelect.append(`<option value="${key}">${key}</option>`);
    });

    // Simpan data asli ke global variable untuk digunakan kembali
    window.salesData = data;

    // Event listener untuk memperbarui chart saat pilihan berubah
    $salesKeySelect.change(function() {
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
        success: function(response) {
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
        error: function(xhr, status, error) {
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
        success: function(response) {
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
        error: function(xhr, status, error) {
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
        success: function(response) {
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
        error: function(xhr, status, error) {
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
        success: function(response) {
            const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
            if(month == 'All') {
                // Extract the data for the chart
                const labels = response.data.map(item => item.karyawan.kode_karyawan);
                const data = response.data.map(item => item.total_keterlambatan);
                renderAbsenPerbulanChart(labels, data, `Total Absen Terlambat Per Tahun`);
            } 
            else if(response.success == false) {
                console.warn("Data Total Absen Terlambat tidak tersedia");
                alert(`Data bulan ${monthNames[month - 1]} tidak tersedia`);
                
            }
            else{
                const labels = response.data.map(item => item.karyawan.kode_karyawan);
                const data = response.data.map(item => item.total_keterlambatan);
                renderAbsenPerbulanChart(labels, data, `Total Absen Terlambat Bulan ${monthNames[month - 1]}`);
            }
        },
        error: function(xhr, status, error) {
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




function fetchTabInix(year) {
    $.ajax({
        url: `/getTabInix/${year}`, // URL endpoint
        type: 'GET',
        dataType: 'json',
        success: function(response) {
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
                    // "foto_office": `/storage/posts/${data.office_terbaik.sales.foto}`
                };

                // Menetapkan src pada setiap elemen .dynamic-image berdasarkan id
                $('.dynamic-image').each(function() {
                    const imageId = $(this).attr('id'); // Mendapatkan id dari setiap elemen gambar

                    // Cek apakah id ada di objek imageUrls, lalu set src-nya
                    if (imageUrls[imageId]) {
                        $(this).attr('src', imageUrls[imageId]);
                    }
                });
                $('#nama_sales').text(data.sales_terbaik.sales.nama_lengkap);
                $('#nama_instruktur').text(data.instruktur_terbaik.instruktur.nama_lengkap);

                if (data.keterlambatan.length >= 3) {
                    // Mengisi src untuk foto peringkat kedua
                    $('.second-position #present-photo').attr('src', '/storage/' + data.keterlambatan[1].foto);
        
                    // Mengisi src untuk foto peringkat pertama
                    $('.first-position #present-photo-satu').attr('src', '/storage/' + data.keterlambatan[0].foto);
        
                    // Mengisi src untuk foto peringkat ketiga
                    $('.third-position #present-photo').attr('src', '/storage/' + data.keterlambatan[2].foto);
                } else {
                    console.warn('Data keterlambatan kurang dari 3');
                }
                
            } else {
                console.warn("Data pada tahun ini tidak tersedia");
                alert("Data pada tahun ini tidak tersedia");
            }
        },
        error: function(xhr, status, error) {
            console.error('Error fetching data:', error);
        }
    });
}

function fetchNilaiFeedback(year, month) {
    $.ajax({
        url: `/getTotalFeedbackPerbulan/${year}/${month}`, // Fetch the specific year and month
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
            if(response.success) {
                if(response.data.length > 0) {
                    // Extract the data for the chart
                    const labels = response.data.map(item => item.instruktur_key);
                    const data = response.data.map(item => item.nilairatarata);
                    renderNilaiFeedbackChart(labels, data, `Nilai Feedback Bulan ${monthNames[month - 1]}`);
                }else{
                    console.warn("Data Nilai Feedback tidak tersedia");
                    alert(`Data bulan ${monthNames[month - 1]} tidak tersedia`);
                }
            } else {
                console.warn("Data Nilai Feedback tidak tersedia");
                alert(`Data bulan ${monthNames[month - 1]} tidak tersedia`);
            }
        },
        error: function(xhr, status, error) {
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
        success: function(response) {
            const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
            if(response.success == true) {
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
        error: function(xhr, status, error) {
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
        success: function(response) {
            const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
            if(month == 'All') {
                // Extract the data for the chart
                const labels = response.data.map(item => item.kategori_materi);
                const data = response.data.map(item => item.total_materi);
                renderTotalMateriChart(labels, data, `Total Mengajar Per Tahun`);
            } 
            else if(response.success == false) {
                console.warn("Data Total Mengajar tidak tersedia");
                alert(`Data bulan ${monthNames[month - 1]} tidak tersedia`);
                
            }
             else{
                const labels = response.data.map(item => item.kategori_materi);
                const data = response.data.map(item => item.total_materi);
                renderTotalMateriChart(labels, data, `Total Mengajar Bulan ${monthNames[month - 1]}`);
            }
        },
        error: function(xhr, status, error) {
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
                        padding: 10  // Memberikan jarak antara bar dengan sumbu Y
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
        success: function(response) {
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
        error: function(xhr, status, error) {
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
    if(title == 'Total Mengajar Per Tahun'){
        const ctx = document.getElementById('totalMengajarPerMateriChart').getContext('2d');
    
        if (window.totalMengajarPerMateriChart instanceof Chart) {
            window.totalMengajarPerMateriChart.destroy();  // Hapus chart yang lama
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
                            label: function(tooltipItem) {
                                return `${tooltipItem.dataset.label}: ${tooltipItem.raw}`; // Format label tooltip
                            }
                        }
                    }
                }
            }
        });
    }else{
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
                        beginAtZero: true // Menjaga agar sumbu Y mulai dari 0
                    }
                },
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.raw + ' kali'; // Menambahkan teks tambahan pada tooltip
                            }
                        }
                    }
                }
            }
        });
    }
}

function generateRandomColor() {
    // Fungsi untuk menghasilkan warna acak
    const letters = '0123456789ABCDEF';
    let color = '#';
    for (let i = 0; i < 6; i++) {
        color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
}