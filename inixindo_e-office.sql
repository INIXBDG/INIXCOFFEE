-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 16 Bulan Mei 2024 pada 05.34
-- Versi server: 10.4.28-MariaDB
-- Versi PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `inixindo_e-office`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `comments`
--

CREATE TABLE `comments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `rkm_key` varchar(255) NOT NULL,
  `materi_key` varchar(255) NOT NULL,
  `karyawan_key` varchar(255) NOT NULL,
  `content` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `comments`
--

INSERT INTO `comments` (`id`, `rkm_key`, `materi_key`, `karyawan_key`, `content`, `created_at`, `updated_at`) VALUES
(1, '14', '193', '14', 'nitip pre post tes ya pak', '2024-05-15 02:19:44', '2024-05-15 02:19:44');

-- --------------------------------------------------------

--
-- Struktur dari tabel `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `feedback`
--

CREATE TABLE `feedback` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kategori_feedback` varchar(255) NOT NULL,
  `pertanyaan` varchar(255) NOT NULL,
  `key` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `feedback`
--

INSERT INTO `feedback` (`id`, `kategori_feedback`, `pertanyaan`, `key`, `created_at`, `updated_at`) VALUES
(1, 'Materi', 'Sesuai dengan harapan anda', 'M', '2024-05-14 09:43:44', '2024-05-14 09:43:44'),
(2, 'Materi', 'Proporsi antara teori dengan praktek', 'M', '2024-05-14 09:44:01', '2024-05-14 09:44:01'),
(3, 'Materi', 'Mutu Materi', 'M', '2024-05-14 09:44:10', '2024-05-14 09:44:10'),
(4, 'Materi', 'Hasil cetakan materi', 'M', '2024-05-14 09:44:17', '2024-05-14 09:44:17'),
(5, 'Pelayanan', 'Informasi mudah dan tepat', 'P', '2024-05-14 09:44:28', '2024-05-14 09:44:28'),
(6, 'Pelayanan', 'Penyambutan dan pembukaan', 'P', '2024-05-14 09:44:36', '2024-05-14 09:44:36'),
(7, 'Pelayanan', 'Kenyamanan ruang kelas', 'P', '2024-05-14 09:44:45', '2024-05-14 09:44:45'),
(8, 'Pelayanan', 'Keramahan staf', 'P', '2024-05-14 09:44:54', '2024-05-14 09:44:54'),
(9, 'Pelayanan', 'Kesigapan staf dalam menangani masalah', 'P', '2024-05-14 09:45:03', '2024-05-14 09:45:03'),
(10, 'Pelayanan', 'Registrasi dan administrasi training', 'P', '2024-05-14 09:45:12', '2024-05-14 09:45:12'),
(11, 'Pelayanan', 'Kualitas makanan dan minuman', 'P', '2024-05-14 09:45:19', '2024-05-14 09:45:19'),
(12, 'Fasilitas Laboratium', 'Persiapan laboratorium sebelum training dimulai', 'F', '2024-05-14 09:45:30', '2024-05-14 09:45:30'),
(13, 'Fasilitas Laboratium', 'Kelengkapan sarana pendukung', 'F', '2024-05-14 09:45:39', '2024-05-14 09:45:39'),
(14, 'Fasilitas Laboratium', 'Kondisi peralatan laboratorium selama praktek', 'F', '2024-05-14 09:45:49', '2024-05-14 09:45:49'),
(15, 'Fasilitas Laboratium', 'Penataan instalasi laboratorium', 'F', '2024-05-14 09:45:59', '2024-05-14 09:45:59'),
(16, 'Fasilitas Laboratium', 'Kecepatan mengatasi problem di laboratorium', 'F', '2024-05-14 09:46:07', '2024-05-14 09:46:07'),
(17, 'Instruktur', 'Penguasaan Materi', 'I', '2024-05-14 09:46:19', '2024-05-14 09:46:19'),
(18, 'Instruktur', 'Penyampaian materi jelas dan baik', 'I', '2024-05-14 09:46:30', '2024-05-14 09:46:30'),
(19, 'Instruktur', 'Cara menjawab pertanyaan', 'I', '2024-05-14 09:46:42', '2024-05-14 09:46:42'),
(20, 'Instruktur', 'Cara menanggapi permasalahan dalam kelas', 'I', '2024-05-14 09:46:54', '2024-05-14 09:46:54'),
(21, 'Instruktur', 'Kesigapan membantu siswa dalam belajar', 'I', '2024-05-14 09:47:10', '2024-05-14 09:47:10'),
(22, 'Instruktur', 'Kepedulian Instruktur/Asisten diluar kelas', 'I', '2024-05-14 09:47:19', '2024-05-14 09:47:19'),
(23, 'Instruktur', 'Instruktur/Asisten mencerminkan profesional image', 'I', '2024-05-14 09:47:28', '2024-05-14 09:47:28'),
(24, 'Instruktur', 'Tepat waktu', 'I', '2024-05-14 09:47:34', '2024-05-14 09:47:34'),
(25, 'Umum', 'Pengalaman yang anda angggap berkesan sewaktu mengikuti training di sini?', 'U', '2024-05-14 09:47:48', '2024-05-14 09:47:48'),
(26, 'Umum', 'Saran dan Usulan perbaikan', 'U', '2024-05-14 09:47:56', '2024-05-14 09:47:56');

-- --------------------------------------------------------

--
-- Struktur dari tabel `jabatans`
--

CREATE TABLE `jabatans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_jabatan` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `jabatans`
--

INSERT INTO `jabatans` (`id`, `nama_jabatan`, `created_at`, `updated_at`) VALUES
(1, 'Komisaris', NULL, NULL),
(2, 'Direktur Utama', NULL, NULL),
(3, 'Direktur', NULL, NULL),
(4, 'Education Manager', NULL, NULL),
(5, 'Instruktur', NULL, NULL),
(6, 'Technical Support', NULL, NULL),
(7, 'GM', NULL, NULL),
(8, 'SPV Sales', NULL, NULL),
(9, 'Adm Sales', NULL, NULL),
(10, 'Sales', NULL, NULL),
(11, 'Tim Digital', NULL, NULL),
(12, 'Accounting', NULL, NULL),
(13, 'Finance & Accounting', NULL, NULL),
(14, 'HRD', NULL, NULL),
(15, 'Customer Service', NULL, NULL),
(16, 'Customer Care', NULL, NULL),
(17, 'Office Boy', NULL, NULL),
(18, 'Driver', NULL, NULL),
(19, 'Programmer', NULL, NULL),
(20, 'Office Manager', '2024-05-13 08:09:37', '2024-05-13 08:09:37'),
(21, 'Admin Holding', '2024-05-13 08:09:46', '2024-05-13 08:09:46');

-- --------------------------------------------------------

--
-- Struktur dari tabel `karyawans`
--

CREATE TABLE `karyawans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `nip` varchar(255) DEFAULT NULL,
  `nama_lengkap` varchar(255) NOT NULL,
  `divisi` varchar(255) DEFAULT NULL,
  `jabatan` varchar(255) DEFAULT NULL,
  `rekening_maybank` varchar(255) DEFAULT NULL,
  `rekening_bca` varchar(255) DEFAULT NULL,
  `status_aktif` enum('0','1') NOT NULL,
  `awal_probation` date DEFAULT NULL,
  `akhir_probation` date DEFAULT NULL,
  `awal_kontrak` date DEFAULT NULL,
  `akhir_kontrak` date DEFAULT NULL,
  `awal_tetap` date DEFAULT NULL,
  `akhir_tetap` date DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `kode_karyawan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `karyawans`
--

INSERT INTO `karyawans` (`id`, `foto`, `nip`, `nama_lengkap`, `divisi`, `jabatan`, `rekening_maybank`, `rekening_bca`, `status_aktif`, `awal_probation`, `akhir_probation`, `awal_kontrak`, `akhir_kontrak`, `awal_tetap`, `akhir_tetap`, `keterangan`, `kode_karyawan`, `created_at`, `updated_at`) VALUES
(1, '', '1000000', 'Ifik Arifin', 'Direksi', 'Komisaris', 'CIMB Niaga: 707294444000', '', '1', '0000-00-00', '0000-00-00', '0000-00-00', '0000-00-00', '0000-00-00', '0000-00-00', '', '', NULL, NULL),
(2, '', '1010001', 'Ray G. Manurung', 'Direksi', 'Direktur Utama', '1-044-697947', '', '1', '0000-00-00', '0000-00-00', '0000-00-00', '0000-00-00', '0000-00-00', '0000-00-00', '', '', NULL, NULL),
(3, '', '1010002', 'Stannia Lestari', 'Direksi', 'Direktur', '1-141-53725-7', '', '1', '0000-00-00', '0000-00-00', '0000-00-00', '0000-00-00', '0000-00-00', '0000-00-00', '', '', NULL, NULL),
(4, '', '2110004', 'M.Adhisyanda Aditya', 'Education', 'Education Manager', '1-424-00249-6', '183441694', '1', '2011-09-11', '2011-11-30', '2011-12-01', '2013-11-30', '2015-12-15', NULL, NULL, 'AD', NULL, '2024-05-13 08:21:35'),
(5, '', '2190010', 'Wahyu Tri Setiawan', 'Education', 'Instruktur', '1-757-00138-6', '2820376903', '1', '2019-08-22', '2020-01-31', '2020-02-01', '2020-01-31', NULL, NULL, NULL, 'WY', NULL, '2024-05-13 08:23:03'),
(6, '', '2220014', 'Muhamad Pani Rayadi', 'Education', 'Instruktur', '875-791-9438', '7772691842', '1', '2022-06-01', '2022-08-31', '2022-09-01', '2024-08-31', NULL, NULL, NULL, 'PN', NULL, '2024-05-13 08:25:12'),
(7, '', '2220015', 'Sabdhan Prasetio', 'Education', 'Instruktur', '873-292-1625', '2831887216', '1', '2022-06-01', '2022-08-31', '2022-09-01', '2024-08-31', NULL, NULL, NULL, 'SB', NULL, '2024-05-13 08:26:07'),
(8, '', '2220019', 'Luki Prasetyo', 'Education', 'Instruktur', '804-492-5444', '1390810632', '1', '2022-08-11', '2022-11-11', '2022-11-12', '2024-11-11', NULL, NULL, NULL, 'LU', NULL, '2024-05-13 08:27:04'),
(9, '', '2230023', 'Rustan', 'Education', 'Instruktur', '879-194-6388', '8480404549', '1', NULL, NULL, '2024-01-01', '2024-12-31', NULL, NULL, NULL, 'RS', NULL, '2024-05-13 08:27:55'),
(10, '', '2230025', 'Syahrul Firdaus', 'Education', 'Instruktur', '804-493-8366', '1393267422', '1', '2023-08-01', '2023-12-31', '2024-01-01', '2025-12-31', NULL, NULL, NULL, 'SH', NULL, '2024-05-13 08:28:52'),
(11, '', '2220016', 'Naufal Hatta', 'Education', 'Technical Support', '875-791-9421', '84054573', '1', '2023-08-01', '2023-12-31', '2024-01-01', '2025-12-31', NULL, NULL, NULL, 'NF', NULL, '2024-05-13 08:29:49'),
(12, '', '2220018', 'Virel Nativiti', 'Education', 'Technical Support', '879-291-0830', '160351993', '1', '2022-07-01', '2022-09-30', '2022-10-01', '2024-09-30', NULL, NULL, NULL, 'VR', NULL, '2024-05-13 08:31:41'),
(13, '', '3070003', 'Hani Meylani', 'Sales & Marketing', 'GM', '1-183-15268-4', '4020195113', '1', '2007-03-01', '2007-04-30', '2007-05-01', '2008-04-30', NULL, NULL, NULL, 'HM', NULL, '2024-05-13 08:33:06'),
(14, '', '3160007', 'Aryani Meitasari', 'Sales & Marketing', 'SPV Sales', '1-424-37837-2', '2330093326', '1', '2016-10-01', '2016-12-31', '2017-01-01', '2018-12-31', NULL, NULL, NULL, 'AM', NULL, '2024-05-13 08:34:03'),
(15, '', '3230024', 'Donna Zahrah H', 'Sales & Marketing', 'Adm Sales', '8-710-94200-6', '7772902207', '1', '2023-09-11', '2023-11-30', '2023-12-01', '2025-07-31', NULL, NULL, NULL, NULL, NULL, '2024-05-13 08:35:20'),
(16, '', '3230021', 'Herawati', 'Sales & Marketing', 'Sales', '8-760-95648-8', '4372779148', '1', '2023-05-08', '2023-07-31', '2023-08-01', '2025-07-31', NULL, NULL, NULL, 'HW', NULL, '2024-05-13 08:37:17'),
(17, '', '3180008', 'Zenith Ratu Negara', 'Sales & Marketing', 'Sales', '1-424-37974-0', '83617397', '1', '0000-00-00', '2018-02-01', '0000-00-00', '0000-00-00', '0000-00-00', '0000-00-00', '', 'ZN', NULL, NULL),
(18, '', '3210011', 'Tanjung Mirza Savana', 'Sales & Marketing', 'Sales', '8-757-00611-1', '1393096857', '1', '2021-01-15', '2021-04-30', '2021-05-01', '2023-04-30', NULL, NULL, NULL, 'VN', NULL, '2024-05-13 09:12:25'),
(19, '', '3220013', 'Haura Navita Haya', 'Sales & Marketing', 'Sales', '8-044-91112-5', '2330508674', '1', '2022-03-28', '2022-06-30', '2022-07-01', '2024-06-30', NULL, NULL, NULL, 'RR', NULL, '2024-05-13 09:13:54'),
(20, '', '3210012', 'Prayuni Larasati', 'Sales & Marketing', 'Tim Digital', '8-757-01065-5', '6395034409', '0', '2021-10-11', '2022-01-30', '2022-02-01', '2024-01-30', NULL, NULL, NULL, NULL, NULL, '2024-05-14 03:56:37'),
(21, '', '4190009', 'Muhamad Dzarin W', 'Office', 'Accounting', '1-757-24574-5', '1392908506', '1', '2019-08-12', '2019-11-12', '2019-11-12', '2020-10-12', NULL, NULL, NULL, NULL, NULL, '2024-05-13 09:20:36'),
(22, '', '4230026', 'Xepi Twinarti', 'Office', 'Finance & Accounting', NULL, '7940378642', '1', '2023-12-19', '2024-03-30', '2024-04-01', '2026-03-30', NULL, NULL, NULL, NULL, NULL, '2024-05-13 09:21:44'),
(23, '', '4220017', 'Aulira Reta Sari', 'Office', 'HRD', '871-091-9360', '8480377274', '1', '2022-06-01', '2024-08-31', '2022-09-13', '2024-08-31', NULL, NULL, NULL, NULL, NULL, '2024-05-13 09:22:36'),
(24, '', '4220020', 'Ratu Salma Salsabila', 'Office', 'Admin Holding', '801-591-3799', '7751201439', '1', '2022-09-13', '2022-11-30', '2022-12-01', '2024-11-30', NULL, NULL, NULL, NULL, NULL, '2024-05-13 09:23:39'),
(25, '', '4230022', 'Rissa Damayanti', 'Office', 'Customer Care', '875-793-9322', '6395658542', '1', '2023-06-19', '2023-09-30', '2023-10-01', '2025-09-30', NULL, NULL, NULL, NULL, NULL, '2024-05-13 09:24:44'),
(26, '', '3240028', 'Reni Nuraeni', 'Sales & Marketing', 'Sales', NULL, '2810618569', '1', '2024-02-19', '2024-05-31', '2024-06-01', '2026-05-31', NULL, NULL, NULL, 'RN', NULL, '2024-05-13 09:25:54'),
(27, '', '2240029', 'Yanuar Taruna Lutfi', 'Education', 'Instruktur', NULL, '3370913781', '1', '2024-02-19', '2024-05-31', '2024-06-01', '2026-05-31', NULL, NULL, NULL, 'YN', NULL, '2024-05-13 09:26:28'),
(28, '', '3240030', 'Julietta Siti Refqa', 'Sales & Marketing', 'Tim Digital', NULL, '7772970296', '1', NULL, NULL, '2024-02-21', '2026-02-28', NULL, NULL, NULL, NULL, NULL, '2024-05-13 09:27:08'),
(29, '', '2240032', 'Muhamad Ardhan H', 'Education', 'Programmer', NULL, '4490162106', '1', NULL, NULL, '2024-03-13', '2026-03-31', NULL, NULL, NULL, NULL, NULL, '2024-05-13 09:28:10'),
(30, NULL, '03240033', 'Nabila', 'Sales & Marketing', 'Sales', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'NA', '2024-05-13 08:13:36', '2024-05-14 03:44:07'),
(31, NULL, '04140005', 'Cecep Supriyadi', 'Office', 'Office Boy', '1-424-11232-0', '2832556719', '1', '2014-02-01', '2014-04-30', '2014-05-01', '2016-04-30', NULL, NULL, NULL, 'CS', '2024-05-13 08:16:42', '2024-05-13 09:49:17'),
(32, NULL, '04150006', 'Asep Koswara', 'Office', 'Driver', '1-424-52463-3', '7771875614', '1', '2015-06-01', '2015-05-31', '2015-06-01', '2016-05-31', NULL, NULL, NULL, 'AK', '2024-05-13 08:17:12', '2024-05-13 09:51:39'),
(33, NULL, '04240027', 'Triyono', 'Office', 'Driver', NULL, '3371995461', '1', '2024-01-22', '2024-04-30', '2024-05-01', '2026-04-30', NULL, NULL, NULL, 'TR', '2024-05-13 08:17:48', '2024-05-14 03:45:46'),
(34, NULL, '04240031', 'Muhamad Ramdhani', 'Office', 'Office Boy', NULL, '4490162106', '1', '2024-02-22', '2024-05-31', '2024-06-01', '2026-05-31', NULL, NULL, NULL, 'MR', '2024-05-13 08:18:23', '2024-05-14 03:47:35'),
(35, NULL, '12345678', 'spvsales', 'Sales & Marketing', 'SPV Sales', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-05-14 03:54:24', '2024-05-14 03:55:29');

-- --------------------------------------------------------

--
-- Struktur dari tabel `materis`
--

CREATE TABLE `materis` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_materi` varchar(255) NOT NULL,
  `kode_materi` varchar(255) DEFAULT NULL,
  `kategori_materi` varchar(255) DEFAULT NULL,
  `vendor` varchar(255) DEFAULT NULL,
  `silabus` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `materis`
--

INSERT INTO `materis` (`id`, `nama_materi`, `kode_materi`, `kategori_materi`, `vendor`, `silabus`, `created_at`, `updated_at`) VALUES
(1, 'Administering Microsoft SQL Server Database', '', 'Data Engineer', 'Microsoft', NULL, NULL, NULL),
(2, 'Agile and Scrum Framework', '', 'Software Engineer', 'Regular', NULL, NULL, NULL),
(4, 'Analisa dan Test Penetrasi Keamanan TI', '', 'Security', 'Regular', NULL, NULL, NULL),
(5, 'Analisis Cloud Computing', '', 'Cloud', 'LSP', NULL, NULL, NULL),
(6, 'Analisis Sistem Pertahanan dan Perlindungan Keamanan Informasi', '', 'Security', 'Regular', NULL, NULL, NULL),
(7, 'Android Programming with Kotlin', '', 'Programming', 'Regular', NULL, NULL, NULL),
(8, 'Artificial Intellegence Fundamentals with Python', '', 'Data Analist', 'Regular', NULL, NULL, NULL),
(9, 'Audit Keamanan TI', '', 'Management', 'Regular', NULL, NULL, NULL),
(10, 'Augmented Reality (AR)', '', 'Programming', 'Regular', NULL, NULL, NULL),
(11, 'AWS Certified Cloud Practitioner', '', 'Cloud', 'AWS', NULL, NULL, NULL),
(12, 'Basic Java Programming', '', 'Programming', 'Regular', NULL, NULL, NULL),
(13, 'Bigdata with Hadoop and Spark', '', 'Data Engineer', 'Regular', NULL, NULL, NULL),
(15, 'BNSP Pengelolaan Layanan Teknologi Informasi', '', 'Management', 'LSP', NULL, NULL, NULL),
(16, 'Building Android/IOS Application with IONIC', '', 'Programming', 'Regular', NULL, NULL, NULL),
(17, 'Building Web Application with Laravel', '', 'Programming', 'Regular', NULL, NULL, NULL),
(18, 'Building Web Application with PHP & MySQL', '', 'Programming', 'Regular', NULL, NULL, NULL),
(19, 'Building Web Application with PHP CodeIgniter', '', 'Programming', 'Regular', NULL, NULL, NULL),
(22, 'Business Analysis Essentials ( BABOK )', '', 'Management', 'Regular', NULL, NULL, NULL),
(23, 'Business Process Management', '', 'Management', 'Regular', NULL, NULL, NULL),
(24, 'Business Writing Skill', '', 'Non IT', 'Regular', NULL, NULL, NULL),
(26, 'Certified Cisco Network Associate Accelerated', '', 'Networking', 'Cisco', NULL, NULL, NULL),
(27, 'Certified Data Center Professional (CDCP)', '', 'Data Center', 'EPI', NULL, NULL, NULL),
(29, 'Certified Ethical Hacker (CEH)', '', 'Security', 'EC-Council', NULL, NULL, NULL),
(31, 'Certified in The Governance Of Enterprise', '', 'Management', 'Regular', NULL, NULL, NULL),
(32, 'Certified Incident Handler', '', 'Security', 'EC-Council', NULL, NULL, NULL),
(33, 'Certified Infomation Security Manager', '', 'Security', 'Regular', NULL, NULL, NULL),
(34, 'Certified Network Defender (CND)', '', 'Security', 'EC-Council', NULL, NULL, NULL),
(37, 'Certified Secure Computer User', '', 'Security', 'EC-Council', NULL, NULL, NULL),
(38, 'Chief Information Officer', '', 'Management', 'LSP', NULL, NULL, NULL),
(39, 'CISSP', '', 'Security', 'Regular', NULL, NULL, NULL),
(41, 'COBIT 2019', '', 'Management', 'Regular', NULL, NULL, NULL),
(42, 'CompTIa Network+', '', 'Networking', 'CompTIA', NULL, NULL, NULL),
(44, 'CompTIA Security+', '', 'Security', 'CompTIA', NULL, NULL, NULL),
(46, 'Computer Hacking Forensic Investigator (CHFI)', '', 'Security', 'EC-Council', NULL, NULL, NULL),
(48, 'Cross Platform Mobile Development with Flutter', '', 'Programming', 'Regular', NULL, NULL, NULL),
(50, 'Cyber Security : SOC Analyst Incident Response and Forensic Analyst', '', 'Security', 'Regular', NULL, NULL, NULL),
(51, 'Data Analysis and Visualization with Microsoft Excel', '', 'Office', 'Microsoft', NULL, NULL, NULL),
(52, 'Data Analysis Fundamentals using Excel', '', 'Office', 'Microsoft', NULL, NULL, NULL),
(53, 'Data Analyst', '', 'Data Analist', 'Regular', NULL, NULL, NULL),
(54, 'Data Analytics and Machine Learning with Python', '', 'Data Analist', 'Regular', NULL, NULL, NULL),
(55, 'Data Center Virtualization with VMware vSphere', '', 'Virtualization', 'Regular', NULL, NULL, NULL),
(56, 'Data Exchange (X-road)', '', 'Server', 'Regular', NULL, NULL, NULL),
(57, 'Data Storytelling and Visualization with Tableau', '', 'Data Analist', 'Regular', NULL, NULL, NULL),
(58, 'Data Visualization and Data Blending with Tableau', '', 'Data Analist', 'Regular', NULL, NULL, NULL),
(59, 'Data Visualization with Phyton', '', 'Data Analist', 'Regular', NULL, NULL, NULL),
(60, 'Data Warehouse Microsoft 20463D', '', 'Data Engineer', 'Microsoft', NULL, NULL, NULL),
(61, 'Database Administrator', '', 'Data Engineer', 'Regular', NULL, NULL, NULL),
(62, 'Database Design : A Modern Approach & Database Administrator', '', 'Data Engineer', 'Regular', NULL, NULL, NULL),
(64, 'Desain Presentasi Interaktif Bagi ASN (Kelas 1)', '', 'Office', 'Regular', NULL, NULL, NULL),
(65, 'Desain Presentasi Interaktif Bagi ASN (Kelas 2)', '', 'Office', 'Regular', NULL, NULL, NULL),
(66, 'Designing and Administresing Database', '', 'Data Engineer', 'Regular', NULL, NULL, NULL),
(67, 'DevOps Engineer', '', 'Software Engineer', 'Regular', NULL, NULL, NULL),
(69, 'Devops Introduction and Docker', '', 'Software Engineer', 'Regular', NULL, NULL, NULL),
(70, 'Digital Leadership', '', 'Management', 'Regular', NULL, NULL, NULL),
(71, 'Digital Transformation Plan and Strategy', '', 'Management', 'Regular', NULL, NULL, NULL),
(72, 'Disaster Recovery and Business Continuity', '', 'Management', 'Regular', NULL, NULL, NULL),
(73, 'Distributed Machine Learning', '', 'Data Analist', 'Regular', NULL, NULL, NULL),
(74, 'DMBOK', '', 'Management', 'Regular', NULL, NULL, NULL),
(75, 'Docker and Kubernates', '', 'Server', 'Regular', NULL, NULL, NULL),
(76, 'Document Management System', '', 'Management', 'Regular', NULL, NULL, NULL),
(77, 'E-learning Methodologies and Good Practice', '', 'Management', 'Regular', NULL, NULL, NULL),
(78, 'EC Council Certified Incident Handler', '', 'Security', 'EC-Council', NULL, NULL, NULL),
(80, 'EDRP', '', 'Management', 'EC-Council', NULL, NULL, NULL),
(82, 'Fiber Optic for Beginner', '', 'Networking', 'Regular', NULL, NULL, NULL),
(83, 'Figma UI/UX', '', 'Software Engineer', 'Regular', NULL, NULL, NULL),
(84, 'Fortigate', '', 'Security', 'Regular', NULL, NULL, NULL),
(85, 'Foundations for Micorosft Excel Knowledge and Skills', '', 'Office', 'Microsoft', NULL, NULL, NULL),
(86, 'Fullstack (Backend Web Api Programming, Front End Web Development, Mobile Front End with PWA)', '', 'Programming', 'Regular', NULL, NULL, NULL),
(87, 'Fullstack Developer with Laravel', '', 'Programming', 'Regular', NULL, NULL, NULL),
(88, 'Fullstack Developer with Laravel & React Js', '', 'Programming', 'Regular', NULL, NULL, NULL),
(89, 'Fullstack Developer with Laravel and Angular', '', 'Programming', 'Regular', NULL, NULL, NULL),
(90, 'Fundamental DevOps', '', 'Software Engineer', 'Regular', NULL, NULL, NULL),
(91, 'Government Chief Information Officer', '', 'Management', 'LSP', NULL, NULL, NULL),
(92, 'Google Cloud Fundamental: Core Infrastruktur', '', 'Cloud', 'Google', NULL, NULL, NULL),
(94, 'HTML, CSS3 and Javascript programming', '', 'Programming', 'Regular', NULL, NULL, NULL),
(95, 'Implementasi Hadoop Big Data', '', 'Data Engineer', 'Regular', NULL, NULL, NULL),
(96, 'Implementing a Data Warehouse with Microsoft SQL Server', '', 'Data Engineer', 'Microsoft', NULL, NULL, NULL),
(97, 'Implementing and Administering Cisco Solutions (CCNA) V 1.0', '', 'Networking', 'Cisco', NULL, NULL, NULL),
(98, 'Implementing and Operating Cisco Enterprise Network Core technologies (ENCOR)', '', 'Networking', 'Cisco', NULL, NULL, NULL),
(99, 'Implementing Cyber Security to Securing Your Organization', '', 'Security', 'Regular', NULL, NULL, NULL),
(100, 'Impleneting Cisco Enterprise Advanced Routing and Services (ENARSI)', '', 'Networking', 'Cisco', NULL, NULL, NULL),
(102, 'Information Security Management System (ISMS) Lead to Implement ISO 27001', '', 'Security', 'Regular', NULL, NULL, NULL),
(103, 'Information System Auditor', '', 'Management', 'Regular', NULL, NULL, NULL),
(104, 'Information System Security Protection Knowledge and Preparation for Certification', '', 'Security', 'Regular', NULL, NULL, NULL),
(105, 'Introduction Microsoft Excel 2019', '', 'Office', 'Microsoft', NULL, NULL, NULL),
(106, 'Introduction to DevOps With Docker', '', 'Software Engineer', 'Regular', NULL, NULL, NULL),
(107, 'Introduction to google cloud security', '', 'Cloud', 'Google', NULL, NULL, NULL),
(108, 'Introduction to Sharepoint 2016 (MOC 55193AC)', '', 'Server', 'Microsoft', NULL, NULL, NULL),
(110, 'IT Business Analyst', '', 'Software Engineer', 'Regular', NULL, NULL, NULL),
(111, 'IT Governance and Entreprise Architecture', '', 'Management', 'Regular', NULL, NULL, NULL),
(112, 'IT Governance with Cobit 2019', '', 'Management', 'Regular', NULL, NULL, NULL),
(113, 'IT Infrastructure Library (IT-IL)', '', 'Management', 'Regular', NULL, NULL, NULL),
(116, 'IT Management Essentials', '', 'Management', 'Regular', NULL, NULL, NULL),
(118, 'IT Risk Management', '', 'Management', 'Regular', NULL, NULL, NULL),
(119, 'IT Service Management', '', 'Management', 'Regular', NULL, NULL, NULL),
(120, 'Junior Linux System Administrator', '', 'Server', 'Regular', NULL, NULL, NULL),
(121, 'Junior Web Developer', '', 'Programming', 'LSP', NULL, NULL, NULL),
(122, 'Juniper Networks', '', 'Networking', 'Regular', NULL, NULL, NULL),
(123, 'Knowledge on Enterprise IT Architecture (TOGAF)', '', 'Management', 'Regular', NULL, NULL, NULL),
(125, 'Linux from Scratch to Advanced Level', '', 'Server', 'Regular', NULL, NULL, NULL),
(126, 'Linux Fundamentals', '', 'Server', 'Regular', NULL, NULL, NULL),
(127, 'Linux System Administration', '', 'Server', 'Regular', NULL, NULL, NULL),
(128, 'Managing Data With Google Sheet', '', 'Office', 'Google', NULL, NULL, NULL),
(129, 'Managing Microsoft Teams', '', 'Office', 'Microsoft', NULL, NULL, NULL),
(130, 'Manajemen Pengawasan', '', 'Non-IT', 'Regular', NULL, NULL, NULL),
(131, 'Mastering Microsoft Project 2016', '', 'Management', 'Microsoft', NULL, NULL, NULL),
(134, 'Microservice', '', 'Software Engineer', 'Regular', NULL, NULL, NULL),
(135, 'Microservice with Docker', '', 'Software Engineer', 'Regular', NULL, NULL, NULL),
(136, 'Microsoft 365 Fundamental', '', 'Server', 'Microsoft', NULL, NULL, NULL),
(137, 'Microsoft Azure Fundamental ( AZ - 900T00 ) + MS - 101T00 MIcrosoft 365 Mobility and Security', '', 'Cloud', 'Microsoft', NULL, NULL, NULL),
(140, 'Microsoft Azure Administrator', '', 'Cloud', 'Microsoft', NULL, NULL, NULL),
(141, 'Microsoft Excel Basic', '', 'Office', 'Microsoft', NULL, NULL, NULL),
(144, 'Microsoft Power BI Data Analyst', '', 'Data Analist', 'Microsoft', NULL, NULL, NULL),
(145, 'Mikrotik Certified Network Associate', '', 'Networking', 'Mikrotik', NULL, NULL, NULL),
(146, 'MOC 20486 D Developing ASP NET Core MVC Web Applications', '', 'Programming', 'Microsoft', NULL, NULL, NULL),
(149, 'MikroTik Certified Routing Engineer (MTCRE)', '', 'Networking', 'Mikrotik', NULL, NULL, NULL),
(150, 'MySQL Backup Recovery and Replication', '', 'Data Engineer', 'Regular', NULL, NULL, NULL),
(151, 'MySQL Full Package', '', 'Data Engineer', 'Regular', NULL, NULL, NULL),
(152, 'MySQL Performance Tuning', '', 'Data Engineer', 'Regular', NULL, NULL, NULL),
(153, 'Network Security Auditor', '', 'Security', 'LSP', NULL, NULL, NULL),
(155, 'NodeJs', '', 'Programming', 'Regular', NULL, NULL, NULL),
(156, 'Office 365 For The End User', '', 'Office', 'Microsoft', NULL, NULL, NULL),
(157, 'Oracle Data Warehousing Fundamentals', '', 'Data Engineer', 'Regular', NULL, NULL, NULL),
(158, 'PC Hardware and Network Technical Support', '', 'Hardware', 'CompTIA', NULL, NULL, NULL),
(163, 'Penerapan Microservices dengan NodeJS Backend & Web', '', 'Programming', 'Regular', NULL, NULL, NULL),
(164, 'Pengelolaan Data Center', '', 'Data Center', 'LSP', NULL, NULL, NULL),
(166, 'Pengelolaan Keamanan Informasi', '', 'Security', 'LSP', NULL, NULL, NULL),
(170, 'Pentaho', '', 'Data Analist', 'Regular', NULL, NULL, NULL),
(171, 'Phyton Advance', '', 'Programming', 'Regular', NULL, NULL, NULL),
(172, 'Phyton Programming Introduction', '', 'Programming', 'Regular', NULL, NULL, NULL),
(173, 'PMP', '', 'Management', 'Regular', NULL, NULL, NULL),
(174, 'Postgree Advance', '', 'Data Engineer', 'Regular', NULL, NULL, NULL),
(177, 'Programming with Golang', '', 'Programming', 'Regular', NULL, NULL, NULL),
(179, 'Project Management (PMBOKv7)', '', 'Management', 'Regular', NULL, NULL, NULL),
(183, 'Proxmox Installation and Administration', '', 'Virtualization', 'Regular', NULL, NULL, NULL),
(184, 'Querying Data with Transact-SQL', '', 'Data Engineer', 'Microsoft', NULL, NULL, NULL),
(185, 'Querying Microsoft SQL Server', '', 'Data Engineer', 'Microsoft', NULL, NULL, NULL),
(187, 'Rest Full API With Laravel', '', 'Programming', 'Regular', NULL, NULL, NULL),
(189, 'SC - 900T0 : Microsoft Security , Compliance , and Identity Fundamentals', '', 'Cloud', 'Microsoft', NULL, NULL, NULL),
(190, 'Secure Software Development Cycle', '', 'Software Engineer', 'Regular', NULL, NULL, NULL),
(191, 'Security Operation center', '', 'Security', 'Regular', NULL, NULL, NULL),
(193, 'SharePoint 2019 Power User', '', 'Server', 'Microsoft', NULL, NULL, NULL),
(194, 'SIG Tingkat Dasar Menggunakan arcGIS 10.3', '', 'GIS', 'Regular', NULL, NULL, NULL),
(195, 'Social Media Analyst', '', 'Non IT', 'Regular', NULL, NULL, NULL),
(196, 'Software Quality Assurance', '', 'Software Engineer', 'Regular', NULL, NULL, NULL),
(199, 'Springboot', '', 'Programming', 'Regular', NULL, NULL, NULL),
(200, 'System Analyst and Design', '', 'Software Engineer', 'Regular', NULL, NULL, NULL),
(202, 'Tableau Desktop', '', 'Data Analist', 'Regular', NULL, NULL, NULL),
(203, 'Tata Kelola Command Center', '', 'Management', 'Regular', NULL, NULL, NULL),
(204, 'Video Editing With Adobe Premiere', '', 'Multimedia', 'Regular', NULL, NULL, NULL),
(205, 'Virtual Reality with Unity', '', 'Programming', 'Regular', NULL, NULL, NULL),
(206, 'VMware vSphere Install Configure Manage', '', 'Virtualization', 'Regular', NULL, NULL, NULL),
(207, 'Web Security', '', 'Programming', 'Regular', NULL, NULL, NULL),
(209, 'Windows Server 2019 Administration', '', 'Server', 'Microsoft', NULL, NULL, NULL),
(210, 'Programming Series: Hybrid Mobile App Development Course With React Native', NULL, 'Programming', 'Regular', NULL, '2024-05-15 02:54:42', '2024-05-15 02:54:42'),
(212, 'Certified Risk and Information System Control (CRISC)', NULL, 'Security', 'Regular', NULL, '2024-05-16 01:44:29', '2024-05-16 01:44:29'),
(213, 'Junior Mobile Programming', NULL, 'Programming', 'Regular', NULL, '2024-05-16 03:01:41', '2024-05-16 03:01:41'),
(214, 'Certified Data Science Specialist - CDSS Certification', NULL, 'Data Center', 'Regular', NULL, '2024-05-16 03:15:25', '2024-05-16 03:15:25'),
(215, 'Microsoft Excel Intermediate', NULL, 'Office', 'Microsoft', NULL, '2024-05-16 03:28:34', '2024-05-16 03:28:34'),
(216, 'Microsoft Excel Advance', NULL, 'Office', 'Microsoft', NULL, '2024-05-16 03:28:47', '2024-05-16 03:28:47');

-- --------------------------------------------------------

--
-- Struktur dari tabel `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(2, '2014_10_12_000000_create_users_table', 1),
(3, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(4, '2014_10_12_100000_create_password_resets_table', 1),
(5, '2019_08_19_000000_create_failed_jobs_table', 1),
(6, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(7, '2024_03_19_012107_create_karyawans_table', 1),
(8, '2024_03_25_022126_create_perusahaans_table', 1),
(9, '2024_03_25_022132_create_materis_table', 1),
(10, '2024_03_25_062357_create_r_k_m_s_table', 1),
(11, '2024_03_26_020534_create_comments_table', 1),
(12, '2024_04_04_082648_create_pesertas_table', 1),
(13, '2024_04_04_131532_create_registrasis_table', 1),
(14, '2024_04_19_150756_create_feedback_table', 1),
(15, '2024_04_23_085257_create_nilaifeedbacks_table', 1),
(16, '2024_04_29_085258_create_jabatans_table', 1),
(17, '2024_05_08_115639_create_notifs_table', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `nilaifeedbacks`
--

CREATE TABLE `nilaifeedbacks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_regist` varchar(255) NOT NULL,
  `id_rkm` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `M1` varchar(255) NOT NULL,
  `M2` varchar(255) NOT NULL,
  `M3` varchar(255) NOT NULL,
  `M4` varchar(255) NOT NULL,
  `P1` varchar(255) NOT NULL,
  `P2` varchar(255) NOT NULL,
  `P3` varchar(255) NOT NULL,
  `P4` varchar(255) NOT NULL,
  `P5` varchar(255) NOT NULL,
  `P6` varchar(255) NOT NULL,
  `P7` varchar(255) NOT NULL,
  `F1` varchar(255) NOT NULL,
  `F2` varchar(255) NOT NULL,
  `F3` varchar(255) NOT NULL,
  `F4` varchar(255) NOT NULL,
  `F5` varchar(255) NOT NULL,
  `I1` varchar(255) NOT NULL,
  `I2` varchar(255) NOT NULL,
  `I3` varchar(255) NOT NULL,
  `I4` varchar(255) NOT NULL,
  `I5` varchar(255) NOT NULL,
  `I6` varchar(255) NOT NULL,
  `I7` varchar(255) NOT NULL,
  `I8` varchar(255) NOT NULL,
  `I1b` varchar(255) DEFAULT NULL,
  `I2b` varchar(255) DEFAULT NULL,
  `I3b` varchar(255) DEFAULT NULL,
  `I4b` varchar(255) DEFAULT NULL,
  `I5b` varchar(255) DEFAULT NULL,
  `I6b` varchar(255) DEFAULT NULL,
  `I7b` varchar(255) DEFAULT NULL,
  `I8b` varchar(255) DEFAULT NULL,
  `I1as` varchar(255) DEFAULT NULL,
  `I2as` varchar(255) DEFAULT NULL,
  `I3as` varchar(255) DEFAULT NULL,
  `I4as` varchar(255) DEFAULT NULL,
  `I5as` varchar(255) DEFAULT NULL,
  `I6as` varchar(255) DEFAULT NULL,
  `I7as` varchar(255) DEFAULT NULL,
  `I8as` varchar(255) DEFAULT NULL,
  `U1` varchar(255) NOT NULL,
  `U2` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `nilaifeedbacks`
--

INSERT INTO `nilaifeedbacks` (`id`, `id_regist`, `id_rkm`, `email`, `M1`, `M2`, `M3`, `M4`, `P1`, `P2`, `P3`, `P4`, `P5`, `P6`, `P7`, `F1`, `F2`, `F3`, `F4`, `F5`, `I1`, `I2`, `I3`, `I4`, `I5`, `I6`, `I7`, `I8`, `I1b`, `I2b`, `I3b`, `I4b`, `I5b`, `I6b`, `I7b`, `I8b`, `I1as`, `I2as`, `I3as`, `I4as`, `I5as`, `I6as`, `I7as`, `I8as`, `U1`, `U2`, `created_at`, `updated_at`) VALUES
(1, '1', '3', 'pandu.kawan@gmail.com', '4', '4', '4', '4', '4', '4', '4', '4', '4', '4', '4', '4', '4', '4', '4', '4', '4', '4', '4', '4', '4', '4', '4', '4', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Materinya luar biasa', 'Mantap', '2024-05-14 09:49:56', '2024-05-14 09:49:56');

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifs`
--

CREATE TABLE `notifs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_user` varchar(255) NOT NULL,
  `tipe_notifikasi` varchar(255) NOT NULL,
  `isi_notifikasi` text NOT NULL,
  `tanggal_awal` text NOT NULL,
  `tanggal_akhir` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `perusahaans`
--

CREATE TABLE `perusahaans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_perusahaan` varchar(255) NOT NULL,
  `kategori_perusahaan` varchar(255) DEFAULT NULL,
  `lokasi` varchar(255) DEFAULT NULL,
  `sales_key` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `npwp` varchar(255) DEFAULT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `cp` varchar(255) DEFAULT NULL,
  `no_telp` varchar(255) DEFAULT NULL,
  `foto_npwp` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `perusahaans`
--

INSERT INTO `perusahaans` (`id`, `nama_perusahaan`, `kategori_perusahaan`, `lokasi`, `sales_key`, `status`, `npwp`, `alamat`, `cp`, `no_telp`, `foto_npwp`, `created_at`, `updated_at`) VALUES
(1, 'Kementerian Komunikasi dan Informatika', 'Kementerian', '', 'VN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 'Diskominfo Provinsi Kalimantan Timur', 'Pemerintahan Daerah', '', 'VN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'Diskominfo Kota Bontang', 'Pemerintahan Daerah', '', 'VN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'Diskominfo Kabupaten Kutai Kartanegara', 'Pemerintahan Daerah', '', 'VN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 'Diskominfo Kabupaten Ciamis', 'Pemerintahan Daerah', '', 'VN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 'BKPSDM Kabupaten Ciamis', 'Pemerintahan Daerah', '', 'VN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(7, 'Diskominfo Kabupaten Bekasi', 'Pemerintahan Daerah', '', 'VN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(8, 'Bapenda Kabupaten Bekasi', 'Pemerintahan Daerah', '', 'VN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9, 'Diskominfo Kabupaten Purwakarta', 'Pemerintahan Daerah', '', 'VN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(10, 'Diskominfo Kabupaten Banjar', 'Pemerintahan Daerah', '', 'VN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(11, 'Disdukcapil Kabupaten Banjar', 'Pemerintahan Daerah', '', 'VN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(12, 'Bank Kaltimtara', 'Bank Daerah', '', 'VN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(13, 'Bank Kaltimtara Syariah', 'Bank Daerah', '', 'VN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14, 'Bank Kalbar', 'Bank Daerah', '', 'VN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(15, 'Bank Kalsel', 'Bank Daerah', '', 'VN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(16, 'Bank Sumut', 'Bank Daerah', '', 'VN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(17, 'Bank Sumsel Babel', 'Bank Daerah', '', 'VN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(18, 'Bank Sulselbar', 'Bank Daerah', '', 'VN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(19, 'Bank Maluku Malut', 'Bank Daerah', '', 'VN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(20, 'Bank Kalimantan Tengah', 'Bank Daerah', '', 'VN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(21, 'Bank Perkreditan Rakyat Cianjur Jabar', 'Bank Daerah', '', 'VN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(22, 'PT. Sawit Sumbernas Sarana Tbk', 'Swasta', '', 'VN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(23, 'PT. HK Pati', 'Swasta', '', 'VN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(24, 'PT. Kaltim Prima Coal', 'Swasta', '', 'VN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(25, 'PT. Citra Borneo Utama', 'Swasta', '', 'VN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(26, 'PT. Donggi Senoro', 'Swasta', '', 'VN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(27, 'PT. Jasa Raharja Putera', 'BUMN', '', 'VN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(28, 'PT. Jasa Raharja Jawa Barat', 'BUMN', '', 'VN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(29, 'PT. Telkom Indonesia', 'BUMN', '', 'VN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(30, 'RSUD Kota Bandung', 'Rumah Sakit', '', 'VN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(31, 'Diskoiminfo Provinsi Jawa Barat', 'Pemerintahan Daerah', '', 'VN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(32, 'Bapenda Provinsi Jawa Barat', 'Pemerintahan Daerah', '', 'VN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(33, 'LPSE Provinsi Jawa Barat', 'Pemerintahan Daerah', '', 'VN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(34, 'Diskominfo Kota Bogor', 'Pemerintahan Daerah', '', 'VN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(35, 'Diskominfo Kabupaten Bogor', 'Pemerintahan Daerah', '', 'VN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(36, 'Diskominfo Kabupaten Cirebon', 'Pemerintahan Daerah', '', 'VN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(37, 'BPD Aceh', 'Bank Daerah', '', 'VN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(38, 'Diskominfo Kota Tasik', 'Pemerintahan Daerah', '', 'VN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(39, 'Diskominfo Kabupaten Sumedang', 'Pemerintahan Daerah', '', 'VN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(40, 'BKPSDM Kabupaten Sumedang', 'Pemerintahan Daerah', '', 'VN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(41, 'Diskominfo Kabupaten Subang', 'Pemerintahan Daerah', '', 'VN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(42, 'UIN Raden Fatah', 'Akademik', '', 'VN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(43, 'PPSDM KEMENDAGRI Regional Jawa Barat', 'Kementerian', '', 'VN', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(44, 'Diskominfo Provinsi Kalimantan Selatan', 'Pemerintahan Daerah', '', 'VN', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(45, 'Diskominfo Provinsi Bangka Belitung', 'Pemerintahan Daerah', '', 'VN', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(46, 'Diskominnfo Provinsi Banten', 'Pemerintahan Daerah', '', 'VN', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(47, 'BKPSDMD Provinsi Banten', 'Pemerintahan Daerah', '', 'VN', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(48, 'Diskominfo Kota Cimahi', 'Pemerintahan Daerah', '', 'VN', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(49, 'BKPSDMD Kota Cimahi', 'Pemerintahan Daerah', '', 'VN', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(50, 'Diskominfo Kota Tangerang', 'Pemerintahan Daerah', '', 'VN', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(51, 'BKPSDM Kota Tangerang', 'Pemerintahan Daerah', '', 'VN', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(52, 'Bank Mizuho Indonesia', 'Bank Umum', '', 'VN', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(53, 'Bank Perkreditan Rakyat Syariah Al Masoem', 'Bank Umum', '', 'VN', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(54, 'PT. Multi Nitro Kimia', 'Swasta', '', 'VN', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(55, 'PT. Shoetown Ligung', 'Swasta', '', 'VN', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(56, 'PT. Krakatau Posco', 'BUMN', '', 'VN', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(57, 'PT. PALYJA', 'BUMN', '', 'VN', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(58, 'PT. Dialogue Baby', 'Swasta', '', 'VN', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(59, 'PT. Timah Industri', 'BUMN', '', 'VN', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(60, 'PT. Telemedia Dinamika Sarana', 'Swasta', '', 'VN', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(61, 'PT. Pertamina Star Energy Geothermal', 'BUMN', '', 'VN', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(62, 'Asuransi Jiwa Inhealth', 'BUMN', '', 'VN', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(63, 'Terminal Peti Kemas Koja', 'Swasta', '', 'VN', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(64, 'Sefope Timor Leste', 'Swasta', '', 'VN', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(65, 'Semen Baruraja', 'BUMN', '', 'VN', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(66, 'Perum Perumnas', 'BUMN', '', 'VN', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(67, 'RS ST Calorus Salemba', 'Rumah Sakit', '', 'VN', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(68, 'Canggu Comunity School', 'Akademik', '', 'VN', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(69, 'Politeknik Negeri Padang', 'Akademik', '', 'VN', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(70, 'Politeknik Negeri Balikpapan', 'Akademik', '', 'VN', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(71, 'Institut Pendidikan Indonesia Garut', 'Akademik', '', 'VN', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(72, 'Bank Mestika', 'Bank Umum', '', 'VN', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(73, 'Kementerian Dalam Negeri', 'Kementerian', '', 'VN', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(74, 'Arsip Nasional RI', 'Lembaga Pemerintahan', '', 'VN', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(75, 'BBSPJI Bahan dan Barang Teknik', 'Lembaga Pemerintahan', '', 'VN', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(76, 'BBSPJI Logam dan Mesin', 'Lembaga Pemerintahan', '', 'VN', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(77, 'BBSPJI Selulosa', 'Lembaga Pemerintahan', '', 'VN', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(78, 'Bank Panin Dubai Syariah', 'Bank Umum', '', 'VN', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(79, 'Bank Nagari Padang', 'Bank Daerah', '', 'VN', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(80, 'PT. Indorama', 'Swasta', '', 'VN', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(81, 'PT. Petamina Asset 3', 'BUMN', '', 'VN', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(82, 'PT. Kawasan Industri Wijayakusuma', 'BUMN', '', 'VN', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(83, 'PT. Suzuki Finance', 'Swasta', '', 'VN', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(84, 'PT. PLN Tarakan', 'BUMN', '', 'VN', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(85, 'PT. Sanbe Farma', 'Swasta', '', 'VN', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(86, 'Sucofindo', 'BUMN', '', 'VN', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(87, 'PAMJAYA DKI', 'BUMD', '', 'VN', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(88, 'RSUD Ulin Kalsel', 'Rumah Sakit', '', 'VN', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(89, 'Kementerian Pariwisata', 'Kementerian', '', 'VN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(90, 'Kementerian Lingkungan Hidup', 'Kementerian', '', 'VN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(91, 'Kementerian Pertanian', 'Kementerian', '', 'VN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(92, 'Kementerian Perdagangan', 'Kementerian', '', 'VN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(93, 'Kementerian Perindustrian', 'Kementerian', '', 'VN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(94, 'Basarnas', 'Lembaga Pemerintahan', '', 'VN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(95, 'Pemprov DKI', 'Pemerintahan Daerah', '', 'VN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(96, 'Bank Woori Bersaudara', 'Bank Umum', '', 'VN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(97, 'Bank Indonesia Exim', 'Bank Umum', '', 'VN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(98, 'Bank Perkreditan Rakyat Sri Artha', 'Bank Umum', '', 'VN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(99, 'Bank Perkreditan Rakyat Syariah Alsalaam', 'Bank Umum', '', 'VN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(100, 'PT. Vale Indonesia', 'BUMN', '', 'VN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(101, 'PT. Kutai Timber Indonesia', 'BUMN', '', 'VN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(102, 'PT. Eiger', 'Swasta', '', 'VN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(103, 'PT. Milennia Solusi Informatika', 'Swasta', '', 'VN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(104, 'PT. Lautan Luas', 'Swasta', '', 'VN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(105, 'PT. Inka', 'BUMN', '', 'VN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(106, 'PT. Nikomas Gemilang', 'Swasta', '', 'VN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(107, 'PT. Kawasan Berikat Nusantara', 'BUMN', '', 'VN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(108, 'PT. Nuansa Cahaya Persada', 'Swasta', '', 'VN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(109, 'Polnes Samarinda', 'Akademik', '', 'VN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(110, 'STT Wastukencana', 'Akademik', '', 'VN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(111, 'Universitas Mulawarman', 'Akademik', '', 'VN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(112, 'Mega Life Assurance', 'Swasta', '', 'VN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(113, 'PDAM Tirta Galuh', 'BUMD', '', 'VN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(114, 'PDAM Tirta Kencana', 'BUMD', '', 'VN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(115, 'Sucofindo Bandung', 'BUMN', '', 'VN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(116, 'LAPI DIVUSI', 'Akademik', '', 'VN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(117, 'IDEC Telkom', 'BUMN', '', 'VN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(118, 'PT. Gerbang Sinergi Prima', 'Swasta', '', 'RR', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(119, 'Pemerintahan Kota Bandung', 'Pemerintahan Daerah', '', 'RR', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(120, 'BPRKS', 'Bank Umum', '', 'RR', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(121, 'Universitas Silliwangi', 'Akademik', '', 'RR', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(122, 'Pemerintahan Kabupaten Tangerang', 'Pemerintahan Daerah', '', 'RR', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(123, 'SKK Migas', 'BUMN', '', 'RR', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(124, 'PT. PLN Batubara', 'BUMN', '', 'RR', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(125, 'PT. PLN Energi Primer Indonesia', 'BUMN', '', 'RR', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(126, 'PT. Knitto Tekstil Indonesia', 'Swasta', '', 'RR', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(127, 'PT. Kartika Sinar Mulia', 'Swasta', '', 'RR', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(128, 'Yayasan Pendidikan Telkom', 'BUMN', '', 'RR', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(129, 'RS. Santo Yusuf', 'Rumah Sakit', '', 'RR', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(130, 'RSHS', 'Rumah Sakit', '', 'RR', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(131, 'Daya AdiciPT.a Motora', 'Swasta', '', 'RR', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(132, 'PT. Bank BJB Syariah', 'Bank Daerah', '', 'RR', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(133, 'PT. Immortal', 'Swasta', '', 'RR', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(134, 'Bank BJB', 'Bank Daerah', '', 'RR', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(135, 'PT. SUMMI RUBBER INDONESIA', 'Swasta', '', 'RR', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(136, 'PT. TT Metals', 'Swasta', '', 'RR', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(137, 'P4TK IPA', 'Lembaga Pemerintahan', '', 'RR', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(138, 'Dapen PLN', 'BUMN', '', 'RR', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(139, 'PT. Indo Raya Tenaga', 'Swasta', '', 'RR', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(140, 'PT. Bursa Efek Indonesia', 'BUMN', '', 'RR', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(141, 'Lembaga Sandi Negara (BSSN)', 'Lembaga Pemerintahan', '', 'RR', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(142, 'PT. Bank Mandiri (Persero) Tbk', 'Bank Umum', '', 'RR', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(143, 'PT. PERUSAHAAN GAS NEGARA Tbk', 'BUMN', '', 'RR', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(144, 'PT. SARANA MULTI INFRASTRUKTUR', 'BUMN', '', 'RR', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(145, 'PT. Bina Karya (Persero)', 'BUMN', '', 'RR', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(146, 'Asuransi Jasindo', 'Swasta', '', 'RR', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(147, 'Husky Energy', 'Swasta', '', 'RR', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(148, 'Petronas Carigali Indonesia', 'Swasta', '', 'RR', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(149, 'ANGKASA PURA 1', 'BUMN', '', 'RR', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(150, 'PT. Kangean-Energy', 'BUMN', '', 'RR', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(151, 'Petrochina', 'Swasta', '', 'RR', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(152, 'Nusantara Regas', 'BUMN', '', 'RR', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(153, 'PT. Penjaminan Infrastruktur Indonesia', 'BUMN', '', 'RR', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(154, 'PT. Pertamina Geotermal Energy', 'BUMN', '', 'RR', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(155, 'PT. PJB (Pembangkitan Jawa Bali)', 'BUMN', '', 'RR', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(156, 'PT. PERUSAHAAN PENGELOLA ASSET', 'BUMN', '', 'RR', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(157, 'IP Suralaya', 'BUMN', '', 'RR', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(158, 'PERTAMINA EP Asset 1', 'BUMN', '', 'RR', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(159, 'Pertamina EP Asset 2', 'BUMN', '', 'RR', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(160, 'Pertamina EP Asset 4', 'BUMN', '', 'RR', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(161, 'PT. Satnetcom', 'Swasta', '', 'RR', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(162, 'Pertamina Asset 5', 'BUMN', '', 'RR', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(163, 'BP Batam', 'Swasta', '', 'RR', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(164, 'Pemerintah Kab Pangandaran', 'Pemerintahan Daerah', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(165, 'Pemerintah Kab Kuningan', 'Pemerintahan Daerah', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(166, 'Pemerintah Kab Majalengka', 'Pemerintahan Daerah', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(167, 'PT. Medika Antapani', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(168, 'PT. Multi star rukun abadi (sharon bakery)', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(169, 'RS Edelweiss', 'Rumah Sakit', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(170, 'RS AMC', 'Rumah Sakit', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(171, 'RS Pasar Minggu', 'Rumah Sakit', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(172, 'RS Azra', 'Rumah Sakit', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(173, 'PT. Alfa Polimer Indonesia', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(174, 'PT. Leuwitex', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(175, 'PT. Sipatex', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(176, 'PT. Swisstex', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(177, 'PT. Mustika Citra Rasa (holand bakery)', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(178, 'PT. Jalawave Cakrawala', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(179, 'PT. Anggana Kurnia Putra', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(180, 'RSUD Bandung Kiwari', 'Rumah Sakit', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(181, 'PT. Comnet', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(182, 'PDAM Tirta Raharja', 'BUMD', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(183, 'Cirebon Power', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(184, 'PDAM Tirta Gemah Ripah', 'BUMD', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(185, 'PT. Otto pharmaceutical industries', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(186, 'PT. Alkindo Naratama', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(187, 'Migas Utama Jabar', 'Lembaga Pemerintahan', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(188, 'PT. Tata Nutrisana', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(189, 'PT. Iwaki glass', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(190, 'DT Peduli', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(191, 'Tricada Intronik', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(192, 'Kaneka Foods Indonesia', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(193, 'PT. Bino Mitra Sejati (Bantex)', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(194, 'PT. Bondor Indoensia', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(195, 'Pertamina Training and Consulting', 'BUMN', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(196, 'PT. Jasuindo Tiga Perkasa', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(197, 'PT. Rintis Sejahtera', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(198, 'PT. Inalum', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(199, 'PT. Aica Indria', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(200, 'PT. Alto network', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(201, 'PT. Rintis Sejahtera', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(202, 'PT. Indonesian Nippon Steel Pipe', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(203, 'PT. Verdhana', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(204, 'PT. Jasnita Telekomindo', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(205, 'PT. Sigma CiPT.a Utama', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(206, 'PT. Kresna Reksa Finance', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(207, 'PT. Jaya Teknik', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(208, 'PT. Petrogas', 'BUMN', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(209, 'PT. BPR Polatama Kusuma', 'Bank Umum', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(210, 'PT. BPR Mahkota Artha Sejahtera', 'Bank Umum', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(211, 'PT. BPR Universal', 'Bank Umum', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(212, 'PT. BPR Surya yudha', 'Bank Umum', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(213, 'Perumda Air Minum Tirta Khatulistiwa', 'BUMD', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(214, 'PDAM Kota Samarinda', 'BUMD', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(215, 'Perumdam Tirta Alam Tarakan', 'BUMD', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(216, 'RSJ dr. Soeharto Heerdjan', 'Rumah Sakit', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(217, 'RS Juanda', 'Rumah Sakit', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(218, 'PT. Kresna Reksa Finance', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(219, 'Kalla group', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(220, 'PT. Pins Indonesia', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(221, 'TPPI', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(222, 'PT. Bussan Auto Finance', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(223, 'Pertamina PDC', 'BUMN', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(224, 'Pertamina Lubricant', 'BUMN', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(225, 'Bank CCB Indonesia', 'Bank Umum', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(226, 'PT. Oryx Services', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(227, 'PT. Krakatau engineering', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(228, 'PT. Rea Kaltim', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(229, 'RSUD Doris sylvanus', 'Rumah Sakit', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(230, 'PT. Absolut Realitas Solusi', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(231, 'PT. Prima Qualita Pratama', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(232, 'PT. Kustodian Sentral Efek Indonesia', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(233, 'RSU Islam Harapan Anda', 'Rumah Sakit', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(234, 'PDAM Surya Sembada', 'BUMD', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(235, 'PT. Cifor', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(236, 'Diskominfo kab. Parigi moutong kab sulteng', 'Pemerintahan Daerah', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(237, 'Bumi Suksesindo', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(238, 'PT. Emka Pasific', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(239, 'Kaneka Foods Indonesia', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(240, 'PT. Wismilak', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(241, 'PT. Berau coal', 'Swasta', '', 'RR', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(242, 'PT. Mitsui Leasing', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(243, 'PT. Melvar Prima Solusi', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(244, 'PT. Sygma Innovation', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(245, 'PT. Krakatau Medika', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(246, 'PT. Securindo Packatama Inodnesia', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(247, 'PT. YKK Zipper', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(248, 'PT. Palma serasih Tbk', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(249, 'PT. Sampharindo Perdana', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(250, 'PT. Rama Emerald Multi Sukses Tbk', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(251, 'PT. Hasil Damai Textile (Hadtex)', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(252, 'PT. Daliatex Kusuma', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(253, 'PT. Metro Garmin', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(254, 'PT. Mora Telematika Indonesia', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(255, 'PT. Asian Cotton', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(256, 'PT. Firsta Garmen', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(257, 'PT. Perushaan Industri Ceres', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(258, 'PT. Behaestex', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(259, 'PT. BPR Utomo bank', 'Bank Umum', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(260, 'PT. Dian Swastatika Indonesia', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(261, 'PT. BPR Restu Group', 'Bank Umum', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(262, 'PT. BPR Pijer Podi Kekelengen', 'Bank Umum', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(263, 'PT. Energi Pelabuhan Indonesia', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(264, 'PT. Medan Smart Jaya', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(265, 'PT. Asuransi Bina Dana Arta Tbk.', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(266, 'PT. Tira Austenite Tbk', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(267, 'PT. Asuransi Perisai Listrik Nasional', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(268, 'PT. BPR Bapas 69', 'Bank Umum', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(269, 'PT. OTICS Indonesia', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(270, 'PT. Asuransi Tri Pakarta', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(271, 'PT. Smart multi finance', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(272, 'PT. MTM Bali (Mitra Telemedia Manunggal)', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(273, 'PT. Paboxin', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(274, 'Universitas Bung Hatta', 'Akademik', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(275, 'PT. Aneka Gas Industri', 'BUMN', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(276, 'PT. Indo Acidatama', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(277, 'PT. Allo Bank Indonesia', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(278, 'PT. Dian Swastatika Indonesia', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(279, 'PT. Mulia Boga Raya (prochiz)', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(280, 'PT. Kirana Megatara Group', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(281, 'PT. Lautan Luas', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(282, 'Bank Mayapada', 'Bank Umum', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(283, 'PT. Pioneerindo Gourmet International, Tbk.', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(284, 'PT. Indospring Tbk', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(285, 'PT. Codemi', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(286, 'PT. Bali Towerindo Sentra', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(287, 'PT. Asia Sakti Wahid Manufacture', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(288, 'PT. Selamat Sempurna', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(289, 'PT. Equality Life Indonesia', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(290, 'RS Universitas Indonesia', 'Rumah Sakit', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(291, 'RSAU Dr. M. Hassan Toto', 'Rumah Sakit', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(292, 'RS AN-NISA Tangerang', 'Rumah Sakit', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(293, 'PERUMDA BPR KARYA REMAJA INDRAMAYU', 'BUMD', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(294, 'PT. Aditya Sarana Graha', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(295, 'PT. Securindo Packatama Inodnesia', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(296, 'PT. Sampharindo Perdana', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(297, 'PT. Sumi Indo Kabel', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(298, 'PT. NOBI PUTRA ANGKASA', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(299, 'PT. LEUWIJAYA UTAMA TEXTILE', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(300, 'PT. BPR Karya Utama Jabar', 'Bank Umum', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(301, 'PT. Sigma CiPT.a Utama', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(302, 'RS Azra', 'Rumah Sakit', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(303, 'RSI As syifa', 'Rumah Sakit', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(304, 'RS Karya Bhakti Pratiwi', 'Rumah Sakit', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(305, 'RSUP Persahabatan', 'Rumah Sakit', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(306, 'RS Mitra Plumbon Cirebon (Pusat)', 'Rumah Sakit', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(307, 'RS Bhakti Asih', 'Rumah Sakit', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(308, 'PT. Nissin batam', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(309, 'Kominfo Prov Bengkulu', 'Pemerintahan Daerah', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(310, 'LPSE/Sekda Provinsi Bengkulu', 'Pemerintahan Daerah', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(311, 'BPKAD Provinsi Bengkulu', 'Pemerintahan Daerah', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(312, 'Disdukcapil Provinsi Bengkulu', 'Pemerintahan Daerah', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(313, 'Bapedda Provinsi Bengkulu', 'Pemerintahan Daerah', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(314, 'Bapedda Kab. Bengkulu Selatan', 'Pemerintahan Daerah', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(315, 'LPSE Kab Rejang Lebong', 'Pemerintahan Daerah', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(316, 'Dinas Kesehatan Kab Rejang Lebong', 'Pemerintahan Daerah', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(317, 'RSUD Bengkulu Tengah', 'Rumah Sakit', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(318, 'RSUD M. Yunus Bengkulu', 'Rumah Sakit', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(319, 'RSUD Hasanuddin Damrah', 'Rumah Sakit', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(320, 'RSUD Lagita', 'Rumah Sakit', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(321, 'RSUD Kab Kaur', 'Rumah Sakit', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(322, 'RSUD Kepahiang', 'Rumah Sakit', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(323, 'Universitas Sangga Buana YPKP', 'Akademik', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(324, 'Universitas Trilogi Jakarta', 'Akademik', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(325, 'Politeknik Batam', 'Akademik', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(326, 'Universitas Jember', 'Akademik', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(327, 'Universitas Bengkulu', 'Akademik', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(328, 'Universitas Bandar Lampung', 'Akademik', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(329, 'Universitas Bung Hatta', 'Akademik', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(330, 'Diskominfo kab donggala', 'Pemerintahan Daerah', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(331, 'Diskominfo kab morowali', 'Pemerintahan Daerah', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(332, 'Diskominfo Provinsi Sulawesi Barat', 'Pemerintahan Daerah', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(333, 'Diskominfo kota gorontalo', 'Pemerintahan Daerah', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(334, 'Diskominfo kota tidore', 'Pemerintahan Daerah', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(335, 'PT. Grafindo', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(336, 'PT. BPRS HIK MCI', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(337, 'PT. BPRS Dinar Ashri', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(338, 'PT. BPR Bank Klaten', 'Bank Daerah', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(339, 'PT. Tropica Mas Pharmaceuticals', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(340, 'PT. Sinar Mulia Plasindo Lestari', 'Swasta', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(341, 'BPR Subang', 'Bank Umum', '', 'RR', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(342, 'PT. Kereta Api Indonesia', 'BUMN', '', 'RN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(343, 'PT. Starcom Solusindo', 'Swasta', '', 'RN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(344, 'PT. Akur Pratama', 'Swasta', '', 'RN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(345, 'Universitas Islam Bandung', 'Akademik', '', 'RN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(346, 'Universitas Katolik Parahyangan', 'Akademik', '', 'RN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(347, 'Politeknik Negeri Bandung', 'Akademik', '', 'RN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(348, 'Institute Teknologi Nasional', 'Akademik', '', 'RN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(349, 'PT. Bridgestone Tire Indonesia', 'Swasta', '', 'RN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(350, 'Balai Besar Survei dan Pemetaan Geologi Kelautan', 'Lembaga Pemerintahan', '', 'RN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(351, 'PPSDM Aparatur - Kementerian ESDM', 'Lembaga Pemerintahan', '', 'RN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(352, 'Pusadatin - Kementerian ESDM', 'Lembaga Pemerintahan', '', 'RN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(353, 'Balai Besar Standarisasi dan Pelayanan Jasa Indutri Kramik dan Mineral Non Logam', 'Lembaga Pemerintahan', '', 'RN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(354, 'Politeknik Manufaktur Bandung', 'Akademik', '', 'RN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(355, 'PT. Transportasi Gas Indonesia', 'Swasta', '', 'RN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(356, 'Lembaga Management Aset Negara', 'Lembaga Pemerintahan', '', 'RN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(357, 'PT. Pertamina (Persero)', 'BUMN', '', 'RN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(358, 'Lembaga Penjamin Simpanan', 'Lembaga Pemerintahan', '', 'RN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(359, 'PT. Media Telekomunikasi Mandiri', 'Swasta', '', 'RN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(360, 'PT. Kliring Penjaminan Efek Indonesia', 'Lembaga Pemerintahan', '', 'RN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(361, 'PT. Indonesia Koito', 'Swasta', '', 'RN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(362, 'BPD Jambi', 'Bank Daerah', '', 'RN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(363, 'Politeknik Negeri Nusa Utara', 'Akademik', '', 'RN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(364, 'PT. Bukit Asam Tbk', 'Swasta', '', 'RN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(365, 'RSUD Bendan Pekalongan', 'Rumah Sakit', '', 'RN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(366, 'Universitas Islam Negeri Raden Fattah Palembang', 'Akademik', '', 'RN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(367, 'BPD NTB Syariah', 'Bank Daerah', '', 'RN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(368, 'PT. Bumi Siak Pusako', 'Swasta', '', 'RN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(369, 'BPSDM Provinsi Aceh', 'Pemerintahan Daerah', '', 'RN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(370, 'RSUD Abdul Wahab', 'Rumah Sakit', '', 'RN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(371, 'PT. PLN (Persero) Unit Pelaksana Pengatur Beban Jawa Barat', 'BUMN', '', 'RN', 'Q1', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(372, 'PT. Pos Indonesia (Persero)', 'BUMN', '', 'RN', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(373, 'Universitas Singaperbangsa', 'Akademik', '', 'RN', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(374, 'Politeknik Negeri Sriwijaya', 'Akademik', '', 'RN', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(375, 'PT. Hypernet Indodata', 'Swasta', '', 'RN', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(376, 'PT. Sakuratex', 'Swasta', '', 'RN', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(377, 'PT. Pengembangan Parawisata Indonesia (persero) / ITDC', 'BUMN', '', 'RN', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(378, 'Neuviz Batam', 'Swasta', '', 'RN', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(379, 'LPSE Provinisi Lampung', 'Pemerintahan Daerah', '', 'RN', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(380, 'Universitas Atma Jaya Yogya', 'Akademik', '', 'RN', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(381, 'Dinas Komunikasi dan Informatika Kabupaten Kolaka', 'Pemerintahan Daerah', '', 'RN', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(382, 'PT. Tanjung Enim Pulp and Paper', 'Swasta', '', 'RN', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(383, 'PT. Pertamina (EP)', 'BUMN', '', 'RN', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(384, 'PT. Vox Teneo Indonesia', 'Swasta', '', 'RN', 'Q2', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(385, 'BPD Sultra', 'Bank Daerah', '', 'RN', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(386, 'Komisi Pembaratasan Korupsi', 'Lembaga Pemerintahan', '', 'RN', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(387, 'Universitas Brawijaya', 'Akademik', '', 'RN', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(388, 'Universitas Padjajaran', 'Akademik', '', 'RN', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(389, 'BPR Utomo Lampung', 'Bank Umum', '', 'RN', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(390, 'PT. Pertamina Trans Kontinental', 'BUMN', '', 'RN', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(391, 'Bandara Internasional Jawa Barat', 'BUMD', '', 'RN', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(392, 'PLN Pusdiklat', 'BUMN', '', 'RN', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(393, 'BPJS Ketenagakerjaan', 'Lembaga Pemerintahan', '', 'RN', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(394, 'Universitas Maranatha', 'Akademik', '', 'RN', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(395, 'Politeknik Sosial', 'Akademik', '', 'RN', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(396, 'IPDN', 'Akademik', '', 'RN', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(397, 'RSUD Sumedang', 'Rumah Sakit', '', 'RN', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(398, 'PT. Kaldu Sari Nabati', 'Swasta', '', 'RN', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(399, 'PT. Asuransi Ekspor Indonesia', 'Swasta', '', 'RN', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(400, 'PT. Riau Andalan Pulp and Paper', 'Swasta', '', 'RN', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(401, 'Universitas Sriwijaya', 'Akademik', '', 'RN', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(402, 'BPR Hasamitra', 'Bank Umum', '', 'RN', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(403, 'Universitas Udayana', 'Akademik', '', 'RN', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(404, 'BPD Bali', 'Bank Daerah', '', 'RN', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(405, 'Universitas Lampung', 'Akademik', '', 'RN', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(406, 'PT. Marga Mandalasakti', 'Swasta', '', 'RN', 'Q3', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(407, 'Politeknik Pos', 'Akademik', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(408, 'STIMIK AMIK', 'Akademik', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(409, 'Universitas Jendral Achmad Yani', 'Akademik', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(410, 'Universitas Widyatama', 'Akademik', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(411, 'Universitas Mulawarman', 'Akademik', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(412, 'Rumah Sakit Immanuel', 'Rumah Sakit', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(413, 'Rumah Sakit Al-Islam', 'Rumah Sakit', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(414, 'Rumah Sakit Dhamaris', 'Rumah Sakit', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(415, 'RS Pusat Pertamina', 'Rumah Sakit', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(416, 'RS Pondok Indah', 'Rumah Sakit', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(417, 'PT. Pertamina Bina Medika Indonesia Healthcare Corporation', 'Swasta', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(418, 'MAP (Mitra Adhiperkasa)', 'Swasta', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(419, 'Kompas Gramedia Group of Magazine', 'Swasta', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(420, 'PT. Eka Boga Inti', 'Swasta', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(421, 'PT. Visionet Data Internasional', 'Swasta', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(422, 'PT. Pangansari Utama', 'Swasta', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(423, 'PT. Yamaha Motor Manufacturing', 'Swasta', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(424, 'Waskita Beton Percast Tbk', 'Swasta', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(425, 'Bank Of India', 'Bank Umum', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(426, 'PT. Kalimantan Energi Lestari', 'Swasta', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(427, 'Politeknik Negeri Bali', 'Akademik', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(428, 'Universitas Indonesia', 'Akademik', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(429, 'Universitas Gajah Mada', 'Akademik', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(430, 'Rumah Sakit JIH', 'Rumah Sakit', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(431, 'RSUD dr H Moch Ansari Saleh', 'Rumah Sakit', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(432, 'Bank Syariah Indonesia', 'Bank BUMN', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(433, 'Bank Bukopin', 'Bank Umum', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(434, 'Bank Maspion', 'Bank Umum', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(435, 'Bank Sampoerna', 'Bank Umum', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(436, 'Bank Maybank', 'Bank Umum', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(437, 'BPR Eka Lampung', 'Bank Umum', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(438, 'Pemerintah Provinsi Kalimantan Barat', 'Pemerintahan Daerah', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(439, 'Pemerintah Provinsi Bali', 'Pemerintahan Daerah', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(440, 'Universitas Komputer Indonesia', 'Akademik', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(441, 'PT. Perikanan Nusantara Persero', 'Swasta', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(442, 'PT. Indah Kilat Plup & Paper', 'Swasta', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(443, 'PT. Berca', 'Swasta', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(444, 'PT. Maha Tandra', 'Swasta', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(445, 'PT. Meprofarm', 'Swasta', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(446, 'Ismaya Group', 'Swasta', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(447, 'Rumah Sakit Islam Jemusari', 'Rumah Sakit', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(448, 'PT. Kalbe Morinaga Indonesia', 'Swasta', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(449, 'PT. Sakae Riken', 'Swasta', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(450, 'PT. Osimo', 'Swasta', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(451, 'PT. TKG', 'Swasta', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(452, 'PT. Medion Farma', 'Swasta', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(453, 'PT. Bahtera Pesat', 'Swasta', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(454, 'PT. Piston', 'Swasta', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(455, 'PT. Daido Metal', 'Swasta', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(456, 'PT. Namicoh', 'Swasta', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(457, 'PT. Kotaminyak', 'Swasta', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(458, 'PT. Tifico Fiber', 'Swasta', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(459, 'PT. Suprabakti Mandiri', 'Swasta', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(460, 'MT Jewelry', 'Swasta', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(461, 'PT. Wonokoyo Jaya', 'Swasta', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(462, 'PT. Primastraw', 'Swasta', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(463, 'PT. Pardic Jaya', 'Swasta', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(464, 'PT. Pacinesia Chemical', 'Swasta', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(465, 'PT. Matsuo Indonesia', 'Swasta', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(466, 'BPR Eka Bumi Artha', 'Bank Umum', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(467, 'BPR Supra', 'Bank Umum', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(468, 'BPR Sum Adiyatra', 'Bank Umum', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(469, 'BPRS Mitra Mentari Sejahtera', 'Bank Umum', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(470, 'Butik Zyku Xena', 'Swasta', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(471, 'PT. Parahyangan Motor Prakassa', 'Swasta', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(472, 'Yayasan Pendidikan Salman Al-Farisi', 'Swasta', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(473, 'PT. Pelat TImah Nusantara', 'Swasta', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(474, 'PT. Dermaga Emas Nusantara', 'Swasta', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(475, 'PT. Sumbe Sinergi Makmur', 'Swasta', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(476, 'PT. GALVA TECHNOLOGIES TBK', 'Swasta', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(477, 'PT. Bintang Oto Global', 'Swasta', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(478, 'Vibicloud', 'Swasta', '', 'RN', 'Q4', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(479, 'Personal', 'Personal', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-05-14 09:26:54', '2024-05-14 09:26:54'),
(480, 'PT Krakatau Tirta', 'Swasta', NULL, 'ZN', NULL, NULL, NULL, NULL, NULL, NULL, '2024-05-15 01:26:54', '2024-05-15 01:26:54'),
(481, 'RS Kesehatan Kerja Jawa Barat', 'Rumah Sakit', 'Jawa Barat', 'ZN', NULL, NULL, NULL, NULL, NULL, NULL, '2024-05-15 01:39:24', '2024-05-15 01:39:24'),
(482, 'Institut Teknologi Bandung', 'Akademik', 'Jawa Barat', 'ZN', 'Q2', NULL, NULL, NULL, NULL, NULL, '2024-05-15 01:41:53', '2024-05-15 01:48:55'),
(483, 'PT. Jasaraharja Putera', 'Swasta', NULL, 'VN', 'Q1', NULL, NULL, NULL, NULL, NULL, '2024-05-15 01:46:25', '2024-05-15 01:46:25'),
(484, 'BPR Cianjur', 'Bank Umum', 'Jawa Barat', 'VN', 'Q1', NULL, NULL, NULL, NULL, NULL, '2024-05-15 01:55:25', '2024-05-15 01:55:25'),
(485, 'PT. Gunung Madu Plantations', 'Swasta', NULL, 'HW', 'Q1', NULL, NULL, NULL, NULL, NULL, '2024-05-15 01:59:52', '2024-05-15 01:59:52'),
(486, 'Sekretariat Daerah Kabupaten Karawang', 'Pemerintahan Daerah', 'Jawa Barat', 'HW', NULL, NULL, NULL, NULL, NULL, NULL, '2024-05-15 02:16:47', '2024-05-15 02:16:47'),
(487, 'Pupuk Indonesia', 'Swasta', NULL, 'HW', 'Q2', NULL, NULL, NULL, NULL, NULL, '2024-05-15 02:36:49', '2024-05-15 02:36:49');
INSERT INTO `perusahaans` (`id`, `nama_perusahaan`, `kategori_perusahaan`, `lokasi`, `sales_key`, `status`, `npwp`, `alamat`, `cp`, `no_telp`, `foto_npwp`, `created_at`, `updated_at`) VALUES
(488, 'Inspektorat Kabupaten Tangerang', 'Pemerintahan Daerah', 'Banten', 'RR', NULL, NULL, NULL, NULL, NULL, NULL, '2024-05-15 02:38:33', '2024-05-15 02:38:33'),
(489, 'LPSE Kabupaten Berau', NULL, NULL, 'VN', NULL, NULL, NULL, NULL, NULL, NULL, '2024-05-15 02:47:52', '2024-05-15 02:47:52'),
(490, 'PT. Tugu Reasuransi Indonesia', 'Swasta', NULL, 'HW', NULL, NULL, NULL, NULL, NULL, NULL, '2024-05-15 02:58:22', '2024-05-15 02:58:22'),
(491, 'Bank Lampung', 'Bank Daerah', 'Lampung', 'HW', 'Q1', NULL, NULL, NULL, NULL, NULL, '2024-05-15 03:02:14', '2024-05-15 03:02:14'),
(492, 'Telkom University', 'Akademik', NULL, 'HW', 'Q1', NULL, NULL, NULL, NULL, NULL, '2024-05-15 03:07:28', '2024-05-15 03:07:28'),
(493, 'Diskominfo Kabupaten Sukabumi', 'Pemerintahan Daerah', 'Jawa Barat', 'HW', NULL, NULL, NULL, NULL, NULL, NULL, '2024-05-15 03:31:56', '2024-05-15 03:31:56'),
(494, 'Diskominfo Kabupaten Karawang', 'Pemerintahan Daerah', 'Jawa Barat', 'HW', NULL, NULL, NULL, NULL, NULL, NULL, '2024-05-15 07:50:02', '2024-05-15 07:50:02'),
(495, 'LMAN', NULL, NULL, 'ZN', NULL, NULL, NULL, NULL, NULL, NULL, '2024-05-15 08:08:31', '2024-05-15 08:08:31'),
(496, 'BPD Maluku', NULL, NULL, 'VN', NULL, NULL, NULL, NULL, NULL, NULL, '2024-05-15 09:43:33', '2024-05-15 09:43:33'),
(497, 'PT. Medco Energy', 'Swasta', NULL, 'ZN', NULL, NULL, NULL, NULL, NULL, NULL, '2024-05-16 01:45:43', '2024-05-16 01:45:43'),
(498, 'Bank Indonesia', 'Lembaga Pemerintahan', NULL, 'HW', 'Q1', NULL, NULL, NULL, NULL, NULL, '2024-05-16 02:26:30', '2024-05-16 02:26:30'),
(499, 'Kaltim Industrial Estate', 'Swasta', 'Kalimantan Timur', 'VN', NULL, NULL, NULL, NULL, NULL, NULL, '2024-05-16 03:07:03', '2024-05-16 03:07:03');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pesertas`
--

CREATE TABLE `pesertas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama` varchar(255) NOT NULL,
  `jenis_kelamin` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `no_hp` varchar(255) DEFAULT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `perusahaan_key` varchar(255) NOT NULL,
  `tanggal_lahir` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `pesertas`
--

INSERT INTO `pesertas` (`id`, `nama`, `jenis_kelamin`, `email`, `no_hp`, `alamat`, `perusahaan_key`, `tanggal_lahir`, `created_at`, `updated_at`) VALUES
(1, 'Pandu Kurniawan', 'L', 'pandu.kawan@gmail.com', '082237970236', 'Vila Indah Permai Blok E 18 No 27, RT 010 RW 033, Kelurahan Teluk Pucung, Kecamatan Bekasi Utara, Kota Bekasi', '479', '1997-05-28', '2024-05-14 09:39:22', '2024-05-14 09:39:22'),
(2, 'Azis Sumaryono', 'L', 'azismozak@gmail.com', '087772556363', 'Jl. Cihampelas Cililin No 25 KBB', '48', '1975-06-02', '2024-05-14 09:52:38', '2024-05-14 09:52:38'),
(3, 'Ayu Wulandari', 'P', 'wulandaryyayu@gmail.com', '089633376627', 'Perum Pesona Rancaekek Indah Jl. Pesona Raya No. 16 Bojongloa, Kecamatan Rancaekek 40394', '48', '1998-01-20', '2024-05-14 09:55:19', '2024-05-14 09:55:19');

-- --------------------------------------------------------

--
-- Struktur dari tabel `registrasis`
--

CREATE TABLE `registrasis` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_rkm` varchar(255) NOT NULL,
  `id_peserta` varchar(255) NOT NULL,
  `id_materi` varchar(255) NOT NULL,
  `id_instruktur` varchar(255) NOT NULL,
  `id_sales` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `registrasis`
--

INSERT INTO `registrasis` (`id`, `id_rkm`, `id_peserta`, `id_materi`, `id_instruktur`, `id_sales`, `created_at`, `updated_at`) VALUES
(1, '3', '1', '38', 'AD', 'VN', '2024-05-14 09:39:22', '2024-05-14 09:39:22'),
(2, '2', '2', '51', 'SH', 'VN', '2024-05-14 09:52:38', '2024-05-14 09:52:38'),
(3, '2', '3', '51', 'SH', 'VN', '2024-05-14 09:55:19', '2024-05-14 09:55:19');

-- --------------------------------------------------------

--
-- Struktur dari tabel `r_k_m_s`
--

CREATE TABLE `r_k_m_s` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sales_key` varchar(255) NOT NULL,
  `materi_key` varchar(255) NOT NULL,
  `perusahaan_key` varchar(255) NOT NULL,
  `harga_jual` varchar(255) NOT NULL,
  `pax` varchar(255) NOT NULL,
  `isi_pax` varchar(255) NOT NULL,
  `tanggal_awal` date NOT NULL,
  `tanggal_akhir` date NOT NULL,
  `metode_kelas` varchar(255) DEFAULT NULL,
  `event` varchar(255) DEFAULT NULL,
  `ruang` varchar(255) DEFAULT NULL,
  `instruktur_key` varchar(255) DEFAULT NULL,
  `instruktur_key2` varchar(255) DEFAULT NULL,
  `asisten_key` varchar(255) DEFAULT NULL,
  `status` enum('0','1','2') NOT NULL,
  `exam` enum('0','1') NOT NULL,
  `authorize` enum('0','1') NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `r_k_m_s`
--

INSERT INTO `r_k_m_s` (`id`, `sales_key`, `materi_key`, `perusahaan_key`, `harga_jual`, `pax`, `isi_pax`, `tanggal_awal`, `tanggal_akhir`, `metode_kelas`, `event`, `ruang`, `instruktur_key`, `instruktur_key2`, `asisten_key`, `status`, `exam`, `authorize`, `created_at`, `updated_at`) VALUES
(1, 'VN', '29', '1', '16900000', '6', '6', '2024-01-15', '2024-01-19', 'Offline', 'Kelas', 'ADOC', 'SB', '-', '-', '0', '0', '0', '2024-05-14 09:06:14', '2024-05-14 09:15:56'),
(2, 'VN', '51', '48', '8000000', '2', '0', '2024-01-22', '2024-01-26', 'Offline', 'Kelas', 'ADOC', 'SH', '-', '-', '0', '0', '0', '2024-05-14 09:23:56', '2024-05-14 09:55:19'),
(3, 'VN', '38', '479', '2500000', '1', '0', '2024-01-22', '2024-01-24', 'Virtual', 'Kelas', NULL, 'AD', '-', '-', '0', '0', '0', '2024-05-14 09:29:57', '2024-05-14 09:39:22'),
(4, 'ZN', '69', '480', '8000000', '2', '2', '2024-01-15', '2024-01-18', 'Offline', 'Kelas', '1', 'RS', '-', '-', '0', '0', '0', '2024-05-15 01:28:36', '2024-05-15 01:34:10'),
(5, 'ZN', '17', '481', '8000000', '2', '2', '2024-01-22', '2024-01-26', 'Offline', 'Kelas', '3', 'RS', '-', '-', '0', '0', '0', '2024-05-15 01:40:08', '2024-05-15 01:40:55'),
(6, 'ZN', '58', '482', '7500000', '1', '1', '2024-01-23', '2024-01-26', 'Offline', 'Kelas', '1', 'LU', '-', '-', '0', '0', '0', '2024-05-15 01:42:46', '2024-05-15 01:43:18'),
(7, 'ZN', '123', '480', '8000000', '3', '3', '2024-01-24', '2024-01-26', 'Offline', 'Kelas', '4', 'AD', '-', '-', '0', '0', '0', '2024-05-15 01:44:42', '2024-05-16 02:19:16'),
(8, 'VN', '123', '483', '8500000', '1', '1', '2024-01-24', '2024-01-26', 'Offline', 'Kelas', '4', 'AD', '-', '-', '0', '0', '0', '2024-05-15 01:47:13', '2024-05-16 02:19:16'),
(9, 'VN', '99', '484', '7500000', '1', '1', '2024-01-29', '2024-02-01', 'Offline', 'Kelas', '1', 'SB', '-', '-', '0', '0', '0', '2024-05-15 01:56:22', '2024-05-15 01:57:03'),
(10, 'RR', '128', '140', '7000000', '2', '2', '2024-02-12', '2024-02-13', 'Offline', 'Kelas', '3', 'WY', '-', '-', '0', '0', '0', '2024-05-15 01:58:28', '2024-05-15 02:02:03'),
(11, 'HW', '204', '485', '7500000', '2', '2', '2024-01-29', '2024-01-31', 'Offline', 'Kelas', 'ADOC', 'SH', '-', '-', '0', '0', '0', '2024-05-15 02:00:44', '2024-05-15 02:01:17'),
(13, 'RR', '110', '140', '7500000', '1', '1', '2024-02-12', '2024-02-13', 'Offline', 'Kelas', '1', 'AD', '-', '-', '0', '0', '0', '2024-05-15 02:09:36', '2024-05-15 02:10:20'),
(14, 'HW', '193', '485', '8300000', '2', '2', '2024-02-19', '2024-02-22', 'Offline', 'Kelas', '1', 'WY', '-', '-', '0', '0', '0', '2024-05-15 02:11:19', '2024-05-15 02:46:46'),
(15, 'HW', '85', '486', '3000000', '4', '4', '2024-01-08', '2024-01-09', 'Offline', 'Kelas', '4', 'SH', '-', '-', '0', '0', '0', '2024-05-15 02:20:50', '2024-05-15 02:21:32'),
(16, 'RR', '130', '488', '6306306', '6', '6', '2024-01-27', '2024-01-28', 'Offline', 'Kelas', NULL, NULL, NULL, NULL, '0', '0', '0', '2024-05-15 02:39:33', '2024-05-15 02:39:33'),
(17, 'HW', '113', '487', '7000000', '1', '1', '2024-01-29', '2024-02-01', 'Offline', 'Kelas', '4', 'AD', '-', '-', '0', '1', '0', '2024-05-15 02:42:01', '2024-05-15 02:42:59'),
(18, 'VN', '210', '489', '9000000', '1', '1', '2024-02-19', '2024-02-23', 'Offline', 'Kelas', '2', 'RS', '-', '-', '0', '0', '0', '2024-05-15 02:55:34', '2024-05-15 02:56:20'),
(19, 'HW', '106', '490', '8500000', '2', '2', '2024-01-30', '2024-02-02', 'Offline', 'Kelas', NULL, NULL, NULL, NULL, '0', '0', '0', '2024-05-15 02:59:36', '2024-05-15 02:59:36'),
(20, 'HW', '116', '491', '7968468', '2', '2', '2024-02-19', '2024-02-21', 'Offline', 'Kelas', '4', 'AD', '-', '-', '0', '0', '0', '2024-05-15 03:03:19', '2024-05-15 03:04:10'),
(21, 'HW', '5', '492', '4200000', '3', '3', '2024-02-15', '2024-02-16', 'Offline', 'Kelas', '3', 'WY', '-', '-', '0', '1', '0', '2024-05-15 03:09:10', '2024-05-15 03:19:43'),
(22, 'VN', '44', '18', '8500000', '4', '4', '2024-02-19', '2024-02-23', 'Offline', 'Kelas', '3', 'SB', '-', '-', '0', '1', '0', '2024-05-15 03:23:01', '2024-05-15 03:23:53'),
(23, 'HW', '200', '491', '6306306', '2', '2', '2024-02-21', '2024-02-23', 'Offline', 'Kelas', 'ADOC', 'PN', '-', '-', '0', '0', '0', '2024-05-15 03:26:39', '2024-05-15 03:27:29'),
(24, 'HW', '50', '493', '7500000', '2', '2', '2024-02-21', '2024-02-23', 'Offline', 'Kelas', '4', 'AD', '-', '-', '0', '0', '0', '2024-05-15 03:33:09', '2024-05-15 03:34:15'),
(25, 'RR', '136', '479', '2200000', '1', '1', '2024-02-26', '2024-02-26', 'Offline', 'Kelas', '1', 'WY', '-', '-', '0', '0', '0', '2024-05-15 03:35:28', '2024-05-15 03:36:51'),
(26, 'HW', '17', '491', '7788288', '1', '1', '2024-02-26', '2024-02-29', 'Offline', 'Kelas', '2', 'SH', '-', '-', '0', '0', '0', '2024-05-15 03:39:22', '2024-05-15 04:50:46'),
(27, 'VN', '29', '1', '15100000', '5', '5', '2024-02-26', '2024-02-29', 'Inhouse Luar Bandung', 'Kelas', NULL, 'SB', '-', '-', '0', '0', '0', '2024-05-15 04:52:35', '2024-05-15 04:53:25'),
(28, 'HW', '177', '490', '8400000', '1', '1', '2024-02-27', '2024-03-01', 'Offline', 'Kelas', '3', 'RS', '-', '-', '0', '0', '0', '2024-05-15 06:20:00', '2024-05-15 06:26:12'),
(29, 'VN', '37', '23', '7000000', '1', '1', '2024-02-28', '2024-02-29', 'Virtual', 'Kelas', NULL, NULL, NULL, NULL, '0', '0', '0', '2024-05-15 06:27:35', '2024-05-15 06:27:35'),
(30, 'HW', '196', '491', '5986486', '2', '2', '2024-03-04', '2024-03-06', 'Offline', 'Kelas', NULL, 'PN', '-', '-', '0', '0', '0', '2024-05-15 07:27:37', '2024-05-15 07:28:00'),
(31, 'HW', '179', '491', '6899679', '2', '2', '2024-03-04', '2024-03-07', 'Offline', 'Kelas', '4', 'AD', '-', '-', '0', '1', '0', '2024-05-15 07:31:51', '2024-05-15 07:37:05'),
(32, 'HW', '179', '493', '6833333', '3', '3', '2024-03-04', '2024-03-07', 'Offline', 'Kelas', '4', 'AD', '-', '-', '0', '1', '0', '2024-05-15 07:35:31', '2024-05-15 07:37:05'),
(33, 'HW', '145', '494', '4233405', '2', '2', '2024-03-04', '2024-03-06', 'Offline', 'Kelas', '2', 'WY', '-', '-', '0', '0', '0', '2024-05-15 07:51:34', '2024-05-15 07:52:27'),
(34, 'HW', '54', '491', '7558558', '2', '2', '2024-03-04', '2024-03-07', 'Offline', 'Kelas', '3', 'LU', '-', '-', '0', '0', '0', '2024-05-15 07:55:06', '2024-05-15 07:56:37'),
(35, 'HW', '74', '493', '4009009', '3', '3', '2024-03-04', '2024-03-06', 'Offline', 'Kelas', '4', 'AD', '-', '-', '0', '0', '0', '2024-05-15 07:58:27', '2024-05-15 07:59:00'),
(36, 'HW', '42', '491', '6826269', '2', '2', '2024-03-04', '2024-03-08', 'Offline', 'Kelas', NULL, 'WY', '-', '-', '0', '1', '0', '2024-05-15 08:04:42', '2024-05-15 08:05:34'),
(37, 'ZN', '46', '495', '13500000', '2', '2', '2024-03-04', '2024-03-08', 'Offline', 'Kelas', '1', 'SB', '-', '-', '0', '1', '0', '2024-05-15 08:10:22', '2024-05-15 08:11:39'),
(38, 'VN', '74', '16', '5686162', '2', '2', '2024-03-06', '2024-03-08', 'Offline', 'Kelas', '4', 'AD', '-', '-', '0', '1', '0', '2024-05-15 08:15:56', '2024-05-15 09:40:15'),
(40, 'VN', '199', '496', '7000000', '6', '6', '2024-03-04', '2024-03-08', 'Offline', 'Kelas', NULL, 'RS', '-', '-', '0', '0', '0', '2024-05-15 09:45:03', '2024-05-15 09:46:12'),
(41, 'ZN', '196', '31', '4504505', '5', '5', '2024-03-13', '2024-03-15', 'Offline', 'Kelas', NULL, 'PN', '-', '-', '0', '0', '0', '2024-05-15 09:50:53', '2024-05-15 09:51:54'),
(42, 'ZN', '90', '31', '4500000', '6', '6', '2024-03-18', '2024-03-21', 'Offline', 'Kelas', 'ADOC', 'RS', '-', '-', '0', '0', '0', '2024-05-16 01:28:05', '2024-05-16 01:28:54'),
(43, 'VN', '144', '29', '7000000', '2', '2', '2024-03-18', '2024-03-20', 'Virtual', 'Kelas', 'Pilih Ruang', 'LU', '-', '-', '0', '0', '0', '2024-05-16 01:30:27', '2024-05-16 01:33:29'),
(44, 'ZN', '113', '343', '7000000', '1', '1', '2024-03-18', '2024-03-20', 'Offline', 'Kelas', '1', 'AD', '-', '-', '0', '1', '0', '2024-05-16 01:34:40', '2024-05-16 01:43:05'),
(45, 'ZN', '200', '31', '4500000', '6', '6', '2024-03-20', '2024-03-22', 'Offline', 'Kelas', '4', 'PN', '-', '-', '0', '0', '0', '2024-05-16 01:36:53', '2024-05-16 01:37:52'),
(46, 'ZN', '29', '357', '18100000', '6', '6', '2024-03-25', '2024-03-28', 'Offline', 'Kelas', 'ADOC', 'SB', '-', '-', '0', '1', '0', '2024-05-16 01:39:39', '2024-05-16 01:40:11'),
(47, 'VN', '120', '50', '4500000', '4', '4', '2024-04-22', '2024-04-26', 'Offline', 'Kelas', 'ADOC', 'RS', '-', '-', '0', '0', '0', '2024-05-16 01:41:27', '2024-05-16 02:17:46'),
(48, 'ZN', '212', '497', '2500000', '1', '1', '2024-04-01', '2024-04-03', 'Virtual', 'Kelas', NULL, NULL, NULL, NULL, '0', '0', '0', '2024-05-16 01:47:23', '2024-05-16 01:47:23'),
(49, 'VN', '151', '51', '4500000', '2', '2', '2024-04-22', '2024-04-26', 'Offline', 'Kelas', '2', 'LU', '-', '-', '0', '0', '0', '2024-05-16 01:52:26', '2024-05-16 01:52:56'),
(50, 'HW', '2', '491', '6814702', '1', '1', '2024-04-23', '2024-04-26', 'Offline', 'Kelas', '3', 'AD', '-', '-', '0', '1', '0', '2024-05-16 02:21:49', '2024-05-16 02:23:51'),
(51, 'HW', '2', '498', '5612000', '3', '3', '2024-04-23', '2024-04-25', 'Offline', 'Kelas', '3', 'AD', '-', '-', '0', '1', '0', '2024-05-16 02:27:26', '2024-05-16 02:28:11'),
(52, 'VN', '113', '491', '7657657', '1', '1', '2024-04-25', '2024-04-26', 'Offline', 'Kelas', '4', 'AD', '-', '-', '0', '0', '0', '2024-05-16 02:33:53', '2024-05-16 02:36:14'),
(53, 'VN', '145', '8', '3835000', '2', '2', '2024-04-29', '2024-04-30', 'Offline', 'Kelas', '2', 'WY', '-', '-', '0', '0', '0', '2024-05-16 02:37:39', '2024-05-16 02:38:06'),
(54, 'AM', '38', '495', '4500000', '1', '1', '2024-04-29', '2024-05-01', 'Offline', 'Kelas', '1', 'AD', '-', '-', '0', '1', '0', '2024-05-16 02:46:29', '2024-05-16 02:47:16'),
(55, 'VN', '46', '1', '12500000', '9', '9', '2024-04-29', '2024-05-03', 'Inhouse Bandung', 'Kelas', NULL, 'SB', '-', '-', '0', '1', '0', '2024-05-16 02:48:29', '2024-05-16 02:49:01'),
(56, 'AM', '84', '495', '7500000', '1', '1', '2024-05-06', '2024-05-08', 'Offline', 'Kelas', '1', 'SB', '-', '-', '0', '0', '0', '2024-05-16 02:49:55', '2024-05-16 02:50:24'),
(57, 'HW', '213', '492', '4000000', '2', '2', '2024-05-06', '2024-05-08', 'Offline', 'Kelas', '2', 'SH', '-', '-', '0', '0', '0', '2024-05-16 03:03:33', '2024-05-16 03:04:09'),
(58, 'VN', '210', '499', '8500000', '1', '1', '2024-05-13', '2024-05-17', 'Offline', 'Kelas', '1', 'PN', '-', '-', '0', '0', '0', '2024-05-16 03:08:14', '2024-05-16 03:08:42'),
(59, 'HW', '121', '492', '2800000', '2', '2', '2024-05-13', '2024-05-15', 'Offline', 'Kelas', '3', 'SH', '-', '-', '0', '1', '0', '2024-05-16 03:09:54', '2024-05-16 03:10:23'),
(60, 'VN', '46', '1', '12500000', '12', '12', '2024-05-13', '2024-05-17', 'Inhouse Bandung', 'Kelas', NULL, 'SB', '-', '-', '0', '0', '0', '2024-05-16 03:11:17', '2024-05-16 03:11:50'),
(61, 'HW', '2', '498', '5612000', '4', '4', '2024-05-14', '2024-05-16', 'Offline', 'Kelas', '4', 'AD', '-', '-', '0', '1', '0', '2024-05-16 03:12:44', '2024-05-16 03:13:33'),
(62, 'VN', '170', '7', '4000000', '12', '12', '2024-05-15', '2024-05-17', 'Inhouse Bandung', 'Kelas', NULL, 'LU', '-', 'YN', '0', '0', '0', '2024-05-16 03:27:02', '2024-05-16 03:27:40'),
(63, 'VN', '216', '29', '2000000', '36', '36', '2024-04-26', '2024-04-26', 'Virtual', 'Kelas', NULL, 'YN', '-', 'SH', '0', '0', '0', '2024-05-16 03:29:43', '2024-05-16 03:32:50'),
(64, 'VN', '215', '29', '2000000', '12', '12', '2024-04-25', '2024-04-25', 'Virtual', 'Kelas', NULL, 'YN', '-', 'SH', '0', '0', '0', '2024-05-16 03:30:48', '2024-05-16 03:32:31');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(255) NOT NULL,
  `jabatan` varchar(255) NOT NULL,
  `status_akun` enum('0','1') NOT NULL,
  `password` varchar(255) NOT NULL,
  `karyawan_id` varchar(255) DEFAULT NULL,
  `id_instruktur` varchar(255) DEFAULT NULL,
  `id_sales` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `jabatan`, `status_akun`, `password`, `karyawan_id`, `id_instruktur`, `id_sales`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, '', '', '', '', '1', '', '', NULL, NULL, NULL),
(2, 'ray', 'Direktur Utama', '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2', '', '', NULL, NULL, NULL),
(3, 'stannia', 'Direktur', '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3', '', '', NULL, NULL, NULL),
(4, 'adit', 'Education Manager', '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '4', 'AD', '', NULL, NULL, NULL),
(5, 'uway', 'Instruktur', '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '5', 'WY', '', NULL, NULL, NULL),
(6, 'pani', 'Instruktur', '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '6', 'PN', '', NULL, NULL, NULL),
(7, 'sabdhan', 'Instruktur', '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '7', 'SB', '', NULL, NULL, NULL),
(8, 'luki', 'Instruktur', '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '8', 'LU', '', NULL, NULL, NULL),
(9, 'rustan', 'Instruktur', '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9', 'RS', '', NULL, NULL, NULL),
(10, 'syahrul', 'Instruktur', '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '10', 'SH', '', NULL, NULL, NULL),
(11, 'naufal', 'Technical Support', '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '11', 'NF', '', NULL, NULL, NULL),
(12, 'virel', 'Technical Support', '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '12', 'VR', '', NULL, NULL, NULL),
(13, 'hani', 'GM', '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '13', '', '', NULL, NULL, NULL),
(14, 'aryani', 'SPV Sales', '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '14', '', 'AM', NULL, NULL, NULL),
(15, 'donna', 'Adm Sales', '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '15', '', '', NULL, NULL, NULL),
(16, 'hera', 'Sales', '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '16', '', 'HW', NULL, NULL, NULL),
(17, 'jeje', 'Sales', '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '17', '', 'ZN', NULL, NULL, NULL),
(18, 'savana', 'Sales', '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '18', '', 'VN', NULL, NULL, NULL),
(19, 'rara', 'Sales', '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '19', '', 'RR', NULL, NULL, NULL),
(20, 'ayas', 'Tim Digital', '0', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '20', '', '', NULL, NULL, '2024-05-14 03:56:37'),
(21, 'dzarin', 'Accounting', '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '21', '', '', NULL, NULL, NULL),
(22, 'xepi', 'Finance & Accounting', '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '22', '', '', NULL, NULL, NULL),
(23, 'aulira', 'HRD', '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '23', '', '', NULL, NULL, NULL),
(24, 'ratu', 'Admin Holding', '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '24', '', '', NULL, NULL, '2024-05-13 09:23:39'),
(25, 'rissa', 'Customer Care', '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '25', '', '', NULL, NULL, NULL),
(26, 'reni', 'Sales', '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '26', '', 'RN', NULL, NULL, NULL),
(27, 'yanuar', 'Instruktur', '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '27', 'YN', '', NULL, NULL, NULL),
(28, 'juliet', 'Tim Digital', '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '28', '', '', NULL, NULL, NULL),
(29, 'ardhan', 'Programmer', '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '29', '', '', NULL, NULL, NULL),
(30, 'nabila', 'Sales', '1', '$2y$10$vCnWpm7D13BZMLITLCjp1ujDkv1fmZ197Kr.n1Gz8FdALxGpTMRya', '30', NULL, 'NA', NULL, '2024-05-13 08:13:36', '2024-05-13 08:13:36'),
(31, 'cecep', 'Office Boy', '1', '$2y$10$fwjY0CihBMai8yv0mxL6tem7ht84EFrErIfGp7nT7in0amG0hjJGS', '31', NULL, NULL, NULL, '2024-05-13 08:16:42', '2024-05-13 08:16:42'),
(32, 'asep', 'Driver', '1', '$2y$10$TgvsZBUUFQRN3g91kmqGO.1ZNe0acK6.lCax1UXKYlf8Nn8BoSqey', '32', NULL, NULL, NULL, '2024-05-13 08:17:12', '2024-05-13 08:17:12'),
(33, 'triyono', 'Driver', '1', '$2y$10$EZ4O7Uwz0XCFHykMFS9wveDovXZ.XMhJYnG2RpQYd6y/ERi4WjDJK', '33', NULL, NULL, NULL, '2024-05-13 08:17:48', '2024-05-13 08:17:48'),
(34, 'ramdhani', 'Office Boy', '1', '$2y$10$9aRBFUsVtcTlpdDK3nL84.kkURFlSkf9rGeGifmCmB8e254pGcqSC', '34', NULL, NULL, NULL, '2024-05-13 08:18:23', '2024-05-13 08:18:23'),
(35, 'spvsales', 'SPV Sales', '1', '$2y$10$B3IfP4MAQUT.lLPtBHXpL.VMaFlgG9RBJh1vJJApEH0eIz7yD/1Ge', '35', NULL, NULL, NULL, '2024-05-14 03:54:24', '2024-05-14 03:55:29');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indeks untuk tabel `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `jabatans`
--
ALTER TABLE `jabatans`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `karyawans`
--
ALTER TABLE `karyawans`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `materis`
--
ALTER TABLE `materis`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `nilaifeedbacks`
--
ALTER TABLE `nilaifeedbacks`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `notifs`
--
ALTER TABLE `notifs`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indeks untuk tabel `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indeks untuk tabel `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indeks untuk tabel `perusahaans`
--
ALTER TABLE `perusahaans`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `pesertas`
--
ALTER TABLE `pesertas`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `registrasis`
--
ALTER TABLE `registrasis`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `r_k_m_s`
--
ALTER TABLE `r_k_m_s`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `comments`
--
ALTER TABLE `comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT untuk tabel `jabatans`
--
ALTER TABLE `jabatans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT untuk tabel `karyawans`
--
ALTER TABLE `karyawans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT untuk tabel `materis`
--
ALTER TABLE `materis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=217;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT untuk tabel `nilaifeedbacks`
--
ALTER TABLE `nilaifeedbacks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `notifs`
--
ALTER TABLE `notifs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `perusahaans`
--
ALTER TABLE `perusahaans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=500;

--
-- AUTO_INCREMENT untuk tabel `pesertas`
--
ALTER TABLE `pesertas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `registrasis`
--
ALTER TABLE `registrasis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `r_k_m_s`
--
ALTER TABLE `r_k_m_s`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
